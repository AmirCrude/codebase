<?php
require_once __DIR__ . '/../services/clearance.service.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validator.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $clearanceService = new ClearanceService();
    
    switch ($action) {
        case 'initiate':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['academic_year', 'user_id']);
            
            $result = $clearanceService->initiateClearance($data['user_id'], $data['academic_year']);
            Response::success($result, 'Clearance initiated');
            break;
            
        case 'my-status':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $userId = $_GET['user_id'] ?? '2';
            $result = $clearanceService->getStudentClearanceStatus($userId);
            Response::success($result);
            break;
            
        case 'pending':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $role = $_GET['role'] ?? 'department_head';
            $result = $clearanceService->getPendingByRole($role);
            Response::success($result);
            break;
            
        case 'all-by-role':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $role = $_GET['role'] ?? '';
            if (empty($role)) Response::error('Role parameter required');
            
            $result = $clearanceService->getAllByRole($role);
            Response::success($result);
            break;
            
        case 'approve':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['item_id', 'officer_id']);
            
            $result = $clearanceService->approveClearance(
                $data['item_id'], 
                $data['officer_id'], 
                $data['remarks'] ?? ''
            );
            Response::success($result, 'Clearance approved');
            break;
            
        case 'reject':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['item_id', 'remarks', 'officer_id']);
            
            $result = $clearanceService->rejectClearance(
                $data['item_id'], 
                $data['officer_id'], 
                $data['remarks']
            );
            Response::success($result, 'Clearance rejected');
            break;
            
        case 'final-approve':
            if ($method !== 'POST') Response::error('Method not allowed', 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            Validator::required($data, ['session_id', 'officer_id']);
            
            $result = $clearanceService->finalApproval($data['session_id'], $data['officer_id']);
            Response::success($result, 'Final approval granted');
            break;
            
        case 'far-review':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $result = $clearanceService->getFarReviewData();
            Response::success($result);
            break;
            
        case 'aggregated-data':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $result = $clearanceService->getAggregatedData();
            Response::success($result);
            break;
            
        case 'search-student':
            if ($method !== 'GET') Response::error('Method not allowed', 405);
            
            $search = $_GET['search'] ?? '';
            if (empty($search)) Response::error('Search parameter required');
            
            $result = $clearanceService->searchStudent($search);
            Response::success($result);
            break;
            
        default:
            Response::error('Endpoint not found: ' . $action, 404);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
?>