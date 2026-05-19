<?php
/**
 * Global Error & Exception Handler
 * 
 * Centralized interception of all uncaught exceptions and fatal errors.
 * Guarantees raw stack traces and SQL errors are never displayed to end users.
 * Logs full diagnostic context for developers.
 */

class ErrorHandler
{
    /**
     * Register global exception and error handlers
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Convert PHP errors to ErrorException to be handled uniformly
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting, or was silenced with @
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Intercept fatal errors on script shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE])) {
            self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    /**
     * Main exception handler
     */
    public static function handleException(Throwable $e): void
    {
        // 1. Log detailed error for developers
        self::logDeveloperDiagnostic($e);

        // 2. Clean buffer if any output started
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 3. Determine HTTP Status Code
        $statusCode = 500;
        if ($e instanceof ValidationException) {
            $statusCode = 422;
        } elseif ($e->getCode() >= 400 && $e->getCode() < 600) {
            $statusCode = (int) $e->getCode();
        }
        http_response_code($statusCode);

        // 4. Determine user message
        $userMessage = "An unexpected system error occurred. Please try again later.";
        $errTitle = "System Error";

        if ($e instanceof ValidationException) {
            $userMessage = implode('<br>', $e->getErrors());
            $errTitle = "Validation Failed";
        } elseif ($e instanceof DatabaseException) {
            $userMessage = "A database error occurred while processing your request. Our technical team has been notified.";
            $errTitle = "Database Transaction Error";
        } elseif ($e instanceof AppException) {
            $userMessage = $e->getMessage();
            $errTitle = "Application Notice";
        }

        // 5. Handle AJAX/JSON requests
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => [
                    'title' => $errTitle,
                    'message' => APP_ENV === 'development' ? $e->getMessage() : $userMessage,
                    'file' => APP_ENV === 'development' ? $e->getFile() : null,
                    'line' => APP_ENV === 'development' ? $e->getLine() : null,
                ]
            ]);
            exit;
        }

        // 6. Display appropriate HTML error page
        if (APP_ENV === 'development') {
            self::renderDevelopmentPage($e);
        } else {
            self::renderProductionPage($errTitle, $userMessage);
        }
        exit;
    }

    /**
     * Format and record detailed developer logs to error_log()
     */
    private static function logDeveloperDiagnostic(Throwable $e): void
    {
        $log = "\n=================================================================\n";
        $log .= sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), get_class($e), $e->getMessage());
        $log .= sprintf("Location: %s on line %d\n", $e->getFile(), $e->getLine());
        
        if ($e instanceof DatabaseException) {
            $log .= sprintf("SQL Query: %s\n", $e->getSqlQuery());
            $log .= sprintf("SQL Params: %s\n", json_encode($e->getSqlParams()));
        }

        if ($e instanceof AppException && !empty($e->getContext())) {
            $log .= sprintf("Exception Context: %s\n", json_encode($e->getContext()));
        }

        // Request details
        $sanitizedPost = $_POST;
        foreach (['password', 'password_confirmation', 'current_password', 'secret'] as $sensitive) {
            if (isset($sanitizedPost[$sensitive])) {
                $sanitizedPost[$sensitive] = '[REDACTED]';
            }
        }
        
        $log .= sprintf("Request URI: %s %s\n", $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN', $_SERVER['REQUEST_URI'] ?? 'UNKNOWN');
        $log .= sprintf("Request Data: %s\n", json_encode(['GET' => $_GET, 'POST' => $sanitizedPost]));
        $log .= "Stack Trace:\n" . $e->getTraceAsString() . "\n";
        $log .= "=================================================================\n";

        error_log($log);
    }

    /**
     * Render beautiful production error page (Generic & Safe)
     */
    private static function renderProductionPage(string $title, string $message): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($title) ?> | <?= APP_NAME ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
            <style>
                body { background-color: #121826; color: #e2e8f0; font-family: 'Inter', system-ui, -apple-system, sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
                .error-box { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1rem; padding: 3rem 2.5rem; max-width: 550px; width: 90%; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); }
                .error-icon { font-size: 4rem; color: #f43f5e; margin-bottom: 1.5rem; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
                <h2 class="mb-3 fw-bold"><?= htmlspecialchars($title) ?></h2>
                <p class="text-muted mb-4 fs-5"><?= $message ?></p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="javascript:history.back()" class="btn btn-outline-light px-4 py-2"><i class="bi bi-arrow-left me-2"></i>Go Back</a>
                    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary px-4 py-2"><i class="bi bi-house-door me-2"></i>Dashboard</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Render detailed developer diagnostic page (Development Mode Only)
     */
    private static function renderDevelopmentPage(Throwable $e): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Developer Exception | <?= get_class($e) ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background-color: #0f172a; color: #f8fafc; font-family: monospace, system-ui; padding: 2rem; }
                .debug-card { background: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; padding: 2rem; margin-bottom: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); }
                h1 { color: #f43f5e; font-weight: 800; font-size: 1.75rem; border-bottom: 2px solid #334155; padding-bottom: 1rem; margin-bottom: 1.5rem; }
                .code-box { background: #0b0f19; padding: 1rem; border-radius: 0.5rem; color: #38bdf8; overflow-x: auto; }
                .trace-box { background: #0b0f19; padding: 1.25rem; border-radius: 0.5rem; color: #94a3b8; font-size: 0.9rem; white-space: pre-wrap; overflow-x: auto; }
            </style>
        </head>
        <body>
            <div class="container-fluid max-w-7xl">
                <div class="debug-card">
                    <h1>[DEVELOPMENT MODE] <?= htmlspecialchars(get_class($e)) ?></h1>
                    <div class="mb-4">
                        <h5 class="text-slate-400 mb-1">Message:</h5>
                        <div class="code-box fs-5 fw-bold text-white"><?= htmlspecialchars($e->getMessage()) ?></div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <h5 class="text-slate-400 mb-1">File Location:</h5>
                            <div class="code-box"><?= htmlspecialchars($e->getFile()) ?> : <?= $e->getLine() ?></div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="text-slate-400 mb-1">Status Code:</h5>
                            <div class="code-box text-warning"><?= http_response_code() ?></div>
                        </div>
                    </div>
                    <?php if ($e instanceof DatabaseException): ?>
                        <div class="mb-4">
                            <h5 class="text-slate-400 mb-1">SQL Query:</h5>
                            <div class="code-box text-warning"><?= htmlspecialchars($e->getSqlQuery()) ?></div>
                            <h5 class="text-slate-400 mb-1 mt-2">SQL Parameters:</h5>
                            <div class="code-box text-success"><?= htmlspecialchars(json_encode($e->getSqlParams(), JSON_PRETTY_PRINT)) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mb-4">
                        <h5 class="text-slate-400 mb-2">Stack Trace:</h5>
                        <div class="trace-box"><?= htmlspecialchars($e->getTraceAsString()) ?></div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
