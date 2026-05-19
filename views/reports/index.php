<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>System Reports & Analytics</h2>
            <p class="text-muted">Overview of library operations</p>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title small text-uppercase fw-bold opacity-75"><i class="bi bi-bookmark-check me-2"></i>Active Borrowings</h5>
                        <h2 class="mb-0"><?= number_format($activeBorrowingsCount) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title small text-uppercase fw-bold opacity-75"><i class="bi bi-journals me-2"></i>Total Inventory</h5>
                        <h2 class="mb-0"><?= number_format($inventoryStats['total_books'] ?? 0) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title small text-uppercase fw-bold opacity-75"><i class="bi bi-journal-check me-2"></i>Available Copies</h5>
                        <h2 class="mb-0"><?= number_format($inventoryStats['total_available'] ?? 0) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-warning text-dark shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title small text-uppercase fw-bold opacity-75"><i class="bi bi-cash-stack me-2"></i>Fines Collected</h5>
                        <h2 class="mb-0">$<?= number_format((float)($fineStats['total_paid'] ?? 0), 2) ?></h2>
                    </div>
                    <a href="<?= BASE_URL ?>/reports/fines" class="btn btn-dark btn-sm mt-3 fw-bold align-self-start">
                        <i class="bi bi-receipt me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Overdue Books List -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 text-danger">Currently Overdue Books</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Book Title</th>
                                    <th>Member Name</th>
                                    <th>Due Date</th>
                                    <th>Overdue Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($overdueBooks)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No overdue books at the moment.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($overdueBooks as $ob): ?>
                                        <tr>
                                            <td><?= e($ob['book_title']) ?></td>
                                            <td><?= e($ob['member_name']) ?></td>
                                            <td><?= formatDate($ob['due_date']) ?></td>
                                            <td><span class="badge bg-danger"><?= calculateOverdueDays($ob['due_date']) ?> Days</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Borrowed Books -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Most Borrowed Books</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($mostBorrowed)): ?>
                            <li class="list-group-item text-muted text-center py-3">No borrow history yet.</li>
                        <?php else: ?>
                            <?php foreach ($mostBorrowed as $mb): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong><?= e($mb['title']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= e($mb['author']) ?></small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?= $mb['borrow_count'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
