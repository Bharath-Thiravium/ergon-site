/**
 * Enhanced SLA Timer - Proper implementation for Daily Planner
 * Fixes: Timer increases after page refresh, incorrect break time tracking, overdue calculations
 */

class EnhancedSLATimer {
    constructor() {
        this.timers = new Map();
        this.initialized = false;
        this.updateInterval = 1000; // 1 second
    }

    init() {
        if (this.initialized) return;
        
        console.log('Initializing Enhanced SLA Timer...');
        
        // Initialize all task timers
        document.querySelectorAll('.task-card').forEach(card => {
            const taskId = card.dataset.taskId;
            if (taskId) {
                this.initializeTaskTimer(taskId);
            }
        });
        
        this.initialized = true;
    }

    initializeTaskTimer(taskId) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (!card) return;

        const status = card.dataset.status;
        const slaDuration = parseInt(card.dataset.slaDuration) || 900; // 15 minutes default
        const activeSeconds = parseInt(card.dataset.activeSeconds) || 0;
        const pauseDuration = parseInt(card.dataset.pauseDuration) || 0;
        const startTime = card.dataset.startTime;
        const resumeTime = card.dataset.resumeTime;
        const pauseStartTime = card.dataset.pauseStartTime;

        const timer = {
            taskId,
            status,
            slaDuration,
            storedActiveSeconds: activeSeconds,
            storedPauseDuration: pauseDuration,
            startTime,
            resumeTime,
            pauseStartTime,
            sessionStartTime: null,
            sessionPauseStartTime: null,
            interval: null
        };

        // Set session times for currently active tasks
        if (status === 'in_progress') {
            const referenceTime = resumeTime || startTime;
            if (referenceTime) {
                timer.sessionStartTime = new Date(referenceTime).getTime();
            }
        } else if (status === 'on_break' && pauseStartTime) {
            timer.sessionPauseStartTime = new Date(pauseStartTime).getTime();
        }

        this.timers.set(taskId, timer);
        this.startTimer(taskId);
    }

    startTimer(taskId) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        // Clear existing interval
        if (timer.interval) {
            clearInterval(timer.interval);
        }

        // Update display immediately
        this.updateDisplay(taskId);

        // Start interval only for active tasks
        if (timer.status === 'in_progress' || timer.status === 'on_break') {
            timer.interval = setInterval(() => {
                this.updateDisplay(taskId);
            }, this.updateInterval);
        }
    }

    updateDisplay(taskId) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        const { currentActiveSeconds, currentPauseSeconds } = this.calculateCurrentTimes(timer);
        
        // Calculate SLA metrics
        const remainingSeconds = Math.max(0, timer.slaDuration - currentActiveSeconds);
        const isOverdue = currentActiveSeconds > timer.slaDuration;
        const overdueSeconds = isOverdue ? currentActiveSeconds - timer.slaDuration : 0;

        // Update countdown display
        this.updateCountdownDisplay(taskId, timer.status, remainingSeconds, overdueSeconds, isOverdue);
        
        // Update time used display
        this.updateTimeUsedDisplay(taskId, currentActiveSeconds);
        
        // Update pause time display
        this.updatePauseTimeDisplay(taskId, currentPauseSeconds, timer.status);
    }

    calculateCurrentTimes(timer) {
        let currentActiveSeconds = timer.storedActiveSeconds;
        let currentPauseSeconds = timer.storedPauseDuration;

        const now = Date.now();

        // Calculate current session active time
        if (timer.status === 'in_progress' && timer.sessionStartTime) {
            const sessionTime = Math.floor((now - timer.sessionStartTime) / 1000);
            currentActiveSeconds += Math.max(0, sessionTime);
        }

        // Calculate current session pause time
        if (timer.status === 'on_break' && timer.sessionPauseStartTime) {
            const sessionPauseTime = Math.floor((now - timer.sessionPauseStartTime) / 1000);
            currentPauseSeconds += Math.max(0, sessionPauseTime);
        }

        return { currentActiveSeconds, currentPauseSeconds };
    }

    updateCountdownDisplay(taskId, status, remainingSeconds, overdueSeconds, isOverdue) {
        const countdownDisplay = document.querySelector(`#countdown-${taskId} .countdown-display`);
        if (!countdownDisplay) return;

        if (status === 'in_progress') {
            if (isOverdue) {
                countdownDisplay.textContent = 'OVERDUE: ' + this.formatTime(overdueSeconds);
                countdownDisplay.style.color = '#dc2626';
                countdownDisplay.className = 'countdown-display overdue';
            } else {
                countdownDisplay.textContent = this.formatTime(remainingSeconds);
                countdownDisplay.style.color = remainingSeconds < 300 ? '#f59e0b' : '#059669';
                countdownDisplay.className = remainingSeconds < 300 ? 'countdown-display warning' : 'countdown-display';
            }
        } else if (status === 'on_break') {
            countdownDisplay.textContent = 'PAUSED';
            countdownDisplay.style.color = '#6b7280';
            countdownDisplay.className = 'countdown-display paused';
        } else {
            countdownDisplay.textContent = this.formatTime(remainingSeconds);
            countdownDisplay.style.color = '#374151';
            countdownDisplay.className = 'countdown-display';
        }
    }

    updateTimeUsedDisplay(taskId, currentActiveSeconds) {
        const timeUsedDisplay = document.getElementById(`time-used-${taskId}`);
        if (timeUsedDisplay) {
            timeUsedDisplay.textContent = this.formatTime(currentActiveSeconds);
        }
    }

    updatePauseTimeDisplay(taskId, currentPauseSeconds, status) {
        const pauseDisplay = document.getElementById(`pause-timer-${taskId}`);
        if (pauseDisplay) {
            pauseDisplay.textContent = this.formatTime(currentPauseSeconds);
            pauseDisplay.style.color = status === 'on_break' ? '#f59e0b' : '#6b7280';
            pauseDisplay.className = status === 'on_break' ? 'timing-value break-active' : 'timing-value';
        }
    }

    updateTaskStatus(taskId, newStatus, serverData = {}) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        if (!card) return;

        // Update card data attributes
        card.dataset.status = newStatus;
        
        if (serverData.start_time) {
            card.dataset.startTime = serverData.start_time;
            timer.startTime = serverData.start_time;
        }
        
        if (serverData.resume_time) {
            card.dataset.resumeTime = serverData.resume_time;
            timer.resumeTime = serverData.resume_time;
        }
        
        if (serverData.pause_start_time) {
            card.dataset.pauseStartTime = serverData.pause_start_time;
            timer.pauseStartTime = serverData.pause_start_time;
        }
        
        if (serverData.active_seconds !== undefined) {
            card.dataset.activeSeconds = serverData.active_seconds;
            timer.storedActiveSeconds = serverData.active_seconds;
        }
        
        if (serverData.total_pause_duration !== undefined) {
            card.dataset.pauseDuration = serverData.total_pause_duration;
            timer.storedPauseDuration = serverData.total_pause_duration;
        }

        // Handle status transitions
        const now = Date.now();
        
        if (newStatus === 'in_progress') {
            const referenceTime = timer.resumeTime || timer.startTime;
            timer.sessionStartTime = referenceTime ? new Date(referenceTime).getTime() : now;
            timer.sessionPauseStartTime = null;
            card.dataset.resumeTime = '';
            card.dataset.pauseStartTime = '';
        } else if (newStatus === 'on_break') {
            timer.sessionPauseStartTime = timer.pauseStartTime ? new Date(timer.pauseStartTime).getTime() : now;
            timer.sessionStartTime = null;
            card.dataset.resumeTime = '';
        } else {
            timer.sessionStartTime = null;
            timer.sessionPauseStartTime = null;
        }

        timer.status = newStatus;
        this.startTimer(taskId);
    }

    formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }

    stopTimer(taskId) {
        const timer = this.timers.get(taskId);
        if (timer && timer.interval) {
            clearInterval(timer.interval);
            timer.interval = null;
        }
    }

    stopAllTimers() {
        this.timers.forEach((timer, taskId) => {
            this.stopTimer(taskId);
        });
    }

    // Public API methods
    startTask(taskId) {
        const timer = this.timers.get(taskId);
        if (timer) {
            timer.status = 'in_progress';
            timer.sessionStartTime = Date.now();
            timer.sessionPauseStartTime = null;
            this.startTimer(taskId);
        }
    }

    pauseTask(taskId) {
        const timer = this.timers.get(taskId);
        if (timer) {
            timer.status = 'on_break';
            timer.sessionPauseStartTime = Date.now();
            timer.sessionStartTime = null;
            this.startTimer(taskId);
        }
    }

    resumeTask(taskId) {
        const timer = this.timers.get(taskId);
        if (timer) {
            timer.status = 'in_progress';
            timer.sessionStartTime = Date.now();
            timer.sessionPauseStartTime = null;
            this.startTimer(taskId);
        }
    }

    completeTask(taskId) {
        const timer = this.timers.get(taskId);
        if (timer) {
            timer.status = 'completed';
            timer.sessionStartTime = null;
            timer.sessionPauseStartTime = null;
            this.stopTimer(taskId);
            this.updateDisplay(taskId);
        }
    }
}

// Global instance
window.enhancedSLATimer = new EnhancedSLATimer();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        window.enhancedSLATimer.init();
    }, 100);
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    window.enhancedSLATimer.stopAllTimers();
});

// Export for use in other scripts
window.updateSLATimer = (taskId, status, serverData) => {
    window.enhancedSLATimer.updateTaskStatus(taskId, status, serverData);
};