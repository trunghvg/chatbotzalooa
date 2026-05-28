<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('admin/conversations') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="fw-bold mb-0"><?= esc($conversation['user_name'] ?? 'Học viên') ?></h5>
        <small class="text-muted">Zalo ID: <?= esc($conversation['zalo_user_id']) ?></small>
    </div>
</div>

<div class="row g-3">
    <!-- Chat window -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="height:70vh;display:flex;flex-direction:column">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                     style="width:36px;height:36px;background:#e8f0fe;color:#0068ff;font-size:.8rem">
                    <?= strtoupper(substr($conversation['user_name'] ?? 'H', 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-semibold small"><?= esc($conversation['user_name'] ?? 'Học viên') ?></div>
                    <div class="text-muted" style="font-size:.7rem">
                        <?= count($conversation['messages'] ?? []) ?> tin nhắn
                    </div>
                </div>
            </div>
            <div class="card-body overflow-y-auto p-3 d-flex flex-column gap-3" id="chatBox">
                <?php if (empty($conversation['messages'])): ?>
                <div class="text-center text-muted py-4">Chưa có tin nhắn nào</div>
                <?php else: ?>
                <?php foreach ($conversation['messages'] as $msg): ?>
                <div class="d-flex flex-column <?= $msg['role'] === 'user' ? 'align-items-end msg-user' : 'align-items-start msg-bot' ?>">
                    <div class="text-muted mb-1" style="font-size:.7rem">
                        <?= $msg['role'] === 'user' ? '👤 Học viên' : '🤖 Bot AI' ?>
                        · <?= date('d/m H:i:s', strtotime($msg['created_at'])) ?>
                    </div>
                    <div class="msg-bubble"><?= esc($msg['content']) ?></div>
                    <?php if (!empty($msg['processing_time_ms'])): ?>
                    <div class="text-muted mt-1" style="font-size:.65rem">
                        ⚡ <?= $msg['processing_time_ms'] ?>ms
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Info panel -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Thông tin học viên</h6>

                <?php if (!empty($conversation['user_avatar'])): ?>
                <div class="text-center mb-3">
                    <img src="<?= esc($conversation['user_avatar']) ?>" class="rounded-circle"
                         width="64" height="64" style="object-fit:cover">
                </div>
                <?php endif; ?>

                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Tên:</dt>
                    <dd class="col-7 fw-semibold"><?= esc($conversation['user_name'] ?? '—') ?></dd>

                    <dt class="col-5 text-muted">Zalo ID:</dt>
                    <dd class="col-7 font-monospace" style="font-size:.75rem;word-break:break-all">
                        <?= esc($conversation['zalo_user_id']) ?>
                    </dd>

                    <dt class="col-5 text-muted">Trạng thái:</dt>
                    <dd class="col-7">
                        <?php
                        $statusMap = ['active' => ['success','Hoạt động'], 'pending_human' => ['warning','Chờ NV'], 'closed' => ['secondary','Đóng']];
                        [$c, $l] = $statusMap[$conversation['status']] ?? ['secondary', $conversation['status']];
                        ?>
                        <span class="badge bg-<?= $c ?>-subtle text-<?= $c ?>"><?= $l ?></span>
                    </dd>

                    <dt class="col-5 text-muted">Tổng tin:</dt>
                    <dd class="col-7"><?= number_format($conversation['message_count'] ?? 0) ?></dd>

                    <dt class="col-5 text-muted">Bắt đầu:</dt>
                    <dd class="col-7"><?= date('d/m/Y', strtotime($conversation['created_at'])) ?></dd>

                    <dt class="col-5 text-muted">Hoạt động:</dt>
                    <dd class="col-7"><?= date('d/m/Y H:i', strtotime($conversation['last_message_at'] ?? $conversation['created_at'])) ?></dd>
                </dl>
            </div>
        </div>

        <!-- Send test message -->
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Gửi tin nhắn test</h6>
                <div class="mb-2">
                    <textarea class="form-control form-control-sm" id="testMsg" rows="3"
                              placeholder="Nhập tin nhắn để gửi qua Zalo..."></textarea>
                </div>
                <button class="btn btn-sm btn-primary w-100" onclick="sendTestMsg()">
                    <i class="bi bi-send me-1"></i>Gửi qua Zalo
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto scroll to bottom
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;

async function sendTestMsg() {
    const msg = document.getElementById('testMsg').value.trim();
    if (!msg) return;

    const form = new FormData();
    form.append('user_id', '<?= esc($conversation['zalo_user_id']) ?>');
    form.append('message', msg);

    const resp = await fetch('<?= base_url('admin/api/test-zalo') ?>', { method: 'POST', body: form });
    const data = await resp.json();
    alert(data.success ? '✅ Gửi thành công!' : '❌ Lỗi: ' + JSON.stringify(data.result));
}
</script>
<?= $this->endSection() ?>
