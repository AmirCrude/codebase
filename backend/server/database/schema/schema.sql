-- Clearance Management System Database Schema
-- Hawassa University

DROP DATABASE IF EXISTS clearance_system;
CREATE DATABASE clearance_system;
USE clearance_system;

-- Users table (all 12 actor types)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'department_head', 'faculty_dean', 'dormitory_chief', 
              'library_chief', 'bookstore_keeper', 'student_service_officer',
              'sports_master', 'faculty_assistant_registrar', 'data_analyst',
              'gate_security', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    dormitory_name VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Officers table
CREATE TABLE officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    officer_id VARCHAR(20) UNIQUE NOT NULL,
    unit_name VARCHAR(100) NOT NULL,
    role_type VARCHAR(50) NOT NULL,
    faculty VARCHAR(100),
    department VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Clearance sessions
CREATE TABLE clearance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    overall_status ENUM('pending', 'in_progress', 'partially_cleared', 
                        'fully_cleared', 'final_approved', 'rejected') DEFAULT 'pending',
    final_approved_by INT,
    final_approved_at TIMESTAMP NULL,
    initiated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (final_approved_by) REFERENCES users(id)
);

-- Clearance items (8 items per session - one per verifying officer)
CREATE TABLE clearance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    officer_role VARCHAR(50) NOT NULL,
    officer_id INT,
    status ENUM('pending', 'cleared', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    processed_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES clearance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (officer_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('email', 'in_app', 'both') DEFAULT 'in_app',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Audit logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Override records
CREATE TABLE override_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clearance_item_id INT NOT NULL,
    admin_id INT NOT NULL,
    previous_status VARCHAR(50) NOT NULL,
    new_status VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clearance_item_id) REFERENCES clearance_items(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Insert test data
-- Admin (password: admin123)
INSERT INTO users (full_name, email, password_hash, role) VALUES 
('System Administrator', 'admin@hu.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Student (password: student123)
INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Abebe Tesfaye', 'student@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'student');
INSERT INTO students (user_id, student_id, faculty, department, academic_year, dormitory_name, phone) VALUES 
(2, 'HU/2023/001', 'Engineering', 'Computer Science', '2025/2026', 'Block A', '0911223344');

-- Verifying Officers (password: officer123)
INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Dr. Kebede Alemu', 'depthead@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'department_head');
INSERT INTO officers (user_id, officer_id, unit_name, role_type, faculty, department) VALUES 
(3, 'DH001', 'Computer Science Department', 'department_head', 'Engineering', 'Computer Science');

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Dr. Tirunesh Haile', 'dean@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'faculty_dean');
INSERT INTO officers (user_id, officer_id, unit_name, role_type, faculty) VALUES 
(4, 'FD001', 'Engineering Faculty', 'faculty_dean', 'Engineering');

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Ato Bekele Tadesse', 'dormitory@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'dormitory_chief');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(5, 'DC001', 'Block A Dormitory', 'dormitory_chief', 'Block A');

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('W/ro Almaz Haile', 'library@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'library_chief');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(6, 'LC001', 'Main Library Circulation', 'library_chief', NULL);

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Ato Dawit Mengistu', 'bookstore@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'bookstore_keeper');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(7, 'BK001', 'Main Campus Bookstore', 'bookstore_keeper', NULL);

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('W/ro Selam Tesfaye', 'studentservice@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'student_service_officer');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(8, 'SS001', 'Student Services Office', 'student_service_officer', NULL);

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Ato Henok Girma', 'sports@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'sports_master');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(9, 'SM001', 'Sports Complex', 'sports_master', NULL);

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('W/ro Tigist Assefa', 'far@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'faculty_assistant_registrar');
INSERT INTO officers (user_id, officer_id, unit_name, role_type, faculty) VALUES 
(10, 'FAR001', 'Registrar Office', 'faculty_assistant_registrar', 'Engineering');

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Ato Yonas Alemu', 'dataanalyst@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'data_analyst');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(11, 'DA001', 'Data Analytics Unit', 'data_analyst', NULL);

INSERT INTO users (full_name, email, password_hash, role) VALUES 
('Ato Tesfaye Wolde', 'gatesecurity@hu.edu.et', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'gate_security');
INSERT INTO officers (user_id, officer_id, unit_name, role_type) VALUES 
(12, 'GS001', 'Main Gate', 'gate_security', NULL);