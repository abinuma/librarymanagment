<?php
/**
 * System Settings View
 */
$pageTitle = 'Settings';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-gear-fill text-primary"></i> System Settings</h4>
</div>

<div class="glass-card" style="max-width: 700px;">
    <h5 class="mb-4 text-primary"><i class="bi bi-sliders"></i> Borrowing & Fine Rules</h5>
    <form action="<?= BASE_URL ?>/settings/update" method="POST">
        <?= csrfField() ?>
        
        <div class="mb-3">
            <label for="borrow_duration_days" class="form-label">Default Borrow Duration (Days) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="borrow_duration_days" name="borrow_duration_days" 
                value="<?= e($settings['borrow_duration_days'] ?? 14) ?>" required min="1">
            <div class="form-text text-secondary">How many days a member can keep a book before it becomes overdue.</div>
        </div>

        <div class="mb-3">
            <label for="fine_per_day" class="form-label">Fine Per Overdue Day ($) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="fine_per_day" name="fine_per_day" 
                value="<?= e($settings['fine_per_day'] ?? 1.00) ?>" required min="0">
            <div class="form-text text-secondary">Amount charged per day when a book is overdue.</div>
        </div>

        <div class="mb-4">
            <label for="max_borrow_limit" class="form-label">Maximum Borrow Limit <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="max_borrow_limit" name="max_borrow_limit" 
                value="<?= e($settings['max_borrow_limit'] ?? 5) ?>" required min="1">
            <div class="form-text text-secondary">Maximum number of books a member can have borrowed at one time.</div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Settings</button>
    </form>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
