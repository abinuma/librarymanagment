<?php
/**
 * Categories Index View
 */
$pageTitle = 'Categories';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-tags-fill text-primary"></i> Manage Categories</h4>
    <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Category
    </a>
</div>

<div class="table-container">
    <div class="table-header">
        <h5>All Categories <span class="badge bg-primary ms-2"><?= count($categories) ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Books Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-tags-x d-block"></i>
                                <p>No categories found.</p>
                                <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary btn-sm">Add First Category</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><strong><?= e($c['name']) ?></strong></td>
                            <td class="text-truncate" style="max-width: 300px;"><?= e($c['description']) ?></td>
                            <td>
                                <span class="badge bg-primary bg-opacity-25 text-primary"><?= isset($c['book_count']) ? $c['book_count'] : 'N/A' ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/categories/edit/<?= $c['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                                        Edit
                                    </a>
                                    <form action="<?= BASE_URL ?>/categories/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? It will fail if it contains books.');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
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
