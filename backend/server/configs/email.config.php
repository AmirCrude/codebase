<?php
class EmailConfig {
    public static function sendEmail($to, $subject, $body) {
        // Skip email sending in local development
        error_log("Email would be sent to: $to - Subject: $subject");
        return true;
    }
    
    public static function sendClearanceUpdate($to, $studentName, $officerRole, $status) {
        $subject = "Clearance Update - " . ucfirst(str_replace('_', ' ', $officerRole));
        
        // Log instead of sending (development mode)
        error_log("=== CLEARANCE EMAIL ===");
        error_log("To: $to");
        error_log("Officer: $officerRole");
        error_log("Status: $status");
        error_log("======================");
        
        return true;
    }
}
?>