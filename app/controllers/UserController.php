<?php
/**
 * UserController - Admin User Management
 */

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        try {
            $users = $this->userModel->getAll();
            require VIEW_PATH . '/users/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading users index");
            errorResponse('Unable to load user accounts at this time.', '/dashboard');
        }
    }

    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        require VIEW_PATH . '/users/create.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        try {
            UserValidator::validateOrThrow($_POST);

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'librarian';

            $this->userModel->create([
                'username' => sanitize($username),
                'email' => sanitize($email),
                'full_name' => sanitize($fullName),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => sanitize($role),
                'is_active' => 1
            ]);

            clearOldInput();
            successResponse('User account created successfully.', '/users');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/users/create');
        } catch (Throwable $e) {
            logAppError($e, "Failed to create user account");
            errorResponse('Unable to create user account right now. Please verify inputs or contact support.', '/users/create');
        }
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAdmin();
        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                errorResponse('User not found.', '/users');
                return;
            }
            require VIEW_PATH . '/users/edit.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading user edit form for ID {$id}");
            errorResponse('Unable to load user details right now.', '/users');
        }
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        try {
            UserValidator::validateOrThrow($_POST, $id);

            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'librarian';
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $password = $_POST['password'] ?? '';

            if ($id === authUser()['id'] && (!$isActive || $role !== 'admin')) {
                errorResponse('You cannot deactivate your own account or remove your admin privileges.', "/users/edit/{$id}");
                return;
            }

            $data = [
                'email' => sanitize($email),
                'full_name' => sanitize($fullName),
                'role' => sanitize($role),
                'is_active' => $isActive
            ];

            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $this->userModel->update($id, $data);

            successResponse('User account updated successfully.', '/users');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), "/users/edit/{$id}");
        } catch (Throwable $e) {
            logAppError($e, "Failed to update user ID {$id}");
            errorResponse('Unable to update user account right now.', "/users/edit/{$id}");
        }
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        try {
            if ($id === authUser()['id']) {
                errorResponse('You cannot delete your own account.', '/users');
                return;
            }

            $this->userModel->update($id, ['is_active' => 0]);
            successResponse('User account has been successfully disabled.', '/users');
        } catch (Throwable $e) {
            logAppError($e, "Failed to disable user ID {$id}");
            errorResponse('Unable to disable user account right now.', '/users');
        }
    }
}
