<?php
/**
 * Validation Exception
 * 
 * Thrown when business rules or input validation constraints fail.
 */

class ValidationException extends AppException
{
    protected array $errors = [];

    public function __construct(array|string $errors, string $message = "Validation failed", int $code = 422, ?Throwable $previous = null)
    {
        $errorList = is_string($errors) ? [$errors] : $errors;
        parent::__construct(is_string($errors) ? $errors : $message, $code, $previous, ['validation_errors' => $errorList]);
        $this->errors = $errorList;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
