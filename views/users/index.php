<?php
/**
 * Users Index View
 */
$pageTitle = 'Users';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-people-fill text-primary"></i> Manage Users</h4>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<div class="table-container">
    <div class="table-header">
        <h5>All System Users <span class="badge bg-primary ms-2"><?= count($users) ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-person-x d-block"></i>
                                <p>No users found.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= e($u['username']) ?></strong></td>
                            <td><?= e($u['full_name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-info text-dark' ?>">
                                    <?= ucfirst(e($u['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $u['last_login'] ? formatDate($u['last_login']) : 'Never' ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <?php if ($u['id'] !== authUser()['id']): ?>
                                        <form action="<?= BASE_URL ?>/users/delete" method="POST" class="d-inline" <?= $u['is_active'] ? 'onsubmit="return confirm(\'Are you sure you want to disable this user?\');"' : '' ?>>
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <?php if ($u['is_active']): ?>
                                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Disable">
                                                    <i class="bi bi-slash-circle"></i> Disable
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.65; cursor: not-allowed;" data-bs-toggle="tooltip" title="Already Disabled">
                                                    <i class="bi bi-slash-circle"></i> Disabled
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
