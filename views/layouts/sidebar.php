<?php
/**
 * Sidebar Navigation Component
 */
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$cleanUri = parse_url($requestUri, PHP_URL_PATH);

// Normalize by removing base path if it exists
if (defined('BASE_URL') && BASE_URL !== '') {
    $base = rtrim(BASE_URL, '/');
    if ($base !== '' && strpos($cleanUri, $base) === 0) {
        $cleanUri = substr($cleanUri, strlen($base));
    }
}
$cleanUri = '/' . ltrim($cleanUri, '/');
if ($cleanUri !== '/') {
    $cleanUri = rtrim($cleanUri, '/');
}

// Exact active flags for each menu item to guarantee high precision
$isDashboardActive = ($cleanUri === '/dashboard');
$isBooksActive = (strpos($cleanUri, '/books') === 0);
$isMembersActive = (strpos($cleanUri, '/members') === 0);
$isAllTransactionsActive = (strpos($cleanUri, '/transactions') === 0 && $cleanUri !== '/transactions/borrow' && $cleanUri !== '/transactions/return');
$isBorrowActive = ($cleanUri === '/transactions/borrow');
$isReturnActive = ($cleanUri === '/transactions/return');
$isUsersActive = (strpos($cleanUri, '/users') === 0);
$isCategoriesActive = (strpos($cleanUri, '/categories') === 0);
$isReportsActive = (strpos($cleanUri, '/reports') === 0);
$isSettingsActive = (strpos($cleanUri, '/settings') === 0);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-book-half"></i>
        </div>
        <span><?= APP_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>

        <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= $isDashboardActive ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>

        <div class="nav-section-title">Management</div>

        <a href="<?= BASE_URL ?>/books" class="nav-link <?= $isBooksActive ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark-fill"></i> Books
        </a>

        <a href="<?= BASE_URL ?>/members" class="nav-link <?= $isMembersActive ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Members
        </a>

        <div class="nav-section-title">Transactions</div>

        <a href="<?= BASE_URL ?>/transactions" class="nav-link <?= $isAllTransactionsActive ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right"></i> All Transactions
        </a>

        <a href="<?= BASE_URL ?>/transactions/borrow" class="nav-link <?= $isBorrowActive ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-up-right"></i> Borrow Book
        </a>

        <a href="<?= BASE_URL ?>/transactions/return" class="nav-link <?= $isReturnActive ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-in-down-left"></i> Return Book
        </a>

        <?php if (isAdmin()): ?>
        <div class="nav-section-title">Admin Controls</div>
        
        <a href="<?= BASE_URL ?>/users" class="nav-link <?= $isUsersActive ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>
        
        <a href="<?= BASE_URL ?>/categories" class="nav-link <?= $isCategoriesActive ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Categories
        </a>

        <a href="<?= BASE_URL ?>/reports" class="nav-link <?= $isReportsActive ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> Reports
        </a>

        <a href="<?= BASE_URL ?>/settings" class="nav-link <?= $isSettingsActive ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Settings
        </a>
        <?php endif; ?>

        <div class="nav-section-title">Account</div>

        <a href="<?= BASE_URL ?>/logout" class="nav-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</aside>
