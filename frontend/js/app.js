// ============================================
// MAIN APP INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    Auth.updateNavbar();
    
    // Mobile menu toggle
    const toggleBtn = document.querySelector('.mobile-toggle');
    const navLinks = document.getElementById('navLinks');
    
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
});