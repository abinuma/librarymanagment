<?php
/**
 * ReportController - System Reports & Analytics
 */

class ReportController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        try {
            // Overdue Books
            $overdueStmt = $this->db->query(
                "SELECT bt.*, b.title as book_title, m.full_name as member_name 
                 FROM borrow_transactions bt
                 JOIN borrow_transaction_items bti ON bt.id = bti.transaction_id
                 JOIN books b ON bti.book_id = b.id
                 JOIN members m ON bt.member_id = m.id
                 WHERE bt.status = 'overdue'
                 ORDER BY bt.due_date ASC LIMIT 50"
            );
            $overdueBooks = $overdueStmt->fetchAll();

            // Most Borrowed Books
            $popularStmt = $this->db->query(
                "SELECT b.title, b.author, COUNT(bti.id) as borrow_count 
                 FROM books b
                 LEFT JOIN borrow_transaction_items bti ON b.id = bti.book_id
                 GROUP BY b.id
                 ORDER BY borrow_count DESC LIMIT 10"
            );
            $mostBorrowed = $popularStmt->fetchAll();

            // Active Borrowings (currently checked out including overdue)
            $transactionModel = new BorrowTransaction();
            $activeBorrowingsCount = $transactionModel->getBorrowedCount();

            // Inventory Statistics
            $inventoryStmt = $this->db->query(
                "SELECT SUM(quantity) as total_books, SUM(available_copies) as total_available FROM books"
            );
            $inventoryStats = $inventoryStmt->fetch();

            // Fine Statistics
            $fineModel = new Fine();
            $fineStats = $fineModel->getRevenueStats();

            require VIEW_PATH . '/reports/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading system reports");
            errorResponse('Unable to load analytical reports at this time. Our technical team has been notified.', '/dashboard');
        }
    }

    public function fines(): void
    {
        AuthMiddleware::requireAdmin();

        try {
            $page = max(1, (int)queryParam('page', '1'));
            $search = queryParam('search');

            $fineModel = new Fine();
            $fineStats = $fineModel->getRevenueStats();
            $memberSummary = $fineModel->getMemberFineSummary(15);
            $result = $fineModel->getPaginatedDetails($page, RECORDS_PER_PAGE, $search);

            $fines = $result['data'];
            $pagination = $result;

            require VIEW_PATH . '/reports/fines.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading fine revenue report");
            errorResponse('Unable to load fine details at this time.', '/reports');
        }
    }
}
