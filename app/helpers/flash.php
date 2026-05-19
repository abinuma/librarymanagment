<?php
/**
 * Flash Message Helper
 * 
 * Provides session-based flash messages for user feedback.
 */

/**
 * Set a flash message
 * 
 * @param string $type    Message type: 'success', 'danger', 'warning', 'info'
 * @param string $message The message text
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message array or null
 */
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Render flash message as Bootstrap alert HTML
 */
function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) return '';

    $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

    // Determine the icon based on message type
    $icon = $type === 'success' ? 'info-circle' : ($type === 'danger' ? 'exclamation-triangle' : 'info-circle');

    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show" role="alert">
        <i class="bi bi-{$icon}-fill me-2"></i>
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    HTML;
}
