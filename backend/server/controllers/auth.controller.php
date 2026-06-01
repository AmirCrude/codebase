<?php
require_once __DIR__ . '/../services/auth.service.php';
require_once __DIR__ . '/../services/notification.service.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['email', 'password']);
            
            $authService = new AuthService();
            $user = $authService->login($data['email'], $data['password']);
            
            // Get notification count
            $notificationService = new NotificationService();
            $unreadCount = $notificationService->getUnreadCount($user['id']);
            
            Response::success([
                'user' => $user,
                'token' => bin2hex(random_bytes(32)),
                'unread_notifications' => $unreadCount
            ], 'Login successful');
            break;
            
        case 'register':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $requiredFields = ['full_name', 'email', 'password', 'role'];
            
            if ($data['role'] === 'student') {
                $requiredFields = array_merge($requiredFields, ['student_id', 'faculty', 'department', 'academic_year']);
            } elseif ($data['role'] !== 'admin') {
                $requiredFields = array_merge($requiredFields, ['officer_id', 'unit_name']);
            }
            
            Validator::required($data, $requiredFields);
            Validator::email($data['email']);
            Validator::minLength($data['password'], 6, 'Password');
            
            $authService = new AuthService();
            $result = $authService->register($data);
            
            Response::success($result, 'Registration successful', 201);
            break;
            
        case 'logout':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            Response::success(null, 'Logged out successfully');
            break;
            
        case 'me':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            // For now, return mock user data since we removed sessions
            // In production, validate the token from Authorization header
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? '';
            
            if (empty($authHeader)) {
                Response::error('Not authenticated', 401);
            }
            
            // Token validation would go here
            // For now, return success to avoid blocking the frontend
            Response::success([
                'user' => [
                    'id' => 1,
                    'full_name' => 'User',
                    'email' => 'user@hu.edu.et',
                    'role' => 'student'
                ],
                'unread_notifications' => 0
            ]);
            break;
            
        default:
            Response::error('Endpoint not found', 404);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    Response::error($e->getMessage(), $code);
}
?>