<?php
/**
 * Database Exception
 * 
 * Thrown when database queries, connections, or constraints fail.
 * Wraps PDOExceptions and SQL errors cleanly.
 */

class DatabaseException extends AppException
{
    private string $sqlQuery;
    private array $sqlParams;

    public function __construct(string $message, int $code = 500, ?Throwable $previous = null, string $sqlQuery = '', array $sqlParams = [])
    {
        parent::__construct($message, $code, $previous, [
            'sql_query' => $sqlQuery,
            'sql_params' => $sqlParams
        ]);
        $this->sqlQuery = $sqlQuery;
        $this->sqlParams = $sqlParams;
    }

    public function getSqlQuery(): string
    {
        return $this->sqlQuery;
    }

    public function getSqlParams(): array
    {
        return $this->sqlParams;
    }
}
