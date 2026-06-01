<?php
class ClearanceQueries {
    public static $createSession = "INSERT INTO clearance_sessions (student_id, academic_year, overall_status) VALUES (:student_id, :academic_year, 'in_progress')";
    
    public static $createItem = "INSERT INTO clearance_items (session_id, officer_role) VALUES (:session_id, :officer_role)";
    
    public static $getStudentProfile = "SELECT id FROM students WHERE user_id = :user_id LIMIT 1";
    
    public static $getOfficerProfile = "SELECT id, role_type, unit_name FROM officers WHERE user_id = :user_id LIMIT 1";
    
    public static $getActiveSession = "SELECT id FROM clearance_sessions WHERE student_id = :student_id AND overall_status NOT IN ('final_approved', 'rejected') ORDER BY initiated_at DESC LIMIT 1";
    
    public static $getStudentSessions = "SELECT cs.*, s.student_id, s.faculty, s.department 
        FROM clearance_sessions cs 
        JOIN students s ON cs.student_id = s.id 
        WHERE s.user_id = :user_id 
        ORDER BY cs.initiated_at DESC";
    
    public static $getSessionItems = "SELECT ci.*, u.full_name as officer_name 
        FROM clearance_items ci 
        LEFT JOIN users u ON ci.officer_id = u.id 
        WHERE ci.session_id = :session_id 
        ORDER BY ci.id";
    
    public static $getPendingByRole = "SELECT ci.id as item_id, ci.officer_role, ci.status, ci.remarks,
        cs.id as session_id, cs.academic_year, cs.overall_status as session_status,
        s.student_id, s.faculty, s.department, s.phone,
        u.full_name as student_name, u.email as student_email
        FROM clearance_items ci 
        JOIN clearance_sessions cs ON ci.session_id = cs.id 
        JOIN students s ON cs.student_id = s.id 
        JOIN users u ON s.user_id = u.id 
        WHERE ci.officer_role = :officer_role AND ci.status = 'pending'
        ORDER BY cs.initiated_at ASC";
    
    public static $getAllByRole = "SELECT ci.id as item_id, ci.officer_role, ci.status, ci.remarks,
        cs.id as session_id, cs.academic_year, cs.overall_status as session_status,
        s.student_id, s.faculty, s.department,
        u.full_name as student_name
        FROM clearance_items ci 
        JOIN clearance_sessions cs ON ci.session_id = cs.id 
        JOIN students s ON cs.student_id = s.id 
        JOIN users u ON s.user_id = u.id 
        WHERE ci.officer_role = :officer_role
        ORDER BY ci.updated_at DESC";
    
    public static $updateItemStatus = "UPDATE clearance_items 
        SET status = :status, remarks = :remarks, officer_id = :officer_id, processed_at = NOW() 
        WHERE id = :id";
    
    public static $getItemById = "SELECT * FROM clearance_items WHERE id = :id";
    
    public static $checkAllCleared = "SELECT COUNT(*) as total, 
        SUM(CASE WHEN status = 'cleared' THEN 1 ELSE 0 END) as cleared_count 
        FROM clearance_items WHERE session_id = :session_id";
    
    public static $updateSessionStatus = "UPDATE clearance_sessions SET overall_status = :status WHERE id = :id";
    
    public static $finalApproval = "UPDATE clearance_sessions 
        SET overall_status = 'final_approved', final_approved_by = :officer_id, 
        final_approved_at = NOW(), completed_at = NOW() 
        WHERE id = :id";
    
    public static $getStudentById = "SELECT u.email, u.full_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = :student_id";
    
    public static $getAggregatedData = "SELECT 
        s.faculty, s.department, cs.academic_year,
        COUNT(DISTINCT cs.id) as total_sessions,
        SUM(CASE WHEN cs.overall_status = 'final_approved' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN cs.overall_status IN ('pending','in_progress','partially_cleared','fully_cleared') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN cs.overall_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, cs.initiated_at, cs.completed_at)), 1) as avg_completion_hours
        FROM clearance_sessions cs 
        JOIN students s ON cs.student_id = s.id 
        GROUP BY s.faculty, s.department, cs.academic_year
        ORDER BY s.faculty, s.department";
    
    public static $getUnitWisePerformance = "SELECT 
        ci.officer_role,
        COUNT(*) as total_processed,
        SUM(CASE WHEN ci.status = 'cleared' THEN 1 ELSE 0 END) as cleared,
        SUM(CASE WHEN ci.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN ci.status = 'pending' THEN 1 ELSE 0 END) as pending,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, cs.initiated_at, ci.processed_at)), 1) as avg_processing_hours
        FROM clearance_items ci
        JOIN clearance_sessions cs ON ci.session_id = cs.id
        WHERE ci.status != 'pending'
        GROUP BY ci.officer_role
        ORDER BY ci.officer_role";
    
    public static $searchStudentClearance = "SELECT cs.*, s.student_id, s.faculty, s.department, 
        u.full_name as student_name, u.email
        FROM clearance_sessions cs 
        JOIN students s ON cs.student_id = s.id 
        JOIN users u ON s.user_id = u.id 
        WHERE s.student_id LIKE :search OR u.full_name LIKE :search2
        ORDER BY cs.initiated_at DESC LIMIT 10";
    
    public static $getFarReviewData = "SELECT cs.*, s.student_id, s.faculty, s.department, 
        u.full_name as student_name, u.email,
        (SELECT COUNT(*) FROM clearance_items ci WHERE ci.session_id = cs.id AND ci.status = 'cleared') as cleared_count,
        (SELECT COUNT(*) FROM clearance_items ci WHERE ci.session_id = cs.id) as total_count
        FROM clearance_sessions cs 
        JOIN students s ON cs.student_id = s.id 
        JOIN users u ON s.user_id = u.id 
        WHERE cs.overall_status = 'fully_cleared'
        ORDER BY cs.initiated_at ASC";
}
?>