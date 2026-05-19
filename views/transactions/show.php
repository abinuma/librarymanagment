<?php
/**
 * Member Borrowing Overview View
 */
$pageTitle = 'Member Borrowing Overview';
require VIEW_PATH . '/layouts/header.php';
$member = $memberData['member'];
$items = $memberData['items'];
$overallStatus = $memberData['status'];
?>

<div class="page-header">
    <h4><i class="bi bi-person-lines-fill text-info"></i> Borrowing Overview: <?= e($member['member_name']) ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/transactions" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Back to Transactions
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Member Info -->
    <div class="col-md-5">
        <div class="glass-card h-100 slide-in">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                <h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i> Member Overview</h5>
            </div>

            <!-- Borrower Information -->
            <div class="mb-4 p-3 rounded" style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border);">
                <span class="text-white-50 d-block small mb-2"><i class="bi bi-person-badge text-primary me-1"></i> Borrower Information</span>
                <strong class="fs-5 text-white d-block mb-1"><?= e($member['member_name']) ?></strong>
                <div class="d-flex flex-wrap gap-3 small">
                    <span class="text-white-50"><i class="bi bi-card-heading me-1"></i> Student ID: <code class="text-info"><?= e($member['student_id']) ?></code></span>
                    <span class="text-white-50"><i class="bi bi-envelope me-1"></i> <?= e($member['member_email'] ?? 'N/A') ?></span>
                </div>
            </div>

            <!-- Aggregate Status -->
            <div class="p-3 rounded" style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border);">
                <span class="text-white-50 d-block small mb-2"><i class="bi bi-activity text-success me-1"></i> Current Account Status</span>
                <?= statusBadge($overallStatus) ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Books & Fines -->
    <div class="col-md-7">
        <div class="glass-card mb-4">
            <h5 class="mb-4"><i class="bi bi-book me-2"></i> All Borrowed Books (<?= count($items) ?>)</h5>
            
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-dark-custom mb-0">
                    <thead style="position: sticky; top: 0; background: var(--glass-bg); z-index: 1;">
                        <tr>
                            <th>Book Title</th>
                            <th>ISBN</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="3" class="text-muted text-center py-4">No borrowing history.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($item['book_title']) ?></strong><br>
                                        <small class="text-muted">Due: <?= formatDate($item['due_date']) ?></small>
                                    </td>
                                    <td><code><?= e($item['isbn']) ?></code></td>
                                    <td><?= statusBadge($item['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php 
            // Calculate aggregate fines and pending fines
            $fineModel = new Fine();
            // We need to fetch all fines for the member
            $db = Database::getInstance();
            $finesStmt = $db->prepare('SELECT SUM(amount) as total_fines, SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as paid_fines, SUM(CASE WHEN is_paid = 0 THEN amount ELSE 0 END) as unpaid_fines FROM fines WHERE member_id = :mid');
            $finesStmt->execute(['mid' => $member['id']]);
            $finesData = $finesStmt->fetch();
            
            // Calculate pending fines for active overdue transactions
            $pendingFine = 0;
            foreach ($memberData['transactions'] as $t) {
                if ($t['status'] !== 'returned' && strtotime($t['due_date']) < time()) {
                    $overdue = calculateOverdueDays($t['due_date']);
                    if ($overdue > 0) {
                        $pendingFine += $fineService->calculateFine($overdue);
                    }
                }
            }
            
            $totalUnpaid = ($finesData['unpaid_fines'] ?? 0) + $pendingFine;
            $hasHistory = ($finesData['total_fines'] ?? 0) > 0 || $pendingFine > 0;
        ?>
        
        <?php if ($hasHistory): ?>
            <div class="glass-card <?= $totalUnpaid > 0 ? 'border-danger' : 'border-success' ?> slide-in mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                    <h5 class="mb-0 <?= $totalUnpaid > 0 ? 'text-danger' : 'text-success' ?>">
                        <i class="bi <?= $totalUnpaid > 0 ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?> me-2"></i> Aggregate Fine & Penalty Log
                    </h5>
                    <?php if ($totalUnpaid > 0): ?>
                        <span class="badge bg-danger py-1 px-2"><i class="bi bi-x-circle me-1"></i> Outstanding Balance</span>
                    <?php else: ?>
                        <span class="badge bg-success py-1 px-2"><i class="bi bi-cash-coin me-1"></i> Cleared</span>
                    <?php endif; ?>
                </div>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: rgba(15, 23, 42, 0.4);">
                            <span class="text-muted small d-block mb-1"><i class="bi bi-cash me-1"></i> Total Fines Accumulated</span>
                            <strong class="fs-4 text-white">$<?= number_format((float)($finesData['total_fines'] ?? 0) + $pendingFine, 2) ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background: rgba(15, 23, 42, 0.4);">
                            <span class="text-muted small d-block mb-1"><i class="bi bi-credit-card me-1"></i> Current Unpaid Fines</span>
                            <strong class="fs-4 <?= $totalUnpaid > 0 ? 'text-warning' : 'text-success' ?>">$<?= number_format((float)$totalUnpaid, 2) ?></strong>
                            <?php if ($pendingFine > 0): ?>
                                <small class="d-block text-muted mt-1">(Includes $<?= number_format((float)$pendingFine, 2) ?> unbilled overdue)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
