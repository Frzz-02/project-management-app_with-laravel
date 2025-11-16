/**
 * Unassigned Dashboard Auto-Check Script
 * 
 * Automatically checks if user has been assigned to a project every 60 seconds.
 * When assignment detected, shows notification and redirects to member dashboard.
 */

// Check for assignment status
function checkAssignment() {
    fetch('/api/check-assignment', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_assignment) {
            // Show success notification
            showAssignmentNotification();
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = data.redirect_url || '/dashboard';
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error checking assignment:', error);
    });
}

// Show notification when assigned
function showAssignmentNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl z-50 animate-slide-in';
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-bold">🎉 Great News!</p>
                <p class="text-sm">You've been assigned to a project! Redirecting...</p>
            </div>
        </div>
    `;
    document.body.appendChild(notification);
}

// FAQ Accordion Toggle
function initFAQAccordion() {
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const isActive = this.classList.contains('active');
            
            // Close all FAQs
            faqToggles.forEach(t => {
                t.classList.remove('active');
                t.nextElementSibling.style.display = 'none';
            });
            
            // Toggle current FAQ
            if (!isActive) {
                this.classList.add('active');
                content.style.display = 'block';
            }
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Unassigned Dashboard initialized');
    
    // Initialize FAQ accordion
    initFAQAccordion();
    
    // Check assignment immediately
    checkAssignment();
    
    // Then check every 60 seconds (60000 milliseconds)
    setInterval(checkAssignment, 60000);
    
    console.log('Auto-check running every 60 seconds');
});

// Add animation style
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
`;
document.head.appendChild(style);
