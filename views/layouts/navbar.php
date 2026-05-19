<?php
/**
 * Top Navbar Component
 */
$currentUser = authUser();
$initials = '';
if ($currentUser) {
    $parts = explode(' ', $currentUser['full_name']);
    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
}
?>
<nav class="top-navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <span class="navbar-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button id="themeToggleBtn" class="btn btn-sm btn-outline-light d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; padding: 0;" aria-label="Toggle Theme">
            <i class="bi bi-moon-stars-fill" id="themeToggleIcon"></i>
        </button>
        <div class="navbar-user">
        <div class="user-info d-none d-sm-flex">
            <span class="user-name"><?= e($currentUser['full_name'] ?? 'User') ?></span>
            <span class="user-role"><?= e($currentUser['role'] ?? '') ?></span>
        </div>
        <div class="user-avatar"><?= $initials ?></div>
    </div>
</nav>
