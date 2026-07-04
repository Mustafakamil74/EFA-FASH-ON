<?php
/** @var array $logs */
?>
<div class="d-flex mb-3">
    <a href="<?= e(url('audit')) ?>" class="btn btn-outline-secondary ms-auto"><i class="bi bi-clock-history me-1"></i><?= e(__('nav_audit')) ?></a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr>
                <th>#</th><th><?= e(__('date')) ?></th><th><?= e(__('level')) ?></th>
                <th><?= e(__('message')) ?></th><th><?= e(__('file')) ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= e($l['id']) ?></td>
                    <td class="text-muted small text-nowrap"><?= e($l['created_at']) ?></td>
                    <td><span class="badge bg-danger"><?= e($l['level']) ?></span></td>
                    <td><?= e($l['message']) ?></td>
                    <td class="text-muted small"><?= e($l['file'] ?? '') ?><?= $l['line'] ? ':' . e($l['line']) : '' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$logs): ?><tr><td colspan="5" class="text-center text-muted py-3"><?= e(__('no_records')) ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
