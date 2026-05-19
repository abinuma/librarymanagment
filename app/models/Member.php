<?php
/**
 * Member Model
 * 
 * Handles all database operations related to library members/students.
 */

class Member
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get paginated members with optional search
     */
    public function getPaginated(int $page = 1, int $perPage = RECORDS_PER_PAGE, string $search = '', string $status = 'all'): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = [];

        if (!empty($search)) {
            $conditions[] = '(full_name LIKE :search OR email LIKE :search2 OR student_id LIKE :search3 OR department LIKE :search4)';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }

        if ($status === 'active') {
            $conditions[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'is_active = 0';
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM members {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Records
        $sql = "SELECT * FROM members {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll();

        return [
            'data'        => $members,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Find member by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $member = $stmt->fetch();
        return $member ?: null;
    }

    /**
     * Find member by student ID
     */
    public function findByStudentId(string $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE student_id = :student_id');
        $stmt->execute(['student_id' => $studentId]);
        $member = $stmt->fetch();
        return $member ?: null;
    }

    /**
     * Find member by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $member = $stmt->fetch();
        return $member ?: null;
    }

    /**
     * Create new member
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO members (full_name, email, phone, student_id, department, address)
             VALUES (:full_name, :email, :phone, :student_id, :department, :address)'
        );
        $stmt->execute([
            'full_name'  => $data['full_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'student_id' => $data['student_id'],
            'department' => $data['department'] ?? null,
            'address'    => $data['address'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update member
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE members SET 
                full_name = :full_name, 
                email = :email, 
                phone = :phone, 
                student_id = :student_id, 
                department = :department,
                address = :address,
                is_active = :is_active
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'         => $id,
            'full_name'  => $data['full_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'student_id' => $data['student_id'],
            'department' => $data['department'] ?? null,
            'address'    => $data['address'] ?? null,
            'is_active'  => $data['is_active'] ?? 1,
        ]);
    }

    /**
     * Delete member
     */
    public function delete(int $id): bool
    {
        // Check for active borrow transactions (borrowed or overdue)
        if ($this->getActiveBorrowCount($id) > 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM members WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get total member count
     */
    public function getTotalCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM members')->fetchColumn();
    }

    /**
     * Get all active members (for dropdowns)
     */
    public function getActive(): array
    {
        return $this->db->query('SELECT id, full_name, student_id, email FROM members WHERE is_active = 1 ORDER BY full_name ASC')->fetchAll();
    }

    /**
     * Get active borrow count for a member
     */
    public function getActiveBorrowCount(int $memberId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM borrow_transactions WHERE member_id = :id AND status IN ("borrowed", "overdue")'
        );
        $stmt->execute(['id' => $memberId]);
        return (int) $stmt->fetchColumn();
    }
}
