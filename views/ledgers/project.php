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

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Filter by Project</h2>
    </div>
    <div class="card__body">
        <form method="GET" style="display: flex; gap: 12px; align-items: end;">
            <div style="flex: 1;">
                <label>Select Project</label>
                <select name="project_id" class="form-input" required>
                    <option value="">All Projects</option>
                    <?php foreach ($projects ?? [] as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($project_id ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['project_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">🔍 Filter</button>
        </form>
    </div>
</div>

<?php if (isset($project_id) && $project_id): ?>
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Project: <?= htmlspecialchars($project_name ?? 'Unknown') ?></h2>
    </div>
    <div class="card__body">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <!-- Budget Overview -->
            <div style="background: #e7f3ff; padding: 16px; border-radius: 8px; border: 2px solid #007bff;">
                <div style="font-weight: bold; color: #007bff; margin-bottom: 12px; font-size: 0.9rem;">💰 BUDGET OVERVIEW</div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Allocated:</span>
                    <strong style="color: #212529;">₹<?= number_format($budget ?? 0, 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Utilized:</span>
                    <strong style="color: #ffc107;">₹<?= number_format($total_debits ?? 0, 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #ccc;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Remaining:</span>
                    <strong style="color: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>;">₹<?= number_format($balance_amount ?? 0, 2) ?> <?= $balance_type ?? 'Cr' ?></strong>
                </div>
                <div style="margin-top: 8px; font-size: 0.75rem; color: #6c757d; text-align: center;"><?= number_format($utilization ?? 0, 1) ?>% Utilized</div>
            </div>
            
            <!-- Transaction Summary -->
            <div style="background: #fff8e1; padding: 16px; border-radius: 8px; border: 2px solid #ffc107;">
                <div style="font-weight: bold; color: #f57c00; margin-bottom: 12px; font-size: 0.9rem;">📊 TRANSACTIONS</div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Credits:</span>
                    <strong style="color: #28a745;">₹<?= number_format($total_credits ?? 0, 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Debits:</span>
                    <strong style="color: #dc3545;">₹<?= number_format($total_debits ?? 0, 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #ccc;">
                    <span style="color: #6c757d; font-size: 0.85rem;">Net:</span>
                    <strong style="color: <?= ($net_balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>;">₹<?= number_format($net_balance_amount ?? 0, 2) ?> <?= $net_balance_type ?? 'Cr' ?></strong>
                </div>
            </div>
            
            <!-- Balance Summary -->
            <div style="background: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#d4edda' : '#f8d7da' ?>; padding: 16px; border-radius: 8px; border: 2px solid <?= ($balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>;">
                <div style="font-weight: bold; color: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>; margin-bottom: 12px; font-size: 0.9rem;">⚖️ BALANCE STATUS</div>
                <div style="text-align: center; margin: 20px 0;">
                    <div style="font-size: 0.75rem; color: #6c757d; margin-bottom: 4px;">Current Balance</div>
                    <div style="font-size: 2rem; font-weight: bold; color: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>;">₹<?= number_format($balance_amount ?? 0, 2) ?></div>
                    <div style="font-size: 1rem; font-weight: bold; color: <?= ($balance_type ?? 'Credit') === 'Credit' ? '#28a745' : '#dc3545' ?>; margin-top: 4px;"><?= $balance_type ?? 'Credit' ?></div>
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
