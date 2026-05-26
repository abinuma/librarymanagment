<?php
/**
 * Books Index View - List all books with search/filter/pagination
 */
$pageTitle = 'Books';
require VIEW_PATH . '/layouts/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-journal-bookmark-fill text-primary"></i> Book Management</h4>
    <a href="<?= BASE_URL ?>/books/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Book
    </a>
</div>

<!-- Search & Filters -->
<div class="table-container mb-4 slide-in">
    <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="bi bi-funnel-fill text-primary me-2"></i> Search & Filters</h5>
        <?php if (!empty($search) || ($categoryId > 0) || ($availability !== 'all') || ($shelf !== '')): ?>
            <a href="<?= BASE_URL ?>/books" class="btn btn-outline-light btn-sm">
                <i class="bi bi-x-circle me-1"></i> Clear All Filters
            </a>
        <?php endif; ?>
    </div>
    <div class="p-4 p-md-5">
        <form method="GET" action="<?= BASE_URL ?>/books" id="filterForm">
            <div class="row g-4">
                <!-- Search Bar -->
                <div class="col-12">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-search text-primary me-1"></i> Search Query</span>
                        <span class="text-muted small" data-bs-toggle="tooltip" title="Search supports matching partial text across Book Title, Author Name, and ISBN">
                            <i class="bi bi-info-circle me-1"></i> Searchable fields
                        </span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-muted border-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control form-control-lg" name="search" placeholder="Search by title, author, ISBN..." value="<?= e($search ?? '') ?>">
                        <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-search me-1"></i> Search</button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-grid text-primary me-1"></i> Category</label>
                    <select class="form-select form-select-lg" name="category" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($categoryId ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Availability Filter -->
                <div class="col-md-4">
                    <label class="form-label">Availability</label>
                    <select class="form-select form-select-lg" name="availability" onchange="this.form.submit()">
                        <option value="all" <?= ($availability ?? 'all') === 'all' ? 'selected' : '' ?>>All Books</option>
                        <option value="available" <?= ($availability ?? 'all') === 'available' ? 'selected' : '' ?>>Available Only</option>
                        <option value="unavailable" <?= ($availability ?? 'all') === 'unavailable' ? 'selected' : '' ?>>No Available Copies</option>
                    </select>
                </div>

                <!-- Searchable Shelf Dropdown Filter -->
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-bookshelf text-primary me-1"></i> Shelf Location</label>
                    <div class="dropdown">
                        <input type="hidden" name="shelf" id="selectedShelfInput" value="<?= e($shelf ?? '') ?>">
                        <button class="btn btn-outline-light dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start form-select-lg" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255, 255, 255, 0.18); padding: 0.8rem 1rem; font-size: 1.05rem;">
                            <span id="selectedShelfText"><?= !empty($shelf) ? 'Shelf ' . e($shelf) : 'All Shelves' ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-dark p-2 shadow w-100 shelf-dropdown-menu" style="background: var(--bg-sidebar); border: 1px solid var(--border); max-height: 350px; overflow-y: auto; z-index: 1050;">
                            <div class="p-1 mb-2">
                                <input type="text" class="form-control form-control-sm" id="shelfSearchInput" placeholder="Type to search shelves..." onclick="event.stopPropagation();" onkeyup="filterShelves(this.value, event);">
                            </div>
                            <ul class="list-unstyled mb-0" id="shelfListOptions">
                                <li>
                                    <button type="button" class="dropdown-item rounded py-2.5 px-3 shelf-option <?= empty($shelf) ? 'active bg-primary' : '' ?>" data-value="" onclick="selectShelf('')">
                                        <i class="bi bi-collection me-2"></i> All Shelves
                                    </button>
                                </li>
                                <?php foreach ($shelves as $sh): ?>
                                    <li>
                                        <button type="button" class="dropdown-item rounded py-2.5 px-3 shelf-option <?= ($shelf ?? '') === $sh ? 'active bg-primary' : '' ?>" data-value="<?= e($sh) ?>" onclick="selectShelf('<?= e($sh) ?>')">
                                            <i class="bi bi-bookmark me-2"></i> Shelf <?= e($sh) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function filterShelves(val, event) {
    event.stopPropagation();
    const query = val.toLowerCase().trim();
    const items = document.querySelectorAll('#shelfListOptions li');
    items.forEach(li => {
        const button = li.querySelector('button');
        if (!button) return;
        const text = button.textContent.toLowerCase();
        if (text.includes(query)) {
            li.style.display = '';
        } else {
            li.style.display = 'none';
        }
    });
}

function selectShelf(val) {
    document.getElementById('selectedShelfInput').value = val;
    document.getElementById('filterForm').submit();
}
</script>

<!-- Books Table -->
<div class="table-container">
    <div class="table-header">
        <h5>All Books <span class="badge bg-primary ms-2"><?= $pagination['total'] ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Shelf</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-journal-x d-block"></i>
                                <p>No books found</p>
                                <a href="<?= BASE_URL ?>/books/create" class="btn btn-primary btn-sm">Add First Book</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $i => $book): ?>
                        <tr>
                            <td><?= ($pagination['current_page'] - 1) * $pagination['per_page'] + $i + 1 ?></td>
                            <td><strong><?= e($book['title']) ?></strong></td>
                            <td><?= e($book['author']) ?></td>
                            <td><code><?= e($book['isbn']) ?></code></td>
                            <td><span class="badge bg-primary bg-opacity-25 text-primary"><?= e($book['category_name']) ?></span></td>
                            <td>
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="badge bg-success"><?= $book['available_copies'] ?> / <?= $book['quantity'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">0 / <?= $book['quantity'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($book['shelf_number'] ?? 'N/A') ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/books/show/<?= $book['id'] ?>" class="btn btn-outline-light btn-sm" data-bs-toggle="tooltip" title="View">
                                        View
                                    </a>
                                    <a href="<?= BASE_URL ?>/books/edit/<?= $book['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                                        Edit
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-name="<?= e($book['title']) ?>"
                                            data-action="<?= BASE_URL ?>/books/delete/<?= $book['id'] ?>">
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
            <?= paginationHtml($pagination['current_page'], $pagination['total_pages'], BASE_URL . '/books') ?>
        </div>
    <?php endif; ?>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
