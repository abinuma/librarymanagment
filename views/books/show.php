<?php
 * Book Detail View
$pageTitle = 'Book Details';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-journal-text text-info"></i> Book Details</h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/books/edit/<?= $book['id'] ?>" class="btn btn-warning btn-sm">
            Edit
        </a>
        <a href="<?= BASE_URL ?>/books" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-3"><?= e($book['title']) ?></h5>
            <div class="detail-row">
                <span class="detail-label">Author</span>
                <span class="detail-value"><?= e($book['author']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ISBN</span>
                <span class="detail-value"><code><?= e($book['isbn']) ?></code></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Category</span>
                <span class="detail-value"><span class="badge bg-primary"><?= e($book['category_name']) ?></span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Shelf Number</span>
                <span class="detail-value"><?= e($book['shelf_number'] ?? 'N/A') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Published Year</span>
                <span class="detail-value"><?= e($book['published_year'] ?? 'N/A') ?></span>
            </div>
            <?php if (!empty($book['description'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value"><?= e($book['description']) ?></span>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label">Added On</span>
                <span class="detail-value"><?= formatDate($book['created_at']) ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="glass-card text-center">
            <div class="mb-3">
                <i class="bi bi-journal-bookmark-fill" style="font-size:3rem;color:var(--primary-light)"></i>
            </div>
            <h6 class="text-muted mb-2">Availability</h6>
            <h2 class="mb-1"><?= $book['available_copies'] ?> <small class="text-muted fs-6">/ <?= $book['quantity'] ?></small></h2>
            <p class="text-muted small">copies available</p>
            <?php if ($book['available_copies'] > 0): ?>
                <span class="badge bg-success">In Stock</span>
            <?php else: ?>
                <span class="badge bg-danger">Out of Stock</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
