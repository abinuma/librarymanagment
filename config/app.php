<?php
// ==========================================
// app.php
// ==========================================
/**
 * Application Configuration
 * 
 * Central configuration constants for the application.
 */

// Application
define('APP_NAME', 'LibraryMS');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // 'development' or 'production'

// Base URL - adjust if your project is in a subdirectory
define('BASE_URL', '/php/library_managment_system/public');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Session
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'LMS_SESSION');

// Pagination
define('RECORDS_PER_PAGE', 10);

// FIX: Added missing constants for fine calculation used in views
define('FINE_PER_DAY', 1.00); // Default fine per day (can be overridden by settings)
define('BORROW_DURATION_DAYS', 14); // Default borrow duration (can be overridden by settings)
define('MAX_BORROW_LIMIT', 5); // Default max borrow limit (can be overridden by settings)

// Borrow rules are now fetched dynamically via the setting() helper
// using the 'borrow_duration_days', 'fine_per_day', and 'max_borrow_limit' keys.

// Date format
define('DATE_FORMAT', 'Y-m-d');
define('DISPLAY_DATE_FORMAT', 'M d, Y');

// Error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}