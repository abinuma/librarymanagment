-- ============================================
-- Library Management System - Database Schema
-- MySQL 8.0+
-- ============================================

CREATE DATABASE IF NOT EXISTS library_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE library_management;

-- ============================================
-- Categories Table
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_name (name)
) ENGINE=InnoDB;

-- ============================================
-- Users Table (Admin, Librarian)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'librarian') NOT NULL DEFAULT 'librarian',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role),
    INDEX idx_user_email (email)
) ENGINE=InnoDB;

-- ============================================
-- Books Table
-- ============================================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    shelf_number VARCHAR(20) NULL,
    published_year YEAR NULL,
    description TEXT NULL,
    cover_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_book_title (title),
    INDEX idx_book_author (author),
    INDEX idx_book_isbn (isbn),
    INDEX idx_book_category (category_id),
    INDEX idx_book_available (available_copies)
) ENGINE=InnoDB;

-- ============================================
-- Members Table (Students / Library Members)
-- ============================================
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NULL,
    address TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    membership_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member_name (full_name),
    INDEX idx_member_student_id (student_id),
    INDEX idx_member_email (email),
    INDEX idx_member_department (department)
) ENGINE=InnoDB;

-- ============================================
-- Borrow Transactions Table
-- ============================================
CREATE TABLE IF NOT EXISTS borrow_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    user_id INT NOT NULL,               -- Staff who processed the transaction
    borrow_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_borrow_member (member_id),
    INDEX idx_borrow_status (status),
    INDEX idx_borrow_due_date (due_date),
    INDEX idx_borrow_date (borrow_date)
) ENGINE=InnoDB;

-- ============================================
-- Borrow Transaction Items Table
-- ============================================
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

-- ============================================
-- Fines Table
-- ============================================
CREATE TABLE IF NOT EXISTS fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    member_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    reason VARCHAR(255) NOT NULL DEFAULT 'Overdue book',
    is_paid TINYINT(1) NOT NULL DEFAULT 0,
    paid_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_fine_member (member_id),
    INDEX idx_fine_paid (is_paid),
    INDEX idx_fine_transaction (transaction_id)
) ENGINE=InnoDB;

-- ============================================
-- Seed Data: Categories
-- ============================================
INSERT INTO categories (name, description) VALUES
('Fiction', 'Novels, short stories, and literary fiction'),
('Non-Fiction', 'Biographies, essays, and factual works'),
('Science', 'Physics, chemistry, biology, and earth sciences'),
('Technology', 'Computer science, engineering, and IT'),
('Mathematics', 'Algebra, calculus, statistics, and geometry'),
('History', 'World history, civilizations, and historical events'),
('Philosophy', 'Ethics, logic, metaphysics, and aesthetics'),
('Literature', 'Poetry, drama, and classic literary works'),
('Business', 'Management, economics, and entrepreneurship'),
('Medicine', 'Health sciences, anatomy, and medical research');

-- ============================================
-- Seed Data: Admin User
-- Password: Admin@123
-- ============================================
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin'),
('librarian', 'librarian@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Librarian', 'librarian');

-- ============================================
-- Seed Data: Sample Books
-- ============================================
INSERT INTO books (title, author, isbn, category_id, quantity, available_copies, shelf_number, published_year) VALUES
('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 1, 5, 5, 'A-01', 1960),
('1984', 'George Orwell', '978-0451524935', 1, 3, 3, 'A-02', 1949),
('A Brief History of Time', 'Stephen Hawking', '978-0553380163', 3, 4, 4, 'B-01', 1988),
('Clean Code', 'Robert C. Martin', '978-0132350884', 4, 6, 6, 'C-01', 2008),
('Design Patterns', 'Gang of Four', '978-0201633610', 4, 3, 3, 'C-02', 1994),
('The Art of War', 'Sun Tzu', '978-1599869773', 6, 2, 2, 'D-01', 2009),
('Sapiens', 'Yuval Noah Harari', '978-0062316097', 6, 4, 4, 'D-02', 2015),
('Thinking, Fast and Slow', 'Daniel Kahneman', '978-0374533557', 7, 3, 3, 'E-01', 2011),
('The Lean Startup', 'Eric Ries', '978-0307887894', 9, 5, 5, 'F-01', 2011),
('Principles of Anatomy', 'Gerard Tortora', '978-0470565100', 10, 4, 4, 'G-01', 2011),
('Introduction to Algorithms', 'Thomas Cormen', '978-0262033848', 5, 3, 3, 'C-03', 2009),
('Pride and Prejudice', 'Jane Austen', '978-0141439518', 8, 4, 4, 'A-03', 1813),
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 1, 3, 3, 'A-04', 1925),
('Brave New World', 'Aldous Huxley', '978-0060850524', 1, 2, 2, 'A-05', 1932),
('The Republic', 'Plato', '978-0140455113', 7, 3, 3, 'E-02', 2003);

-- ============================================
-- Seed Data: Sample Members
-- ============================================
INSERT INTO members (full_name, email, phone, student_id, department) VALUES
('John Smith', 'john.smith@university.edu', '+1234567890', 'STU-2024-001', 'Computer Science'),
('Emily Johnson', 'emily.j@university.edu', '+1234567891', 'STU-2024-002', 'Mathematics'),
('Michael Brown', 'michael.b@university.edu', '+1234567892', 'STU-2024-003', 'Physics'),
('Sarah Davis', 'sarah.d@university.edu', '+1234567893', 'STU-2024-004', 'Literature'),
('David Wilson', 'david.w@university.edu', '+1234567894', 'STU-2024-005', 'Business Administration');

-- ============================================
-- Seed Data: Sample Borrow Transactions
-- ============================================
INSERT INTO borrow_transactions (id, member_id, user_id, borrow_date, due_date, status) VALUES
(1, 1, 1, '2026-05-01', '2026-05-15', 'borrowed'),
(2, 2, 1, '2026-05-05', '2026-05-19', 'borrowed'),
(3, 3, 2, '2026-04-20', '2026-05-04', 'overdue'),
(4, 4, 2, '2026-05-10', '2026-05-24', 'borrowed');

INSERT INTO borrow_transaction_items (transaction_id, book_id, quantity) VALUES
(1, 4, 1),
(2, 3, 1),
(3, 1, 1),
(4, 12, 1);

-- Update available copies for borrowed books
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (4, 3, 1, 12);
