<?php
// Router for PHP built-in development server

$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://127.0.0.1:5500';

// Handle CORS preflight requests FIRST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit;
}

// Set CORS headers for all responses
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Route API requests
if (strpos($path, '/api/auth') === 0) {
    $_GET['action'] = $_GET['action'] ?? '';
    require_once __DIR__ . '/controllers/auth.controller.php';
} elseif (strpos($path, '/api/clearance') === 0) {
    $_GET['action'] = $_GET['action'] ?? '';
    require_once __DIR__ . '/controllers/clearance.controller.php';
} elseif (strpos($path, '/api/notifications') === 0) {
    $_GET['action'] = $_GET['action'] ?? '';
    require_once __DIR__ . '/controllers/notification.controller.php';
} elseif (strpos($path, '/api/admin') === 0) {
    $_GET['action'] = $_GET['action'] ?? '';
    require_once __DIR__ . '/controllers/admin.controller.php';
} else {
    // For all other requests, let PHP's built-in server handle static files
    // This allows direct access to .php files like test-db.php
    return false;
}
?>