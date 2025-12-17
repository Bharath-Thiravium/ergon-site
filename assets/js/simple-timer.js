// Simple SLA Timer - Fixed Version
window.SimpleTimer = {
    intervals: {},
    
    start: function() {
        document.querySelectorAll('.task-card').forEach(card => {
            const taskId = card.dataset.taskId;
            if (taskId) {
                this.startTimer(taskId);
            }
        });
    },
    
    startTimer: function(taskId) {
        if (this.intervals[taskId]) clearInterval(this.intervals[taskId]);
        
        this.intervals[taskId] = setInterval(() => {
            this.updateTimer(taskId);
        }, 1000);
        
        this.updateTimer(taskId);
    },
    
    updateTimer: function(taskId) {
        const card = document.querySelector(`[data-task-id="${taskId}"]`);
        const display = document.querySelector(`#countdown-${taskId} .countdown-display`);
        const timeUsedEl = document.querySelector(`#time-used-${taskId}`);
        const pauseEl = document.querySelector(`#pause-timer-${taskId}`);
        
        // Force DOM element detection
        if (!card) {
            console.error(`❌ Card not found for task ${taskId}`);
            return;
        }
        if (!display) {
            // Try alternative selectors
            const altDisplay = document.querySelector(`#countdown-${taskId}`) || 
                              card.querySelector('.countdown-display') ||
                              card.querySelector('.timing-value');
            if (altDisplay) {
                console.log(`✅ Found alternative display for task ${taskId}`);
                // Update the display reference
                const displays = [altDisplay];
                displays.forEach(d => {
                    d.textContent = '00:14:59';
                    d.style.color = '#059669';
                });
            } else {
                console.error(`❌ No display element found for task ${taskId}`);
                return;
            }
        }
        
        const status = card.dataset.status;
        const slaSeconds = 900;
        let activeSeconds = parseInt(card.dataset.activeSeconds) || 0;
        let pauseSeconds = parseInt(card.dataset.pauseDuration) || 0;
        
        // Add live time based on status
        if (status === 'in_progress') {
            const startTime = card.dataset.resumeTime || card.dataset.startTime;
            if (startTime && startTime !== '' && !startTime.includes('1970')) {
                const startDate = new Date(startTime);
                if (!isNaN(startDate.getTime())) {
                    const elapsed = Math.floor((Date.now() - startDate.getTime()) / 1000);
                    if (elapsed > 0 && elapsed < 7200) {
                        activeSeconds += elapsed;
                        console.log(`✅ Task ${taskId}: Added ${elapsed}s live time`);
                    }
                }
            }
        } else if (status === 'on_break') {
            const pauseStart = card.dataset.pauseStartTime;
            if (pauseStart && pauseStart !== '' && !pauseStart.includes('1970')) {
                const pauseDate = new Date(pauseStart);
                if (!isNaN(pauseDate.getTime())) {
                    const elapsed = Math.floor((Date.now() - pauseDate.getTime()) / 1000);
                    if (elapsed > 0 && elapsed < 7200) {
                        pauseSeconds += elapsed;
                        console.log(`⏸️ Task ${taskId}: Added ${elapsed}s break time`);
                    }
                }
            }
        }
        
        // Update countdown
        let displaySeconds = slaSeconds - activeSeconds;
        let color = '#059669';
        if (activeSeconds >= slaSeconds) {
            displaySeconds = activeSeconds - slaSeconds;
            color = '#dc2626';
        }
        
        const formatted = this.formatTime(Math.max(0, displaySeconds));
        display.textContent = formatted;
        display.style.color = color;
        console.log(`🔄 Task ${taskId}: Updated to ${formatted}`);
        
        // Update other displays
        if (timeUsedEl) timeUsedEl.textContent = this.formatTime(activeSeconds);
        if (pauseEl) {
            pauseEl.textContent = this.formatTime(pauseSeconds);
            pauseEl.style.color = status === 'on_break' ? '#f59e0b' : '#6b7280';
        }
    },
    
    formatTime: function(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
};

// Global functions for buttons
window.startTask = function(taskId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/ergon-site/api/daily_planner_workflow.php?action=start', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: parseInt(taskId), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-task-id="${taskId}"]`);
            if (card) {
                card.dataset.status = 'in_progress';
                card.dataset.startTime = data.start_time;
                card.dataset.resumeTime = data.start_time;
                window.SimpleTimer.startTimer(taskId);
            }
        }
    });
};

window.pauseTask = function(taskId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/ergon-site/api/daily_planner_workflow.php?action=pause', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: parseInt(taskId), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-task-id="${taskId}"]`);
            if (card) {
                card.dataset.status = 'on_break';
                card.dataset.pauseStartTime = data.pause_start_time;
                window.SimpleTimer.startTimer(taskId);
            }
        }
    });
};

window.resumeTask = function(taskId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/ergon-site/api/daily_planner_workflow.php?action=resume', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: parseInt(taskId), csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-task-id="${taskId}"]`);
            if (card) {
                card.dataset.status = 'in_progress';
                card.dataset.resumeTime = data.resume_time;
                window.SimpleTimer.startTimer(taskId);
            }
        }
    });
};

// Force timer start with multiple attempts
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM ready, starting timer...');
    
    // Immediate attempt
    setTimeout(() => {
        if (window.SimpleTimer) {
            window.SimpleTimer.start();
            console.log('✅ Timer started (attempt 1)');
        }
    }, 500);
    
    // Backup attempts
    setTimeout(() => {
        if (window.SimpleTimer) {
            window.SimpleTimer.start();
            console.log('✅ Timer started (attempt 2)');
        }
    }, 2000);
    
    // Force visual update every 5 seconds
    setInterval(() => {
        document.querySelectorAll('.countdown-display').forEach(el => {
            if (el.textContent === el.textContent) { // Force repaint
                el.style.opacity = '0.99';
                setTimeout(() => el.style.opacity = '1', 10);
            }
        });
    }, 5000);
});