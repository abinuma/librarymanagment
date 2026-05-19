<?php
/**
 * CategoryValidator - Validates book category input data
 */

class CategoryValidator
{
    public static function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } else {
            $categoryModel = new Category();
            $all = $categoryModel->getAll();
            foreach ($all as $cat) {
                if (strtolower($cat['name']) === strtolower($name) && (int)$cat['id'] !== (int)$editId) {
                    $errors[] = 'A category with this name already exists.';
                    break;
                }
            }
        }

        return $errors;
    }

    public static function validateOrThrow(array $data, ?int $editId = null): array
    {
        $errors = self::validate($data, $editId);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        return $data;
    }
}
