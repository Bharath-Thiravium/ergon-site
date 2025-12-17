/**
 * Real-time SLA Timer - Immediate Visual Feedback
 */

let slaTimers = {};

function initSLATimer() {
    document.querySelectorAll('.task-card').forEach(card => {
        const taskId = card.dataset.taskId;
        if (taskId) {
            startSLATimer(taskId);
        }
    });
}

function startSLATimer(taskId) {
    if (slaTimers[taskId]) {
        clearInterval(slaTimers[taskId]);
    }
    
    // Start live timer - runs every second for real-time updates
    slaTimers[taskId] = setInterval(() => {
        updateSLADisplay(taskId);
    }, 1000);
    
    // Update immediately
    updateSLADisplay(taskId);
}

function updateSLADisplay(taskId) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!card) return;
    
    const status = card.dataset.status || 'not_started';
    const slaDuration = 900; // 15 minutes = 900 seconds
    
    let activeTime = 0;
    let pauseTime = 0;
    
    // Calculate active time
    if (status === 'in_progress') {
        const storedActive = parseInt(card.dataset.activeSeconds) || 0;
        activeTime = storedActive;
        
        // Add current session time
        const startTime = card.dataset.startTime;
        const resumeTime = card.dataset.resumeTime;
        const refTime = resumeTime || startTime;
        
        if (refTime && refTime !== '' && refTime !== 'null' && refTime !== '1970-01-01 00:00:00') {
            try {
                const refDate = new Date(refTime);
                if (refDate.getTime() > 0 && refDate.getFullYear() > 1970) {
                    const sessionSeconds = Math.floor((Date.now() - refDate.getTime()) / 1000);
                    if (sessionSeconds >= 0 && sessionSeconds < 86400) { // Max 24 hours
                        activeTime += sessionSeconds;
                    }
                }
            } catch (e) {
                console.log('Invalid date:', refTime);
            }
        }
    } else {
        activeTime = parseInt(card.dataset.activeSeconds) || 0;
    }
    
    // Calculate pause time
    const storedPause = parseInt(card.dataset.pauseDuration) || 0;
    pauseTime = storedPause;
    
    if (status === 'on_break') {
        const pauseStart = card.dataset.pauseStartTime;
        if (pauseStart && pauseStart !== '' && pauseStart !== 'null' && pauseStart !== '1970-01-01 00:00:00') {
            try {
                const pauseDate = new Date(pauseStart);
                if (pauseDate.getTime() > 0 && pauseDate.getFullYear() > 1970) {
                    const sessionPause = Math.floor((Date.now() - pauseDate.getTime()) / 1000);
                    if (sessionPause >= 0 && sessionPause < 86400) { // Max 24 hours
                        pauseTime += sessionPause;
                    }
                }
            } catch (e) {
                console.log('Invalid pause date:', pauseStart);
            }
        }
    }
    
    // Update countdown display - ALWAYS show time format
    const countdownEl = document.querySelector(`#countdown-${taskId} .countdown-display`);
    if (countdownEl) {
        let displayText = '';
        let displayColor = '#374151';
        
        if (status === 'not_started' || status === 'assigned') {
            displayText = formatTime(slaDuration); // Always show 00:15:00
            displayColor = '#374151';
        } else if (status === 'in_progress') {
            if (activeTime >= slaDuration) {
                const overdue = activeTime - slaDuration;
                displayText = formatTime(overdue); // Show overdue time in HH:MM:SS
                displayColor = '#dc2626';
            } else {
                const remaining = slaDuration - activeTime;
                displayText = formatTime(remaining); // Live countdown HH:MM:SS
                displayColor = remaining < 300 ? '#f59e0b' : '#059669';
            }
        } else if (status === 'on_break') {
            if (activeTime >= slaDuration) {
                const overdue = activeTime - slaDuration;
                displayText = formatTime(overdue); // Show overdue time even when paused
                displayColor = '#dc2626';
            } else {
                const remaining = slaDuration - activeTime;
                displayText = formatTime(remaining); // Show remaining time when paused
                displayColor = '#6b7280';
            }
        }
        
        // Immediate visual update
        countdownEl.textContent = displayText;
        countdownEl.style.color = displayColor;
    }
    
    // Update time used display
    const timeUsedEl = document.getElementById(`time-used-${taskId}`);
    if (timeUsedEl) {
        timeUsedEl.textContent = formatTime(activeTime);
    }
    
    // Update pause time display with live updates
    const pauseEl = document.getElementById(`pause-timer-${taskId}`);
    if (pauseEl) {
        pauseEl.textContent = formatTime(pauseTime);
        pauseEl.style.color = status === 'on_break' ? '#f59e0b' : '#6b7280';
    }
}



function formatTime(seconds) {
    const s = Math.max(0, Math.floor(seconds || 0));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
}

function updateTaskTimer(taskId, status, serverData = {}) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!card) return;
    
    // Update status
    card.dataset.status = status;
    
    // Update server data with validation
    if (serverData.start_time && serverData.start_time !== '1970-01-01 00:00:00') {
        card.dataset.startTime = serverData.start_time;
    }
    if (serverData.resume_time && serverData.resume_time !== '1970-01-01 00:00:00') {
        card.dataset.resumeTime = serverData.resume_time;
    }
    if (serverData.pause_start_time && serverData.pause_start_time !== '1970-01-01 00:00:00') {
        card.dataset.pauseStartTime = serverData.pause_start_time;
    }
    if (serverData.active_seconds !== undefined) {
        card.dataset.activeSeconds = Math.max(0, parseInt(serverData.active_seconds));
    }
    if (serverData.total_pause_duration !== undefined) {
        card.dataset.pauseDuration = Math.max(0, parseInt(serverData.total_pause_duration));
    }
    
    // Clear conflicting timestamps based on status
    if (status === 'in_progress') {
        card.dataset.pauseStartTime = '';
    } else if (status === 'on_break') {
        card.dataset.resumeTime = '';
    }
    
    // IMMEDIATE display update before restarting timer
    updateSLADisplay(taskId);
    
    // Restart timer
    startSLATimer(taskId);
}

// Initialize when DOM loads with immediate start
document.addEventListener('DOMContentLoaded', () => {
    // Start immediately
    initSLATimer();
    
    // Also start after a short delay to catch any late-loading elements
    setTimeout(initSLATimer, 100);
    
    // Force update all displays immediately
    setTimeout(() => {
        document.querySelectorAll('.task-card').forEach(card => {
            const taskId = card.dataset.taskId;
            if (taskId) {
                updateSLADisplay(taskId);
            }
        });
    }, 200);
});

// Immediate status change for button clicks
function setImmediateStatus(taskId, status) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!card) return;
    
    const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
    
    card.dataset.status = status;
    
    if (status === 'in_progress') {
        if (!card.dataset.startTime) card.dataset.startTime = now;
        card.dataset.resumeTime = now;
        card.dataset.pauseStartTime = '';
    } else if (status === 'on_break') {
        card.dataset.pauseStartTime = now;
        card.dataset.resumeTime = '';
    }
    
    updateSLADisplay(taskId);
}

// Global functions
window.updateSLATimer = updateTaskTimer;
window.startSLATimer = startSLATimer;
window.initSLATimer = initSLATimer;
window.setImmediateStatus = setImmediateStatus;