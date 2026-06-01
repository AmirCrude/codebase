<?php
class NotificationQueries {
    public static $createNotification = "INSERT INTO notifications (recipient_id, message, type) VALUES (:recipient_id, :message, :type)";
    
    public static $getUserNotifications = "SELECT * FROM notifications WHERE recipient_id = :recipient_id ORDER BY created_at DESC LIMIT 50";
    
    public static $getUnreadCount = "SELECT COUNT(*) as count FROM notifications WHERE recipient_id = :recipient_id AND is_read = 0";
    
    public static $markAsRead = "UPDATE notifications SET is_read = 1 WHERE id = :id AND recipient_id = :recipient_id";
    
    public static $markAllAsRead = "UPDATE notifications SET is_read = 1 WHERE recipient_id = :recipient_id AND is_read = 0";
    
    public static $getUserByRole = "SELECT id, full_name, email FROM users WHERE role = :role";
}
?>