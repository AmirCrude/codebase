<?php
require_once __DIR__ . '/../configs/database.config.php';
require_once __DIR__ . '/../database/queries/notification.query.php';

class NotificationService {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getUserNotifications($userId) {
        $stmt = $this->conn->prepare(NotificationQueries::$getUserNotifications);
        $stmt->bindParam(':recipient_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadCount($userId) {
        $stmt = $this->conn->prepare(NotificationQueries::$getUnreadCount);
        $stmt->bindParam(':recipient_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->conn->prepare(NotificationQueries::$markAsRead);
        $stmt->bindParam(':id', $notificationId);
        $stmt->bindParam(':recipient_id', $userId);
        return $stmt->execute();
    }
    
    public function markAllAsRead($userId) {
        $stmt = $this->conn->prepare(NotificationQueries::$markAllAsRead);
        $stmt->bindParam(':recipient_id', $userId);
        return $stmt->execute();
    }
}
?>