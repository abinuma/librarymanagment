<?php
/**
 * Return Book View
 */
$pageTitle = 'Return Book';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-box-arrow-in-down-left text-success"></i> Return a Book</h4>
</div>

<div class="glass-card" style="max-width:700px">
    <?php if (empty($activeBorrows)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox d-block"></i>
            <p>No active borrows to return</p>
            <a href="<?= BASE_URL ?>/transactions/borrow" class="btn btn-primary btn-sm">Borrow a Book</a>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/transactions/return" class="needs-validation" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label for="transaction_id" class="form-label">Select Borrowed Book to Return <span class="text-danger">*</span></label>
                <select class="form-select" id="transaction_id" name="transaction_id" required>
                    <option value="">-- Select a Transaction --</option>
                    <?php foreach ($activeBorrows as $b): ?>
                        <?php
                            $overdue = calculateOverdueDays($b['due_date']);
                            $overdueText = $overdue > 0 ? " ⚠️ OVERDUE {$overdue} days" : '';
                            $fine = $overdue > 0 ? ' — Fine: ' . formatCurrency($overdue * FINE_PER_DAY) : '';
                        ?>
                        <option value="<?= $b['id'] ?>">
                            <?= e($b['member_name']) ?> (<?= e($b['student_id']) ?>) → "<?= e($b['book_title']) ?>" | Due: <?= formatDate($b['due_date']) ?><?= $overdueText ?><?= $fine ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Active Borrows Quick Reference -->
            <div class="mb-3">
                <h6 class="text-muted mb-2"><i class="bi bi-list-ul me-1"></i>Active Borrows</h6>
                <div class="table-responsive">
                    <table class="table table-dark-custom table-sm">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Book</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeBorrows as $b): ?>
                                <tr>
                                    <td><?= e($b['member_name']) ?></td>
                                    <td><?= e($b['book_title']) ?></td>
                                    <td>
                                        <?= formatDate($b['due_date']) ?>
                                        <?php $od = calculateOverdueDays($b['due_date']); ?>
                                        <?php if ($od > 0): ?>
                                            <br><small class="text-danger"><?= $od ?> days overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= statusBadge($b['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-box-arrow-in-down-left"></i> Process Return</button>
                <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
