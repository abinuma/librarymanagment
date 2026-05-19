<?php
/**
 * Login Page
 */
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Library Management System - Login">
    <title>Login | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card fade-in-up">
        <div class="logo-icon">
            <i class="bi bi-book-half text-white"></i>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to Library Management System</p>

        <?= renderFlash() ?>

        <form method="POST" action="<?= BASE_URL ?>/login" class="needs-validation" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--bg-glass);border-color:var(--border);color:var(--text-muted)">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= e(old('username')) ?>" placeholder="Enter username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:var(--bg-glass);border-color:var(--border);color:var(--text-muted)">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Default: admin / password</small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
<?php clearOldInput(); ?>
