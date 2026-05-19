<?php
/**
 * Front Controller (Entry Point)
 * 
 * All requests are routed through this file via .htaccess.
 * Bootstraps the application: session, config, autoloading, routing.
 */

// ── Bootstrap ────────────────────────────────────────
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/flash.php';

// ── Session ──────────────────────────────────────────
session_name(SESSION_NAME);
session_start();

// ── Autoload classes ────────────────────────────────
$classDirs = [
    APP_PATH . '/exceptions/',
    APP_PATH . '/helpers/',
    APP_PATH . '/controllers/',
    APP_PATH . '/models/',
    APP_PATH . '/services/',
    APP_PATH . '/middleware/',
    APP_PATH . '/validators/',
];

foreach ($classDirs as $dir) {
    if (is_dir($dir)) {
        foreach (glob($dir . '*.php') as $file) {
            require_once $file;
        }
    }
}

// ── Initialize Global Error Handling ─────────────────
ErrorHandler::register();


// ── Load Routes ──────────────────────────────────────
$routes = require_once __DIR__ . '/../routes/web.php';

// ── Parse Request ────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

// Get the URI relative to the public directory
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = BASE_URL;

// Remove base path and query string
$uri = parse_url($requestUri, PHP_URL_PATH);
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$uri = '/' . ltrim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

// ── Route Matching ────────────────────────────────────
$id = null;

// Try exact match first
if (isset($routes[$method][$uri])) {
    $route = $routes[$method][$uri];
} else {
    // Try matching routes with ID parameter (e.g., /books/edit/5)
    $matched = false;
    foreach ($routes[$method] ?? [] as $pattern => $handler) {
        // Check if URI matches pattern with an ID suffix
        if (preg_match('#^' . preg_quote($pattern, '#') . '/(\d+)$#', $uri, $matches)) {
            $route = $handler;
            $id = (int) $matches[1];
            $matched = true;
            break;
        }
    }
    
    if (!$matched) {
        // Check for /index.php or root redirects
        if ($uri === '/index.php' || $uri === '') {
            redirect('/dashboard');
            exit;
        }
        
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 - Page Not Found</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '</head><body class="bg-dark text-white d-flex align-items-center justify-content-center" style="min-height:100vh">';
        echo '<div class="text-center"><h1 class="display-1">404</h1><p class="lead">Page not found</p>';
        echo '<a href="' . BASE_URL . '/dashboard" class="btn btn-primary">Go to Dashboard</a></div>';
        echo '</body></html>';
        exit;
    }
}

// ── Dispatch ─────────────────────────────────────────
[$controllerName, $actionName] = $route;

if (!class_exists($controllerName)) {
    die("Controller {$controllerName} not found.");
}

$controller = new $controllerName();

if (!method_exists($controller, $actionName)) {
    die("Action {$actionName} not found in {$controllerName}.");
}

// Call the controller action, passing ID if present
if ($id !== null) {
    $controller->$actionName($id);
} else {
    $controller->$actionName();
}
