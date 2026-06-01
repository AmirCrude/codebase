<?php
require_once __DIR__ . '/../configs/database.config.php';
require_once __DIR__ . '/../database/queries/auth.query.php';

class AuthService {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($email, $password) {
        $stmt = $this->conn->prepare(AuthQueries::$checkEmail);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('Invalid email or password');
        }
        
        if ($user['status'] !== 'active') {
            throw new Exception('Account is not active. Contact administrator.');
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid email or password');
        }
        
        unset($user['password_hash']);
        
        $profile = null;
        if ($user['role'] === 'student') {
            $profile = $this->getStudentProfile($user['id']);
        } elseif ($user['role'] !== 'admin') {
            $profile = $this->getOfficerProfile($user['id']);
        }
        
        return [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status'],
            'profile' => $profile
        ];
    }
    
    public function register($data) {
        $stmt = $this->conn->prepare(AuthQueries::$checkEmail);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt = $this->conn->prepare(AuthQueries::$registerUser);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':role', $data['role']);
        $stmt->execute();
        
        $userId = $this->conn->lastInsertId();
        
        if ($data['role'] === 'student') {
            $stmt = $this->conn->prepare(AuthQueries::$registerStudent);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':student_id', $data['student_id']);
            $stmt->bindParam(':faculty', $data['faculty']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':academic_year', $data['academic_year']);
            $dormitory = $data['dormitory_name'] ?? null;
            $phone = $data['phone'] ?? null;
            $stmt->bindParam(':dormitory_name', $dormitory);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
        } elseif ($data['role'] !== 'admin') {
            $stmt = $this->conn->prepare(AuthQueries::$registerOfficer);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':officer_id', $data['officer_id']);
            $stmt->bindParam(':unit_name', $data['unit_name']);
            $stmt->bindParam(':role_type', $data['role']);
            $faculty = $data['faculty'] ?? null;
            $department = $data['department'] ?? null;
            $stmt->bindParam(':faculty', $faculty);
            $stmt->bindParam(':department', $department);
            $stmt->execute();
        }
        
        return ['id' => $userId, 'message' => 'Registration successful'];
    }
    
    private function getStudentProfile($userId) {
        $stmt = $this->conn->prepare(AuthQueries::$getStudentByUserId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getOfficerProfile($userId) {
        $stmt = $this->conn->prepare(AuthQueries::$getOfficerByUserId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserById($id) {
        $stmt = $this->conn->prepare(AuthQueries::$getUserById);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllUsers() {
        $stmt = $this->conn->query(AuthQueries::$getAllUsers);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateUserStatus($userId, $status) {
        $stmt = $this->conn->prepare(AuthQueries::$updateUserStatus);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
}
?>