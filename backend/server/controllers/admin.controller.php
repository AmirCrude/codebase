<?php
require_once __DIR__ . '/../services/admin.service.php';
require_once __DIR__ . '/../services/auth.service.php';
require_once __DIR__ . '/../services/clearance.service.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $adminService = new AdminService();
    
    switch ($action) {
        case 'dashboard':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $stats = $adminService->getDashboardStats();
            Response::success($stats);
            break;
            
        case 'users':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $authService = new AuthService();
            $users = $authService->getAllUsers();
            Response::success($users);
            break;
            
        case 'update-user-status':
            if ($method !== 'PUT' && $method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['user_id', 'status']);
            
            $authService = new AuthService();
            $authService->updateUserStatus($data['user_id'], $data['status']);
            
            Response::success(null, 'User status updated');
            break;
            
        case 'override':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['item_id', 'new_status', 'reason']);
            
            $adminId = $data['admin_id'] ?? 1;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $result = $adminService->overrideClearance(
                $data['item_id'],
                $adminId,
                $data['new_status'],
                $data['reason'],
                $ipAddress
            );
            
            Response::success($result, 'Clearance overridden');
            break;
            
        case 'override-history':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $history = $adminService->getOverrideHistory();
            Response::success($history);
            break;
            
        default:
            Response::error('Endpoint not found', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
?>