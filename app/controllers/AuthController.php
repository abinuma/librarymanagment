<?php
// ==========================================
// AuthController.php
// ==========================================
/**
 * AuthController - Handles login/logout
 */

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        AuthMiddleware::guest();
        require VIEW_PATH . '/auth/login.php';
    }

    public function login(): void
    {
        AuthMiddleware::verifyCsrf();

        try {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                errorResponse('Please enter both username and password.', '/login');
                return;
            }

            $user = $this->userModel->findByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {
                errorResponse('Invalid username or password.', '/login');
                return;
            }

            if (!$user['is_active']) {
                errorResponse('Your account is disabled. Please contact the administrator.', '/login');
                return;
            }

            // Set session securely
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $user['role'],
            ];
            $_SESSION['last_activity'] = time();

            $this->userModel->updateLastLogin($user['id']);

            successResponse('Welcome back, ' . e($user['full_name']) . '!', '/dashboard');
        } catch (Throwable $e) {
            logAppError($e, "Authentication error during login attempt for username '{$username}'");
            errorResponse('Unable to process login request at this time. Please try again later.', '/login');
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        successResponse('You have been logged out.', '/login');
    }
}