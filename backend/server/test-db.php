<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/configs/database.config.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $users = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM students");
    $students = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM clearance_sessions");
    $sessions = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connected!',
        'users' => $users['count'],
        'students' => $students['count'],
        'sessions' => $sessions['count']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>