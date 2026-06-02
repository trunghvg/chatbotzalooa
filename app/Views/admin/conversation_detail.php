<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('admin/conversations') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0" id="displayName"><?= esc($conversation['user_name'] ?? 'Học viên') ?></h5>
            <button class="btn btn-sm btn-link text-muted p-0" onclick="toggleEditName()" title="Sửa tên">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>
        <div id="editNameRow" class="d-none mt-1">
            <div class="input-group input-group-sm" style="max-width:320px">
                <input type="text" class="form-control" id="nameInput"
                       value="<?= esc($conversation['user_name'] ?? '') ?>"
                       placeholder="Nhập tên thật của người dùng...">
                <button class="btn btn-primary" onclick="saveName()">Lưu</button>
                <button class="btn btn-outline-secondary" onclick="toggleEditName()">Hủy</button>
            </div>
        </div>
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
                    <dd class="col-7 fw-semibold" id="sidebarName"><?= esc($conversation['user_name'] ?? '—') ?></dd>

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
const chatBox  = document.getElementById('chatBox');
const convId   = <?= (int)$conversation['id'] ?>;
const apiBase  = '<?= base_url('admin/api/conversations') ?>';
let lastMsgId  = <?= !empty($conversation['messages']) ? (int)end($conversation['messages'])['id'] : 0 ?>;
let atBottom   = true;

chatBox.addEventListener('scroll', () => {
    atBottom = chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 10;
});

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderMsg(msg) {
    const isUser = msg.role === 'user';
    const d = new Date(msg.created_at.replace(' ', 'T'));
    const ts = d.toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'})
             + ' ' + d.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    const ms = msg.processing_time_ms
        ? `<div class="text-muted mt-1" style="font-size:.65rem">⚡ ${msg.processing_time_ms}ms</div>` : '';
    return `<div class="d-flex flex-column ${isUser ? 'align-items-end msg-user' : 'align-items-start msg-bot'}">
        <div class="text-muted mb-1" style="font-size:.7rem">${isUser ? '👤 Học viên' : '🤖 Bot AI'} · ${ts}</div>
        <div class="msg-bubble">${escHtml(msg.content)}</div>${ms}
    </div>`;
}

async function pollMessages() {
    try {
        const res  = await fetch(`${apiBase}/${convId}/messages?after=${lastMsgId}`);
        const data = await res.json();
        if (data.messages && data.messages.length > 0) {
            // Remove empty-state placeholder if present
            const placeholder = chatBox.querySelector('.text-center.text-muted');
            if (placeholder) placeholder.remove();

            data.messages.forEach(msg => {
                chatBox.insertAdjacentHTML('beforeend', renderMsg(msg));
                lastMsgId = Math.max(lastMsgId, msg.id);
            });
            // Update message count label
            const countEl = document.querySelector('.card-header .text-muted');
            if (countEl && data.message_count) countEl.textContent = data.message_count + ' tin nhắn';
            if (atBottom) chatBox.scrollTop = chatBox.scrollHeight;
        }
    } catch(e) { /* ignore network errors */ }
}

// Initial scroll to bottom
chatBox.scrollTop = chatBox.scrollHeight;

// Poll every 3 seconds
setInterval(pollMessages, 3000);

function toggleEditName() {
    const row = document.getElementById('editNameRow');
    row.classList.toggle('d-none');
    if (!row.classList.contains('d-none')) {
        document.getElementById('nameInput').focus();
    }
}

async function saveName() {
    const newName = document.getElementById('nameInput').value.trim();
    if (!newName) return;

    const form = new FormData();
    form.append('name', newName);

    const resp = await fetch('<?= base_url('admin/api/conversations/' . $conversation['id'] . '/update-name') ?>', {
        method: 'POST', body: form
    });
    const data = await resp.json();

    if (data.success) {
        document.getElementById('displayName').textContent = newName;
        document.getElementById('sidebarName').textContent = newName;
        document.getElementById('editNameRow').classList.add('d-none');
    } else {
        alert('Lỗi khi lưu tên: ' + (data.message ?? 'Unknown error'));
    }
}

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
