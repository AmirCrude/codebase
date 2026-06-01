// ============================================
// CONFIGURATION
// ============================================

const CONFIG = {
    API_BASE_URL: 'http://localhost:8000/api',
    // Change this to your actual backend URL
    // API_BASE_URL: 'http://localhost/clearance-system/backend',
    
    OFFICER_ROLES: [
        'department_head',
        'faculty_dean', 
        'dormitory_chief',
        'library_chief',
        'bookstore_keeper',
        'student_service_officer',
        'sports_master'
    ],
    
    OFFICER_LABELS: {
        'department_head': 'Department Head',
        'faculty_dean': 'Faculty Dean',
        'dormitory_chief': 'Dormitory Chief',
        'library_chief': 'Library Chief of Circulation',
        'bookstore_keeper': 'College Bookstore Keeper',
        'student_service_officer': 'Student Service Officer',
        'sports_master': 'Sports Master',
        'faculty_assistant_registrar': 'Faculty Assistant Registrar',
        'data_analyst': 'Data Analyst',
        'gate_security': 'Gate Security',
        'admin': 'System Administrator'
    },
    
    STATUS_LABELS: {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'partially_cleared': 'Partially Cleared',
        'fully_cleared': 'Fully Cleared',
        'final_approved': 'Final Approved',
        'rejected': 'Rejected',
        'cleared': 'Cleared'
    },
    
    STATUS_CLASSES: {
        'pending': 'badge-pending',
        'in_progress': 'badge-in-progress',
        'cleared': 'badge-cleared',
        'rejected': 'badge-rejected',
        'final_approved': 'badge-final-approved'
    },
    
    FACULTIES: [
        'Engineering',
        'Business and Economics',
        'Natural Sciences',
        'Social Sciences',
        'Law',
        'Medicine',
        'Agriculture'
    ]
};