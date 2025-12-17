/**
 * Complete SLA Timer - Final Implementation
 * Handles all timer functionality with proper error handling and debouncing
 */

window.SLATimer = {
    timers: {},
    debounce: {},
    
    init: function() {
        console.log('Initializing SLA Timer...');
        
        // Find all task cards
        const taskCards = document.querySelectorAll('.task-card[data-task-id]');
        console.log(`Found ${taskCards.length} task cards`);
        
        if (taskCards.length === 0) {
            console.warn('No task cards found! Retrying in 2 seconds...');
            setTimeout(() => this.init(), 2000);
            return;
        }
        
        taskCards.forEach(card => {
            const taskId = card.dataset.taskId;
            if (taskId) {
                // Start timer for ALL tasks, not just validated ones
                this.startTimer(taskId);
                console.log(`Timer started for task ${taskId}, status: ${card.dataset.status}`);
            }
        });
        
        // Single event delegation with debouncing
        document.addEventListener('click', this.handleButtonClick.bind(this));
        
        console.log('SLA Timer initialization complete!');
    },
    
    validateTaskData: function(card) {
        const required = ['taskId', 'status', 'slaDuration', 'activeSeconds', 'pauseDuration'];
        return required.every(attr => card.dataset[attr] !== undefined);
    },
    
    handleButtonClick: function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        
        const taskCard = btn.closest('.task-card');
        if (!taskCard) return;
        
        const taskId = taskCard.dataset.taskId;
        if (!taskId) return;
        
        // Prevent multiple rapid clicks
        if (this.debounce[taskId]) return;
        
        if (btn.textContent.includes('Start')) {
            e.preventDefault();
            e.stopPropagation();
            this.startTask(taskId);
            return false;
        }
        
        if (btn.textContent.includes('Break')) {
            e.preventDefault();
            e.stopPropagation();
            this.pauseTask(taskId);
            return false;
        }
        
        if (btn.textContent.includes('Resume')) {
            e.preventDefault();
            e.stopPropagation();
            this.resumeTask(taskId);
            return false;
        }
    },
    
    startTimer: function(taskId) {
        // Always clear existing timer first
        if (this.timers[taskId]) {
            clearInterval(this.timers[taskId]);
            delete this.timers[taskId];
        }
        
        // Start new timer with immediate update
        this.timers[taskId] = setInterval(() => {
            this.updateDisplay(taskId);
        }, 1000);
        
        // Immediate first update
        this.updateDisplay(taskId);
        console.log(`⏰ Timer restarted for task ${taskId}`);
    },
    
    updateDisplay: function(taskId) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        const countdownEl = document.querySelector(`#countdown-${taskId} .countdown-display`);
        
        if (!card) {
            console.log(`Card not found for task ${taskId}`);
            return;
        }
        if (!countdownEl) {
            console.log(`Countdown element not found for task ${taskId}`);
            // Try alternative selector
            const altCountdownEl = document.querySelector(`#countdown-${taskId}`);
            if (altCountdownEl) {
                console.log(`Using alternative countdown element for task ${taskId}`);
            } else {
                console.log(`No countdown element found at all for task ${taskId}`);
                return;
            }
        }
        
        const status = card.dataset.status || 'not_started';
        const slaSeconds = 900; // 15 minutes
        const now = Date.now();
        
        let activeSeconds = parseInt(card.dataset.activeSeconds) || 0;
        let pauseSeconds = parseInt(card.dataset.pauseDuration) || 0;
        
        // Calculate live session duration for active tasks
        if (status === 'in_progress') {
            const refTime = card.dataset.resumeTime || card.dataset.startTime;
            if (refTime && refTime !== '' && !refTime.includes('1970')) {
                const refTimestamp = new Date(refTime).getTime();
                if (!isNaN(refTimestamp) && refTimestamp > 0) {
                    const sessionDuration = Math.floor((now - refTimestamp) / 1000);
                    if (sessionDuration >= 0 && sessionDuration < 7200) { // Max 2 hours
                        activeSeconds += sessionDuration;
                        console.log(`Task ${taskId}: Adding ${sessionDuration}s, total: ${activeSeconds}s`);
                    }
                }
            }
        } else if (status === 'on_break') {
            const pauseStart = card.dataset.pauseStartTime;
            if (pauseStart && pauseStart !== '' && !pauseStart.includes('1970')) {
                const pauseTimestamp = new Date(pauseStart).getTime();
                if (!isNaN(pauseTimestamp) && pauseTimestamp > 0) {
                    const currentPauseDuration = Math.floor((now - pauseTimestamp) / 1000);
                    if (currentPauseDuration >= 0 && currentPauseDuration < 7200) {
                        pauseSeconds += currentPauseDuration;
                    }
                }
            }
        }
        
        // Calculate countdown display and color
        let displaySeconds, color;
        
        if (status === 'not_started' || status === 'assigned') {
            displaySeconds = slaSeconds;
            color = '#374151';
        } else if (activeSeconds >= slaSeconds) {
            // Overdue - show time over SLA
            displaySeconds = activeSeconds - slaSeconds;
            color = '#dc2626';
        } else {
            // Within SLA - show remaining time (countdown)
            displaySeconds = slaSeconds - activeSeconds;
            color = status === 'in_progress' ? '#059669' : '#6b7280';
        }
        
        // Update displays with HH:MM:SS format - FORCE update every time
        const formattedTime = this.formatDuration(displaySeconds);
        const targetEl = countdownEl || document.querySelector(`#countdown-${taskId}`);
        
        if (targetEl) {
            targetEl.textContent = formattedTime;
            targetEl.style.color = color;
            console.log(`✅ Task ${taskId}: Updated display to ${formattedTime}`);
        } else {
            console.error(`❌ Task ${taskId}: No countdown element found to update!`);
        }
        
        console.log(`Task ${taskId}: Status=${status}, Display=${formattedTime}, Active=${activeSeconds}s`);
        
        const timeUsedEl = document.getElementById(`time-used-${taskId}`);
        if (timeUsedEl) {
            const formattedUsed = this.formatDuration(activeSeconds);
            timeUsedEl.textContent = formattedUsed;
        }
        
        const pauseEl = document.getElementById(`pause-timer-${taskId}`);
        if (pauseEl) {
            const formattedPause = this.formatDuration(pauseSeconds);
            pauseEl.textContent = formattedPause;
            pauseEl.style.color = status === 'on_break' ? '#f59e0b' : '#6b7280';
        }
    },
    
    formatDuration: function(totalSeconds) {
        const seconds = Math.max(0, Math.floor(totalSeconds));
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    },
    
    isValidTimestamp: function(timestamp) {
        if (!timestamp || timestamp === '' || timestamp === null || timestamp === undefined) return false;
        if (timestamp.includes('1970')) return false;
        
        const date = new Date(timestamp);
        const time = date.getTime();
        
        // Simple validation - just check if it's a valid date and not too old
        return !isNaN(time) && time > 946684800000; // After year 2000
    },
    
    sanitizeTimestamp: function(timestamp) {
        if (!this.isValidTimestamp(timestamp)) {
            return new Date().toISOString().replace('T', ' ').substring(0, 19);
        }
        return timestamp;
    },
    
    setDebounce: function(taskId) {
        this.debounce[taskId] = true;
        setTimeout(() => {
            this.debounce[taskId] = false;
        }, 2000);
    },
    
    validateStatus: function(taskId, expectedStatus) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (!card) return false;
        
        const currentStatus = card.dataset.status;
        return currentStatus === expectedStatus;
    },
    
    revertStatus: function(taskId, originalStatus) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (card) {
            card.dataset.status = originalStatus;
            this.updateTaskUI(taskId, originalStatus);
            this.updateDisplay(taskId);
        }
    },
    
    updateTaskUI: function(taskId, newStatus) {
        const statusBadge = document.querySelector(`#status-${taskId}`);
        if (statusBadge) {
            statusBadge.textContent = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            statusBadge.className = `badge badge--${newStatus}`;
        }
        
        const actionsDiv = document.querySelector(`#actions-${taskId}`);
        if (actionsDiv) {
            if (newStatus === 'in_progress') {
                actionsDiv.innerHTML = `
                    <button class="btn btn--sm btn--warning" onclick="window.SLATimer.pauseTask(${taskId})" title="Take a break from this task">
                        <i class="bi bi-pause"></i> Break
                    </button>
                    <button class="btn btn--sm btn--primary" onclick="openProgressModal(${taskId}, 0, '${newStatus}')" title="Update task completion progress">
                        <i class="bi bi-percent"></i> Update Progress
                    </button>
                `;
            } else if (newStatus === 'on_break') {
                actionsDiv.innerHTML = `
                    <button class="btn btn--sm btn--success" onclick="window.SLATimer.resumeTask(${taskId})" title="Resume working on this task">
                        <i class="bi bi-play"></i> Resume
                    </button>
                    <button class="btn btn--sm btn--primary" onclick="openProgressModal(${taskId}, 0, '${newStatus}')" title="Update task completion progress">
                        <i class="bi bi-percent"></i> Update Progress
                    </button>
                `;
            }
        }
    },
    
    // API Integration Methods
    startTask: function(taskId) {
        if (!this.validateStatus(taskId, 'not_started') && !this.validateStatus(taskId, 'assigned')) {
            this.showNotification('Task cannot be started from current status', 'error');
            return;
        }
        
        this.setDebounce(taskId);
        const originalStatus = document.querySelector(`[data-task-id="${taskId}"]`).dataset.status;
        
        // Immediate UI feedback
        this.setImmediateStatus(taskId, 'in_progress');
        
        this.callAPI('start', taskId, originalStatus);
    },
    
    pauseTask: function(taskId) {
        if (!this.validateStatus(taskId, 'in_progress')) {
            this.showNotification('Task must be in progress to pause', 'error');
            return;
        }
        
        this.setDebounce(taskId);
        const originalStatus = document.querySelector(`[data-task-id="${taskId}"]`).dataset.status;
        
        // Immediate UI feedback
        this.setImmediateStatus(taskId, 'on_break');
        
        this.callAPI('pause', taskId, originalStatus);
    },
    
    resumeTask: function(taskId) {
        if (!this.validateStatus(taskId, 'on_break')) {
            this.showNotification('Task must be on break to resume', 'error');
            return;
        }
        
        this.setDebounce(taskId);
        const originalStatus = document.querySelector(`[data-task-id="${taskId}"]`).dataset.status;
        
        // Immediate UI feedback
        this.setImmediateStatus(taskId, 'in_progress');
        
        this.callAPI('resume', taskId, originalStatus);
    },
    
    setImmediateStatus: function(taskId, newStatus) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (!card) return;
        
        const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
        const currentStatus = card.dataset.status;
        
        // Accumulate time before status change (except when starting fresh)
        if (currentStatus !== 'not_started' && currentStatus !== 'assigned') {
            this.accumulateTime(card);
        }
        
        // Set new status and timestamps
        card.dataset.status = newStatus;
        
        if (newStatus === 'in_progress') {
            // If starting fresh, reset active seconds
            if (currentStatus === 'not_started' || currentStatus === 'assigned') {
                card.dataset.activeSeconds = '0';
            }
            if (!card.dataset.startTime) card.dataset.startTime = now;
            card.dataset.resumeTime = now;
            card.dataset.pauseStartTime = '';
        } else if (newStatus === 'on_break') {
            card.dataset.pauseStartTime = now;
            card.dataset.resumeTime = '';
        }
        
        this.updateTaskUI(taskId, newStatus);
        // CRITICAL: Ensure timer continues after status change
        this.startTimer(taskId);
        this.updateDisplay(taskId);
    },
    
    accumulateTime: function(card) {
        const currentStatus = card.dataset.status;
        const now = Date.now();
        
        if (currentStatus === 'in_progress') {
            const refTime = this.sanitizeTimestamp(card.dataset.resumeTime || card.dataset.startTime);
            if (this.isValidTimestamp(refTime)) {
                const refTimestamp = new Date(refTime).getTime();
                const sessionDuration = Math.floor((now - refTimestamp) / 1000);
                if (sessionDuration > 0) {
                    const currentActive = parseInt(card.dataset.activeSeconds) || 0;
                    card.dataset.activeSeconds = currentActive + sessionDuration;
                }
            }
        } else if (currentStatus === 'on_break') {
            const pauseStart = this.sanitizeTimestamp(card.dataset.pauseStartTime);
            if (this.isValidTimestamp(pauseStart)) {
                const pauseTimestamp = new Date(pauseStart).getTime();
                const pauseDuration = Math.floor((now - pauseTimestamp) / 1000);
                if (pauseDuration > 0) {
                    const currentPause = parseInt(card.dataset.pauseDuration) || 0;
                    card.dataset.pauseDuration = currentPause + pauseDuration;
                }
            }
        }
    },
    
    callAPI: function(action, taskId, originalStatus) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        fetch(`/ergon-site/api/daily_planner_workflow.php?action=${action}`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ task_id: parseInt(taskId, 10), csrf_token: csrfToken })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.syncWithAPI(taskId, data);
                this.showNotification(`Task ${action}ed successfully`, 'success');
                // Force timer restart after successful API call
                setTimeout(() => this.startTimer(taskId), 100);
            } else {
                this.revertStatus(taskId, originalStatus);
                this.showNotification(`Failed to ${action}: ${data.error || data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error(`API Error (${action}):`, error);
            this.revertStatus(taskId, originalStatus);
            this.showNotification(`Network error: ${error.message}`, 'error');
        });
    },
    
    syncWithAPI: function(taskId, data) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (!card) return;
        
        // Update data attributes from API response
        if (data.start_time) card.dataset.startTime = data.start_time;
        if (data.resume_time) card.dataset.resumeTime = data.resume_time;
        if (data.pause_start_time) card.dataset.pauseStartTime = data.pause_start_time;
        if (data.active_seconds !== undefined) card.dataset.activeSeconds = data.active_seconds;
        if (data.total_pause_duration !== undefined) card.dataset.pauseDuration = data.total_pause_duration;
        
        // CRITICAL: Restart timer after API sync to ensure continuous updates
        this.startTimer(taskId);
        this.updateDisplay(taskId);
    },
    
    showNotification: function(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 12px 16px; 
            border-radius: 4px; color: white; z-index: 10000; max-width: 300px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }
};

// Global compatibility functions
window.startTask = function(taskId) { return window.SLATimer.startTask(taskId); };
window.pauseTask = function(taskId) { return window.SLATimer.pauseTask(taskId); };
window.resumeTask = function(taskId) { return window.SLATimer.resumeTask(taskId); };
window.setImmediateStatus = function(taskId, status) { return window.SLATimer.setImmediateStatus(taskId, status); };
window.startSLATimer = function(taskId) { return window.SLATimer.startTimer(taskId); };
window.updateSLATimer = function(taskId, status, data) { return window.SLATimer.syncWithAPI(taskId, data); };

// Initialize on DOM ready with multiple attempts
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM loaded, initializing SLA Timer...');
    
    // Immediate initialization
    if (window.SLATimer && typeof window.SLATimer.init === 'function') {
        window.SLATimer.init();
    }
    
    // Backup initialization after 500ms
    setTimeout(() => {
        if (window.SLATimer && typeof window.SLATimer.init === 'function') {
            console.log('🔄 Backup timer initialization...');
            window.SLATimer.init();
        }
    }, 500);
    
    // Final attempt after 2 seconds
    setTimeout(() => {
        if (window.SLATimer && typeof window.SLATimer.init === 'function') {
            console.log('⚡ Final timer initialization attempt...');
            window.SLATimer.init();
        }
    }, 2000);
});