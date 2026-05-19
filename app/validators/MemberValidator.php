<?php
/**
 * MemberValidator - Validates member input data
 */

class MemberValidator
{
    public static function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        if (empty(trim($data['full_name'] ?? ''))) {
            $errors[] = 'Full name is required.';
        }
        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } else {
            $memberModel = new Member();
            $existing = $memberModel->findByEmail(trim($data['email']));
            if ($existing && (int)$existing['id'] !== (int)$editId) {
                $errors[] = 'A member with this email already exists.';
            }
        }
        if (empty(trim($data['student_id'] ?? ''))) {
            $errors[] = 'Student ID is required.';
        } else {
            $memberModel = new Member();
            $existing = $memberModel->findByStudentId(trim($data['student_id']));
            if ($existing && (int)$existing['id'] !== (int)$editId) {
                $errors[] = 'A member with this Student ID already exists.';
            }
        }
        if (!empty($data['phone']) && !preg_match('/^[\+\d\s\-\(\)]{7,20}$/', $data['phone'])) {
            $errors[] = 'Invalid phone number format.';
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
