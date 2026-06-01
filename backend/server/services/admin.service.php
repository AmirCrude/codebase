<?php
require_once __DIR__ . '/../configs/database.config.php';
require_once __DIR__ . '/../database/queries/admin.query.php';
require_once __DIR__ . '/../database/queries/clearance.query.php';

class AdminService {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getDashboardStats() {
        $stmt = $this->conn->query(AdminQueries::$getDashboardStats);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function overrideClearance($itemId, $adminId, $newStatus, $reason, $ipAddress) {
        // Get current status
        $stmt = $this->conn->prepare(ClearanceQueries::$getItemById);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            throw new Exception('Clearance item not found');
        }
        
        $previousStatus = $item['status'];
        
        // Update the item
        $stmt = $this->conn->prepare(ClearanceQueries::$updateItemStatus);
        $stmt->bindParam(':status', $newStatus);
        $remarks = "Overridden by Admin: " . $reason;
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':officer_id', $adminId);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();
        
        // Record the override
        $stmt = $this->conn->prepare(AdminQueries::$createOverride);
        $stmt->bindParam(':item_id', $itemId);
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':prev_status', $previousStatus);
        $stmt->bindParam(':new_status', $newStatus);
        $stmt->bindParam(':reason', $reason);
        $stmt->execute();
        
        // Audit log
        $stmt = $this->conn->prepare(AdminQueries::$insertAuditLog);
        $action = 'override_clearance';
        $details = "Override item #{$itemId} from '{$previousStatus}' to '{$newStatus}'. Reason: {$reason}";
        $stmt->bindParam(':user_id', $adminId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':details', $details);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->execute();
        
        return ['message' => 'Clearance overridden successfully'];
    }
    
    public function getOverrideHistory() {
        $stmt = $this->conn->query(AdminQueries::$getOverrideHistory);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>