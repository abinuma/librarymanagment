<?php
/**
 * Transactions Index View - All borrow/return transactions
 */
$pageTitle = 'Transactions';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-arrow-left-right text-primary"></i> Transactions</h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/transactions/borrow" class="btn btn-primary">
            <i class="bi bi-box-arrow-up-right"></i> Borrow
        </a>
        <a href="<?= BASE_URL ?>/transactions/return" class="btn btn-success">
            <i class="bi bi-box-arrow-in-down-left"></i> Return
        </a>
    </div>
</div>

<!-- Search & Filter -->
<div class="table-container mb-4">
    <div class="table-header">
        <h5><i class="bi bi-funnel me-2"></i>Filter Transactions</h5>
    </div>
    <div class="p-3">
        <form method="GET" action="<?= BASE_URL ?>/transactions" class="search-bar">
            <input type="text" class="form-control" name="search" placeholder="Search member, book, or student ID..."
                   value="<?= e($search ?? '') ?>">
            <select class="form-select" name="status" style="max-width:180px">
                <option value="">All Status</option>
                <option value="borrowed" <?= ($status ?? '') === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                <option value="returned" <?= ($status ?? '') === 'returned' ? 'selected' : '' ?>>Returned</option>
                <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-light"><i class="bi bi-x-lg"></i> Clear</a>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="table-container">
    <div class="table-header">
        <h5>All Transactions <span class="badge bg-primary ms-2"><?= $pagination['total_books'] ?> Books</span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Student ID</th>
                    <th>Items</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-inbox d-block"></i>
                                <p>No transactions found</p>
                                <a href="<?= BASE_URL ?>/transactions/borrow" class="btn btn-primary btn-sm">Create First Transaction</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $i => $t): ?>
                        <tr>
                            <td><?= ($pagination['current_page'] - 1) * $pagination['per_page'] + $i + 1 ?></td>
                            <td><strong><?= e($t['member_name']) ?></strong></td>
                            <td><code><?= e($t['student_id']) ?></code></td>
                            <td><span class="badge bg-secondary"><?= $t['book_count'] == 1 ? '1 book' : ($t['book_count'] ?? 0) . ' books' ?></span></td>
                            <td><?= formatDate($t['borrow_date']) ?></td>
                            <td>
                                <?= formatDate($t['due_date']) ?>
                                <?php if ($t['status'] !== 'returned' && strtotime($t['due_date']) < time()): ?>
                                    <br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> <?= calculateOverdueDays($t['due_date']) ?> days overdue</small>
                                <?php endif; ?>
                            </td>
                            <td><?= $t['return_date'] ? formatDate($t['return_date']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/transactions/show/<?= $t['member_id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="p-3">
            <?= paginationHtml($pagination['current_page'], $pagination['total_pages'], BASE_URL . '/transactions') ?>
        </div>
    <?php endif; ?>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
