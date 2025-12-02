var currentTaskId;

function openProgressModal(taskId, progress, status) {
    currentTaskId = taskId;
    var container = document.querySelector('[data-task-id="' + taskId + '"]');
    var currentProgress = container ? container.querySelector('.progress-percentage').textContent.replace('%', '') : progress;
    
    document.getElementById('progressSlider').value = currentProgress;
    document.getElementById('progressValue').textContent = currentProgress;
    document.getElementById('progressDescription').value = '';
    document.getElementById('progressDialog').style.display = 'flex';
    
    // Focus on description field
    setTimeout(() => {
        document.getElementById('progressDescription').focus();
    }, 100);
}

function closeDialog() {
    document.getElementById('progressDialog').style.display = 'none';
    document.getElementById('progressHistoryDialog').style.display = 'none';
}

function saveProgress() {
    var progress = document.getElementById('progressSlider').value;
    var description = document.getElementById('progressDescription').value.trim();
    
    if (!description) {
        alert('Please provide a description for this progress update.');
        document.getElementById('progressDescription').focus();
        return;
    }
    
    // Show loading state
    var saveBtn = document.querySelector('#progressDialog .btn-primary');
    var originalText = saveBtn.textContent;
    saveBtn.textContent = 'Updating...';
    saveBtn.disabled = true;
    
    fetch('/ergon-site/tasks/update-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            task_id: currentTaskId, 
            progress: parseInt(progress),
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var container = document.querySelector('[data-task-id="' + currentTaskId + '"]');
            if (container) {
                var fill = container.querySelector('.progress-fill');
                var percentage = container.querySelector('.progress-percentage');
                var statusEl = container.querySelector('.progress-status');
                
                fill.style.width = progress + '%';
                fill.style.background = getProgressColor(progress);
                percentage.textContent = progress + '%';
                
                var status = progress >= 100 ? 'completed' : progress > 0 ? 'in_progress' : 'assigned';
                var icon = status === 'completed' ? '✅' : status === 'in_progress' ? '⚡' : '📋';
                statusEl.textContent = icon + ' ' + status.replace('_', ' ');
            }
            closeDialog();
            
            // Show success message
            showNotification('Progress updated successfully!', 'success');
        } else {
            alert('Error: ' + (data.message || 'Failed to update progress'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating task progress');
    })
    .finally(() => {
        // Reset button state
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function getProgressColor(progress) {
    if (progress >= 100) return '#10b981';
    if (progress >= 75) return '#8b5cf6';
    if (progress >= 50) return '#3b82f6';
    if (progress >= 25) return '#f59e0b';
    return '#e2e8f0';
}

function showProgressHistory(taskId) {
    fetch('/ergon-site/tasks/progress-history/' + taskId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('progressHistoryContent').innerHTML = data.html;
            document.getElementById('progressHistoryDialog').style.display = 'flex';
        } else {
            alert('Error loading progress history');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading progress history');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    var notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.innerHTML = '<span>' + message + '</span><button onclick="this.parentElement.remove()">×</button>';
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Update progress slider display
document.addEventListener('DOMContentLoaded', function() {
    var progressSlider = document.getElementById('progressSlider');
    if (progressSlider) {
        progressSlider.oninput = function() {
            document.getElementById('progressValue').textContent = this.value;
        };
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDialog();
    }
    
    // Ctrl+Enter to save progress
    if (e.ctrlKey && e.key === 'Enter') {
        var progressDialog = document.getElementById('progressDialog');
        if (progressDialog && progressDialog.style.display === 'flex') {
            saveProgress();
        }
    }
});
