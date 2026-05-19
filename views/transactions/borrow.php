<?php
/**
 * Borrow Book View
 */
$pageTitle = 'Borrow Book';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-box-arrow-up-right text-primary"></i> Borrow a Book</h4>
</div>

<div class="glass-card" style="max-width:700px">
    <div class="mb-3">
        <div class="alert alert-info py-2">
            <i class="bi bi-info-circle-fill me-2"></i>
            Borrow period: <strong><?= setting('borrow_duration_days', 14) ?> days</strong> | Max books per member: <strong><?= setting('max_borrow_limit', 5) ?></strong> | Fine: <strong><?= formatCurrency((float)setting('fine_per_day', 1.00)) ?>/day</strong> overdue
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/transactions/borrow" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <div class="row g-3">
            <div class="col-12">
                <label for="member_id" class="form-label">Select Member <span class="text-danger">*</span></label>
                <select class="form-select" id="member_id" name="member_id" required>
                    <option value="">-- Choose a Member --</option>
                    <?php foreach ($activeMembers as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= old('member_id') == $m['id'] ? 'selected' : '' ?>>
                            <?= e($m['full_name']) ?> (<?= e($m['student_id']) ?>) — <?= e($m['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label for="book_ids" class="form-label">Select Books <span class="text-danger">*</span></label>
                <select class="form-select" id="book_ids" name="book_ids[]" multiple required style="min-height: 150px;">
                    <?php foreach ($availableBooks as $b): ?>
                        <option value="<?= $b['id'] ?>">
                            <?= e($b['title']) ?> by <?= e($b['author']) ?> (<?= $b['available_copies'] ?> available)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text text-light"><i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Command (Mac) to select multiple books.</div>
            </div>

            <div class="col-md-6">
                <label for="borrow_date" class="form-label">Borrow Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="borrow_date" name="borrow_date" required value="<?= old('borrow_date', date('Y-m-d')) ?>">
            </div>
            
            <div class="col-md-6">
                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control text-warning fw-bold bg-dark" id="due_date" name="due_date" required value="<?= old('due_date', date('Y-m-d', strtotime('+' . setting('borrow_duration_days', 14) . ' days'))) ?>">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-up-right"></i> Process Borrow</button>
            <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const borrowDuration = <?= (int) setting('borrow_duration_days', 14) ?>;
    const borrowDateInput = document.getElementById('borrow_date');
    const dueDateInput = document.getElementById('due_date');

    borrowDateInput.addEventListener('change', function() {
        if(this.value) {
            let date = new Date(this.value);
            date.setDate(date.getDate() + borrowDuration);
            dueDateInput.value = date.toISOString().split('T')[0];
        }
    });
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
