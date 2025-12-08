<?php
$title = 'Project Management';
$active_page = 'project-management';
include __DIR__ . '/../shared/modal_component.php';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1><span>📁</span> Project Management</h1>
        <p>Manage projects and departments for task organization</p>
    </div>
    <div class="page-actions">
        <button class="btn btn--primary" onclick="showAddProjectModal()">
            <span>➕</span> Add Project
        </button>
    </div>
</div>



<div class="dashboard-grid">
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">📁</div>
        </div>
        <div class="kpi-card__value"><?= count($data['projects']) ?></div>
        <div class="kpi-card__label">Total Projects</div>
        <div class="kpi-card__status">Active</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">✅</div>
        </div>
        <div class="kpi-card__value"><?= count(array_filter($data['projects'], function($p) { return $p['status'] === 'active'; })) ?></div>
        <div class="kpi-card__label">Active Projects</div>
        <div class="kpi-card__status">Running</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">🏢</div>
        </div>
        <div class="kpi-card__value"><?= count($data['departments']) ?></div>
        <div class="kpi-card__label">Departments</div>
        <div class="kpi-card__status">Available</div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">
            <span>📁</span> Projects List
        </h2>
    </div>
    <div class="card__body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['projects'] as $project): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($project['name']) ?></strong></td>
                        <td>
                            <?php if ($project['department_name']): ?>
                                <span class="badge badge--info"><?= htmlspecialchars($project['department_name']) ?></span>
                            <?php else: ?>
                                <span class="badge badge--secondary">General</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($project['latitude']) && !empty($project['longitude'])): ?>
                                <span class="badge badge--info">📍 <?= $project['checkin_radius'] ?>m radius</span>
                            <?php else: ?>
                                <span class="badge badge--secondary">No location</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge--<?= $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'info' : 'warning') ?>">
                                <?= ucfirst($project['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($project['created_at'])) ?></td>
                        <td>
                            <div class="ab-container">
                                <button class="ab-btn ab-btn--edit" onclick="editProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['name']) ?>', '<?= htmlspecialchars($project['description']) ?>', <?= $project['latitude'] ?? 'null' ?>, <?= $project['longitude'] ?? 'null' ?>, <?= $project['checkin_radius'] ?? 100 ?>, <?= $project['department_id'] ?? 'null' ?>, '<?= $project['status'] ?>')" title="Edit Project">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        <path d="M15 5l4 4"/>
                                    </svg>
                                </button>
                                <button class="ab-btn ab-btn--danger" onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['name']) ?>')" title="Delete Project">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <polyline points="3,6 5,6 21,6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
let isEditing = false;

function showAddProjectModal() {
    isEditing = false;
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">📁 Add New Project</h3>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="projectForm">
                    
                    <label>Project Name *</label>
                    <input type="text" id="projectName" name="name" class="form-input" required placeholder="Enter project name">
                    
                    <label>Department</label>
                    <select id="projectDepartment" name="department_id" class="form-input">
                        <option value="">Select Department</option>
                        <?php foreach ($data['departments'] as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Description</label>
                    <textarea id="projectDescription" name="description" class="form-input" rows="3" placeholder="Project description"></textarea>
                    
                    <label>GPS Coordinates (for attendance)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="number" id="projectLatitude" name="latitude" class="form-input" step="any" placeholder="Latitude">
                        <input type="number" id="projectLongitude" name="longitude" class="form-input" step="any" placeholder="Longitude">
                    </div>
                    
                    <label>Check-in Radius (meters)</label>
                    <input type="number" id="projectRadius" name="checkin_radius" class="form-input" value="100" min="10" max="1000">
                    
                    <div id="statusGroup" style="display: none;">
                        <label>Status</label>
                        <select id="projectStatus" name="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="withheld">Withheld</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn--secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <button class="btn btn--primary" onclick="submitProjectForm()"><span id="submitText">Add Project</span></button>
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
                width: 500px;
                max-width: 90vw;
                max-height: 90vh;
                overflow-y: auto;
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
                margin-bottom: 4px;
                font-weight: 500;
            }
            .modal-body .form-input {
                width: 100%;
                margin-bottom: 12px;
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
}

function editProject(id, name, description, latitude, longitude, radius, deptId, status) {
    isEditing = true;
    showAddProjectModal();
    
    setTimeout(() => {
        const form = document.getElementById('projectForm');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = 'projectId';
        hiddenInput.name = 'project_id';
        hiddenInput.value = id;
        form.insertBefore(hiddenInput, form.firstChild);
        
        document.getElementById('modalTitle').textContent = '📁 Edit Project';
        document.getElementById('submitText').textContent = 'Update Project';
        document.getElementById('projectName').value = name;
        document.getElementById('projectDescription').value = description;
        document.getElementById('projectLatitude').value = latitude || '';
        document.getElementById('projectLongitude').value = longitude || '';
        document.getElementById('projectRadius').value = radius || 100;
        document.getElementById('projectDepartment').value = deptId || '';
        document.getElementById('projectStatus').value = status;
        document.getElementById('statusGroup').style.display = 'block';
    }, 100);
}

function submitProjectForm() {
    const form = document.getElementById('projectForm');
    const formData = new FormData(form);
    const projectId = document.getElementById('projectId');
    const url = projectId ? '/ergon-site/project-management/update' : '/ergon-site/project-management/create';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.modal-overlay')?.remove();
            location.reload();
        } else {
            alert('Failed to save project: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save project');
    });
}

function deleteProject(id, name) {
    if (confirm(`Are you sure you want to delete project "${name}"? This action cannot be undone.`)) {
        const formData = new FormData();
        formData.append('project_id', id);
        
        fetch('/ergon-site/project-management/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete project: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete project');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
