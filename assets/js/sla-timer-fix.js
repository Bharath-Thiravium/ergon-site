/**
 * SLA Timer Fix - Prevents timer from increasing after page refresh
 * Fixes Error 10: SLA timer counting incorrectly after page refresh
 */

class SLATimerManager {
    constructor() {
        this.timers = new Map();
        this.initialized = false;
    }

    init() {
        if (this.initialized) return;
        
        // Initialize timers for all tasks
        document.querySelectorAll('.task-card').forEach(taskCard => {
            const taskId = taskCard.dataset.taskId;
            const status = taskCard.dataset.status;
            const startTime = parseInt(taskCard.dataset.startTime) || 0;
            const slaDuration = parseInt(taskCard.dataset.slaDuration) || 900; // 15 min default
            const activeSeconds = parseInt(taskCard.dataset.activeSeconds) || 0;
            const pauseDuration = parseInt(taskCard.dataset.pauseDuration) || 0;
            
            if (taskId) {
                this.initializeTaskTimer(taskId, {
                    status,
                    startTime,
                    slaDuration,
                    activeSeconds,
                    pauseDuration
                });
            }
        });
        
        this.initialized = true;
    }

    initializeTaskTimer(taskId, config) {
        const timer = {
            taskId,
            status: config.status,
            startTime: config.startTime,
            slaDuration: config.slaDuration,
            baseActiveSeconds: config.activeSeconds, // Fixed: Use stored active seconds as base
            basePauseDuration: config.pauseDuration,
            sessionStartTime: null,
            sessionPauseStart: null,
            interval: null
        };

        // Set session start time only for currently active tasks
        if (config.status === 'in_progress') {
            timer.sessionStartTime = Date.now();
        } else if (config.status === 'on_break') {
            timer.sessionPauseStart = Date.now();
        }

        this.timers.set(taskId, timer);
        this.startTimerDisplay(taskId);
    }

    startTimerDisplay(taskId) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        // Clear existing interval
        if (timer.interval) {
            clearInterval(timer.interval);
        }

        // Update display immediately
        this.updateTimerDisplay(taskId);

        // Start interval for active tasks only
        if (timer.status === 'in_progress' || timer.status === 'on_break') {
            timer.interval = setInterval(() => {
                this.updateTimerDisplay(taskId);
            }, 1000);
        }
    }

    updateTimerDisplay(taskId) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        const countdownEl = document.getElementById(`countdown-${taskId}`);
        const timeUsedEl = document.getElementById(`time-used-${taskId}`);
        const pauseTimerEl = document.getElementById(`pause-timer-${taskId}`);

        if (!countdownEl) return;

        let currentActiveSeconds = timer.baseActiveSeconds;
        let currentPauseSeconds = timer.basePauseDuration;

        // Calculate session time only for currently active tasks
        if (timer.status === 'in_progress' && timer.sessionStartTime) {
            const sessionElapsed = Math.floor((Date.now() - timer.sessionStartTime) / 1000);
            currentActiveSeconds += sessionElapsed;
        } else if (timer.status === 'on_break' && timer.sessionPauseStart) {
            const sessionPauseElapsed = Math.floor((Date.now() - timer.sessionPauseStart) / 1000);
            currentPauseSeconds += sessionPauseElapsed;
        }

        // Calculate remaining time (fixed: prevent negative values)
        const remainingSeconds = Math.max(0, timer.slaDuration - currentActiveSeconds);
        
        // Update countdown display
        const countdownDisplay = countdownEl.querySelector('.countdown-display');
        if (countdownDisplay) {
            if (timer.status === 'in_progress') {
                countdownDisplay.textContent = this.formatTime(remainingSeconds);
                countdownDisplay.className = remainingSeconds <= 0 ? 'countdown-display overdue' : 'countdown-display';
            } else if (timer.status === 'on_break') {
                countdownDisplay.textContent = 'PAUSED';
                countdownDisplay.className = 'countdown-display paused';
            } else {
                countdownDisplay.textContent = this.formatTime(timer.slaDuration);
                countdownDisplay.className = 'countdown-display';
            }
        }

        // Update time used display
        if (timeUsedEl) {
            timeUsedEl.textContent = this.formatTime(currentActiveSeconds);
        }

        // Update pause timer display
        if (pauseTimerEl) {
            pauseTimerEl.textContent = this.formatTime(currentPauseSeconds);
            pauseTimerEl.className = timer.status === 'on_break' ? 'timing-value break-active' : 'timing-value';
        }
    }

    updateTaskStatus(taskId, newStatus) {
        const timer = this.timers.get(taskId);
        if (!timer) return;

        const now = Date.now();

        // Handle status transitions
        if (timer.status === 'in_progress' && newStatus !== 'in_progress') {
            // Task was running, now stopping - add session time to base
            if (timer.sessionStartTime) {
                const sessionElapsed = Math.floor((now - timer.sessionStartTime) / 1000);
                timer.baseActiveSeconds += sessionElapsed;
                timer.sessionStartTime = null;
            }
        } else if (timer.status === 'on_break' && newStatus !== 'on_break') {
            // Task was paused, now resuming/stopping - add pause time to base
            if (timer.sessionPauseStart) {
                const sessionPauseElapsed = Math.floor((now - timer.sessionPauseStart) / 1000);
                timer.basePauseDuration += sessionPauseElapsed;
                timer.sessionPauseStart = null;
            }
        }

        // Set new session times
        if (newStatus === 'in_progress') {
            timer.sessionStartTime = now;
        } else if (newStatus === 'on_break') {
            timer.sessionPauseStart = now;
        }

        timer.status = newStatus;
        this.startTimerDisplay(taskId);
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
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
}

// Global instance
window.slaTimerManager = new SLATimerManager();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.slaTimerManager.init();
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    window.slaTimerManager.stopAllTimers();
});