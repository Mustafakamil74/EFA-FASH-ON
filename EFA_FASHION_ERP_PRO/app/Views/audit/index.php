<?php
/** @var array $logs @var string $q */
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <form method="get" action="<?= e(url('audit')) ?>" class="d-flex gap-2">
        <input name="q" class="form-control" value="<?= e($q) ?>" placeholder="<?= e(__('search')) ?>">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    </form>
    <a href="<?= e(url('audit/errors')) ?>" class="btn btn-outline-secondary ms-auto"><i class="bi bi-bug me-1"></i><?= e(__('error_logs')) ?></a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr>
                <th>#</th><th><?= e(__('date')) ?></th><th><?= e(__('user')) ?></th>
                <th><?= e(__('action')) ?></th><th><?= e(__('entity')) ?></th>
                <th><?= e(__('description')) ?></th><th><?= e(__('ip_address')) ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= e($l['id']) ?></td>
                    <td class="text-muted small text-nowrap"><?= e($l['created_at']) ?></td>
                    <td><?= e($l['username'] ?? '—') ?></td>
                    <td><span class="badge bg-secondary"><?= e($l['action']) ?></span></td>
                    <td><?= e($l['entity'] ?? '') ?><?= $l['entity_id'] ? ' #' . e($l['entity_id']) : '' ?></td>
                    <td><?= e($l['description'] ?? '') ?></td>
                    <td class="text-muted small"><?= e($l['ip_address'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$logs): ?><tr><td colspan="7" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
