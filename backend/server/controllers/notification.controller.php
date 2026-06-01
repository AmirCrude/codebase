<?php
require_once __DIR__ . '/../services/notification.service.php';
require_once __DIR__ . '/../utils/response.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $notificationService = new NotificationService();
    
    switch ($action) {
        case 'list':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $userId = $_GET['user_id'] ?? 1;
            $notifications = $notificationService->getUserNotifications($userId);
            $unreadCount = $notificationService->getUnreadCount($userId);
            
            Response::success([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            break;
            
        case 'read':
            if ($method !== 'PUT' && $method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['user_id'] ?? 1;
            
            if (isset($data['id'])) {
                $notificationService->markAsRead($data['id'], $userId);
            } else {
                $notificationService->markAllAsRead($userId);
            }
            
            Response::success(null, 'Notifications marked as read');
            break;
            
        default:
            Response::error('Endpoint not found', 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}
?>