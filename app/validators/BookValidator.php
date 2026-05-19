<?php
/**
 * BookValidator - Validates book input data
 */

class BookValidator
{
    public static function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        if (empty(trim($data['title'] ?? ''))) {
            $errors[] = 'Title is required.';
        }
        if (empty(trim($data['author'] ?? ''))) {
            $errors[] = 'Author is required.';
        }
        if (empty(trim($data['isbn'] ?? ''))) {
            $errors[] = 'ISBN is required.';
        } else {
            $bookModel = new Book();
            $existing = $bookModel->findByIsbn(trim($data['isbn']));
            if ($existing && (int)$existing['id'] !== (int)$editId) {
                $errors[] = 'A book with this ISBN already exists.';
            }
        }
        if (empty($data['category_id'])) {
            $errors[] = 'Category is required.';
        }
        if (!isset($data['quantity']) || (int)$data['quantity'] < 1) {
            $errors[] = 'Quantity must be at least 1.';
        }
        if (!empty($data['published_year'])) {
            $year = (int)$data['published_year'];
            if ($year < 1000 || $year > (int)date('Y')) {
                $errors[] = 'Published year is invalid (must be between 1000 and current year).';
            }
        }

        return $errors;
    }

    /**
     * Validates input data and throws ValidationException on failure
     */
    public static function validateOrThrow(array $data, ?int $editId = null): array
    {
        $errors = self::validate($data, $editId);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        return $data;
    }
}
