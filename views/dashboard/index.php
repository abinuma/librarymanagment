<?php
/**
 * Dashboard View
 */
$pageTitle = 'Dashboard';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-grid-1x2-fill text-primary"></i> Dashboard Overview</h4>
    <span class="text-muted" style="font-size:0.85rem">
        <i class="bi bi-calendar3"></i> <?= date('l, F j, Y') ?>
    </span>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card books fade-in-up">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['total_books']) ?></h3>
                <p>Total Books</p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card borrowed fade-in-up">
            <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['borrowed_books']) ?></h3>
                <p>Borrowed</p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card available fade-in-up">
            <div class="stat-icon"><i class="bi bi-bookmark-fill"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['available_books']) ?></h3>
                <p>Available</p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card members fade-in-up">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['total_members']) ?></h3>
                <p>Members</p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card overdue fade-in-up">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['overdue_books']) ?></h3>
                <p>Overdue</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions + Recent Transactions -->
<div class="row g-3">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="glass-card h-100">
            <h5 class="mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/transactions/borrow" class="btn btn-primary">
                    <i class="bi bi-box-arrow-up-right"></i> Borrow Book
                </a>
                <a href="<?= BASE_URL ?>/transactions/return" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-down-left"></i> Return Book
                </a>
                <a href="<?= BASE_URL ?>/books/create" class="btn btn-outline-light">
                    <i class="bi bi-plus-circle"></i> Add New Book
                </a>
                <a href="<?= BASE_URL ?>/members/create" class="btn btn-outline-light">
                    <i class="bi bi-person-plus"></i> Add New Member
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-lg-8">
        <div class="table-container">
            <div class="table-header">
                <h5><i class="bi bi-clock-history text-info me-2"></i>Recent Transactions</h5>
                <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-light btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $t): ?>
                                <tr>
                                    <td><?= e($t['member_name']) ?></td>
                                    <td><?= e($t['book_title']) ?></td>
                                    <td><?= formatDate($t['borrow_date']) ?></td>
                                    <td><?= formatDate($t['due_date']) ?></td>
                                    <td><?= statusBadge($t['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
