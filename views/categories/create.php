<?php
/**
 * Create Category View
 */
$pageTitle = 'Create Category';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-tags-fill text-primary"></i> Create New Category</h4>
    <a href="<?= BASE_URL ?>/categories" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
</div>

<div class="glass-card" style="max-width: 800px;">
    <form action="<?= BASE_URL ?>/categories/store" method="POST">
        <?= csrfField() ?>
        
        <div class="mb-3">
            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="<?= e(old('name')) ?>" required>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= e(old('description')) ?></textarea>
            <div class="form-text text-secondary">Brief description of the types of books in this category.</div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Category</button>
            <a href="<?= BASE_URL ?>/categories" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<?php clearOldInput(); ?>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
