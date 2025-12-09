<?php
$title = 'Expense Claims';
$active_page = 'expenses';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1><span>💰</span> Expense Management</h1>
        <p>Track and manage employee expense claims</p>
    </div>
    <div class="page-actions">
        <button onclick="showExpenseModal()" class="btn btn--primary">
            <span>💰</span> Submit Expense
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

<div class="dashboard-grid">
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">💰</div>
            <div class="kpi-card__trend">↗ +15%</div>
        </div>
        <div class="kpi-card__value"><?= count($expenses ?? []) ?></div>
        <div class="kpi-card__label">Total Claims</div>
        <div class="kpi-card__status">Submitted</div>
    </div>
    
    <div class="kpi-card kpi-card--warning">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">⏳</div>
            <div class="kpi-card__trend kpi-card__trend--down">— 0%</div>
        </div>
        <div class="kpi-card__value"><?= count(array_filter($expenses ?? [], fn($e) => ($e['status'] ?? 'pending') === 'pending')) ?></div>
        <div class="kpi-card__label">Pending Review</div>
        <div class="kpi-card__status kpi-card__status--pending">Under Review</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon">✅</div>
            <div class="kpi-card__trend">↗ +22%</div>
        </div>
        <div class="kpi-card__value">₹<?= number_format(array_sum(array_map(fn($e) => $e['amount'] ?? 0, array_filter($expenses ?? [], fn($e) => ($e['status'] ?? 'pending') === 'approved'))), 2) ?></div>
        <div class="kpi-card__label">Approved Amount</div>
        <div class="kpi-card__status">Processed</div>
    </div>
</div>



<div class="card">
    <div class="card__header">
        <h2 class="card__title">
            <span>💰</span> Expense Claims
        </h2>
    </div>
    <div class="card__body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="col-employee">Employee</th>
                        <th class="col-description">Description</th>
                        <th class="col-amount">Amount</th>
                        <th class="col-date">Date</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses ?? [])): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="empty-state">
                                <div class="empty-icon">💰</div>
                                <h3>No Expense Claims</h3>
                                <p>No expense claims have been submitted yet.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td>
                            <?php 
                            $employeeRole = ucfirst($expense['user_role'] ?? 'user');
                            if ($employeeRole === 'User') $employeeRole = 'Employee';
                            
                            $employeeName = htmlspecialchars($expense['user_name'] ?? 'Unknown');
                            $isCurrentUser = ($expense['user_id'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                            $displayName = $isCurrentUser ? "Myself ({$employeeName})" : $employeeName;
                            ?>
                            <strong><?= $displayName ?></strong>
                            <br><small class="text-muted"><?= $employeeRole ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($expense['description'] ?? '') ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($expense['category'] ?? 'General') ?></small>
                        </td>
                        <td>
                            <strong>₹<?= number_format($expense['amount'] ?? 0, 2) ?></strong>
                        </td>
                        <td><?= !empty($expense['expense_date']) ? date('M d, Y', strtotime($expense['expense_date'])) : 'N/A' ?></td>
                        <td>
                            <?php 
                            $expenseStatus = $expense['status'] ?? 'pending';
                            $statusBadgeClass = match($expenseStatus) {
                                'approved' => 'badge--success',
                                'rejected' => 'badge--danger',
                                default => 'badge--warning'
                            };
                            ?>
                            <span class="badge <?= $statusBadgeClass ?>"><?= ucfirst($expenseStatus) ?></span>
                        </td>
                        <td>
                            <div class="ab-container">
                                <a class="ab-btn ab-btn--view" data-action="view" data-module="expenses" data-id="<?= $expense['id'] ?>" title="View Details">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                </a>
                                <?php if (($expense['status'] ?? 'pending') === 'pending' && ($expense['user_id'] ?? 0) == ($_SESSION['user_id'] ?? 0)): ?>
                                <button class="ab-btn ab-btn--edit" onclick="editExpense(<?= $expense['id'] ?>)" title="Edit Expense">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        <path d="M15 5l4 4"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                                <?php 
                                $userRole = $user_role ?? '';
                                $expenseStatus = $expense['status'] ?? 'pending';
                                $isOwner = $userRole === 'owner';
                                $isAdmin = $userRole === 'admin';
                                $isPending = $expenseStatus === 'pending';
                                $isNotOwnExpense = ($expense['user_id'] ?? 0) != ($_SESSION['user_id'] ?? 0);
                                
                                $canApprove = $isPending && (($isOwner) || ($isAdmin && $isNotOwnExpense));
                                ?>
                                <?php if ($canApprove): ?>
                                <button class="ab-btn ab-btn--approve" data-action="approve" data-module="expenses" data-id="<?= $expense['id'] ?>" data-name="Expense Claim" title="Approve Expense">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <polyline points="20,6 9,17 4,12"/>
                                    </svg>
                                </button>
                                <?php if (($isOwner) || ($isAdmin && $isNotOwnExpense)): ?>
                                <button class="ab-btn ab-btn--reject" onclick="showRejectModal(<?= $expense['id'] ?>)" title="Reject Expense">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php if (in_array($user_role ?? '', ['admin', 'owner']) || (($user_role ?? '') === 'user' && ($expense['status'] ?? 'pending') === 'pending')): ?>
                                <button class="ab-btn ab-btn--delete" data-action="delete" data-module="expenses" data-id="<?= $expense['id'] ?>" data-name="Expense Claim" title="Delete Claim">
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



<!-- Rejection Modal -->
<div id="rejectModal" class="modal-overlay" data-visible="false">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Reject Expense Claim</h3>
            <span class="close" onclick="closeRejectModal()">&times;</span>
        </div>
        <form id="rejectForm" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejection_reason">Reason for Rejection:</label>
                    <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4" placeholder="Please provide a reason for rejecting this expense claim..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn--danger">Reject Expense</button>
            </div>
        </form>
    </div>
</div>



<script>
function showRejectModal(expenseId) {
    document.getElementById('rejectForm').action = '/ergon-site/expenses/reject/' + expenseId;
    const reasonField = document.getElementById('rejection_reason');
    if (reasonField) reasonField.value = '';
    showModal('rejectModal');
}

function closeRejectModal() {
    hideModal('rejectModal');
}
</script>

<!-- Expense Modal -->
<div id="expenseModal" class="modal-overlay" data-visible="false">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="expenseModalTitle">💰 Submit Expense</h3>
            <button class="modal-close" onclick="closeExpenseModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="expenseForm" enctype="multipart/form-data">
                <input type="hidden" id="expense_id" name="expense_id">
                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label>Category *</label>
                        <select id="category" name="category" class="form-input" required>
                            <option value="">Select Category</option>
                            <option value="travel">🚗 Travel & Transportation</option>
                            <option value="food">🍽️ Food & Meals</option>
                            <option value="accommodation">🏨 Accommodation</option>
                            <option value="office_supplies">📋 Office Supplies</option>
                            <option value="communication">📱 Communication</option>
                            <option value="training">📚 Training & Development</option>
                            <option value="medical">🏥 Medical Expenses</option>
                            <option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label>Project (Optional)</label>
                        <select id="project_id" name="project_id" class="form-input">
                            <option value="">Select Project</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label>Amount (₹) *</label>
                        <input type="number" id="amount" name="amount" class="form-input" step="0.01" min="0.01" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Expense Date *</label>
                        <input type="date" id="expense_date" name="expense_date" class="form-input" required>
                    </div>
                </div>
                <label>Receipt (Optional)</label>
                <input type="file" id="receipt" name="receipt" class="form-input" accept=".jpg,.jpeg,.png,.pdf" style="margin-bottom: 12px;">
                <label>Description *</label>
                <textarea id="description" name="description" class="form-input" rows="4" required></textarea>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn--secondary" onclick="closeExpenseModal()">Cancel</button>
            <button class="btn btn--primary" onclick="submitExpenseForm()" id="expenseSubmitBtn">💸 Submit Expense</button>
        </div>
    </div>
</div>

<script>
let isEditingExpense = false;

function showExpenseModal() {
    isEditingExpense = false;
    document.getElementById('expenseModalTitle').textContent = '💰 Submit Expense';
    document.getElementById('expenseSubmitBtn').textContent = '💸 Submit Expense';
    document.getElementById('expenseForm').reset();
    document.getElementById('expense_id').value = '';
    document.getElementById('expense_date').value = new Date().toISOString().split('T')[0];
    showModal('expenseModal');
    loadProjects('project_id');
}

function editExpense(id) {
    isEditingExpense = true;
    document.getElementById('expenseModalTitle').textContent = '💰 Edit Expense';
    document.getElementById('expenseSubmitBtn').textContent = '💾 Update Expense';
    showModal('expenseModal');
    
    fetch(`/ergon-site/api/expense?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const e = data.expense;
                document.getElementById('expense_id').value = e.id;
                document.getElementById('category').value = e.category;
                document.getElementById('project_id').value = e.project_id || '';
                document.getElementById('amount').value = e.amount;
                document.getElementById('expense_date').value = e.expense_date;
                document.getElementById('description').value = e.description;
                loadProjects('project_id', e.project_id);
            }
        });
}

function closeExpenseModal() {
    hideModal('expenseModal');
}

function loadProjects(selectId, selectedId = null) {
    fetch('/ergon-site/api/projects')
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

function submitExpenseForm() {
    const form = document.getElementById('expenseForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const btn = document.getElementById('expenseSubmitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Submitting...';
    
    const formData = new FormData(form);
    const expenseId = formData.get('expense_id');
    const url = isEditingExpense && expenseId ? `/ergon-site/expenses/edit/${expenseId}` : '/ergon-site/expenses/create';
    
    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
                btn.disabled = false;
                btn.textContent = isEditingExpense ? '💾 Update Expense' : '💸 Submit Expense';
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = isEditingExpense ? '💾 Update Expense' : '💸 Submit Expense';
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
