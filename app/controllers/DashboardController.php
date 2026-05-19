<?php
/**
 * DashboardController - Dashboard with statistics
 */

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        try {
            $bookModel = new Book();
            $memberModel = new Member();
            $transactionModel = new BorrowTransaction();

            // Update overdue statuses
            $transactionModel->updateOverdueStatuses();

            $stats = [
                'total_books'     => $bookModel->getTotalCount(),
                'available_books' => $bookModel->getTotalAvailable(),
                'borrowed_books'  => $transactionModel->getBorrowedCount(),
                'total_members'   => $memberModel->getTotalCount(),
                'overdue_books'   => $transactionModel->getOverdueCount(),
            ];

            $recentTransactions = $transactionModel->getRecent(8);

            require VIEW_PATH . '/dashboard/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading dashboard metrics");
            errorResponse('Unable to load dashboard statistics at this time.', '/dashboard');
        }
    }
}
