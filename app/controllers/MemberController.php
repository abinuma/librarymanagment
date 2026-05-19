<?php
/**
 * MemberController - CRUD operations for members
 */

class MemberController
{
    private Member $memberModel;

    public function __construct()
    {
        $this->memberModel = new Member();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $page = max(1, (int)queryParam('page', '1'));
            $search = queryParam('search');
            $status = queryParam('status', 'all');
            
            $result = $this->memberModel->getPaginated($page, RECORDS_PER_PAGE, $search, $status);
            $members = $result['data'];
            $pagination = $result;
            require VIEW_PATH . '/students/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading members index");
            errorResponse('Unable to load members listing at this time.', '/dashboard');
        }
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();
        require VIEW_PATH . '/students/create.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            MemberValidator::validateOrThrow($_POST);

            $this->memberModel->create([
                'full_name'  => sanitize($_POST['full_name']),
                'email'      => sanitize($_POST['email']),
                'phone'      => sanitize($_POST['phone'] ?? ''),
                'student_id' => sanitize($_POST['student_id']),
                'department' => sanitize($_POST['department'] ?? ''),
                'address'    => sanitize($_POST['address'] ?? ''),
            ]);

            clearOldInput();
            successResponse('Member added successfully.', '/members');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/members/create');
        } catch (Throwable $e) {
            logAppError($e, "Failed to create member");
            errorResponse('Unable to save member right now. Please verify inputs or contact support.', '/members/create');
        }
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();
        try {
            $member = $this->memberModel->findById($id);
            if (!$member) {
                errorResponse('Member not found.', '/members');
                return;
            }
            require VIEW_PATH . '/students/edit.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading member edit form for ID {$id}");
            errorResponse('Unable to load member details right now.', '/members');
        }
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            MemberValidator::validateOrThrow($_POST, $id);

            $this->memberModel->update($id, [
                'full_name'  => sanitize($_POST['full_name']),
                'email'      => sanitize($_POST['email']),
                'phone'      => sanitize($_POST['phone'] ?? ''),
                'student_id' => sanitize($_POST['student_id']),
                'department' => sanitize($_POST['department'] ?? ''),
                'address'    => sanitize($_POST['address'] ?? ''),
                'is_active'  => isset($_POST['is_active']) ? 1 : 0,
            ]);

            clearOldInput();
            successResponse('Member updated successfully.', '/members');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/members/edit/' . $id);
        } catch (Throwable $e) {
            logAppError($e, "Failed to update member ID {$id}");
            errorResponse('Unable to update member right now.', '/members/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            if (!$this->memberModel->delete($id)) {
                errorResponse('Cannot delete member with active borrow transactions.', '/members');
            } else {
                successResponse('Member deleted successfully.', '/members');
            }
        } catch (Throwable $e) {
            logAppError($e, "Failed to delete member ID {$id}");
            errorResponse('Unable to process member deletion at this time.', '/members');
        }
    }
}
