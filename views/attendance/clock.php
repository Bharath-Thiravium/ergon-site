<?php
$title = 'Clock In/Out';
$active_page = 'attendance';
require_once __DIR__ . '/../../app/helpers/TimeHelper.php';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1><span>🕰️</span> Clock In/Out</h1>
        <p>Track your attendance with GPS location</p>
    </div>
    <div class="page-actions">
        <a href="/ergon-site/attendance" class="btn btn--secondary">
            <span>📍</span> Back to Attendance
        </a>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1fr; max-width: 600px; margin: 0 auto;">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">
                <span>🕰️</span> Current Time
            </h2>
        </div>
        <div class="card__body" style="text-align: center; padding: 2rem;">
            <div style="margin-bottom: 2rem;">
                <div id="currentTime" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;"></div>
                <div id="currentDate" style="color: #6b7280; font-size: 1rem;"></div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 300px; margin: 0 auto;">
                <?php if ($on_leave): ?>
                    <div style="padding: 1rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; text-align: center; color: #92400e;">
                        <span>🏖️</span> You are on approved leave today
                    </div>
                    <button class="btn btn--secondary" disabled style="padding: 1rem 2rem; font-size: 1.1rem; font-weight: 600; opacity: 0.5; cursor: not-allowed;">
                        <span>🏖️</span> On Leave
                    </button>
                <?php else: ?>
                    <button id="clockBtn" class="btn" style="padding: 1rem 2rem; font-size: 1.1rem; font-weight: 600;">
                        <span id="clockBtnIcon">▶️</span> <span id="clockBtnText">Clock In</span>
                    </button>
                <?php endif; ?>
            </div>
            
            <div id="locationStatus" style="margin-top: 1.5rem; color: #6b7280; font-size: 0.875rem;">
                <span>📍</span> Getting location...
            </div>
        </div>
    </div>
</div>

<script>
let currentPosition = null;

function updateTime() {
    const now = new Date();
    // Convert to IST and format with AM/PM
    const istTime = now.toLocaleTimeString('en-IN', {
        timeZone: 'Asia/Kolkata',
        hour12: true,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('currentTime').textContent = istTime;
    document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getLocation() {
    if (!navigator.geolocation) {
        document.getElementById('locationStatus').innerHTML = '<span>⚠️</span> Location not supported';
        return;
    }
    
    document.getElementById('locationStatus').innerHTML = '<span>📍</span> Detecting location...';
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentPosition = position;
            document.getElementById('locationStatus').innerHTML = '<span>✅</span> Location verified - Ready';
        },
        function(error) {
            currentPosition = null;
            document.getElementById('locationStatus').innerHTML = '<span>⚠️</span> Location required - Enable GPS';
        },
        { enableHighAccuracy: false, timeout: 3000, maximumAge: 300000 }
    );
}

// Smart button state management - shared with header
let attendanceStatus = <?= json_encode($attendance_status ?? []) ?>;

// Sync with header button status if available
if (typeof headerAttendanceStatus !== 'undefined') {
    headerAttendanceStatus = attendanceStatus;
}

function updateClockButton(status) {
    const btn = document.getElementById('clockBtn');
    const icon = document.getElementById('clockBtnIcon');
    const text = document.getElementById('clockBtnText');
    
    if (!btn || !icon || !text) return;
    
    btn.disabled = false;
    btn.onclick = null;
    
    if (status.on_leave) {
        // On Leave state
        text.textContent = 'On Leave';
        icon.textContent = '🏖️';
        btn.className = 'btn btn--secondary';
        btn.disabled = true;
    } else if (status.is_completed || (status.has_clocked_in && status.has_clocked_out)) {
        // Completed state
        text.textContent = 'Completed';
        icon.textContent = '✅';
        btn.className = 'btn btn--secondary';
        btn.disabled = true;
    } else if (!status.has_clocked_in) {
        // Clock In state
        text.textContent = 'Clock In';
        icon.textContent = '▶️';
        btn.className = 'btn btn--success';
        btn.onclick = () => clockAction('in');
    } else if (status.has_clocked_in && !status.has_clocked_out) {
        // Clock Out state
        text.textContent = 'Clock Out';
        icon.textContent = '⏹️';
        btn.className = 'btn btn--danger';
        btn.onclick = () => clockAction('out');
    }
    
    // Sync header button if available
    if (typeof updateHeaderAttendanceButton === 'function') {
        if (typeof headerAttendanceStatus !== 'undefined') {
            headerAttendanceStatus = status;
        }
        updateHeaderAttendanceButton();
    }
}

function clockAction(type) {
    const btn = document.getElementById('clockBtn');
    const text = document.getElementById('clockBtnText');
    
    // For clock in, show project selection
    if (type === 'in') {
        showProjectSelection();
        return;
    }
    
    // Disable button and show loading
    btn.disabled = true;
    const originalText = text.textContent;
    text.textContent = 'Clocking Out...';
    
    const formData = new FormData();
    formData.append('type', type);
    formData.append('latitude', currentPosition ? currentPosition.coords.latitude : 0);
    formData.append('longitude', currentPosition ? currentPosition.coords.longitude : 0);
    
    fetch('/ergon-site/attendance/clock', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update attendance status
            if (type === 'in') {
                attendanceStatus.has_clocked_in = true;
                attendanceStatus.clock_in_time = new Date().toISOString();
            } else {
                attendanceStatus.has_clocked_out = true;
                attendanceStatus.clock_out_time = new Date().toISOString();
            }
            
            // Update both buttons
            updateClockButton(attendanceStatus);
            
            // Sync header button status
            if (typeof headerAttendanceStatus !== 'undefined') {
                headerAttendanceStatus = attendanceStatus;
                if (typeof updateHeaderAttendanceButton === 'function') {
                    updateHeaderAttendanceButton();
                }
            }
            
            if (typeof showMessage === 'function') {
                showMessage(`Clocked ${type} successfully!`, 'success');
            } else {
                showSuccessAlert(`Clocked ${type} successfully!`);
            }
            setTimeout(() => window.location.href = '/ergon-site/attendance', 1500);
        } else {
            if (typeof showMessage === 'function') {
                showMessage(data.error || 'An error occurred', 'error');
            } else {
                // Check if it's a location restriction error
                if (data.error && data.error.includes('Please move within the allowed area')) {
                    showLocationAlert(data.error);
                } else {
                    showErrorAlert(data.error || 'An error occurred');
                }
            }
            // Restore button state
            text.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showMessage === 'function') {
            showMessage('Server error occurred. Please try again.', 'error');
        } else {
            showErrorAlert('Server error occurred. Please try again.');
        }
        // Restore button state
        text.textContent = originalText;
        btn.disabled = false;
    });
}

<?php if (!$on_leave): ?>
// Initialize smart button
updateClockButton(attendanceStatus);
<?php endif; ?>

// Alert functions
function showLocationAlert(message) {
    showModal(message, 'warning', '⚠️');
}

function showSuccessAlert(message) {
    showModal(message, 'success', '✅');
}

function showErrorAlert(message) {
    showModal(message, 'error', '❌');
}

function showModal(message, type, icon) {
    // Remove existing modal if any
    const existingModal = document.querySelector('.message-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = `message-modal ${type}`;
    modal.innerHTML = `
        <div class="message-content">
            <div class="message-icon">${icon}</div>
            <div class="message-text">${message}</div>
            <button class="message-close" onclick="this.closest('.message-modal').remove()">OK</button>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Auto-close after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            if (modal.parentNode) {
                modal.remove();
            }
        }, 5000);
    }
}

function showProjectSelection() {
    fetch('/ergon-site/api/user-projects')
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.projects.length) {
            showErrorAlert('No projects assigned. Contact your administrator.');
            return;
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>📁 Select Project</h3>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <label>Choose project to clock in:</label>
                    <select id="projectSelect" class="form-input">
                        <option value="">Select Project</option>
                        ${data.projects.map(p => `<option value="${p.id}">${p.name} ${p.latitude ? '📍' : ''}</option>`).join('')}
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn--secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                    <button class="btn btn--success" onclick="proceedClockIn()">Clock In</button>
                </div>
            </div>
        `;
        
        if (!document.getElementById('modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'modal-styles';
            styles.textContent = `
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10001;
                }
                .modal-content {
                    background: white;
                    border-radius: 8px;
                    width: 400px;
                    max-width: 90vw;
                }
                .modal-header {
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .modal-body {
                    padding: 16px;
                }
                .modal-body label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 500;
                }
                .modal-body .form-input {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                }
                .modal-footer {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    gap: 8px;
                    justify-content: flex-end;
                }
                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #6b7280;
                }
            `;
            document.head.appendChild(styles);
        }
        
        document.body.appendChild(modal);
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorAlert('Failed to load projects');
    });
}

function proceedClockIn() {
    const projectId = document.getElementById('projectSelect').value;
    if (!projectId) {
        showErrorAlert('Please select a project');
        return;
    }
    
    document.querySelector('.modal-overlay')?.remove();
    
    const btn = document.getElementById('clockBtn');
    const text = document.getElementById('clockBtnText');
    
    btn.disabled = true;
    text.textContent = 'Clocking In...';
    
    const formData = new FormData();
    formData.append('type', 'in');
    formData.append('project_id', projectId);
    formData.append('latitude', currentPosition ? currentPosition.coords.latitude : 0);
    formData.append('longitude', currentPosition ? currentPosition.coords.longitude : 0);
    
    fetch('/ergon-site/attendance/clock', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            attendanceStatus.has_clocked_in = true;
            updateClockButton(attendanceStatus);
            showSuccessAlert(data.message);
            setTimeout(() => window.location.href = '/ergon-site/attendance', 1500);
        } else {
            showErrorAlert(data.error || 'Clock in failed');
            btn.disabled = false;
            text.textContent = 'Clock In';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorAlert('Server error occurred');
        btn.disabled = false;
        text.textContent = 'Clock In';
    });
}

// Initialize
updateTime();
setInterval(updateTime, 1000);
getLocation();
</script>

<style>
.btn--success {
    background: #22c55e !important;
    color: white !important;
    border-color: #22c55e !important;
}

.btn--success:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
}

.btn--danger {
    background: #b91c1c !important;
    color: #ffffff !important;
    border-color: #991b1b !important;
    box-shadow: 0 4px 20px rgba(185,28,28,0.8) !important;
    font-weight: 800 !important;
    border-width: 3px !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

.btn--danger:hover {
    background: #dc2626 !important;
    border-color: #b91c1c !important;
    box-shadow: 0 6px 16px rgba(239,68,68,0.5) !important;
}

.btn--secondary {
    background: #059669 !important;
    color: white !important;
    border-color: #047857 !important;
    box-shadow: 0 4px 12px rgba(5,150,105,0.3) !important;
}

#clockBtn {
    transition: all 0.3s ease;
    min-width: 200px;
}

#clockBtn:disabled {
    opacity: 1 !important;
    cursor: not-allowed;
    background: #059669 !important;
    border-color: #047857 !important;
    box-shadow: 0 4px 12px rgba(5,150,105,0.3) !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
