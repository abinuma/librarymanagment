<?php
/**
 * Edit Member View
 */
$pageTitle = 'Edit Member';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-pencil text-warning"></i> Edit Member</h4>
    <a href="<?= BASE_URL ?>/members" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Members
    </a>
</div>

<div class="glass-card" style="max-width:800px">
    <form method="POST" action="<?= BASE_URL ?>/members/update/<?= $member['id'] ?>" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name', $member['full_name'])) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="<?= e(old('student_id', $member['student_id'])) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email', $member['email'])) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= e(old('phone', $member['phone'])) ?>">
            </div>
            <div class="col-md-6">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" id="department" name="department" value="<?= e(old('department', $member['department'])) ?>">
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?= e(old('address', $member['address'])) ?>">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $member['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active"><?= $member['is_active'] ? 'Active Member' : 'Inactive Member' ?></label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Update Member</button>
            <a href="<?= BASE_URL ?>/members" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isActiveSwitch = document.getElementById('is_active');
    const isActiveLabel = document.querySelector('label[for="is_active"]');
    
    if (isActiveSwitch && isActiveLabel) {
        isActiveSwitch.addEventListener('change', function() {
            isActiveLabel.textContent = this.checked ? 'Active Member' : 'Inactive Member';
        });
    }
});
</script>

<?php clearOldInput(); ?>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
