<?php
/**
 * Edit User View
 */
$pageTitle = 'Edit User';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-gear text-primary"></i> Edit User: <?= e($user['username']) ?></h4>
    <a href="<?= BASE_URL ?>/users" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="glass-card" style="max-width: 800px;">
    <form action="<?= BASE_URL ?>/users/update" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                <div class="form-text text-secondary">Username cannot be changed.</div>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e($user['email']) ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required <?= $user['id'] === authUser()['id'] ? 'disabled' : '' ?>>
                    <option value="librarian" <?= $user['role'] === 'librarian' ? 'selected' : '' ?>>Librarian</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <?php if ($user['id'] === authUser()['id']): ?>
                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                    <div class="form-text text-warning">You cannot change your own role.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password (Optional)</label>
            <input type="password" class="form-control" id="password" name="password">
            <div class="form-text text-secondary">Leave blank to keep the current password.</div>
        </div>

        <div class="mb-4 form-check form-switch">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                <?= $user['is_active'] ? 'checked' : '' ?> <?= $user['id'] === authUser()['id'] ? 'disabled' : '' ?>>
            <label class="form-check-label" for="is_active"><?= $user['is_active'] ? 'Account is Active' : 'Account is Inactive' ?></label>
            <?php if ($user['id'] === authUser()['id']): ?>
                <input type="hidden" name="is_active" value="1">
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">Update User</button>
            <a href="<?= BASE_URL ?>/users" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isActiveSwitch = document.getElementById('is_active');
    const isActiveLabel = document.querySelector('label[for="is_active"]');
    
    if (isActiveSwitch && isActiveLabel) {
        isActiveSwitch.addEventListener('change', function() {
            isActiveLabel.textContent = this.checked ? 'Account is Active' : 'Account is Inactive';
        });
    }
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
