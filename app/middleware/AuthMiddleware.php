<?php
/**
 * Authentication Middleware
 * 
 * Protects routes that require authentication and role-based access.
 */

class AuthMiddleware
{
    /**
     * Require user to be authenticated
     * Redirects to login if not logged in
     */
    public static function requireAuth(): void
    {
        if (!isLoggedIn()) {
            setFlash('warning', 'Please log in to access this page.');
            redirect('/login');
        }

        // Dynamic check for account status and role
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['id']);
        if (!$user || !$user['is_active']) {
            session_unset();
            session_destroy();
            session_start();
            setFlash('danger', 'Your account has been disabled.');
            redirect('/login');
        }
        
        // Refresh session role to ensure up-to-date permissions
        $_SESSION['user']['role'] = $user['role'];

        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > SESSION_LIFETIME) {
                session_unset();
                session_destroy();
                session_start();
                setFlash('warning', 'Your session has expired. Please log in again.');
                redirect('/login');
            }
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Require user to be an admin
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!isAdmin()) {
            setFlash('danger', 'Access denied. Admin privileges required.');
            redirect('/dashboard');
        }
    }

    /**
     * Require user to have a specific role
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (!hasRole($role)) {
            setFlash('danger', 'Access denied. Insufficient permissions.');
            redirect('/dashboard');
        }
    }

    /**
     * Redirect authenticated users away from auth pages (login)
     */
    public static function guest(): void
    {
        if (isLoggedIn()) {
            redirect('/dashboard');
        }
    }

    /**
     * Verify CSRF token on POST requests
     */
    public static function verifyCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken()) {
                setFlash('danger', 'Invalid security token. Please try again.');
                redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
            }
        }
    }
}
