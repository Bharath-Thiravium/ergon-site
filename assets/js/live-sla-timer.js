/**
 * Live SLA Timer - Event-Driven Implementation
 */

let timers = {};

function initSLATimer() {
    document.querySelectorAll('.task-card').forEach(card => {
        const taskId = card.dataset.taskId;
        if (taskId) startTimer(taskId);
    });
    
    // Event delegation for button clicks
    document.addEventListener('click', handleButtonClick);
}

function handleButtonClick(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    
    const taskCard = btn.closest('.task-card');
    if (!taskCard) return;
    
    const taskId = taskCard.dataset.taskId;
    if (!taskId) return;
    
    if (btn.textContent.includes('Start')) {
        e.preventDefault();
        e.stopPropagation();
        changeStatus(taskId, 'in_progress');
        return false;
    }
    
    if (btn.textContent.includes('Break')) {
        e.preventDefault();
        e.stopPropagation();
        changeStatus(taskId, 'on_break');
        return false;
    }
    
    if (btn.textContent.includes('Resume')) {
        e.preventDefault();
        e.stopPropagation();
        changeStatus(taskId, 'in_progress');
        return false;
    }
}

function startTimer(taskId) {
    if (timers[taskId]) clearInterval(timers[taskId]);
    
    timers[taskId] = setInterval(() => updateDisplay(taskId), 1000);
    updateDisplay(taskId);
}

function updateDisplay(taskId) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    const countdownEl = document.querySelector(`#countdown-${taskId} .countdown-display`);
    
    if (!card || !countdownEl) return;
    
    const status = card.dataset.status || 'not_started';
    const slaSeconds = 900;
    
    let activeSeconds = parseInt(card.dataset.activeSeconds) || 0;
    let pauseSeconds = parseInt(card.dataset.pauseDuration) || 0;
    
    // Calculate current session duration
    if (status === 'in_progress') {
        const refTime = card.dataset.resumeTime || card.dataset.startTime;
        if (refTime && !refTime.includes('1970')) {
            const sessionDuration = Math.floor((Date.now() - new Date(refTime).getTime()) / 1000);
            if (sessionDuration > 0 && sessionDuration < 86400) activeSeconds += sessionDuration;
        }
    } else if (status === 'on_break') {
        const pauseStart = card.dataset.pauseStartTime;
        if (pauseStart && !pauseStart.includes('1970')) {
            const pauseDuration = Math.floor((Date.now() - new Date(pauseStart).getTime()) / 1000);
            if (pauseDuration > 0 && pauseDuration < 86400) pauseSeconds += pauseDuration;
        }
    }
    
    // Calculate display duration
    let displaySeconds, color;
    
    if (status === 'not_started' || status === 'assigned') {
        displaySeconds = slaSeconds;
        color = '#374151';
    } else if (activeSeconds >= slaSeconds) {
        displaySeconds = activeSeconds - slaSeconds;
        color = '#dc2626';
    } else {
        displaySeconds = slaSeconds - activeSeconds;
        color = status === 'in_progress' ? '#059669' : '#6b7280';
    }
    
    // Update displays - HH:MM:SS format only
    countdownEl.textContent = formatDuration(displaySeconds);
    countdownEl.style.color = color;
    
    const timeUsedEl = document.getElementById(`time-used-${taskId}`);
    if (timeUsedEl) timeUsedEl.textContent = formatDuration(activeSeconds);
    
    const pauseEl = document.getElementById(`pause-timer-${taskId}`);
    if (pauseEl) {
        pauseEl.textContent = formatDuration(pauseSeconds);
        pauseEl.style.color = status === 'on_break' ? '#f59e0b' : '#6b7280';
    }
}

function formatDuration(totalSeconds) {
    const seconds = Math.max(0, Math.floor(totalSeconds));
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

function changeStatus(taskId, newStatus) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!card) return;
    
    const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
    card.dataset.status = newStatus;
    
    if (newStatus === 'in_progress') {
        if (!card.dataset.startTime) card.dataset.startTime = now;
        card.dataset.resumeTime = now;
        card.dataset.pauseStartTime = '';
    } else if (newStatus === 'on_break') {
        card.dataset.pauseStartTime = now;
        card.dataset.resumeTime = '';
    }
    
    updateDisplay(taskId);
}

// Compatibility functions
window.startSLATimer = startTimer;
window.updateSLATimer = function(taskId, status, data) {
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    if (!card) return;
    
    card.dataset.status = status;
    if (data.start_time) card.dataset.startTime = data.start_time;
    if (data.resume_time) card.dataset.resumeTime = data.resume_time;
    if (data.pause_start_time) card.dataset.pauseStartTime = data.pause_start_time;
    if (data.active_seconds !== undefined) card.dataset.activeSeconds = data.active_seconds;
    if (data.total_pause_duration !== undefined) card.dataset.pauseDuration = data.total_pause_duration;
    
    updateDisplay(taskId);
};

document.addEventListener('DOMContentLoaded', initSLATimer);