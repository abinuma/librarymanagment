<?php
/**
 * Fine Revenue Report View
 */
$pageTitle = 'Fine Revenue Report';

$maxPaidFines = 0;
if (!empty($memberSummary)) {
    $mostFined = null;
    foreach ($memberSummary as $ms) {
        if ($mostFined === null || $ms['total_fines'] > $mostFined['total_fines']) {
            $mostFined = $ms;
        }
    }
    if ($mostFined) {
        $maxPaidFines = (int)$mostFined['paid_count'];
    }
}

require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h4><i class="bi bi-cash-coin text-warning me-2"></i> Fine Revenue & Penalties Report</h4>
    <a href="<?= BASE_URL ?>/reports" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports Overview
    </a>
</div>

<!-- Summary Statistics Row -->
<div class="row g-3 mb-4 slide-in">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card available h-100">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-info">
                <h3>$<?= number_format((float)($fineStats['total_paid'] ?? 0), 2) ?></h3>
                <p>Total Paid Fines (<?= $fineStats['total_paid_transactions'] ?? 0 ?> payments)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card overdue h-100">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-info">
                <h3>$<?= number_format((float)($fineStats['total_unpaid'] ?? 0), 2) ?></h3>
                <p>Total Unpaid Fines (<?= max(0, ($fineStats['total_transactions'] ?? 0) - ($fineStats['total_paid_transactions'] ?? 0)) ?> pending)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card books h-100">
            <div class="stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="stat-info">
                <h3><?= number_format($fineStats['total_transactions'] ?? 0) ?></h3>
                <p>Total Fine Transactions Issued</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card members h-100">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <h3><?= $maxPaidFines ?></h3>
                <p>Max Fine Payments by Single Member</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 slide-in">
    <!-- Left Column: Most Fined Members / Payment Frequency -->
    <div class="col-md-4">
        <div class="glass-card h-100">
            <h5 class="mb-4 pb-2 border-bottom border-secondary"><i class="bi bi-person-lines-fill text-info me-2"></i> Member Fine Summary</h5>
            <div class="list-group list-group-flush bg-transparent">
                <?php if (empty($memberSummary)): ?>
                    <div class="text-muted text-center py-4">No fine records found for members.</div>
                <?php else: ?>
                    <?php foreach ($memberSummary as $ms): ?>
                        <div class="list-group-item bg-transparent text-white border-bottom px-0 py-3 d-flex flex-column gap-2" style="border-color: rgba(255,255,255,0.1) !important;">
                            <div>
                                <strong class="fs-6 d-block mb-1"><?= e($ms['full_name']) ?></strong>
                                <small class="text-muted"><i class="bi bi-card-text me-1"></i> ID: <code class="text-info"><?= e($ms['student_id']) ?></code></small>
                            </div>
                            <div>
                                <span class="badge bg-success mb-1"><?= $ms['paid_count'] ?> fine payments ($<?= number_format((float)$ms['paid_amount'], 2) ?>)</span><br>
                                <?php if (($ms['total_amount'] - $ms['paid_amount']) > 0): ?>
                                    <small class="text-danger small fw-bold">Unpaid: $<?= number_format((float)($ms['total_amount'] - $ms['paid_amount']), 2) ?></small>
                                <?php else: ?>
                                    <small class="text-muted small">All fines paid</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Fine Details Table with Search & Pagination -->
    <div class="col-md-8">
        <div class="table-container mb-4">
            <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="bi bi-table text-primary me-2"></i> Detailed Fine Revenue Log</h5>
                <form method="GET" action="<?= BASE_URL ?>/reports/fines" class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search member, ID, reason..." value="<?= e($search ?? '') ?>" style="max-width: 250px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= BASE_URL ?>/reports/fines" class="btn btn-outline-light btn-sm"><i class="bi bi-x-circle"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark-custom mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Amount</th>
                            <th>Status & Date</th>
                            <th>Payment Method</th>
                            <th>Reason</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fines)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No fine revenue details found matching criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fines as $fine): ?>
                                <tr>
                                    <td>
                                        <strong class="text-white"><?= e($fine['member_name']) ?></strong><br>
                                        <small class="text-muted">ID: <code><?= e($fine['student_id']) ?></code></small>
                                    </td>
                                    <td><strong class="text-warning">$<?= number_format((float)$fine['amount'], 2) ?></strong></td>
                                    <td>
                                        <?php if ($fine['is_paid']): ?>
                                            <span class="badge bg-success mb-1"><i class="bi bi-cash-coin me-1"></i> Paid</span><br>
                                            <small class="text-muted"><?= formatDate($fine['paid_date']) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-danger mb-1"><i class="bi bi-x-circle me-1"></i> Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($fine['is_paid']): ?>
                                            <span class="badge bg-success">Standard / Cash</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Paid Yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-muted small"><?= e($fine['reason']) ?></span></td>
                                    <td><span class="text-muted small"><?= e($fine['processed_by']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <div class="p-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                    <?= paginationHtml($pagination['current_page'], $pagination['total_pages'], BASE_URL . '/reports/fines') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
