/**
 * FeriWebDevProject - User functions and utilities
 * 
 * General functions for website operation
 */

document.addEventListener('DOMContentLoaded', function() {
    // Only run the check if the user is logged in
    if (document.body.classList.contains('user-logged-in')) {
        // Initial check
        checkUserStatus();
        
        // Regular check (every 30 seconds)
        setInterval(checkUserStatus, 30000);
    }

    // Fridge - Add new item function
    setupFridgeItemForm();
});

/**
 * Check user status
 * If the user has been banned, logs them out and redirects
 */
function checkUserStatus() {
    fetch('/api/user/status')
        .then(response => {
            if (!response.ok) {
                throw new Error('A network error occurred');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.is_banned) {
                // User has been banned, log them out
                window.location.href = '/login?banned=1';
            }
        })
        .catch(error => {
            console.error('Error checking user status:', error);
        });
}

/**
 * Refrigerator - form handling
 * Checks if the page contains a refrigerator management form, and if so,
 * calls the initializing function in fridgeFunctions.js
 */
function setupFridgeItemForm() {
    // Check if we're on the fridge page
    if (document.getElementById('addItemForm')) {
        // Check if fridgeFunctions.js has been loaded
        if (typeof initFridgeModal === 'undefined') {
            // If fridgeFunctions.js hasn't been loaded yet, import it dynamically
            import('./fridgeFunctions.js')
                .then(module => {
                    // The module has loaded, but no need to call initFridgeModal,
                    // as fridgeFunctions.js initializes automatically when loaded
                    console.log('fridgeFunctions.js successfully loaded');
                })
                .catch(error => {
                    console.error('Error loading fridgeFunctions.js:', error);
                });
        }
    }
}
