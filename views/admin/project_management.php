<?php
require_once __DIR__ . '/../../app/config/constants.php';
$title = 'Project Management';
$active_page = 'project-management';
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
                                <div>
                                    <?php if (!empty($project['place'])): ?>
                                        <strong><?= htmlspecialchars($project['place']) ?></strong><br>
                                    <?php endif; ?>
                                    <small>📍 <?= round($project['latitude'], 4) ?>, <?= round($project['longitude'], 4) ?></small><br>
                                    <span class="badge badge--info"><?= $project['checkin_radius'] ?>m radius</span>
                                </div>
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
                                <button class="ab-btn ab-btn--edit" onclick="editProject(<?= $project['id'] ?>, '<?= addslashes($project['name']) ?>', '<?= addslashes($project['description'] ?? '') ?>', '<?= addslashes($project['place'] ?? '') ?>', <?= $project['latitude'] ?? 'null' ?>, <?= $project['longitude'] ?? 'null' ?>, <?= $project['checkin_radius'] ?? 100 ?>, <?= $project['department_id'] ?? 'null' ?>, '<?= $project['status'] ?>', <?= $project['budget'] ?? 'null' ?>)" title="Edit Project">
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
    let modal = document.getElementById('projectModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'projectModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="margin: 5% auto; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
                <div class="modal-header">
                    <h3 id="modalTitle">📁 Add New Project</h3>
                    <button class="modal-close" onclick="hideProjectModal()">&times;</button>
                </div>
            <div class="modal-body">
                <form id="projectForm">
                    
                    <label>Project Name *</label>
                    <input type="text" id="projectName" name="name" class="form-control" required placeholder="Enter project name">
                    
                    <label>Department</label>
                    <select id="projectDepartment" name="department_id" class="form-control">
                        <option value="">Select Department</option>
                        <?php foreach ($data['departments'] as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Description</label>
                    <textarea id="projectDescription" name="description" class="form-control" rows="3" placeholder="Project description"></textarea>
                    
                    <label>Budget (₹)</label>
                    <input type="number" id="projectBudget" name="budget" class="form-control" step="0.01" min="0" placeholder="Enter project budget">
                    
                    <label>📍 Place Name</label>
                    <input type="text" id="projectPlace" name="place" class="form-control" placeholder="Enter place name">
                    
                    <label>🔍 Search Location</label>
                    <input type="text" id="locationSearch" class="form-control" placeholder="Search for location...">
                    
                    <div id="locationMap" style="height: 250px; border-radius: 8px; margin: 1rem 0; border: 2px solid #ddd;"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 1rem;">
                        <button type="button" class="btn btn--secondary" onclick="window.toggleCoordinateEdit(false)">📝 Edit Coordinates</button>
                        <button type="button" class="btn btn--secondary" onclick="window.toggleCoordinateEdit(true)">🔒 Lock Coordinates</button>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <label>Latitude</label>
                            <input type="number" id="projectLatitude" name="latitude" class="form-control" step="0.000001" readonly>
                        </div>
                        <div>
                            <label>Longitude</label>
                            <input type="number" id="projectLongitude" name="longitude" class="form-control" step="0.000001" readonly>
                        </div>
                    </div>
                    
                    <label>Check-in Radius (meters)</label>
                    <input type="number" id="projectRadius" name="checkin_radius" class="form-control" value="100" min="10" max="1000">
                    
                    <button type="button" class="btn btn--secondary" onclick="getCurrentLocationForProject()" style="margin-top: 0.5rem;">
                        <span>📍</span> Use Current Location
                    </button>
                    
                    <div id="statusGroup" style="display: none;">
                        <label>Status</label>
                        <select id="projectStatus" name="status" class="form-control">
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
                <button class="btn btn--secondary" onclick="hideProjectModal()">Cancel</button>
                <button class="btn btn--primary" onclick="submitProjectForm()"><span id="submitText">Add Project</span></button>
            </div>
        </div>
        `;
        document.body.appendChild(modal);
    }
    
    document.getElementById('modalTitle').textContent = '📁 Add New Project';
    document.getElementById('submitText').textContent = 'Add Project';
    document.getElementById('projectForm').reset();
    const existingId = document.getElementById('projectId');
    if (existingId) existingId.remove();
    document.getElementById('statusGroup').style.display = 'none';
    
    modal.style.display = 'flex';
}

function editProject(id, name, description, place, latitude, longitude, radius, deptId, status, budget) {
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
        document.getElementById('projectDepartment').value = deptId || '';
        document.getElementById('projectBudget').value = budget || '';
        document.getElementById('projectStatus').value = status;
        document.getElementById('statusGroup').style.display = 'block';
        
        // Set location data
        document.getElementById('projectPlace').value = place || '';
        document.getElementById('projectLatitude').value = latitude || '';
        document.getElementById('projectLongitude').value = longitude || '';
        document.getElementById('projectRadius').value = radius || 100;
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
            hideProjectModal();
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

function hideProjectModal() {
    const modal = document.getElementById('projectModal');
    if (modal) {
        modal.style.display = 'none';
    }
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

<script>
function initProjectMap() {
    const mapElement = document.getElementById('locationMap');
    if (!mapElement) return;
    
    mapElement.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #f0f0f0; border-radius: 8px; padding: 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🗺️</div>
            <div style="font-weight: 600; margin-bottom: 8px;">Click coordinates to set location</div>
            <div style="font-size: 14px; color: #666;">Enter latitude and longitude manually</div>
        </div>
    `;
}

function searchProjectLocation(query) {
    if (query.length < 3) {
        alert('Please enter at least 3 characters to search');
        return;
    }
    alert('Location found! Please enter coordinates manually for: ' + query);
}

window.toggleCoordinateEdit = function(lock) {
    const latInput = document.getElementById('projectLatitude');
    const lngInput = document.getElementById('projectLongitude');
    
    if (latInput && lngInput) {
        latInput.readOnly = lock;
        lngInput.readOnly = lock;
        
        if (lock) {
            latInput.style.backgroundColor = '#f8f9fa';
            lngInput.style.backgroundColor = '#f8f9fa';
        } else {
            latInput.style.backgroundColor = 'white';
            lngInput.style.backgroundColor = 'white';
        }
    }
};

function setProjectMapLocation(lat, lng, place) {
    updateProjectLocationInputs(lat, lng);
    if (place) {
        document.getElementById('projectPlace').value = place;
    }
}

function getCurrentLocationForProject() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                updateProjectLocationInputs(lat, lng);
                document.getElementById('projectPlace').value = `Current Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            },
            function(error) {
                alert('Error getting location: ' + error.message);
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}
</script>

<script>
function updateProjectLocationInputs(lat, lng) {
    document.getElementById('projectLatitude').value = lat.toFixed(6);
    document.getElementById('projectLongitude').value = lng.toFixed(6);
}

// Initialize map when modal is shown
const originalShowAddProjectModal = showAddProjectModal;
showAddProjectModal = function() {
    originalShowAddProjectModal();
    setTimeout(() => {
        initProjectMap();
    }, 300);
};

// Manual coordinate validation and search functionality
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('input', function(e) {
        if (e.target.id === 'projectLatitude' || e.target.id === 'projectLongitude') {
            const lat = document.getElementById('projectLatitude').value;
            const lng = document.getElementById('projectLongitude').value;
            if (lat && lng) {
                document.getElementById('projectPlace').value = `Location (${lat}, ${lng})`;
            }
        }
    });
    
    document.addEventListener('keypress', function(e) {
        if (e.target.id === 'locationSearch' && e.key === 'Enter') {
            e.preventDefault();
            searchProjectLocation(e.target.value);
        }
    });
});
</script>



<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
