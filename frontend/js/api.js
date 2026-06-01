// ============================================
// API HELPER FUNCTIONS
// ============================================

class API {
    static async request(endpoint, method = 'GET', data = null) {
        const url = CONFIG.API_BASE_URL + endpoint;
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // Auth endpoints
    static async login(email, password) {
        return await this.request('/auth?action=login', 'POST', { email, password });
    }
    
    static async register(userData) {
        return await this.request('/auth?action=register', 'POST', userData);
    }
    
    static async logout() {
        return await this.request('/auth?action=logout', 'POST');
    }
    
    static async getCurrentUser() {
        return await this.request('/auth?action=me', 'GET');
    }
    
    // Clearance endpoints
    static async initiateClearance(academicYear, userId) {
        return await this.request('/clearance?action=initiate', 'POST', { 
            academic_year: academicYear, 
            user_id: userId 
        });
    }
    
    static async getMyClearanceStatus(userId) {
        return await this.request(`/clearance?action=my-status&user_id=${userId}`, 'GET');
    }
    
    static async getPendingByRole(role) {
        return await this.request(`/clearance?action=pending&role=${role}`, 'GET');
    }
    
    static async getAllByRole(role) {
        return await this.request(`/clearance?action=all-by-role&role=${role}`, 'GET');
    }
    
    static async approveClearance(itemId, remarks = '', officerId = 3) {
        return await this.request('/clearance?action=approve', 'POST', { 
            item_id: itemId, 
            remarks: remarks,
            officer_id: officerId 
        });
    }
    
    static async rejectClearance(itemId, remarks, officerId = 3) {
        return await this.request('/clearance?action=reject', 'POST', { 
            item_id: itemId, 
            remarks: remarks,
            officer_id: officerId 
        });
    }
    
    static async finalApprove(sessionId, officerId) {
        return await this.request('/clearance?action=final-approve', 'POST', { 
            session_id: sessionId,
            officer_id: officerId 
        });
    }
    
    static async getFarReviewData() {
        return await this.request('/clearance?action=far-review', 'GET');
    }
    
    static async getAggregatedData() {
        return await this.request('/clearance?action=aggregated-data', 'GET');
    }
    
    static async searchStudent(search) {
        return await this.request(`/clearance?action=search-student&search=${encodeURIComponent(search)}`, 'GET');
    }
    
    // Notification endpoints
    static async getNotifications(userId) {
        return await this.request(`/notifications?action=list&user_id=${userId}`, 'GET');
    }
    
    static async markNotificationRead(id, userId) {
        return await this.request('/notifications?action=read', 'PUT', { id, user_id: userId });
    }
    
    // Admin endpoints
    static async getAdminDashboard() {
        return await this.request('/admin?action=dashboard', 'GET');
    }
    
    static async getAllUsers() {
        return await this.request('/admin?action=users', 'GET');
    }
    
    static async updateUserStatus(userId, status) {
        return await this.request('/admin?action=update-user-status', 'PUT', { 
            user_id: userId, 
            status: status 
        });
    }
    
    static async overrideClearance(itemId, newStatus, reason, adminId) {
        return await this.request('/admin?action=override', 'POST', { 
            item_id: itemId, 
            new_status: newStatus, 
            reason: reason,
            admin_id: adminId 
        });
    }
}