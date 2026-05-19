<?php
/**
 * Members Index View
 */
$pageTitle = 'Members';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-people-fill text-info"></i> Member Management</h4>
    <a href="<?= BASE_URL ?>/members/create" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add Member
    </a>
</div>

<!-- Search & Filters -->
<div class="table-container mb-4 slide-in">
    <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="bi bi-funnel-fill text-info me-2"></i> Search & Filters</h5>
        <?php if (!empty($search) || ($status !== 'all')): ?>
            <a href="<?= BASE_URL ?>/members" class="btn btn-outline-light btn-sm">
                <i class="bi bi-x-circle me-1"></i> Clear All Filters
            </a>
        <?php endif; ?>
    </div>
    <div class="p-4">
        <form method="GET" action="<?= BASE_URL ?>/members" id="filterForm">
            <div class="row g-3">
                <!-- Search Bar -->
                <div class="col-md-8">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-search text-info me-1"></i> Search Query</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-muted border-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by name, email, student ID, department..." value="<?= e($search ?? '') ?>">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search me-1"></i> Search</button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-4">
                    <label class="form-label">Member Status</label>
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= ($status ?? 'all') === 'active' ? 'selected' : '' ?>>Active Only</option>
                        <option value="inactive" <?= ($status ?? 'all') === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="table-container">
    <div class="table-header">
        <h5>All Members <span class="badge bg-primary ms-2"><?= $pagination['total'] ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-person-x d-block"></i>
                                <p>No members found</p>
                                <a href="<?= BASE_URL ?>/members/create" class="btn btn-primary btn-sm">Add First Member</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $i => $member): ?>
                        <tr>
                            <td><?= ($pagination['current_page'] - 1) * $pagination['per_page'] + $i + 1 ?></td>
                            <td><strong><?= e($member['full_name']) ?></strong></td>
                            <td><code><?= e($member['student_id']) ?></code></td>
                            <td><?= e($member['email']) ?></td>
                            <td><?= e($member['phone'] ?? 'N/A') ?></td>
                            <td><?= e($member['department'] ?? 'N/A') ?></td>
                            <td><?= statusBadge($member['is_active'] ? 'active' : 'inactive') ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/members/edit/<?= $member['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-name="<?= e($member['full_name']) ?>"
                                            data-action="<?= BASE_URL ?>/members/delete/<?= $member['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="p-3">
            <?= paginationHtml($pagination['current_page'], $pagination['total_pages'], BASE_URL . '/members') ?>
        </div>
    <?php endif; ?>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
