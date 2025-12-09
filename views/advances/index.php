<?php
$title = 'Advance Requests';
$active_page = 'advances';
include __DIR__ . '/../shared/modal_component.php';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1><span>💳</span> Advance Requests</h1>
        <p>Manage employee salary advance requests and approvals</p>
    </div>
    <div class="page-actions">
        <button onclick="showAdvanceModal()" class="btn btn--primary">
            <span>➕</span> Request Advance
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert--success">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert--error">
    ❌ <?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<style>
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
</style>

<?php renderModalCSS(); ?>

<div class="dashboard-grid">
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">💳</div>
            <div class="kpi-card__trend">↗ +10%</div>
        </div>
        <div class="kpi-card__value"><?= count($advances ?? []) ?></div>
        <div class="kpi-card__label">Total Requests</div>
        <div class="kpi-card__status">Submitted</div>
    </div>
    
    <div class="kpi-card kpi-card--warning">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">⏳</div>
            <div class="kpi-card__trend kpi-card__trend--down">— 0%</div>
        </div>
        <div class="kpi-card__value"><?= count(array_filter($advances ?? [], fn($a) => ($a['status'] ?? 'pending') === 'pending')) ?></div>
        <div class="kpi-card__label">Pending Review</div>
        <div class="kpi-card__status kpi-card__status--pending">Under Review</div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">
            <span>💳</span> Advance Requests
        </h2>
    </div>
    <div class="card__body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Repayment Date</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advances ?? [])): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <div class="empty-icon">💳</div>
                                <h3>No Advance Requests</h3>
                                <p>No advance requests have been submitted yet.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($advances as $advance): ?>
                        <tr>
                            <td>
                                <?php 
                                $employeeRole = ucfirst($advance['user_role'] ?? 'user');
                                if ($employeeRole === 'User') $employeeRole = 'Employee';
                                
                                $employeeName = htmlspecialchars($advance['user_name'] ?? 'Unknown');
                                $isCurrentUser = ($advance['user_id'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                $displayName = $isCurrentUser ? "Myself ({$employeeName})" : $employeeName;
                                echo $displayName . ' - ' . $employeeRole;
                                ?>
                            </td>
                            <td><?= htmlspecialchars(!empty($advance['type']) ? $advance['type'] : 'General Advance') ?></td>
                            <td>₹<?= number_format($advance['amount'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($advance['reason'] ?? '') ?></td>
                            <td><?= !empty($advance['repayment_date']) ? date('M d, Y', strtotime($advance['repayment_date'])) : 'N/A' ?></td>
                            <td>
                                <?php 
                                $advanceStatus = $advance['status'] ?? 'pending';
                                $statusBadgeClass = match($advanceStatus) {
                                    'approved' => 'badge--success',
                                    'rejected' => 'badge--danger',
                                    default => 'badge--warning'
                                };
                                ?>
                                <span class="badge <?= $statusBadgeClass ?>"><?= ucfirst($advanceStatus) ?></span>
                            </td>
                            <td><?= date('M d, Y', strtotime($advance['created_at'] ?? 'now')) ?></td>
                            <td>
                                <div class="ab-container">
                                    <a class="ab-btn ab-btn--view" data-action="view" data-module="advances" data-id="<?= $advance['id'] ?>" title="View Details">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14,2 14,8 20,8"/>
                                            <line x1="16" y1="13" x2="8" y2="13"/>
                                            <line x1="16" y1="17" x2="8" y2="17"/>
                                        </svg>
                                    </a>
                                    <?php if (($advance['status'] ?? 'pending') === 'pending' && ($advance['user_id'] ?? 0) == ($_SESSION['user_id'] ?? 0)): ?>
                                    <button class="ab-btn ab-btn--edit" onclick="editAdvance(<?= $advance['id'] ?>)" title="Edit Advance">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="M15 5l4 4"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    <?php 
                                    $currentUserRole = $user_role ?? '';
                                    $isPending = ($advance['status'] ?? 'pending') === 'pending';
                                    $isNotOwnAdvance = ($advance['user_id'] ?? 0) != ($_SESSION['user_id'] ?? 0);
                                    $canApprove = $isPending && in_array($currentUserRole, ['owner', 'admin']);
                                    ?>
                                    <?php if ($canApprove): ?>
                                    <button class="ab-btn ab-btn--approve" data-action="approve" data-module="advances" data-id="<?= $advance['id'] ?>" data-name="Advance Request" title="Approve Advance">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                    </button>
                                    <button class="ab-btn ab-btn--reject" onclick="showRejectModal(<?= $advance['id'] ?>)" title="Reject Advance">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (in_array($user_role ?? '', ['admin', 'owner']) || (($user_role ?? '') === 'user' && ($advance['status'] ?? 'pending') === 'pending')): ?>
                                    <button class="ab-btn ab-btn--delete" data-action="delete" data-module="advances" data-id="<?= $advance['id'] ?>" data-name="Advance Request" title="Delete Request">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M3 6h18"/>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Rejection Modal Content
$rejectContent = '
<form id="rejectForm" method="POST">
    <div class="form-group">
        <label for="rejection_reason">Reason for Rejection:</label>
        <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this advance request..." required></textarea>
    </div>
</form>';

$rejectFooter = '<button type="button" class="btn btn--secondary" onclick="closeModal(\'rejectModal\')">Cancel</button><button type="submit" form="rejectForm" class="btn btn--danger">Reject Advance</button>';

// Render Modal
renderModal('rejectModal', 'Reject Advance Request', $rejectContent, $rejectFooter, ['icon' => '❌']);
?>



<script>
function showRejectModal(advanceId) {
    const form = document.getElementById('rejectForm');
    if (form) {
        form.action = '/ergon-site/advances/reject/' + advanceId;
        form.method = 'POST';
        // Clear previous reason
        const reasonField = document.getElementById('rejection_reason');
        if (reasonField) {
            reasonField.value = '';
        }
    }
    showModal('rejectModal');
}

// Handle form submission validation
document.addEventListener('DOMContentLoaded', function() {
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const reason = document.getElementById('rejection_reason');
            if (!reason || !reason.value.trim()) {
                e.preventDefault();
                alert('Please provide a reason for rejection.');
                if (reason) reason.focus();
                return false;
            }
            
            // Show loading state
            const submitBtn = rejectForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Rejecting...';
            }
        });
    }
});
</script>

<!-- Advance Modal -->
<div id="advanceModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="advanceModalTitle">💳 Request Advance</h3>
            <button class="modal-close" onclick="closeAdvanceModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="advanceForm">
                <input type="hidden" id="advance_id" name="advance_id">
                <label>Advance Type *</label>
                <select id="type" name="type" class="form-input" required>
                    <option value="">Select advance type</option>
                    <option value="Salary Advance">Salary Advance</option>
                    <option value="Travel Advance">Travel Advance</option>
                    <option value="Emergency Advance">Emergency Advance</option>
                    <option value="Project Advance">Project Advance</option>
                </select>
                
                <label>Project *</label>
                <select id="adv_project_id" name="project_id" class="form-input" required>
                    <option value="">Select Project</option>
                </select>
                
                <label>Amount (₹) *</label>
                <input type="number" id="adv_amount" name="amount" class="form-input" step="0.01" min="1" required>
                
                <label>Reason *</label>
                <textarea id="reason" name="reason" class="form-input" rows="4" required></textarea>
                
                <label>Expected Repayment Date (Optional)</label>
                <input type="date" id="repayment_date" name="repayment_date" class="form-input">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn--secondary" onclick="closeAdvanceModal()">Cancel</button>
            <button class="btn btn--primary" onclick="submitAdvanceForm()" id="advanceSubmitBtn">➕ Submit Request</button>
        </div>
    </div>
</div>

<script>
let isEditingAdvance = false;

function showAdvanceModal() {
    isEditingAdvance = false;
    document.getElementById('advanceModalTitle').textContent = '💳 Request Advance';
    document.getElementById('advanceSubmitBtn').textContent = '➕ Submit Request';
    document.getElementById('advanceForm').reset();
    document.getElementById('advance_id').value = '';
    document.getElementById('advanceModal').style.display = 'flex';
    loadAdvanceProjects('adv_project_id');
}

function editAdvance(id) {
    isEditingAdvance = true;
    document.getElementById('advanceModalTitle').textContent = '💳 Edit Advance';
    document.getElementById('advanceSubmitBtn').textContent = '💾 Update Request';
    document.getElementById('advanceModal').style.display = 'flex';
    
    fetch(`/ergon-site/api/advance.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const a = data.advance;
                document.getElementById('advance_id').value = a.id;
                document.getElementById('type').value = a.type;
                document.getElementById('adv_project_id').value = a.project_id || '';
                document.getElementById('adv_amount').value = a.amount;
                document.getElementById('reason').value = a.reason;
                document.getElementById('repayment_date').value = a.repayment_date || '';
                loadAdvanceProjects('adv_project_id', a.project_id);
            }
        });
}

function closeAdvanceModal() {
    document.getElementById('advanceModal').style.display = 'none';
}

function loadAdvanceProjects(selectId, selectedId = null) {
    fetch('/ergon-site/api/projects.php')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">Select Project</option>';
            if (data.success && data.projects) {
                data.projects.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    let text = p.project_name;
                    if (p.department_name) text += ' - ' + p.department_name;
                    if (p.description) text += ' (' + p.description + ')';
                    opt.textContent = text;
                    if (selectedId && p.id == selectedId) opt.selected = true;
                    select.appendChild(opt);
                });
            }
        });
}

function submitAdvanceForm() {
    const form = document.getElementById('advanceForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const btn = document.getElementById('advanceSubmitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Submitting...';
    
    const formData = new FormData(form);
    const url = isEditingAdvance ? `/ergon-site/advances/edit/${formData.get('advance_id')}` : '/ergon-site/advances/create';
    
    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
                btn.disabled = false;
                btn.textContent = isEditingAdvance ? '💾 Update Request' : '➕ Submit Request';
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = isEditingAdvance ? '💾 Update Request' : '➕ Submit Request';
        });
}
</script>

<script>
// Global action button handler
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.ab-btn');
    if (!btn) return;
    
    const action = btn.dataset.action;
    const module = btn.dataset.module;
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    
    if (action === 'view' && module && id) {
        window.location.href = `/ergon-site/${module}/view/${id}`;
    } else if (action === 'delete' && module && id && name) {
        deleteRecord(module, id, name);
    } else if (action === 'approve' && module && id) {
        window.location.href = `/ergon-site/${module}/approve/${id}`;
    }
});
</script>

<?php renderModalJS(); ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>

<script>
// Defensive: ensure no modal remains open when arriving at this page
document.addEventListener('DOMContentLoaded', function() {
    const selectors = ['.modal', '.modal-overlay', '[id$="Modal"]'];
    const els = document.querySelectorAll(selectors.join(','));
    if (typeof hideModalById === 'function') {
        els.forEach(el => { if (el.id) hideModalById(el.id); else el.style.display = 'none'; });
    } else if (typeof hideClosestModal === 'function') {
        els.forEach(el => hideClosestModal(el));
    } else {
        els.forEach(el => { try { el.style.display = 'none'; } catch(e){} });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    }
});
</script>
