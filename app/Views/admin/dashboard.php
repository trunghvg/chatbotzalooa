<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<!-- STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e8f0fe;">
                    <i class="bi bi-chat-dots-fill" style="color:#0068ff"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($convStats['total'] ?? 0) ?></div>
                    <div class="text-muted small">Tổng hội thoại</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7;">
                    <i class="bi bi-person-plus-fill" style="color:#d97706"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($convStats['today'] ?? 0) ?></div>
                    <div class="text-muted small">Học viên mới hôm nay</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7;">
                    <i class="bi bi-activity" style="color:#16a34a"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($convStats['active'] ?? 0) ?></div>
                    <div class="text-muted small">Đang hoạt động (24h)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fce7f3;">
                    <i class="bi bi-robot" style="color:#db2777"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">
                        <?= $botEnabled === '1' ? '<span class="text-success">ON</span>' : '<span class="text-danger">OFF</span>' ?>
                    </div>
                    <div class="text-muted small">Trạng thái Bot</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Biểu đồ hoạt động -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Tin nhắn 7 ngày qua
                </h6>
            </div>
            <div class="card-body">
                <canvas id="msgChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-lightning-fill text-warning me-2"></i>Thao tác nhanh
                </h6>
            </div>
            <div class="card-body d-grid gap-2">
                <!-- Toggle bot -->
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div>
                        <div class="fw-semibold small">Trả lời tự động</div>
                        <div class="text-muted" style="font-size:.75rem">Bot Claude AI</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="botToggle"
                               <?= $botEnabled === '1' ? 'checked' : '' ?>
                               style="width:2.5em;height:1.25em;cursor:pointer">
                    </div>
                </div>

                <!-- Test Claude -->
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testClaudeModal">
                    <i class="bi bi-cpu me-2"></i>Test Claude AI
                </button>

                <!-- Refresh Zalo Token -->
                <button class="btn btn-outline-success" id="refreshTokenBtn">
                    <i class="bi bi-arrow-clockwise me-2"></i>Làm mới Zalo Token
                </button>

                <a href="<?= base_url('admin/conversations') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-chat-dots me-2"></i>Xem hội thoại
                </a>
            </div>
        </div>

        <!-- Webhook Info -->
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-link-45deg text-info me-1"></i>Webhook URL
                </h6>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control font-monospace"
                           id="webhookUrl"
                           value="<?= base_url('webhook') ?>" readonly>
                    <button class="btn btn-outline-secondary" onclick="copyWebhook()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <small class="text-muted mt-1 d-block">Dán URL này vào Zalo OA Developer Console</small>
            </div>
        </div>
    </div>

    <!-- Recent Conversations -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between pt-4 pb-2">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-clock-history text-secondary me-2"></i>Hội thoại gần đây
                </h6>
                <a href="<?= base_url('admin/conversations') ?>" class="btn btn-sm btn-outline-primary">
                    Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentConvs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-chat-square-dots" style="font-size:2.5rem;opacity:.3"></i>
                    <p class="mt-2">Chưa có hội thoại nào</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Học viên</th>
                                <th>Tin nhắn cuối</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentConvs as $conv): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($conv['user_avatar'])): ?>
                                    <img src="<?= esc($conv['user_avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                                    <?php else: ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#e8f0fe;font-size:.75rem;color:#0068ff;font-weight:600">
                                        <?= strtoupper(substr($conv['user_name'] ?? 'H', 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold small"><?= esc($conv['user_name'] ?? 'Học viên') ?></div>
                                        <div class="text-muted" style="font-size:.7rem"><?= esc($conv['zalo_user_id']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small" style="max-width:250px">
                                <div class="text-truncate"><?= esc($conv['last_message'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php
                                $statusMap = [
                                    'active'        => ['success', 'Hoạt động'],
                                    'pending_human' => ['warning', 'Chờ NV'],
                                    'closed'        => ['secondary', 'Đóng'],
                                ];
                                [$color, $label] = $statusMap[$conv['status']] ?? ['secondary', $conv['status']];
                                ?>
                                <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>"><?= $label ?></span>
                            </td>
                            <td class="text-muted small">
                                <?= $conv['last_message_at'] ? date('d/m H:i', strtotime($conv['last_message_at'])) : '-' ?>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/conversations/' . $conv['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Test Claude Modal -->
<div class="modal fade" id="testClaudeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cpu me-2"></i>Test Claude AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tin nhắn test</label>
                    <textarea class="form-control" id="testMessage" rows="3" placeholder="Nhập câu hỏi...">Xin chào! Bạn có thể giới thiệu về bản thân không?</textarea>
                </div>
                <div id="testResult" class="d-none">
                    <hr>
                    <label class="form-label fw-semibold text-success">Phản hồi từ Claude:</label>
                    <div class="p-3 rounded-3 border" id="testOutput" style="background:#f8fafc;white-space:pre-wrap;font-size:.875rem"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="testClaudeBtn">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="testSpinner"></span>
                    <i class="bi bi-send me-1"></i>Gửi
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// ===== Chart =====
const weeklyData = <?= json_encode($weeklyStats) ?>;
const labels = weeklyData.map(d => {
    const dt = new Date(d.date);
    return dt.toLocaleDateString('vi-VN', {weekday:'short', day:'2-digit', month:'2-digit'});
});

new Chart(document.getElementById('msgChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Học viên nhắn',
                data: weeklyData.map(d => d.user_msgs),
                backgroundColor: 'rgba(0,104,255,.7)',
                borderRadius: 4,
            },
            {
                label: 'Bot trả lời',
                data: weeklyData.map(d => d.bot_msgs),
                backgroundColor: 'rgba(16,163,127,.7)',
                borderRadius: 4,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
    },
});

// ===== Bot Toggle =====
document.getElementById('botToggle').addEventListener('change', async function() {
    const val = this.checked ? '1' : '0';
    const form = new FormData();
    form.append('bot_enabled', val);
    const resp = await fetch('<?= base_url('admin/settings') ?>', {
        method: 'POST',
        body: form,
    });
    // Reload to update topbar badge
    location.reload();
});

// ===== Test Claude =====
document.getElementById('testClaudeBtn').addEventListener('click', async function() {
    const msg = document.getElementById('testMessage').value.trim();
    if (!msg) return;

    const spinner = document.getElementById('testSpinner');
    spinner.classList.remove('d-none');
    this.disabled = true;

    const form = new FormData();
    form.append('message', msg);

    const resp = await fetch('<?= base_url('admin/api/test-claude') ?>', {
        method: 'POST',
        body: form,
    });
    const data = await resp.json();

    spinner.classList.add('d-none');
    this.disabled = false;

    document.getElementById('testResult').classList.remove('d-none');
    document.getElementById('testOutput').textContent = data.message || 'Lỗi: ' + JSON.stringify(data);
});

// ===== Refresh Token =====
document.getElementById('refreshTokenBtn').addEventListener('click', async function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang làm mới...';

    const resp = await fetch('<?= base_url('admin/api/refresh-token') ?>', { method: 'POST' });
    const data = await resp.json();

    this.disabled = false;
    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Làm mới Zalo Token';

    alert(data.success ? '✅ Làm mới token thành công!' : '❌ Lỗi: ' + (data.result?.error || 'Unknown'));
});

// ===== Copy Webhook URL =====
function copyWebhook() {
    const input = document.getElementById('webhookUrl');
    navigator.clipboard.writeText(input.value);
    const btn = input.nextElementSibling;
    btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
    setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 2000);
}
</script>
<?= $this->endSection() ?>
