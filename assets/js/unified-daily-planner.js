// Global timer storage
const slaTimers = {};

// Global modal close function
function hideClosestModal(element) {
    const modal = element.closest('.modal-overlay') || element.closest('.notification');
    if (modal && modal.parentElement) {
        modal.remove();
    }
}

// Define pauseTask function globally
window.pauseTask = function(taskId) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    const currentStatus = taskCard?.dataset.status;
    
    if (currentStatus !== 'in_progress') {
        showNotification(`Cannot pause task. Status: ${currentStatus}. Must be 'in_progress'.`, 'error');
        return;
    }
    
    // IMMEDIATE status change for instant visual feedback
    if (window.setImmediateStatus) {
        window.setImmediateStatus(taskId, 'on_break');
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/ergon-site/api/daily_planner_workflow.php?action=pause', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ task_id: parseInt(taskId, 10), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            taskCard.dataset.status = 'on_break';
            updateTaskUI(taskId, 'on_break');
            if (window.updateSLATimer) {
                window.updateSLATimer(taskId, 'on_break', data);
            }
            showNotification('Task paused', 'info');
        } else {
            showNotification('Failed to pause: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'error');
    });
};

window.resumeTask = function(taskId) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    const currentStatus = taskCard?.dataset.status;
    
    if (currentStatus !== 'on_break') {
        showNotification(`Cannot resume task. Status: ${currentStatus}. Must be 'on_break'.`, 'error');
        return;
    }
    
    // IMMEDIATE status change for instant visual feedback
    if (window.setImmediateStatus) {
        window.setImmediateStatus(taskId, 'in_progress');
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/ergon-site/api/daily_planner_workflow.php?action=resume', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ task_id: parseInt(taskId, 10), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            taskCard.dataset.status = 'in_progress';
            updateTaskUI(taskId, 'in_progress');
            if (window.updateSLATimer) {
                window.updateSLATimer(taskId, 'in_progress', data);
            }
            showNotification('Task resumed', 'success');
        } else {
            showNotification('Failed to resume: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'error');
    });
};

window.startTask = function(taskId) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    
    // IMMEDIATE status change for instant visual feedback
    if (window.setImmediateStatus) {
        window.setImmediateStatus(taskId, 'in_progress');
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/ergon-site/api/daily_planner_workflow.php?action=start', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ task_id: parseInt(taskId, 10), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            taskCard.dataset.status = 'in_progress';
            
            // Update progress display if progress was returned
            if (data.progress !== undefined && data.progress > 0) {
                const progressBar = taskCard.querySelector('.progress-fill');
                if (progressBar) {
                    progressBar.style.width = data.progress + '%';
                }
                
                const progressValue = taskCard.querySelector('.progress-value');
                if (progressValue) {
                    progressValue.textContent = data.progress + '%';
                }
            }
            
            updateTaskUI(taskId, 'in_progress');
            if (window.updateSLATimer) {
                window.updateSLATimer(taskId, 'in_progress', data);
            }
            showNotification('Task started', 'success');
        } else {
            showNotification('Failed to start: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'error');
    });
};

// UI update function
function updateTaskUI(taskId, newStatus) {
    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!taskCard) return;
    
    const statusBadge = taskCard.querySelector(`#status-${taskId}`);
    if (statusBadge) {
        statusBadge.textContent = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        statusBadge.className = `badge badge--${newStatus}`;
    }
    
    const actionsDiv = taskCard.querySelector(`#actions-${taskId}`);
    if (actionsDiv) {
        if (newStatus === 'in_progress') {
            actionsDiv.innerHTML = `
                <button class="btn btn--sm btn--warning" onclick="pauseTask(${taskId})" title="Take a break from this task">
                    <i class="bi bi-pause"></i> Break
                </button>
                <button class="btn btn--sm btn--primary" onclick="openProgressModal(${taskId}, 0, '${newStatus}')" title="Update task completion progress">
                    <i class="bi bi-percent"></i> Update Progress
                </button>
                <button class="btn btn--sm btn--secondary" onclick="postponeTask(${taskId})" title="Postpone task to another date">
                    <i class="bi bi-calendar-plus"></i> Postpone
                </button>
            `;
        } else if (newStatus === 'on_break') {
            actionsDiv.innerHTML = `
                <button class="btn btn--sm btn--success" onclick="resumeTask(${taskId})" title="Resume working on this task">
                    <i class="bi bi-play"></i> Resume
                </button>
                <button class="btn btn--sm btn--primary" onclick="openProgressModal(${taskId}, 0, '${newStatus}')" title="Update task completion progress">
                    <i class="bi bi-percent"></i> Update Progress
                </button>
                <button class="btn btn--sm btn--secondary" onclick="postponeTask(${taskId})" title="Postpone task to another date">
                    <i class="bi bi-calendar-plus"></i> Postpone
                </button>
            `;
        } else if (newStatus === 'postponed') {
            actionsDiv.innerHTML = `
                <span class="badge badge--warning"><i class="bi bi-calendar-plus"></i> Postponed</span>
            `;
        } else if (newStatus === 'completed') {
            actionsDiv.innerHTML = `
                <span class="badge badge--success"><i class="bi bi-check-circle"></i> Completed</span>
            `;
        }
    }
    
    const countdownLabel = taskCard.querySelector(`#countdown-${taskId} .countdown-label`);
    if (countdownLabel) {
        countdownLabel.textContent = newStatus === 'in_progress' ? 'Remaining' : (newStatus === 'on_break' ? 'Paused' : 'SLA Time');
    }
}

// Debounce mechanism to prevent multiple modal triggers
let modalDebounce = {};

window.openProgressModal = function(taskId, progress, status) {
    // Prevent multiple rapid clicks
    if (modalDebounce[taskId]) {
        return;
    }
    
    modalDebounce[taskId] = true;
    
    // Clear debounce after 1 second
    setTimeout(() => {
        modalDebounce[taskId] = false;
    }, 1000);
    
    currentTaskId = taskId;
    
    var slider = document.getElementById('progressSlider');
    var valueDisplay = document.getElementById('progressValue');
    var description = document.getElementById('progressDescription');
    var dialog = document.getElementById('progressDialog');
    
    if (!dialog) {
        console.error('Progress modal not found');
        return;
    }
    
    if (slider) slider.value = progress || 0;
    if (valueDisplay) valueDisplay.textContent = (progress || 0) + '%';
    if (description) description.value = '';
    if (dialog) dialog.style.display = 'flex';
    
    // Focus on description field
    setTimeout(() => {
        if (description) description.focus();
    }, 100);
};

// Helper function to set progress from preset buttons
window.setProgress = function(taskId, value) {
    const modal = document.getElementById('updateProgressModal');
    if (!modal) return;
    
    const progressInput = modal.querySelector('#selectedProgressPercentage');
    if (progressInput) {
        progressInput.value = value;
    }
    
    // Update button states
    const percentageBtns = modal.querySelectorAll('.percentage-btn');
    percentageBtns.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.percentage == value) {
            btn.classList.add('active');
        }
    });
};

window.updateTaskProgress = function(taskId) {
    const modal = document.getElementById('updateProgressModal');
    if (!modal) return;
    
    const taskIdInput = modal.querySelector('#updateTaskId');
    const progressInput = modal.querySelector('#selectedProgressPercentage');
    
    if (!taskIdInput || !progressInput) return;
    
    const actualTaskId = taskIdInput.value || taskId;
    const progress = parseInt(progressInput.value);
    
    // Validate progress
    if (isNaN(progress) || progress < 0 || progress > 100) {
        showNotification('Progress must be between 0 and 100', 'error');
        return;
    }
    
    // Determine status based on progress
    let status = 'in_progress';
    if (progress >= 100) {
        status = 'completed';
    } else if (progress === 0) {
        status = 'not_started';
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Close modal
    closeModal('updateProgressModal');
    
    // Send update to daily planner API
    fetch('/ergon-site/api/daily_planner_workflow.php?action=update-progress', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ 
            task_id: parseInt(actualTaskId), 
            progress: progress,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const taskCard = document.querySelector(`[data-task-id="${actualTaskId}"]`);
            if (taskCard) {
                taskCard.dataset.status = status;
                
                // Update progress bar
                const progressBar = taskCard.querySelector('.progress-fill');
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
                
                // Update progress value display
                const progressValue = taskCard.querySelector('.progress-value');
                if (progressValue) {
                    progressValue.textContent = progress + '%';
                }
                
                // Update status badge
                const statusBadge = taskCard.querySelector(`#status-${actualTaskId}`);
                if (statusBadge) {
                    statusBadge.textContent = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                    statusBadge.className = `badge badge--${status}`;
                }
                
                // Update actions based on new status
                updateTaskUI(actualTaskId, status);
                
                if (status === 'completed' && window.enhancedSLATimer) {
                    window.enhancedSLATimer.completeTask(actualTaskId);
                }
            }
            
            showNotification(`Task updated: ${progress}% - ${status.replace('_', ' ')}`, 'success');
        } else {
            showNotification('Failed to update progress: ' + (data.error || data.message), 'error');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'error');
    });
};

window.postponeTask = function(taskId) {
    const newDate = prompt('Enter new date (YYYY-MM-DD):');
    if (!newDate) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('/ergon-site/api/daily_planner_workflow.php?action=postpone', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            task_id: parseInt(taskId), 
            new_date: newDate,
            reason: 'Postponed via daily planner',
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
            if (taskCard) {
                taskCard.dataset.status = 'postponed';
                updateTaskUI(taskId, 'postponed');
                if (window.enhancedSLATimer) {
                    window.enhancedSLATimer.stopTimer(taskId);
                }
            }
            showNotification('Task postponed to ' + newDate, 'success');
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error postponing task: ' + error.message, 'error');
    });
};

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="hideClosestModal(this)">&times;</button>
        </div>
    `;
    
    // Add styles if not exists
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        // Use concatenated strings to avoid CSS parsing errors
        styles.textContent = '.notification { position: fixed; top: 20px; right: 20px; padding: 12px 16px; border-radius: 4px; color: white; z-index: 10000; max-width: 300px; animation: slideIn 0.3s ease; }' +
        '.notification--success { background: #10b981; }' +
        '.notification--error { background: #ef4444; }' +
        '.notification--info { background: #3b82f6; }' +
        '.notification-content { display: flex; justify-content: space-between; align-items: center; }' +
        '.notification-close { background: none; border: none; color: white; font-size: 18px; cursor: pointer; }' +
        '@keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }';
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

function refreshSLADashboard() {
    forceSLARefresh();
}

function forceSLARefresh() {
    return window.forceSLARefresh();
}

window.forceSLARefresh = function() {
    // Disable SLA dashboard refresh due to API issues
    resetSLAToDefaults();
};

// Helper function to update SLA dashboard from API data
function updateSLADashboardFromAPI(slaData) {
    const slaElements = {
        totalTime: document.querySelector('.sla-total-time'),
        usedTime: document.querySelector('.sla-used-time'),
        remainingTime: document.querySelector('.sla-remaining-time'),
        pauseTime: document.querySelector('.sla-pause-time')
    };
    
    if (slaElements.totalTime) slaElements.totalTime.textContent = slaData.total_sla_time || '00:00:00';
    if (slaElements.usedTime) slaElements.usedTime.textContent = slaData.total_time_used || '00:00:00';
    if (slaElements.remainingTime) slaElements.remainingTime.textContent = slaData.total_remaining_time || '00:00:00';
    if (slaElements.pauseTime) slaElements.pauseTime.textContent = slaData.total_pause_time || '00:00:00';
}

// Helper function to reset SLA values to defaults on error
function resetSLAToDefaults() {
    const slaElements = {
        totalTime: document.querySelector('.sla-total-time'),
        usedTime: document.querySelector('.sla-used-time'),
        remainingTime: document.querySelector('.sla-remaining-time'),
        pauseTime: document.querySelector('.sla-pause-time')
    };
    
    Object.values(slaElements).forEach(element => {
        if (element && element.textContent === 'Loading...') {
            element.textContent = '00:00:00';
        }
    });
}

function updateSLADashboard(slaData) {
    // Legacy function - redirect to new API-based update
    if (slaData && typeof slaData === 'object') {
        updateSLADashboardFromAPI(slaData);
    } else {
        // Fallback to manual calculation
        updateSLADashboard();
    }
}



// Compatibility functions
function pauseTask(taskId) { return window.pauseTask(taskId); }
function resumeTask(taskId) { return window.resumeTask(taskId); }
function startTask(taskId) { return window.startTask(taskId); }
function openProgressModal(taskId, progress, status) { return window.openProgressModal(taskId, progress, status); }
function postponeTask(taskId) { return window.postponeTask(taskId); }

// Add event listeners for percentage buttons
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('updateProgressModal');
    if (modal) {
        const percentageBtns = modal.querySelectorAll('.percentage-btn');
        percentageBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const percentage = this.dataset.percentage;
                setProgress(null, percentage);
            });
        });
        
        // Handle form submission
        const form = modal.querySelector('#updateProgressForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const taskIdInput = modal.querySelector('#updateTaskId');
                if (taskIdInput && taskIdInput.value) {
                    updateTaskProgress(taskIdInput.value);
                }
            });
        }
    }
});

