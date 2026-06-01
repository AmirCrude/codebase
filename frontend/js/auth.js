// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

class Auth {
    static async login(email, password) {
        try {
            const response = await API.login(email, password);
            if (response.success) {
                // Store user data in sessionStorage
                sessionStorage.setItem('user', JSON.stringify(response.data.user));
                sessionStorage.setItem('unreadCount', response.data.unread_notifications || 0);
                
                // Redirect to dashboard
                this.redirectToDashboard(response.data.user.role);
                return response;
            }
        } catch (error) {
            throw error;
        }
    }
    
    static async register(userData) {
        try {
            const response = await API.register(userData);
            return response;
        } catch (error) {
            throw error;
        }
    }
    
    static async logout() {
        try {
            await API.logout();
        } catch (e) {
            // Ignore errors during logout
        }
        sessionStorage.clear();
        // Go back to root login page
        window.location.href = '../login.html';
    }
    
    static getCurrentUser() {
        const userData = sessionStorage.getItem('user');
        return userData ? JSON.parse(userData) : null;
    }
    
    static isLoggedIn() {
        return this.getCurrentUser() !== null;
    }
    
    static getUserRole() {
        const user = this.getCurrentUser();
        return user ? user.role : null;
    }
    
    static redirectToDashboard(role) {
        // Determine if we're already in the pages folder or at root
        const isInPagesFolder = window.location.pathname.includes('/pages/');
        const prefix = isInPagesFolder ? '' : 'pages/';
        
        const dashboardMap = {
            'student': 'student-dashboard.html',
            'department_head': 'department-head-dashboard.html',
            'faculty_dean': 'faculty-dean-dashboard.html',
            'dormitory_chief': 'dormitory-chief-dashboard.html',
            'library_chief': 'library-chief-dashboard.html',
            'bookstore_keeper': 'bookstore-keeper-dashboard.html',
            'student_service_officer': 'student-service-dashboard.html',
            'sports_master': 'sports-master-dashboard.html',
            'faculty_assistant_registrar': 'far-dashboard.html',
            'data_analyst': 'data-analyst-dashboard.html',
            'gate_security': 'gate-security-dashboard.html',
            'admin': 'admin-dashboard.html'
        };
        
        const url = prefix + (dashboardMap[role] || 'student-dashboard.html');
        window.location.href = url;
    }
    
    static checkAuth() {
        if (!this.isLoggedIn()) {
            window.location.href = '../login.html';
            return false;
        }
        return true;
    }
    
    static checkRole(allowedRoles) {
        const user = this.getCurrentUser();
        if (!user || !allowedRoles.includes(user.role)) {
            alert('You do not have permission to access this page.');
            window.location.href = '../login.html';
            return false;
        }
        return true;
    }
    
    static updateNavbar() {
        const user = this.getCurrentUser();
        const loginLink = document.getElementById('loginLink');
        const registerLink = document.getElementById('registerLink');
        const dashboardLink = document.getElementById('dashboardLink');
        const logoutLink = document.getElementById('logoutLink');
        const userInfo = document.getElementById('userInfo');
        
        if (user) {
            if (loginLink) loginLink.style.display = 'none';
            if (registerLink) registerLink.style.display = 'none';
            if (dashboardLink) {
                dashboardLink.style.display = 'inline';
                dashboardLink.textContent = 'Dashboard';
            }
            if (logoutLink) logoutLink.style.display = 'inline';
            if (userInfo) {
                userInfo.innerHTML = `
                    <span class="nav-role-badge">${CONFIG.OFFICER_LABELS[user.role] || user.role}</span>
                    <span>${user.full_name}</span>
                `;
                userInfo.style.display = 'flex';
            }
        }
    }
}

// Auto-update navbar on page load
document.addEventListener('DOMContentLoaded', () => {
    Auth.updateNavbar();
});

// Global logout function
function logout() {
    Auth.logout();
}