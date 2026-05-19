<?php
/**
 * Book Model
 * 
 * Handles all database operations related to books.
 */

class Book
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all distinct shelf numbers
     */
    public function getDistinctShelves(): array
    {
        return $this->db->query(
            'SELECT DISTINCT shelf_number FROM books WHERE shelf_number IS NOT NULL AND shelf_number != "" ORDER BY shelf_number ASC'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get paginated books with optional search, category, availability, and shelf filter
     */
    public function getPaginated(
        int $page = 1,
        int $perPage = RECORDS_PER_PAGE,
        string $search = '',
        int $categoryId = 0,
        string $availability = 'all',
        string $shelf = ''
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        if (!empty($search)) {
            $where[] = '(b.title LIKE :search OR b.author LIKE :search2 OR b.isbn LIKE :search3)';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        if ($categoryId > 0) {
            $where[] = 'b.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($availability === 'available') {
            $where[] = 'b.available_copies > 0';
        } elseif ($availability === 'unavailable') {
            $where[] = 'b.available_copies = 0';
        }

        if ($shelf !== '') {
            $where[] = 'b.shelf_number = :shelf';
            $params['shelf'] = $shelf;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total
        $countSql = "SELECT COUNT(*) FROM books b {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get records
        $sql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                {$whereClause} 
                ORDER BY b.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll();

        return [
            'data'        => $books,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }


    /**
     * Find book by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name as category_name 
             FROM books b 
             LEFT JOIN categories c ON b.category_id = c.id 
             WHERE b.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Find book by ISBN
     */
    public function findByIsbn(string $isbn): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM books WHERE isbn = :isbn');
        $stmt->execute(['isbn' => $isbn]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Create a new book
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO books (title, author, isbn, category_id, quantity, available_copies, shelf_number, published_year, description)
             VALUES (:title, :author, :isbn, :category_id, :quantity, :available_copies, :shelf_number, :published_year, :description)'
        );
        $stmt->execute([
            'title'           => $data['title'],
            'author'          => $data['author'],
            'isbn'            => $data['isbn'],
            'category_id'     => $data['category_id'],
            'quantity'        => $data['quantity'],
            'available_copies'=> $data['quantity'], // Initially all copies are available
            'shelf_number'    => $data['shelf_number'] ?? null,
            'published_year'  => $data['published_year'] ?? null,
            'description'     => $data['description'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing book
     */
    public function update(int $id, array $data): bool
    {
        // Calculate the difference in quantity to adjust available copies
        $current = $this->findById($id);
        if (!$current) return false;

        $quantityDiff = $data['quantity'] - $current['quantity'];
        $newAvailable = max(0, $current['available_copies'] + $quantityDiff);

        $stmt = $this->db->prepare(
            'UPDATE books SET 
                title = :title, 
                author = :author, 
                isbn = :isbn, 
                category_id = :category_id, 
                quantity = :quantity, 
                available_copies = :available_copies,
                shelf_number = :shelf_number, 
                published_year = :published_year, 
                description = :description
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'              => $id,
            'title'           => $data['title'],
            'author'          => $data['author'],
            'isbn'            => $data['isbn'],
            'category_id'     => $data['category_id'],
            'quantity'        => $data['quantity'],
            'available_copies'=> $newAvailable,
            'shelf_number'    => $data['shelf_number'] ?? null,
            'published_year'  => $data['published_year'] ?? null,
            'description'     => $data['description'] ?? null,
        ]);
    }

    /**
     * Delete a book
     */
    public function delete(int $id): bool
    {
        // Check if book has active borrow transactions (borrowed or overdue)
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM borrow_transaction_items bti 
             JOIN borrow_transactions bt ON bti.transaction_id = bt.id 
             WHERE bti.book_id = :id AND bt.status IN ("borrowed", "overdue")'
        );
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false; // Cannot delete book with active borrows
        }

        $stmt = $this->db->prepare('DELETE FROM books WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Decrease available copies by 1
     */
    public function decreaseAvailable(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE books SET available_copies = available_copies - 1 WHERE id = :id AND available_copies > 0'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Increase available copies by 1
     */
    public function increaseAvailable(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE books SET available_copies = available_copies + 1 WHERE id = :id AND available_copies < quantity'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get total number of books
     */
    public function getTotalCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM books')->fetchColumn();
    }

    /**
     * Get total available books count
     */
    public function getTotalAvailable(): int
    {
        return (int) $this->db->query('SELECT SUM(available_copies) FROM books')->fetchColumn();
    }

    /**
     * Get all books (for dropdowns)
     */
    public function getAll(): array
    {
        return $this->db->query('SELECT id, title, author, isbn, available_copies FROM books ORDER BY title ASC')->fetchAll();
    }

    /**
     * Get available books (for borrow dropdown)
     */
    public function getAvailable(): array
    {
        return $this->db->query('SELECT id, title, author, isbn, available_copies FROM books WHERE available_copies > 0 ORDER BY title ASC')->fetchAll();
    }
}
