<?php
require_once __DIR__ . '/../configs/session.config.php';

class AuthMiddleware {
    public static function requireLogin() {
        SessionConfig::init();
        
        if (!SessionConfig::isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Authentication required. Please login.'
            ]);
            exit;
        }
        return SessionConfig::getUser();
    }
    
    public static function requireRole($allowedRoles) {
        $user = self::requireLogin();
        
        if (!in_array($user['role'], $allowedRoles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'You do not have permission to access this resource.'
            ]);
            exit;
        }
        return $user;
    }
}
?>