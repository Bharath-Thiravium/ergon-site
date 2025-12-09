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
        <h2 class="card__title">
            Project: <?= htmlspecialchars($project_name ?? 'Unknown') ?>
        </h2>
        <div class="card__actions">
            <strong>Total Expenses: ₹<?= number_format($total_expenses ?? 0, 2) ?></strong>
            <strong>Total Advances: ₹<?= number_format($total_advances ?? 0, 2) ?></strong>
        </div>
    </div>
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
                            <th>Amount</th>
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
                            <td>₹<?= number_format($e['amount'], 2) ?></td>
                            <td>
                                <span class="badge badge--<?= match($e['status']) {
                                    'approved' => 'success',
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
