<?php
// ==========================================
// BorrowController.php
// ==========================================
/**
 * BorrowController - Borrow/Return operations
 */

class BorrowController
{
    private BorrowService $borrowService;
    private BorrowTransaction $transactionModel;

    public function __construct()
    {
        $this->borrowService = new BorrowService();
        $this->transactionModel = new BorrowTransaction();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $this->transactionModel->updateOverdueStatuses();

            $page = max(1, (int)queryParam('page', '1'));
            $status = queryParam('status');
            $search = queryParam('search');
            $result = $this->transactionModel->getPaginated($page, RECORDS_PER_PAGE, $status, $search);
            $transactions = $result['data'];
            $pagination = $result;
            require VIEW_PATH . '/transactions/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading borrow transactions index");
            errorResponse('Unable to load transactions listing at this time.', '/dashboard');
        }
    }

    public function borrowForm(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $bookModel = new Book();
            $memberModel = new Member();
            $availableBooks = $bookModel->getAvailable();
            $activeMembers = $memberModel->getActive();
            require VIEW_PATH . '/transactions/borrow.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading borrow form");
            errorResponse('Unable to load borrowing form right now.', '/transactions');
        }
    }

    public function borrow(): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            $memberId = (int)($_POST['member_id'] ?? 0);
            $bookIds = $_POST['book_ids'] ?? [];
            if (!is_array($bookIds)) {
                $bookIds = [$bookIds];
            }
            $bookIds = array_unique($bookIds);
            $bookIds = array_map('intval', $bookIds);
            $bookIds = array_filter($bookIds, fn($id) => $id > 0);
            
            $borrowDate = $_POST['borrow_date'] ?? date('Y-m-d');
            $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+' . setting('borrow_duration_days', 14) . ' days'));
            
            $userId = authUser()['id'];

            if ($memberId <= 0 || empty($bookIds)) {
                errorResponse('Please select a member and at least one book.', '/transactions/borrow');
                return;
            }

            $result = $this->borrowService->borrowBook($memberId, $bookIds, $userId, $borrowDate, $dueDate);

            if ($result['success']) {
                successResponse($result['message'], '/transactions');
            } else {
                errorResponse($result['message'], '/transactions/borrow');
            }
        } catch (Throwable $e) {
            logAppError($e, "Unexpected error in borrow action");
            errorResponse('Unable to process borrowing request at this time.', '/transactions/borrow');
        }
    }

    public function returnForm(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $activeBorrows = $this->transactionModel->getActiveBorrows();
            require VIEW_PATH . '/transactions/return.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading return form");
            errorResponse('Unable to load return form right now.', '/transactions');
        }
    }

    public function processReturn(): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            $transactionId = (int)($_POST['transaction_id'] ?? 0);
            if ($transactionId <= 0) {
                errorResponse('Please select a valid transaction to return.', '/transactions/return');
                return;
            }

            $result = $this->borrowService->returnBook($transactionId);

            if ($result['success']) {
                successResponse($result['message'], '/transactions');
            } else {
                errorResponse($result['message'], '/transactions/return');
            }
        } catch (Throwable $e) {
            logAppError($e, "Unexpected error processing return");
            errorResponse('Unable to process book return at this time.', '/transactions/return');
        }
    }

    public function show(int $memberId): void
    {
        AuthMiddleware::requireAuth();
        try {
            $memberData = $this->transactionModel->findMemberTransactions($memberId);

            if (!$memberData) {
                errorResponse('Member or transactions not found.', '/transactions');
                return;
            }

            $fineService = new FineService();
            $fineModel = new Fine();
            require VIEW_PATH . '/transactions/show.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading borrowing details for member ID {$memberId}");
            errorResponse('Unable to load member borrowing details right now.', '/transactions');
        }
    }

    public function printBarcode(int $id): void
    {
        AuthMiddleware::requireAuth();
        try {
            $transaction = $this->transactionModel->findById($id);
            if (!$transaction || empty($transaction['items'])) {
                http_response_code(404);
                exit('Transaction or associated books not found.');
            }
            
            $bookModel = new Book();
            $firstBookId = $transaction['items'][0]['book_id'];
            $book = $bookModel->findById($firstBookId);
            
            if (!$book) {
                http_response_code(404);
                exit('Book details not found.');
            }
            
            require VIEW_PATH . '/transactions/print-barcode.php';
        } catch (Throwable $e) {
            logAppError($e, "Error generating barcode for transaction ID {$id}");
            http_response_code(500);
            exit('Unable to generate barcode at this time.');
        }
    }

    public function printBarcodePage(): void
    {
        AuthMiddleware::requireAuth();
        require VIEW_PATH . '/transactions/print-barcode.php';
    }
}