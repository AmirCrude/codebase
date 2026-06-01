<?php
class AdminQueries {
    public static $getDashboardStats = "SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM clearance_sessions) as total_sessions,
        (SELECT COUNT(*) FROM clearance_sessions WHERE overall_status = 'final_approved') as completed_sessions,
        (SELECT COUNT(*) FROM clearance_sessions WHERE overall_status IN ('pending','in_progress','partially_cleared','fully_cleared')) as pending_sessions";
    
    public static $insertAuditLog = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip_address)";
    
    public static $createOverride = "INSERT INTO override_records (clearance_item_id, admin_id, previous_status, new_status, reason) VALUES (:item_id, :admin_id, :prev_status, :new_status, :reason)";
    
    public static $getOverrideHistory = "SELECT ov.*, u.full_name as admin_name, ci.officer_role
        FROM override_records ov
        JOIN users u ON ov.admin_id = u.id
        JOIN clearance_items ci ON ov.clearance_item_id = ci.id
        ORDER BY ov.created_at DESC LIMIT 50";
}
?>