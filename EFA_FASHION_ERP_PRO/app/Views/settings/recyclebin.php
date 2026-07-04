<?php
/** @var array $groups */
?>
<?php if (!$groups): ?>
    <div class="alert alert-info"><?= e(__('recyclebin_empty')) ?></div>
<?php else: ?>
    <?php foreach ($groups as $table => $rows): ?>
        <div class="card mb-3">
            <div class="card-header"><strong><?= e(ucfirst($table)) ?></strong> <span class="badge bg-secondary"><?= count($rows) ?></span></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>#</th><th><?= e(__('field_name')) ?></th><th><?= e(__('deleted_at')) ?></th><th class="text-end"></th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= e($r['id']) ?></td>
                            <td><?= e($r['label']) ?></td>
                            <td class="text-muted small"><?= e($r['deleted_at']) ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= e(url('recyclebin/' . $table . '/' . $r['id'] . '/restore')) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i><?= e(__('restore')) ?></button></form>
                                <form method="post" action="<?= e(url('recyclebin/' . $table . '/' . $r['id'] . '/purge')) ?>" class="d-inline" data-confirm="<?= e(__('confirm_purge')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
