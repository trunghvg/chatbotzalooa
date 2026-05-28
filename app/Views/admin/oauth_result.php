<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body py-5">
                <?php if ($success): ?>
                <div class="mb-3" style="font-size:3rem">✅</div>
                <h4 class="text-success fw-bold"><?= esc($message) ?></h4>
                <?php if (isset($access_token)): ?>
                <p class="text-muted">Token: <code><?= esc($access_token) ?></code></p>
                <p class="text-muted small">Hết hạn sau: <?= $expires_in ?? 3600 ?> giây</p>
                <?php endif; ?>
                <a href="<?= base_url('admin') ?>" class="btn btn-primary mt-3">
                    <i class="bi bi-grid me-2"></i>Về Dashboard
                </a>
                <?php else: ?>
                <div class="mb-3" style="font-size:3rem">❌</div>
                <h4 class="text-danger fw-bold">Có lỗi xảy ra</h4>
                <p class="text-muted"><?= esc($message) ?></p>
                <a href="<?= base_url('zalo/authorize') ?>" class="btn btn-primary mt-3">
                    Thử lại
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
