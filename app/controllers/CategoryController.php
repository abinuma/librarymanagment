<?php
/**
 * CategoryController - Manage Book Categories
 */

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        try {
            $categories = $this->categoryModel->getAllWithBookCount();
            require VIEW_PATH . '/categories/index.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading categories index");
            errorResponse('Unable to load categories listing at this time.', '/dashboard');
        }
    }

    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        require VIEW_PATH . '/categories/create.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        try {
            CategoryValidator::validateOrThrow($_POST);

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            $this->categoryModel->create(['name' => sanitize($name), 'description' => sanitize($description)]);

            clearOldInput();
            successResponse('Category created successfully.', '/categories');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/categories/create');
        } catch (Throwable $e) {
            logAppError($e, "Failed to create category");
            errorResponse('Unable to save category right now. Please verify inputs or contact support.', '/categories/create');
        }
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAdmin();
        try {
            $category = $this->categoryModel->findById($id);
            if (!$category) {
                errorResponse('Category not found.', '/categories');
                return;
            }
            require VIEW_PATH . '/categories/edit.php';
        } catch (Throwable $e) {
            logAppError($e, "Error loading category edit form for ID {$id}");
            errorResponse('Unable to load category details right now.', '/categories');
        }
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        try {
            CategoryValidator::validateOrThrow($_POST, $id);

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            $this->categoryModel->update($id, ['name' => sanitize($name), 'description' => sanitize($description)]);

            successResponse('Category updated successfully.', '/categories');
        } catch (ValidationException $e) {
            errorResponse($e->getErrors(), '/categories/edit/' . $id);
        } catch (Throwable $e) {
            logAppError($e, "Failed to update category ID {$id}");
            errorResponse('Unable to update category right now.', '/categories/edit/' . $id);
        }
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        try {
            if ($this->categoryModel->delete($id)) {
                successResponse('Category deleted successfully.', '/categories');
            } else {
                errorResponse('Cannot delete category because it currently contains books.', '/categories');
            }
        } catch (Throwable $e) {
            logAppError($e, "Failed to delete category ID {$id}");
            errorResponse('Unable to delete category at this time.', '/categories');
        }
    }
}
