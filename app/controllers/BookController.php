<?php

//BookController - CRUD operations for books
 

class BookController
{
    private Book $bookModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->bookModel = new Book();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $page = max(1, (int)queryParam('page', '1'));
            $search = queryParam('search');
            $categoryId = (int)queryParam('category', '0');
            $availability = queryParam('availability', 'all');
            $shelf = queryParam('shelf', '');

            $result = $this->bookModel->getPaginated($page, RECORDS_PER_PAGE, $search, $categoryId, $availability, $shelf);
            $categories = $this->categoryModel->getAll();
            $shelves = $this->bookModel->getDistinctShelves();
            $books = $result['data'];
            $pagination = $result;
            require VIEW_PATH . '/books/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading books index page");
            errorResponse('Unable to load books listing at this time. Please try again later.', '/dashboard');
        }
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();
        try {
            $categories = $this->categoryModel->getAll();
            require VIEW_PATH . '/books/create.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading book creation form");
            errorResponse('Unable to load form right now.', '/books');
        }
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            BookValidator::validateOrThrow($_POST);

            $this->bookModel->create([
                'title'          => sanitize($_POST['title']),
                'author'         => sanitize($_POST['author']),
                'isbn'           => sanitize($_POST['isbn']),
                'category_id'    => (int)$_POST['category_id'],
                'quantity'       => (int)$_POST['quantity'],
                'shelf_number'   => sanitize($_POST['shelf_number'] ?? ''),
                'published_year' => !empty($_POST['published_year']) ? (int)$_POST['published_year'] : null,
                'description'    => sanitize($_POST['description'] ?? ''),
            ]);

            clearOldInput();
            successResponse('Book added successfully.', '/books');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/books/create');
        } catch (Throwable $e) {
            logAppError($e, "Failed to save new book");
            errorResponse('Unable to save book right now. Please verify inputs or contact support.', '/books/create');
        }
    }

    public function show(int $id): void
    {
        AuthMiddleware::requireAuth();
        try {
            $book = $this->bookModel->findById($id);
            if (!$book) {
                errorResponse('Book not found.', '/books');
                return;
            }
            require VIEW_PATH . '/books/show.php';
        } catch (Throwable $e) {
            logAppError($e, "Error displaying book details for ID {$id}");
            errorResponse('Unable to load book details right now.', '/books');
        }
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();
        try {
            $book = $this->bookModel->findById($id);
            if (!$book) {
                errorResponse('Book not found.', '/books');
                return;
            }
            $categories = $this->categoryModel->getAll();
            require VIEW_PATH . '/books/edit.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading book edit form for ID {$id}");
            errorResponse('Unable to load form right now.', '/books');
        }
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        AuthMiddleware::verifyCsrf();

        try {
            BookValidator::validateOrThrow($_POST, $id);

            $this->bookModel->update($id, [
                'title'          => sanitize($_POST['title']),
                'author'         => sanitize($_POST['author']),
                'isbn'           => sanitize($_POST['isbn']),
                'category_id'    => (int)$_POST['category_id'],
                'quantity'       => (int)$_POST['quantity'],
                'shelf_number'   => sanitize($_POST['shelf_number'] ?? ''),
                'published_year' => !empty($_POST['published_year']) ? (int)$_POST['published_year'] : null,
                'description'    => sanitize($_POST['description'] ?? ''),
            ]);

            clearOldInput();
            successResponse('Book updated successfully.', '/books');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/books/edit/' . $id);
        } catch (Throwable $e) {
            logAppError($e, "Failed to update book ID {$id}");
            errorResponse('Unable to update book right now. Please try again.', '/books/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        try {
            if (!$this->bookModel->delete($id)) {
                errorResponse('Cannot delete book. It may have active borrow transactions.', '/books');
            } else {
                successResponse('Book deleted successfully.', '/books');
            }
        } catch (Throwable $e) {
            logAppError($e, "Failed to delete book ID {$id}");
            errorResponse('Unable to process deletion at this time.', '/books');
        }
    }
}