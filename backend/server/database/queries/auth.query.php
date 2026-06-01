<?php
class AuthQueries {
    public static $checkEmail = "SELECT id, email, password_hash, role, full_name, status FROM users WHERE email = :email";
    
    public static $registerUser = "INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)";
    
    public static $registerStudent = "INSERT INTO students (user_id, student_id, faculty, department, academic_year, dormitory_name, phone) VALUES (:user_id, :student_id, :faculty, :department, :academic_year, :dormitory_name, :phone)";
    
    public static $registerOfficer = "INSERT INTO officers (user_id, officer_id, unit_name, role_type, faculty, department) VALUES (:user_id, :officer_id, :unit_name, :role_type, :faculty, :department)";
    
    public static $getUserById = "SELECT id, full_name, email, role, status, created_at FROM users WHERE id = :id";
    
    public static $getStudentByUserId = "SELECT s.*, u.full_name, u.email, u.role FROM students s JOIN users u ON s.user_id = u.id WHERE u.id = :user_id";
    
    public static $getOfficerByUserId = "SELECT o.*, u.full_name, u.email, u.role FROM officers o JOIN users u ON o.user_id = u.id WHERE u.id = :user_id";
    
    public static $getAllUsers = "SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC";
    
    public static $updateUserStatus = "UPDATE users SET status = :status WHERE id = :id";
    
    public static $deleteUser = "DELETE FROM users WHERE id = :id";
}
?>