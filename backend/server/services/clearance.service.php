<?php
require_once __DIR__ . '/../configs/database.config.php';
require_once __DIR__ . '/../database/queries/clearance.query.php';
require_once __DIR__ . '/../database/queries/notification.query.php';
require_once __DIR__ . '/../configs/email.config.php';

class ClearanceService {
    private $conn;
    
    private $officers = [
        'department_head',
        'faculty_dean',
        'dormitory_chief',
        'library_chief',
        'bookstore_keeper',
        'student_service_officer',
        'sports_master'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function initiateClearance($userId, $academicYear) {
        // Get student profile
        $stmt = $this->conn->prepare(ClearanceQueries::$getStudentProfile);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            throw new Exception('Student profile not found');
        }
        
        // Check for active session
        $stmt = $this->conn->prepare(ClearanceQueries::$getActiveSession);
        $stmt->bindParam(':student_id', $student['id']);
        $stmt->execute();
        $activeSession = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($activeSession) {
            throw new Exception('You already have an active clearance session. Please wait for it to be completed.');
        }
        
        // Create clearance session
        $stmt = $this->conn->prepare(ClearanceQueries::$createSession);
        $stmt->bindParam(':student_id', $student['id']);
        $stmt->bindParam(':academic_year', $academicYear);
        $stmt->execute();
        $sessionId = $this->conn->lastInsertId();
        
        // Create clearance items for all 7 officers
        foreach ($this->officers as $officer) {
            $stmt = $this->conn->prepare(ClearanceQueries::$createItem);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->bindParam(':officer_role', $officer);
            $stmt->execute();
        }
        
        // Notify all officers
        $this->notifyOfficers($sessionId);
        
        return [
            'session_id' => $sessionId,
            'message' => 'Clearance initiated successfully. All verifying officers have been notified.',
            'officers_count' => count($this->officers)
        ];
    }
    
    private function notifyOfficers($sessionId) {
        foreach ($this->officers as $officerRole) {
            $stmt = $this->conn->prepare(NotificationQueries::$getUserByRole);
            $stmt->bindParam(':role', $officerRole);
            $stmt->execute();
            $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($officers as $officer) {
                $message = "New clearance request requires your attention. Session #" . $sessionId;
                $stmt = $this->conn->prepare(NotificationQueries::$createNotification);
                $type = 'in_app';
                $stmt->bindParam(':recipient_id', $officer['id']);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
        }
    }
    
    public function getStudentClearanceStatus($userId) {
        $stmt = $this->conn->prepare(ClearanceQueries::$getStudentSessions);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($sessions as $session) {
            $stmt = $this->conn->prepare(ClearanceQueries::$getSessionItems);
            $stmt->bindParam(':session_id', $session['id']);
            $stmt->execute();
            $session['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result[] = $session;
        }
        
        return $result;
    }
    
    public function getPendingByRole($officerRole) {
        $stmt = $this->conn->prepare(ClearanceQueries::$getPendingByRole);
        $stmt->bindParam(':officer_role', $officerRole);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllByRole($officerRole) {
        $stmt = $this->conn->prepare(ClearanceQueries::$getAllByRole);
        $stmt->bindParam(':officer_role', $officerRole);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function approveClearance($itemId, $officerId, $remarks) {
        $stmt = $this->conn->prepare(ClearanceQueries::$updateItemStatus);
        $status = 'cleared';
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':officer_id', $officerId);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        
        // Check if all items are cleared
        $stmt = $this->conn->prepare(ClearanceQueries::$getItemById);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->checkAndUpdateSessionStatus($item['session_id']);
        $this->notifyStudent($item['session_id'], $item['officer_role'], 'cleared');
        
        return ['message' => 'Clearance approved successfully'];
    }
    
    public function rejectClearance($itemId, $officerId, $remarks) {
        $stmt = $this->conn->prepare(ClearanceQueries::$updateItemStatus);
        $status = 'rejected';
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':officer_id', $officerId);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        
        $stmt = $this->conn->prepare(ClearanceQueries::$getItemById);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->notifyStudent($item['session_id'], $item['officer_role'], 'rejected');
        
        return ['message' => 'Clearance rejected with remarks'];
    }
    
    private function checkAndUpdateSessionStatus($sessionId) {
        $stmt = $this->conn->prepare(ClearanceQueries::$checkAllCleared);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0 && $result['total'] == $result['cleared_count']) {
            $status = 'fully_cleared';
            $stmt = $this->conn->prepare(ClearanceQueries::$updateSessionStatus);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $sessionId);
            $stmt->execute();
            
            // Notify FAR
            $this->notifyFAR($sessionId);
        } elseif ($result['cleared_count'] > 0) {
            $status = 'partially_cleared';
            $stmt = $this->conn->prepare(ClearanceQueries::$updateSessionStatus);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $sessionId);
            $stmt->execute();
        }
    }
    
    private function notifyStudent($sessionId, $officerRole, $status) {
        $stmt = $this->conn->prepare(ClearanceQueries::$getItemById);
        // Need to get student from session
        $stmt = $this->conn->prepare("SELECT s.user_id, u.email, u.full_name FROM clearance_sessions cs 
            JOIN students s ON cs.student_id = s.id 
            JOIN users u ON s.user_id = u.id 
            WHERE cs.id = :session_id");
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            $officerName = ucfirst(str_replace('_', ' ', $officerRole));
            $message = "Your clearance from {$officerName} has been {$status}.";
            
            $stmt = $this->conn->prepare(NotificationQueries::$createNotification);
            $type = 'both';
            $stmt->bindParam(':recipient_id', $student['user_id']);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            
            EmailConfig::sendClearanceUpdate($student['email'], $student['full_name'], $officerRole, $status);
        }
    }
    
    private function notifyFAR($sessionId) {
        $stmt = $this->conn->prepare(NotificationQueries::$getUserByRole);
        $role = 'faculty_assistant_registrar';
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $fars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($fars as $far) {
            $message = "Session #{$sessionId} is ready for final approval. All officers have cleared.";
            $stmt = $this->conn->prepare(NotificationQueries::$createNotification);
            $type = 'in_app';
            $stmt->bindParam(':recipient_id', $far['id']);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
        }
    }
    
    public function finalApproval($sessionId, $officerId) {
        $stmt = $this->conn->prepare(ClearanceQueries::$checkAllCleared);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] == 0 || $result['total'] != $result['cleared_count']) {
            throw new Exception('Not all officers have cleared this student. Cannot give final approval.');
        }
        
        $stmt = $this->conn->prepare(ClearanceQueries::$finalApproval);
        $stmt->bindParam(':officer_id', $officerId);
        $stmt->bindParam(':id', $sessionId);
        $stmt->execute();
        
        return ['message' => 'Final approval granted. Student is now fully cleared.'];
    }
    
    public function getAggregatedData() {
        $stmt = $this->conn->query(ClearanceQueries::$getAggregatedData);
        $facultyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->conn->query(ClearanceQueries::$getUnitWisePerformance);
        $unitData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'faculty_summary' => $facultyData,
            'unit_performance' => $unitData
        ];
    }
    
    public function searchStudent($search) {
        $searchParam = "%{$search}%";
        $stmt = $this->conn->prepare(ClearanceQueries::$searchStudentClearance);
        $stmt->bindParam(':search', $searchParam);
        $stmt->bindParam(':search2', $searchParam);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get items for each session
        foreach ($result as &$session) {
            $stmt = $this->conn->prepare(ClearanceQueries::$getSessionItems);
            $stmt->bindParam(':session_id', $session['id']);
            $stmt->execute();
            $session['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $result;
    }
    
    public function getFarReviewData() {
        $stmt = $this->conn->query(ClearanceQueries::$getFarReviewData);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>