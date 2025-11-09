/**
 * Modal helper functions to fix modal issues
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fix z-index issues with modals
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(function(modal) {
        // When modal is opened, make sure it's properly positioned and has correct z-index
        modal.addEventListener('show.bs.modal', function() {
            setTimeout(function() {
                document.body.classList.add('modal-open');
                const modalBackdrops = document.querySelectorAll('.modal-backdrop');
                modalBackdrops.forEach(function(backdrop) {
                    backdrop.style.zIndex = '1040';
                });
                modal.style.zIndex = '1045';
                modal.style.display = 'block';
            }, 10);
        });
        
        // When modal closes, make sure all modal classes are removed properly
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Remove any leftover backdrops
            const modalBackdrops = document.querySelectorAll('.modal-backdrop');
            modalBackdrops.forEach(function(backdrop) {
                backdrop.parentNode.removeChild(backdrop);
            });
        });
    });
});
