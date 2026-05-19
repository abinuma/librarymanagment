<?php
/**
 * Create User View
 */
$pageTitle = 'Create User';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-plus text-success"></i> Create New User</h4>
    <a href="<?= BASE_URL ?>/users" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="glass-card" style="max-width: 800px;">
    <form action="<?= BASE_URL ?>/users/store" method="POST">
        <?= csrfField() ?>
        
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" value="<?= e(old('username')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="librarian" <?= old('role') === 'librarian' ? 'selected' : '' ?>>Librarian</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="form-text text-secondary">Choose a strong password for the new user.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i> Create User</button>
            <a href="<?= BASE_URL ?>/users" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<?php clearOldInput(); ?>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
