<?php 
$title = 'Module Management';
$active_page = 'modules';
ob_start();
?>

<div class="container-fluid">
    <div class="page-header">
        <h1>🔧 Module Management</h1>
        <p>Enable or disable modules for your subscription</p>
    </div>

    <div class="card">
        <div class="card__header">
            <h2>Available Modules</h2>
        </div>
        <div class="card__body">
            <div class="modules-grid">
                <?php foreach ($modules as $module): ?>
                <div class="module-card <?= $module['enabled'] ? 'module-card--enabled' : 'module-card--disabled' ?>">
                    <div class="module-card__header">
                        <h3><?= htmlspecialchars($module['label']) ?></h3>
                        <?php if ($module['is_basic']): ?>
                            <span class="badge badge--success">Basic</span>
                        <?php else: ?>
                            <span class="badge badge--info">Premium</span>
                        <?php endif; ?>
                    </div>
                    <div class="module-card__body">
                        <div class="module-status">
                            Status: <strong><?= $module['enabled'] ? 'Enabled' : 'Disabled' ?></strong>
                        </div>
                        <?php if (!$module['is_basic']): ?>
                        <div class="module-actions">
                            <button class="btn btn--sm <?= $module['enabled'] ? 'btn--danger' : 'btn--success' ?>" 
                                    onclick="toggleModule('<?= $module['name'] ?>', '<?= $module['enabled'] ? 'disable' : 'enable' ?>')">
                                <?= $module['enabled'] ? 'Disable' : 'Enable' ?>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="module-actions">
                            <span class="text-muted">Always enabled</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.module-card {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.module-card--enabled {
    border-color: var(--success);
    background: rgba(16, 185, 129, 0.05);
}

.module-card--disabled {
    border-color: var(--error);
    background: rgba(239, 68, 68, 0.05);
}

.module-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.module-card__header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.module-status {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge--success {
    background: var(--success);
    color: white;
}

.badge--info {
    background: var(--info);
    color: white;
}
</style>

<script>
function toggleModule(module, action) {
    if (!confirm(`Are you sure you want to ${action} this module?`)) {
        return;
    }
    
    fetch('/ergon/modules/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `module=${module}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Network error: ' + error.message);
    });
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/dashboard.php';
?>