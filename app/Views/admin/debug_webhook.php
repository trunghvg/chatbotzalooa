<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between pt-3 pb-2">
        <h6 class="fw-semibold mb-0"><i class="bi bi-bug-fill text-warning me-2"></i>Webhook Payload mới nhất từ Zalo</h6>
        <small class="text-muted">Nhắn 1 tin từ Zalo rồi reload trang này</small>
    </div>
    <div class="card-body p-0">
        <pre class="m-0 p-4" style="background:#1e1e1e;color:#d4d4d4;font-size:.8rem;overflow-x:auto;max-height:80vh"><?= esc($payload) ?></pre>
    </div>
</div>

<?= $this->endSection() ?>
