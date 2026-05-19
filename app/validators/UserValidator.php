<?php
/**
 * UserValidator - Validates system user account data
 */

class UserValidator
{
    public static function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        if ($editId === null) {
            // New user validation
            if (empty(trim($data['username'] ?? ''))) {
                $errors[] = 'Username is required.';
            } else {
                $userModel = new User();
                $existing = $userModel->findByUsername(trim($data['username']));
                if ($existing && (int)$existing['id'] !== (int)$editId) {
                    $errors[] = 'This username is already taken.';
                }
            }
            if (empty($data['password'])) {
                $errors[] = 'Password is required for new accounts.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }
        }

        if (empty(trim($data['full_name'] ?? ''))) {
            $errors[] = 'Full name is required.';
        }

        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address format.';
        }

        if (!empty($data['role']) && !in_array($data['role'], ['admin', 'librarian'])) {
            $errors[] = 'Selected role is invalid.';
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
