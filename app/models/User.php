<?php
//  Handles all database operations related to users (admin, librarian).
 

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Get all users
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * Create a new user
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, full_name, password, role, is_active) 
             VALUES (:username, :email, :full_name, :password, :role, :is_active)'
        );
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'full_name' => $data['full_name'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing user
     */
    public function update(int $id, array $data): bool
    {
        $updates = [];
        $params = ['id' => $id];

        foreach (['email', 'full_name', 'role', 'is_active', 'password'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
