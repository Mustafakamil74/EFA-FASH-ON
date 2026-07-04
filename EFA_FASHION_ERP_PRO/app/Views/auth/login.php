<?php
use App\Core\Lang;
$flashes = flash();
?>
<div class="card auth-card shadow-lg">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-mark mb-2"><i class="bi bi-bag-check-fill"></i></div>
            <h1 class="h4 fw-bold mb-0">EFA FASHION</h1>
            <p class="text-muted small mb-0">ERP PRO</p>
        </div>

        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : e($f['type']) ?> py-2">
                <?= e($f['message']) ?>
            </div>
        <?php endforeach; ?>

        <form method="post" action="<?= e(url('login')) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label"><?= e(__('username')) ?></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" value="<?= e(old('username')) ?>" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= e(__('password')) ?></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                <label class="form-check-label" for="remember"><?= e(__('remember_me')) ?></label>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i><?= e(__('sign_in')) ?>
            </button>
        </form>

        <div class="text-center mt-4">
            <?php foreach (config('app.langs', ['en']) as $lng): ?>
                <a class="small text-decoration-none mx-1 <?= $lng === Lang::current() ? 'fw-bold' : 'text-muted' ?>"
                   href="<?= e(url('login?lang=' . $lng)) ?>"><?= strtoupper(e($lng)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
