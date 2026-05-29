<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Cuộc hội thoại</h5>
        <span class="text-muted small">Tổng cộng <?= number_format($total) ?> cuộc hội thoại</span>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($conversations)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-chat-square-dots" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3">Chưa có hội thoại nào</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Học viên</th>
                        <th>Tin nhắn cuối</th>
                        <th>Tổng tin</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($conversations as $conv): ?>
                <tr data-id="<?= $conv['id'] ?>">
                    <td class="text-muted small">#<?= $conv['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($conv['user_avatar'])): ?>
                            <img src="<?= esc($conv['user_avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                            <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:36px;height:36px;background:#e8f0fe;color:#0068ff;font-size:.8rem">
                                <?= strtoupper(substr($conv['user_name'] ?? 'H', 0, 1)) ?>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-semibold small"><?= esc($conv['user_name'] ?? 'Học viên') ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= esc($conv['zalo_user_id']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="max-width:280px">
                        <div class="text-truncate text-muted small last-msg"><?= esc($conv['last_message'] ?? '—') ?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark msg-count"><?= number_format($conv['message_count'] ?? 0) ?></span>
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
                        <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle">
                            <?= $label ?>
                        </span>
                    </td>
                    <td class="text-muted small conv-time">
                        <?= $conv['last_message_at'] ? date('d/m/Y H:i', strtotime($conv['last_message_at'])) : '—' ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= base_url('admin/conversations/' . $conv['id']) ?>"
                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteConversation(<?= $conv['id'] ?>, this)" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-4">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
async function deleteConversation(id, btn) {
    if (!confirm('Xóa cuộc hội thoại #' + id + ' và toàn bộ tin nhắn?')) return;

    btn.disabled = true;
    const resp = await fetch('<?= base_url('admin/conversations/') ?>' + id, { method: 'DELETE' });
    const data = await resp.json();

    if (data.status === 'ok') {
        btn.closest('tr').remove();
    } else {
        alert('Lỗi khi xóa!');
        btn.disabled = false;
    }
}

// Auto-refresh danh sach hoi thoai moi 10 giay
async function refreshConversationList() {
    try {
        const res  = await fetch('<?= base_url('admin/api/conversations/list') ?>');
        const data = await res.json();
        if (!data.conversations) return;

        const tbody = document.querySelector('table tbody');
        if (!tbody) return;

        // Collect currently displayed ids to detect new rows
        const existingIds = new Set(
            [...tbody.querySelectorAll('tr')].map(r => parseInt(r.querySelector('td')?.textContent.replace('#','')))
        );

        data.conversations.forEach(conv => {
            const row = tbody.querySelector(`tr[data-id="${conv.id}"]`);
            if (!row) return; // new conv only appears on page reload (pagination)

            // Update last message text
            const lastMsgCell = row.querySelector('.last-msg');
            if (lastMsgCell) lastMsgCell.textContent = conv.last_message || '—';

            // Update message count badge
            const countBadge = row.querySelector('.msg-count');
            if (countBadge) countBadge.textContent = Number(conv.message_count || 0).toLocaleString('vi-VN');

            // Update time
            const timeCell = row.querySelector('.conv-time');
            if (timeCell && conv.last_message_at) {
                const d = new Date(conv.last_message_at.replace(' ','T'));
                timeCell.textContent = d.toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit',year:'numeric'})
                    + ' ' + d.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
            }
        });

        // Update total count header
        const totalEl = document.querySelector('.text-muted.small');
        if (totalEl && data.total !== undefined) {
            totalEl.textContent = 'Tổng cộng ' + Number(data.total).toLocaleString('vi-VN') + ' cuộc hội thoại';
        }
    } catch(e) { /* ignore */ }
}

setInterval(refreshConversationList, 10000);
</script>
<?= $this->endSection() ?>
