-- Migration: Split borrow_transactions into borrow_transactions and borrow_transaction_items safely and idempotently
USE library_management;

-- 1. Create borrow_transaction_items table
CREATE TABLE IF NOT EXISTS borrow_transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_bti_transaction (transaction_id),
    INDEX idx_bti_book (book_id)
) ENGINE=InnoDB;

-- Check if book_id column still exists in borrow_transactions
SET @has_book_id = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'library_management' AND TABLE_NAME = 'borrow_transactions' AND COLUMN_NAME = 'book_id');

-- 2. Migrate existing records only if book_id exists
SET @s_insert = IF(@has_book_id > 0, 'INSERT IGNORE INTO borrow_transaction_items (transaction_id, book_id, quantity) SELECT id, book_id, 1 FROM borrow_transactions', 'SELECT 1');
PREPARE stmt_insert FROM @s_insert;
EXECUTE stmt_insert;
DEALLOCATE PREPARE stmt_insert;

-- 3. Drop foreign key constraint if it exists
SET @constraint_name = (SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = 'library_management' 
                        AND TABLE_NAME = 'borrow_transactions' 
                        AND COLUMN_NAME = 'book_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1);

SET @s_fk = IF(@constraint_name IS NOT NULL, CONCAT('ALTER TABLE borrow_transactions DROP FOREIGN KEY ', @constraint_name), 'SELECT 1');
PREPARE stmt_fk FROM @s_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

-- 4. Drop index if it exists
SET @has_idx = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'library_management' AND TABLE_NAME = 'borrow_transactions' AND INDEX_NAME = 'idx_borrow_book');
SET @s_idx = IF(@has_idx > 0, 'ALTER TABLE borrow_transactions DROP INDEX idx_borrow_book', 'SELECT 1');
PREPARE stmt_idx FROM @s_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- 5. Drop column if it exists
SET @s_col = IF(@has_book_id > 0, 'ALTER TABLE borrow_transactions DROP COLUMN book_id', 'SELECT 1');
PREPARE stmt_col FROM @s_col;
EXECUTE stmt_col;
DEALLOCATE PREPARE stmt_col;
