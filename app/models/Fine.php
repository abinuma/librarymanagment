<?php
/**
 * Fine Model
 */

class Fine
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $isPaid = isset($data['is_paid']) ? (int)$data['is_paid'] : 1;
        $paidDate = $isPaid ? date('Y-m-d') : null;

        $stmt = $this->db->prepare(
            'INSERT INTO fines (transaction_id, member_id, amount, reason, is_paid, paid_date) VALUES (:tid, :mid, :amount, :reason, :is_paid, :paid_date)'
        );
        $stmt->execute([
            'tid' => $data['transaction_id'], 'mid' => $data['member_id'],
            'amount' => $data['amount'], 'reason' => $data['reason'] ?? 'Overdue book',
            'is_paid' => $isPaid, 'paid_date' => $paidDate,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findByTransaction(int $transactionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fines WHERE transaction_id = :tid');
        $stmt->execute(['tid' => $transactionId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fines WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function markPaid(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE fines SET is_paid = 1, paid_date = CURDATE() WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function getTotalUnpaid(): float
    {
        return (float) $this->db->query('SELECT COALESCE(SUM(amount), 0) FROM fines WHERE is_paid = 0')->fetchColumn();
    }

    /**
     * Get aggregate revenue statistics
     */
    public function getRevenueStats(): array
    {
        $stmt = $this->db->query(
            "SELECT 
                COALESCE(SUM(CASE WHEN is_paid = 1 THEN amount END), 0) as total_paid,
                COALESCE(SUM(CASE WHEN is_paid = 0 THEN amount END), 0) as total_unpaid,
                COUNT(id) as total_transactions,
                SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as total_paid_transactions
             FROM fines"
        );
        return $stmt->fetch();
    }

    /**
     * Get most fined members and payment counts
     */
    public function getMemberFineSummary(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.full_name, m.student_id, COUNT(f.id) as total_fines, SUM(f.amount) as total_amount,
                    SUM(CASE WHEN f.is_paid = 1 THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN f.is_paid = 1 THEN f.amount ELSE 0 END) as paid_amount
             FROM fines f
             JOIN members m ON f.member_id = m.id
             GROUP BY m.id
             ORDER BY paid_count DESC, total_amount DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get paginated fine revenue details with search
     */
    public function getPaginatedDetails(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        if (!empty($search)) {
            $where[] = '(m.full_name LIKE :search OR m.student_id LIKE :search2 OR f.reason LIKE :search3)';
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total
        $countSql = "SELECT COUNT(*) FROM fines f JOIN members m ON f.member_id = m.id {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get records
        $sql = "SELECT f.*, m.full_name as member_name, m.student_id, u.full_name as processed_by, bt.borrow_date
                FROM fines f
                JOIN members m ON f.member_id = m.id
                JOIN borrow_transactions bt ON f.transaction_id = bt.id
                JOIN users u ON bt.user_id = u.id
                {$whereClause}
                ORDER BY f.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fines = $stmt->fetchAll();

        return [
            'data'        => $fines,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
