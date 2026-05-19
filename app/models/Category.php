<?php
/**
 * Category Model
 * 
 * Handles all database operations related to book categories.
 */

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all categories
     */
    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    }

    /**
     * Find category by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * Get categories with book counts
     */
    public function getAllWithBookCount(): array
    {
        return $this->db->query(
            'SELECT c.*, COUNT(b.id) as book_count 
             FROM categories c 
             LEFT JOIN books b ON c.id = b.category_id 
             GROUP BY c.id 
             ORDER BY c.name ASC'
        )->fetchAll();
    }

    /**
     * Create a new category
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
        $stmt->execute(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing category
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
        return $stmt->execute(['id' => $id, 'name' => $data['name'], 'description' => $data['description'] ?? null]);
    }

    /**
     * Delete a category
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // Will fail if there are books in this category due to RESTRICT foreign key constraint
            return false;
        }
    }
}
