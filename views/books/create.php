<?php
/**
 * Create Book View
 */
$pageTitle = 'Add Book';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-plus-circle text-success"></i> Add New Book</h4>
    <a href="<?= BASE_URL ?>/books" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Books
    </a>
</div>

<div class="glass-card" style="max-width:800px">
    <form method="POST" action="<?= BASE_URL ?>/books/store" class="needs-validation" novalidate>
        <?= csrfField() ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="<?= e(old('title')) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?= e(old('isbn')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="author" name="author" value="<?= e(old('author')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= e(old('quantity', '1')) ?>" min="1" required>
            </div>
            <div class="col-md-4">
                <label for="shelf_number" class="form-label">Shelf Number</label>
                <input type="text" class="form-control" id="shelf_number" name="shelf_number" value="<?= e(old('shelf_number')) ?>" placeholder="e.g. A-01">
            </div>
            <div class="col-md-4">
                <label for="published_year" class="form-label">Published Year</label>
                <input type="number" class="form-control" id="published_year" name="published_year" value="<?= e(old('published_year')) ?>" min="1000" max="<?= date('Y') ?>">
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= e(old('description')) ?></textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Book</button>
            <a href="<?= BASE_URL ?>/books" class="btn btn-outline-light">Cancel</a>
        </div>
    </form>
</div>

<?php clearOldInput(); ?>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
