<?php
$title = 'Project Ledger';
$active_page = 'ledgers';
ob_start();
?>

<div class="page-header">
    <div class="page-title">
        <h1>📊 Project-wise Ledger</h1>
        <p>Consolidated financial transactions by project</p>
    </div>
    <div class="page-actions">
        <a href="/ergon-site/users" class="btn btn--secondary">← Back</a>
    </div>
</div>

<div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; box-shadow: var(--shadow-sm);">
    <form method="GET" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <label style="font-size: 0.875rem; font-weight: 500; color: var(--text-primary); white-space: nowrap;">🔍 Project:</label>
        <select name="project_id" class="form-control" onchange="this.form.submit()" style="flex: 1; min-width: 200px; padding: 8px 12px; font-size: 0.875rem;">
            <option value="">All Projects</option>
            <?php foreach ($projects ?? [] as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($project_id ?? '') == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['project_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (isset($project_id) && $project_id): ?>
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Project: <?= htmlspecialchars($project_name ?? 'Unknown') ?></h2>
    </div>
    <div class="card__body">
        <div class="dashboard-grid" style="margin-bottom: 16px; margin-top: 0;">
            <div class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__title">
                        <span class="stat-card__icon">💰</span>
                        <span class="stat-card__label">Budget</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <div class="stat-card__meta">
                        <div>
                            <small>Allocated</small>
                            <div class="stat-card__meta-value">₹<?= number_format($budget ?? 0, 2) ?></div>
                        </div>
                        <div>
                            <small>Utilized</small>
                            <div class="stat-card__meta-value"><?= number_format($utilization ?? 0, 1) ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__title">
                        <span class="stat-card__icon">➕</span>
                        <span class="stat-card__label">Credits</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <div class="stat-card__meta">
                        <div>
                            <small>Total</small>
                            <div class="stat-card__meta-value" style="color: #059669;">₹<?= number_format($total_credits ?? 0, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__title">
                        <span class="stat-card__icon">➖</span>
                        <span class="stat-card__label">Debits</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <div class="stat-card__meta">
                        <div>
                            <small>Total</small>
                            <div class="stat-card__meta-value" style="color: #dc2626;">₹<?= number_format($total_debits ?? 0, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__title">
                        <span class="stat-card__icon">⚖️</span>
                        <span class="stat-card__label">Net Balance</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <div class="stat-card__meta">
                        <div>
                            <small>Credits - Debits</small>
                            <div class="stat-card__meta-value" style="color: <?= ($net_balance_type ?? 'Credit') === 'Credit' ? '#059669' : '#dc2626' ?>;">₹<?= number_format($net_balance_amount ?? 0, 2) ?></div>
                        </div>
                        <div>
                            <small>Type</small>
                            <div class="stat-card__meta-value"><?= $net_balance_type ?? 'Credit' ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__title">
                        <span class="stat-card__icon">💼</span>
                        <span class="stat-card__label">Budget Balance</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <div class="stat-card__meta">
                        <div>
                            <small>Remaining</small>
                            <div class="stat-card__meta-value" style="color: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#059669' : '#dc2626' ?>;">₹<?= number_format($balance_amount ?? 0, 2) ?></div>
                        </div>
                        <div>
                            <small>Status</small>
                            <div class="stat-card__meta-value"><?= $balance_type ?? 'Credit' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card__body">
    <div class="card__body">
        <?php if (empty($entries)): ?>
            <p>No transactions found for this project.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($e['created_at'])) ?></td>
                            <td><?= htmlspecialchars($e['user_name']) ?></td>
                            <td>
                                <span class="badge badge--<?= $e['type'] === 'expense' ? 'warning' : 'info' ?>">
                                    <?= $e['type'] === 'expense' ? '💰 Expense' : '💳 Advance' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($e['description']) ?></td>
                            <td><?= $e['entry_type'] === 'credit' ? '₹' . number_format($e['amount'], 2) : '-' ?></td>
                            <td><?= $e['entry_type'] === 'debit' ? '₹' . number_format($e['amount'], 2) : '-' ?></td>
                            <td>
                                <span class="badge badge--<?= match($e['status']) {
                                    'approved' => 'success',
                                    'paid' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning'
                                } ?>">
                                    <?= ucfirst($e['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
