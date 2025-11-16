/**
 * Member Dashboard JavaScript
 * Handles Start/Pause task actions dan real-time timer updates
 */

// Start task - begin working and start timer
function startTask(cardId) {
    if (confirm('Start working on this task? Timer will begin tracking your time.')) {
        fetch(`/member/tasks/${cardId}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Show success message
            showNotification('Task started! Timer is running. ⏱️', 'success');
            
            // Reload page after 1 second
            setTimeout(() => {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to start task. Please try again.', 'error');
        });
    }
}

// Pause task - stop timer
function pauseTask(cardId) {
    if (confirm('Pause this task? Your time will be saved.')) {
        fetch(`/member/tasks/${cardId}/pause`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            showNotification('Task paused. Take a break! ☕', 'success');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to pause task. Please try again.', 'error');
        });
    }
}

// Show notification helper
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform translate-x-0 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    } text-white font-medium`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Update all timers every second
function updateTimers() {
    // Update individual task timers
    document.querySelectorAll('[id^="timer-"]').forEach(timerElement => {
        const startTime = timerElement.dataset.startTime;
        if (startTime) {
            const elapsed = Math.floor((Date.now() - new Date(startTime)) / 1000);
            const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            timerElement.textContent = `${hours}:${minutes}:${seconds}`;
        }
    });
    
    // Update active timer in work summary
    const activeTimer = document.getElementById('active-timer');
    if (activeTimer && activeTimer.dataset.startTime) {
        const startTime = activeTimer.dataset.startTime;
        const elapsed = Math.floor((Date.now() - new Date(startTime)) / 1000);
        const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        activeTimer.textContent = `${hours}:${minutes}:${seconds}`;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set start time data attributes for timers
    document.querySelectorAll('[id^="timer-"]').forEach(timerElement => {
        const timeText = timerElement.textContent.trim();
        if (timeText) {
            // Parse H:i:s format and calculate start time
            const [hours, minutes, seconds] = timeText.split(':').map(Number);
            const elapsedMs = (hours * 3600 + minutes * 60 + seconds) * 1000;
            const startTime = new Date(Date.now() - elapsedMs);
            timerElement.dataset.startTime = startTime.toISOString();
        }
    });
    
    const activeTimer = document.getElementById('active-timer');
    if (activeTimer) {
        const timeText = activeTimer.textContent.trim();
        if (timeText) {
            const [hours, minutes, seconds] = timeText.split(':').map(Number);
            const elapsedMs = (hours * 3600 + minutes * 60 + seconds) * 1000;
            const startTime = new Date(Date.now() - elapsedMs);
            activeTimer.dataset.startTime = startTime.toISOString();
        }
    }
    
    // Update timers every second
    setInterval(updateTimers, 1000);
    
    console.log('Member Dashboard initialized');
});
