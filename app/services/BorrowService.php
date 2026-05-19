<?php
/**
 * BorrowService - Business logic for borrowing/returning books
 */

class BorrowService
{
    private BorrowTransaction $transactionModel;
    private Book $bookModel;
    private Member $memberModel;
    private FineService $fineService;

    public function __construct()
    {
        $this->transactionModel = new BorrowTransaction();
        $this->bookModel = new Book();
        $this->memberModel = new Member();
        $this->fineService = new FineService();
    }

    public function borrowBook(int $memberId, array $bookIds, int $userId, string $borrowDate, string $dueDate): array
    {
        // Validate member exists and is active
        $member = $this->memberModel->findById($memberId);
        if (!$member || !$member['is_active']) {
            return ['success' => false, 'message' => 'Member not found or inactive.'];
        }

        if (empty($bookIds)) {
            return ['success' => false, 'message' => 'Please select at least one book.'];
        }

        $maxLimit = (int) setting('max_borrow_limit', 5);
        $activeBorrows = $this->memberModel->getActiveBorrowCount($memberId);

        if (($activeBorrows + count($bookIds)) > $maxLimit) {
            return ['success' => false, 'message' => 'This action exceeds the maximum borrow limit (' . $maxLimit . ' books). Member currently has ' . $activeBorrows . ' active borrows.'];
        }

        // Validate dates
        if (strtotime($dueDate) < strtotime($borrowDate)) {
            return ['success' => false, 'message' => 'Due date cannot be earlier than borrow date.'];
        }

        // Validate books
        foreach ($bookIds as $bookId) {
            $book = $this->bookModel->findById($bookId);
            if (!$book) {
                return ['success' => false, 'message' => 'One or more selected books not found.'];
            }
            if ($book['available_copies'] <= 0) {
                return ['success' => false, 'message' => 'No available copies for book: ' . $book['title']];
            }
            if ($this->transactionModel->hasMemberBorrowedBook($memberId, $bookId)) {
                return ['success' => false, 'message' => 'Member already has book: ' . $book['title'] . ' borrowed.'];
            }
        }

        // Create transaction
        try {
            $transactionId = $this->transactionModel->create([
                'member_id'   => $memberId,
                'user_id'     => $userId,
                'borrow_date' => $borrowDate,
                'due_date'    => $dueDate,
            ], $bookIds);

            return ['success' => true, 'message' => 'Books borrowed successfully. Due date: ' . formatDate($dueDate), 'transaction_id' => $transactionId];
        } catch (ValidationException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            logAppError($e, "Borrow transaction processing failed for member ID {$memberId}");
            return ['success' => false, 'message' => 'Unable to process borrowing transaction at this time. Please try again later.'];
        }
    }

    public function returnBook(int $transactionId): array
    {
        $transaction = $this->transactionModel->findById($transactionId);
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }
        if ($transaction['status'] === 'returned') {
            return ['success' => false, 'message' => 'This transaction has already been returned.'];
        }

        try {
            // Process return (inventory increment is handled automatically in processReturn transaction)
            $this->transactionModel->processReturn($transactionId);

            // Calculate fine if overdue
            $overdueDays = calculateOverdueDays($transaction['due_date']);
            $fineMessage = '';
            if ($overdueDays > 0) {
                $fineAmount = $this->fineService->calculateFine($overdueDays);
                $this->fineService->createFine($transactionId, $transaction['member_id'], $fineAmount, "Overdue by {$overdueDays} days");
                $fineMessage = " Fine of " . formatCurrency($fineAmount) . " applied for {$overdueDays} overdue days.";
            }

            return ['success' => true, 'message' => 'Books returned successfully.' . $fineMessage];
        } catch (Throwable $e) {
            logAppError($e, "Return transaction processing failed for transaction ID {$transactionId}");
            return ['success' => false, 'message' => 'Unable to process return transaction at this time. Please try again.'];
        }
    }
}
