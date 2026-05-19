<?php
/**
 * Create Member View
 */
$pageTitle = 'Add Member';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-plus text-success"></i> Add New Member</h4>
    <a href="<?= BASE_URL ?>/members" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Members
    </a>
</div>

<div class="glass-card" style="max-width:800px">
    <form method="POST" action="<?= BASE_URL ?>/members/store" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="<?= e(old('student_id')) ?>" placeholder="e.g. STU-2024-006" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= e(old('phone')) ?>" placeholder="+1234567890">
            </div>
            <div class="col-md-6">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" id="department" name="department" value="<?= e(old('department')) ?>">
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?= e(old('address')) ?>">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Member</button>
            <a href="<?= BASE_URL ?>/members" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<?php clearOldInput(); ?>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
