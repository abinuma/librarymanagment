<?php
/**
 * BorrowTransaction Model
 */

class BorrowTransaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPaginated(int $page = 1, int $perPage = RECORDS_PER_PAGE, string $status = '', string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        if (!empty($status)) {
            $where[] = 'bt.status = :status';
            $params['status'] = $status;
        }
        if (!empty($search)) {
            $where[] = '(m.full_name LIKE :search OR m.student_id LIKE :search3 OR bt.id IN (SELECT bti.transaction_id FROM borrow_transaction_items bti JOIN books b ON bti.book_id = b.id WHERE b.title LIKE :search2))';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(DISTINCT m.id) FROM borrow_transactions bt JOIN members m ON bt.member_id = m.id {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Calculate total individual books for the UI badge
        $totalBooksSql = "SELECT COUNT(bti.id) FROM borrow_transactions bt 
                          JOIN members m ON bt.member_id = m.id 
                          LEFT JOIN borrow_transaction_items bti ON bti.transaction_id = bt.id 
                          {$whereClause}";
        $booksCountStmt = $this->db->prepare($totalBooksSql);
        $booksCountStmt->execute($params);
        $totalBooks = (int) $booksCountStmt->fetchColumn();

        $sql = "SELECT m.id as member_id, m.full_name as member_name, m.student_id,
                       MIN(bt.borrow_date) as borrow_date,
                       MIN(bt.due_date) as due_date,
                       MAX(bt.return_date) as return_date,
                       COUNT(bti.id) as book_count,
                       CASE 
                           WHEN SUM(CASE WHEN bt.status = 'overdue' THEN 1 ELSE 0 END) > 0 THEN 'overdue'
                           WHEN SUM(CASE WHEN bt.status = 'borrowed' THEN 1 ELSE 0 END) > 0 THEN 'borrowed'
                           ELSE 'returned'
                       END as status
                FROM borrow_transactions bt
                JOIN members m ON bt.member_id = m.id
                LEFT JOIN borrow_transaction_items bti ON bti.transaction_id = bt.id
                {$whereClause}
                GROUP BY m.id
                ORDER BY MAX(bt.created_at) DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data' => $stmt->fetchAll(), 'total' => $total, 'total_books' => $totalBooks, 'per_page' => $perPage,
            'current_page' => $page, 'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function findMemberTransactions(int $memberId): ?array
    {
        $memberStmt = $this->db->prepare('SELECT id, full_name as member_name, student_id, email as member_email FROM members WHERE id = :mid');
        $memberStmt->execute(['mid' => $memberId]);
        $member = $memberStmt->fetch();
        if (!$member) return null;
        
        $transStmt = $this->db->prepare('
            SELECT bt.*, u.full_name as processed_by
            FROM borrow_transactions bt
            JOIN users u ON bt.user_id = u.id
            WHERE bt.member_id = :mid
            ORDER BY bt.created_at DESC
        ');
        $transStmt->execute(['mid' => $memberId]);
        $transactions = $transStmt->fetchAll();
        
        $allItems = [];
        $overallStatus = 'returned';
        $hasOverdue = false;
        $hasBorrowed = false;
        
        foreach ($transactions as $t) {
            if ($t['status'] === 'overdue') $hasOverdue = true;
            if ($t['status'] === 'borrowed') $hasBorrowed = true;
            
            $itemsStmt = $this->db->prepare('SELECT bti.*, b.title as book_title, b.isbn, b.author FROM borrow_transaction_items bti JOIN books b ON bti.book_id = b.id WHERE bti.transaction_id = :tid');
            $itemsStmt->execute(['tid' => $t['id']]);
            $items = $itemsStmt->fetchAll();
            
            foreach ($items as $item) {
                $item['status'] = $t['status'];
                $item['due_date'] = $t['due_date'];
                $item['borrow_date'] = $t['borrow_date'];
                $item['transaction_id'] = $t['id'];
                $item['processed_by'] = $t['processed_by'];
                $allItems[] = $item;
            }
        }
        
        if ($hasOverdue) $overallStatus = 'overdue';
        elseif ($hasBorrowed) $overallStatus = 'borrowed';
        
        return [
            'member' => $member,
            'transactions' => $transactions,
            'items' => $allItems,
            'status' => $overallStatus
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT bt.*, m.full_name as member_name, m.student_id, m.email as member_email,
                    u.full_name as processed_by
             FROM borrow_transactions bt JOIN members m ON bt.member_id = m.id
             JOIN users u ON bt.user_id = u.id WHERE bt.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $transaction = $stmt->fetch();
        if ($transaction) {
            $itemsStmt = $this->db->prepare('SELECT bti.*, b.title as book_title, b.isbn, b.author FROM borrow_transaction_items bti JOIN books b ON bti.book_id = b.id WHERE bti.transaction_id = :tid');
            $itemsStmt->execute(['tid' => $id]);
            $transaction['items'] = $itemsStmt->fetchAll();
        }
        return $transaction ?: null;
    }

    public function create(array $data, array $bookIds): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO borrow_transactions (member_id, user_id, borrow_date, due_date, notes)
                 VALUES (:member_id, :user_id, :borrow_date, :due_date, :notes)'
            );
            $stmt->execute([
                'member_id' => $data['member_id'], 'user_id' => $data['user_id'],
                'borrow_date' => $data['borrow_date'], 'due_date' => $data['due_date'], 'notes' => $data['notes'] ?? null,
            ]);
            $transactionId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare('INSERT INTO borrow_transaction_items (transaction_id, book_id, quantity) VALUES (:tid, :bid, 1)');
            $updateBook = $this->db->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE id = :bid AND available_copies > 0');
            
            foreach ($bookIds as $bookId) {
                $itemStmt->execute(['tid' => $transactionId, 'bid' => $bookId]);
                $updateBook->execute(['bid' => $bookId]);
                if ($updateBook->rowCount() === 0) {
                    throw new ValidationException("One or more selected books are no longer available for borrowing.");
                }
            }
            
            $this->db->commit();
            return $transactionId;
        } catch (Exception $e) {
            $this->db->rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }
            throw new DatabaseException("Failed to create borrowing transaction records.", 500, $e);
        }
    }

    public function processReturn(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE borrow_transactions SET status = "returned", return_date = CURDATE() WHERE id = :id');
            $success = $stmt->execute(['id' => $id]);
            
            $itemsStmt = $this->db->prepare('SELECT book_id FROM borrow_transaction_items WHERE transaction_id = :tid');
            $itemsStmt->execute(['tid' => $id]);
            $items = $itemsStmt->fetchAll();
            
            $incStmt = $this->db->prepare('UPDATE books SET available_copies = available_copies + 1 WHERE id = :bid AND available_copies < quantity');
            foreach ($items as $item) {
                $incStmt->execute(['bid' => $item['book_id']]);
            }
            
            $this->db->commit();
            return $success;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new DatabaseException("Failed to process return transaction records.", 500, $e);
        }
    }

    public function updateOverdueStatuses(): int
    {
        $stmt = $this->db->prepare('UPDATE borrow_transactions SET status = "overdue" WHERE status = "borrowed" AND due_date < CURDATE()');
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getBorrowedCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM borrow_transactions WHERE status IN ("borrowed", "overdue")')->fetchColumn();
    }

    public function getOverdueCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM borrow_transactions WHERE status = "overdue" OR (status = "borrowed" AND due_date < CURDATE())')->fetchColumn();
    }

    public function hasMemberBorrowedBook(int $memberId, int $bookId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM borrow_transactions bt JOIN borrow_transaction_items bti ON bt.id = bti.transaction_id WHERE bt.member_id = :mid AND bti.book_id = :bid AND bt.status IN ("borrowed","overdue")');
        $stmt->execute(['mid' => $memberId, 'bid' => $bookId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getMemberHistory(int $memberId): array
    {
        $stmt = $this->db->prepare('SELECT bt.*, b.title as book_title, b.isbn, b.author FROM borrow_transactions bt JOIN borrow_transaction_items bti ON bt.id = bti.transaction_id JOIN books b ON bti.book_id = b.id WHERE bt.member_id = :mid ORDER BY bt.created_at DESC');
        $stmt->execute(['mid' => $memberId]);
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5): array
    {
        return $this->db->query("
            SELECT bt.*, m.full_name as member_name, 
                   (SELECT COUNT(*) FROM borrow_transaction_items WHERE transaction_id = bt.id) as book_count,
                   (SELECT GROUP_CONCAT(b.title SEPARATOR ', ') FROM borrow_transaction_items bti JOIN books b ON bti.book_id = b.id WHERE bti.transaction_id = bt.id) as book_title
            FROM borrow_transactions bt 
            JOIN members m ON bt.member_id = m.id 
            ORDER BY bt.created_at DESC LIMIT {$limit}
        ")->fetchAll();
    }

    public function getActiveBorrows(): array
    {
        return $this->db->query('
            SELECT bt.id, bt.borrow_date, bt.due_date, bt.status, m.full_name as member_name, m.student_id, 
                   (SELECT COUNT(*) FROM borrow_transaction_items WHERE transaction_id = bt.id) as book_count,
                   (SELECT GROUP_CONCAT(b.title SEPARATOR ", ") FROM borrow_transaction_items bti JOIN books b ON bti.book_id = b.id WHERE bti.transaction_id = bt.id) as book_title
            FROM borrow_transactions bt JOIN members m ON bt.member_id = m.id 
            WHERE bt.status IN ("borrowed","overdue") ORDER BY bt.due_date ASC
        ')->fetchAll();
    }
}
