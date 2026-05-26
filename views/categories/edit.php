<?php
/**
 * Edit Category View
 */
$pageTitle = 'Edit Category';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-tags-fill text-primary"></i> Edit Category: <?= e($category['name']) ?></h4>
    <a href="<?= BASE_URL ?>/categories" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
</div>

<div class="glass-card" style="max-width: 800px;">
    <form action="<?= BASE_URL ?>/categories/update" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $category['id'] ?>">
        
        <div class="mb-3">
            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="<?= e($category['name']) ?>" required>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= e($category['description']) ?></textarea>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-warning">Update Category</button>
            <a href="<?= BASE_URL ?>/categories" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
