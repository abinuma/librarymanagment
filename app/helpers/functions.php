<?php
/**
 * Helper Functions
 * 
 * Reusable utility functions used throughout the application.
 */

/**
 * Redirect to a given URL path
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Escape output to prevent XSS
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get old input value (for form repopulation after validation errors)
 */
function old(string $key, string $default = ''): string
{
    return $_SESSION['old_input'][$key] ?? $default;
}

/**
 * Clear old input from session
 */
function clearOldInput(): void
{
    unset($_SESSION['old_input']);
}

/**
 * Store current POST data as old input
 */
function storeOldInput(): void
{
    $_SESSION['old_input'] = $_POST;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF token hidden input field
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        return false;
    }
    // Regenerate token after verification
    unset($_SESSION['csrf_token']);
    return true;
}

/**
 * Get currently authenticated user
 */
function authUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Check if user has specific role
 */
function hasRole(string $role): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === $role;
}

/**
 * Check if user is admin
 */
function isAdmin(): bool
{
    return hasRole('admin');
}

/**
 * Format date for display
 */
function formatDate(?string $date): string
{
    if (empty($date)) return 'N/A';
    return date(DISPLAY_DATE_FORMAT, strtotime($date));
}

/**
 * Calculate number of overdue days
 */
function calculateOverdueDays(string $dueDate): int
{
    $due = new DateTime($dueDate);
    $today = new DateTime('today');
    if ($today > $due) {
        return (int) $today->diff($due)->days;
    }
    return 0;
}

/**
 * Format currency
 */
function formatCurrency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Generate pagination HTML
 */
function paginationHtml(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) return '';

    // Preserve existing query parameters except 'page'
    $queryParams = $_GET;
    unset($queryParams['page']);
    $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';

    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $prevPage = max(1, $currentPage - 1);
    $html .= "<li class='page-item {$prevDisabled}'>";
    $html .= "<a class='page-link' href='{$baseUrl}?page={$prevPage}{$queryString}'>&laquo;</a></li>";

    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);

    if ($startPage > 1) {
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page=1{$queryString}'>1</a></li>";
        if ($startPage > 2) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= "<li class='page-item {$active}'>";
        $html .= "<a class='page-link' href='{$baseUrl}?page={$i}{$queryString}'>{$i}</a></li>";
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page={$totalPages}{$queryString}'>{$totalPages}</a></li>";
    }

    // Next button
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $nextPage = min($totalPages, $currentPage + 1);
    $html .= "<li class='page-item {$nextDisabled}'>";
    $html .= "<a class='page-link' href='{$baseUrl}?page={$nextPage}{$queryString}'>&raquo;</a></li>";

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Get status badge HTML
 */
function statusBadge(string $status): string
{
    $badges = [
        'borrowed'  => 'bg-warning text-dark',
        'returned'  => 'bg-success',
        'overdue'   => 'bg-danger',
        'active'    => 'bg-success',
        'inactive'  => 'bg-secondary',
        'paid'      => 'bg-success',
        'unpaid'    => 'bg-danger',
    ];

    $class = $badges[$status] ?? 'bg-secondary';
    return "<span class='badge {$class}'>" . ucfirst(e($status)) . "</span>";
}

/**
 * Sanitize string input
 */
function sanitize(string $input): string
{
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * Get query parameter safely
 */
function queryParam(string $key, string $default = ''): string
{
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Asset URL helper
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Get a setting value dynamically
 */
function setting(string $key, $default = null): ?string
{
    static $settingModel = null;
    if ($settingModel === null) {
        $settingModel = new Setting();
    }
    return $settingModel->get($key, $default);
}

/**
 * Send a clean success response with flash message and redirect
 */
function successResponse(string $message, string $redirectUrl): void
{
    setFlash('success', $message);
    redirect($redirectUrl);
}

/**
 * Send a clean error response with flash message, old input retention, and redirect
 */
function errorResponse(string|array $errors, string $redirectUrl, string $flashType = 'danger'): void
{
    if (is_array($errors)) {
        $_SESSION['errors'] = $errors;
        $message = implode('<br>', $errors);
    } else {
        $message = $errors;
    }
    
    storeOldInput();
    setFlash($flashType, $message);
    redirect($redirectUrl);
}

/**
 * Securely log developer diagnostic errors without exposing to end-user
 */
function logAppError(Throwable $e, string $customMessage = '', array $context = []): void
{
    $log = "\n---------------- [ APP ERROR ] ----------------\n";
    $log .= sprintf("[%s] %s\n", date('Y-m-d H:i:s'), !empty($customMessage) ? $customMessage : $e->getMessage());
    $log .= sprintf("Exception: %s in %s:%d\n", get_class($e), $e->getFile(), $e->getLine());
    
    if ($e instanceof DatabaseException) {
        $log .= sprintf("SQL Query: %s\n", $e->getSqlQuery());
        $log .= sprintf("SQL Params: %s\n", json_encode($e->getSqlParams()));
    }
    if (!empty($context)) {
        $log .= sprintf("Context: %s\n", json_encode($context));
    }
    $log .= "Trace:\n" . $e->getTraceAsString() . "\n";
    $log .= "-------------------------------------------------\n";
    error_log($log);
}

