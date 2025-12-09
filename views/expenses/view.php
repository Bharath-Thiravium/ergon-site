<?php
$title = 'Expense Claim Details';
$active_page = 'expenses';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1><span>💰</span> Expense Claim Details</h1>
        <p>View expense claim information</p>
    </div>
    <div class="page-actions">
        <?php 
        $userRole = $_SESSION['role'] ?? '';
        $expenseStatus = $expense['status'] ?? 'pending';
        $isOwner = $userRole === 'owner';
        $isAdmin = $userRole === 'admin';
        $isPending = $expenseStatus === 'pending';
        $isNotOwnExpense = ($expense['user_id'] ?? 0) != ($_SESSION['user_id'] ?? 0);
        
        $canApprove = $isPending && (($isOwner) || ($isAdmin && $isNotOwnExpense));
        ?>
        <?php if ($canApprove): ?>
        <form method="POST" action="/ergon-site/expenses/approve/<?= $expense['id'] ?>" style="display:inline-block;">
            <input type="number" step="0.01" name="approved_amount" value="<?= number_format($expense['amount'] ?? 0, 2, '.', '') ?>" class="form-control" style="display:inline-block; width:140px; margin-right:.5rem;" required />
            <button type="submit" class="btn btn--success">
                <span>✅</span> Approve
            </button>
        </form>
        <button class="btn btn--danger" onclick="showRejectModal(<?= $expense['id'] ?>)">
            <span>❌</span> Reject
        </button>
        <?php endif; ?>
        <a href="/ergon-site/expenses" class="btn btn--secondary">
            <span>←</span> Back to Expenses
        </a>
        <?php if (in_array($userRole, ['admin','owner']) && ($expense['status'] ?? '') === 'approved'): ?>
        <form id="expensePaidForm" method="POST" action="/ergon-site/expenses/paid/<?= $expense['id'] ?>" enctype="multipart/form-data" style="display:inline-block; margin-left:.5rem;" onsubmit="return validateProofUpload();">
            <label for="expense_proof_input" class="btn" style="display:inline-block; cursor:pointer;">
                <span>📎</span> Upload Proof
            </label>
            <input id="expense_proof_input" type="file" name="proof" style="display:none;" accept="image/*,.pdf" required />
            <span id="expense_proof_name" style="margin-left:.5rem; font-size:0.95rem; color:var(--text-secondary);"></span>
            <button id="expense_proof_submit" type="submit" class="btn btn--primary" disabled style="margin-left:.5rem;">Mark Paid</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="expense-compact">
    <div class="card">
        <div class="card__header">
            <div class="expense-title-row">
                <h2 class="expense-title">💰 <?= htmlspecialchars($expense['description'] ?? 'Expense Claim') ?></h2>
                <div class="expense-badges">
                    <?php 
                    $status = $expense['status'] ?? 'pending';
                    $statusClass = match($status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'paid',
                        default => 'warning'
                    };
                    $statusIcon = match($status) {
                        'approved' => '✅',
                        'rejected' => '❌',
                        'paid' => '✓',
                        default => '⏳'
                    };
                    ?>
                    <span class="badge badge--<?= $statusClass ?>"><?= $statusIcon ?> <?= ucfirst($status) ?></span>
                    <div class="amount-display">
                        <?php if (in_array($expense['status'] ?? '', ['approved', 'paid']) && !empty($approved['approved_amount'])): ?>
                            <span class="amount-text" title="Approved Amount">₹<?= number_format($approved['approved_amount'], 2) ?></span>
                        <?php else: ?>
                            <span class="amount-text" title="Claimed Amount">₹<?= number_format($expense['amount'] ?? 0, 2) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card__body">
            <?php if (($expense['status'] ?? 'pending') === 'rejected' && !empty($expense['rejection_reason'])): ?>
            <div class="description-compact rejection-notice">
                <strong>Rejection Reason:</strong> <?= htmlspecialchars($expense['rejection_reason']) ?>
            </div>
            <?php endif; ?>
            
            <div class="details-compact">
                <div class="detail-group">
                    <h4>👤 Employee Details</h4>
                    <div class="detail-items">
                        <span><strong>Name:</strong> 👤 <?= htmlspecialchars($expense['user_name'] ?? 'Unknown') ?></span>
                        <span><strong>Category:</strong> 🏷️ <?= htmlspecialchars($expense['category'] ?? 'General') ?></span>
                        <span>
                            <strong>Claimed:</strong> 💰 ₹<?= number_format($expense['amount'] ?? 0, 2) ?>
                        </span>
                        <?php if (in_array($expense['status'] ?? '', ['approved', 'paid']) && !empty($approved['approved_amount'])): ?>
                        <span>
                            <strong>Approved:</strong> 💵 ₹<?= number_format($approved['approved_amount'], 2) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h4>📅 Timeline</h4>
                    <div class="detail-items">
                        <span><strong>Expense Date:</strong> 📅 <?= date('M d, Y', strtotime($expense['expense_date'] ?? 'now')) ?></span>
                        <span><strong>Submitted:</strong> 📅 <?= date('M d, Y', strtotime($expense['created_at'] ?? 'now')) ?></span>
                        <?php if (!empty($expense['approved_at'])): ?>
                        <span><strong><?= ($expense['status'] ?? 'pending') === 'approved' ? 'Approved' : 'Processed' ?>:</strong> 📅 <?= date('M d, Y', strtotime($expense['approved_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-group">
                    <h4>📋 Details</h4>
                    <div class="detail-items">
                        <span><strong>Description:</strong> <?= nl2br(htmlspecialchars($expense['description'] ?? 'N/A')) ?></span>
                        <span><strong>Status:</strong> 
                            <span class="badge badge--<?= $statusClass ?>"><?= $statusIcon ?> <?= ucfirst($status) ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <?php if (!empty($expense['attachment'])): ?>
            <div class="detail-item">
                <label>Receipt</label>
                <?php require_once __DIR__ . '/../../app/helpers/ProofHelper.php'; echo proof_preview_html('/ergon-site/storage/receipts/' . $expense['attachment'], 'Receipt'); ?>
            </div>
            <?php endif; ?>
            <?php
                // Check both approved_expenses and expenses tables for payment proof
                $proofFile = null;
                if (!empty($approved['payment_proof'])) {
                    $proofFile = $approved['payment_proof'];
                } elseif (!empty($expense['payment_proof'])) {
                    $proofFile = $expense['payment_proof'];
                }
            ?>
            <?php if (!empty($proofFile)): ?>
            <div class="detail-item" style="margin-top:1rem;">
                <label>Payment Proof</label>
                <?php require_once __DIR__ . '/../../app/helpers/ProofHelper.php'; echo proof_preview_html('/ergon-site/storage/proofs/' . $proofFile, 'Payment Proof'); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($expense['approved_at'])): ?>
            <div class="detail-item">
                <label><?= ($expense['status'] ?? 'pending') === 'approved' ? 'Approved' : 'Processed' ?> Date</label>
                <span><?= date('M d, Y', strtotime($expense['approved_at'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-content">
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

<?php include __DIR__ . '/../partials/proof_modal.php'; ?>

<style>
.expense-compact {
    max-width: 1000px;
    margin: 0 auto;
}

.expense-title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    width: 100%;
    gap: 1.5rem;
    min-height: 2rem;
}

.expense-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    flex: 1 1 auto;
    min-width: 200px;
    max-width: calc(100% - 200px);
    overflow-wrap: break-word;
    word-break: break-word;
    line-height: 1.3;
}

.expense-badges {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 0 0 auto;
    min-width: 180px;
    justify-content: flex-end;
}

.amount-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.amount-text {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary);
    background: var(--bg-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    border: 1px solid var(--border-color);
}

.description-compact {
    background: var(--bg-secondary);
    padding: 0.75rem;
    border-radius: 6px;
    border-left: 3px solid var(--primary);
    margin-bottom: 1rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

.rejection-notice {
    background: #fef2f2;
    border-left-color: #dc2626;
    color: #dc2626;
}

.badge--paid {
    /*background: #166534;*/
    color: #3d8a36ff;
}

.details-compact {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-group {
    background: var(--bg-secondary);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.detail-group h4 {
    margin: 0 0 0.75rem 0;
    font-size: 0.9rem;
    color: var(--primary);
    font-weight: 600;
}

.detail-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-items span {
    font-size: 0.85rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-items strong {
    color: var(--text-primary);
    min-width: 80px;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .expense-title-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        min-height: auto;
    }
    
    .expense-title {
        max-width: 100%;
        min-width: auto;
    }
    
    .expense-badges {
        width: 100%;
        min-width: auto;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    
    .details-compact {
        grid-template-columns: 1fr;
    }
}
.receipt-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.receipt-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: transform 0.2s;
}
.receipt-image:hover {
    transform: scale(1.05);
}
.receipt-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
}
.receipt-link:hover {
    text-decoration: underline;
}
.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.modal-content--large {
    max-width: 90%;
    max-height: 90vh;
    margin: 2% auto;
}
.receipt-full {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}
.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}
.close:hover {
    color: #374151;
}
.modal-body {
    padding: 1rem;
    text-align: center;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}
.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    resize: vertical;
}
</style>

<script>
function openReceiptModal(imageSrc) {
    var img = document.getElementById('receiptImage');
    if (img) img.src = imageSrc;
    if (typeof showModalById === 'function') {
        showModalById('receiptModal');
    } else {
        var m = document.getElementById('receiptModal'); if (m) m.style.display = 'block';
        document.body.classList.add('modal-open'); document.body.style.overflow = 'hidden';
    }
}

function closeReceiptModal() {
    var img = document.getElementById('receiptImage'); if (img) img.src = '';
    if (typeof hideModalById === 'function') {
        hideModalById('receiptModal');
    } else {
        var m = document.getElementById('receiptModal'); if (m) m.style.display = 'none';
        document.body.classList.remove('modal-open'); document.body.style.overflow = '';
    }
}

function showRejectModal(expenseId) {
    document.getElementById('rejectForm').action = '/ergon-site/expenses/reject/' + expenseId;
    if (typeof showModalById === 'function') showModalById('rejectModal'); else document.getElementById('rejectModal').style.display = 'block';
}

function closeRejectModal() {
    if (typeof hideModalById === 'function') hideModalById('rejectModal'); else document.getElementById('rejectModal').style.display = 'none';
    var rr = document.getElementById('rejection_reason'); if (rr) rr.value = '';
}

window.onclick = function(event) {
    const receiptModal = document.getElementById('receiptModal');
    const rejectModal = document.getElementById('rejectModal');
    
    if (event.target === receiptModal) {
        closeReceiptModal();
    }
    if (event.target === rejectModal) {
        closeRejectModal();
    }
}
</script>

<script>
function validateProofUpload() {
    var input = document.getElementById('expense_proof_input');
    if (!input || !input.files || input.files.length === 0) {
        alert('Please select a proof file before submitting.');
        return false;
    }
    return true;
}

// Enable expense Mark Paid button only after a proof file is selected
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('expense_proof_input');
    var submitBtn = document.getElementById('expense_proof_submit');
    var nameSpan = document.getElementById('expense_proof_name');
    if (input) {
        input.addEventListener('change', function() {
            if (input.files && input.files.length > 0) {
                submitBtn.disabled = false;
                nameSpan.textContent = input.files[0].name;
            } else {
                submitBtn.disabled = true;
                nameSpan.textContent = '';
            }
        });
    }
});
</script>

<script>
// Show preview for selected proof (image preview or PDF icon)
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('expense_proof_input');
    var previewContainer = document.createElement('div');
    previewContainer.style.marginTop = '0.75rem';
    if (input) {
        input.parentNode.insertBefore(previewContainer, input.nextSibling);
        input.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            if (input.files && input.files.length > 0) {
                var file = input.files[0];
                var reader = new FileReader();
                if (file.type.startsWith('image/')) {
                    reader.onload = function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '200px';
                        img.style.borderRadius = '6px';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    var link = document.createElement('a');
                    link.textContent = 'PDF selected: ' + file.name;
                    link.href = '#';
                    previewContainer.appendChild(link);
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
