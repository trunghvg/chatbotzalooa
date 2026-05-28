<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Nhật ký tin nhắn</h5>
        <span class="text-muted small">Tổng cộng <?= number_format($total) ?> tin nhắn</span>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($messages)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3">Chưa có tin nhắn nào</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Vai trò</th>
                        <th>Nội dung</th>
                        <th>Thời gian xử lý</th>
                        <th>Thời điểm</th>
                        <th>Hội thoại</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td class="text-muted small"><?= $msg['id'] ?></td>
                    <td>
                        <?php if ($msg['role'] === 'user'): ?>
                        <span class="badge bg-blue-subtle text-primary border border-primary-subtle">
                            👤 Học viên
                        </span>
                        <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            🤖 Bot
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width:350px">
                        <div class="text-truncate small"><?= esc($msg['content']) ?></div>
                    </td>
                    <td class="text-muted small">
                        <?= $msg['processing_time_ms'] ? $msg['processing_time_ms'] . 'ms' : '—' ?>
                    </td>
                    <td class="text-muted small text-nowrap">
                        <?= date('d/m/Y H:i:s', strtotime($msg['created_at'])) ?>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/conversations/' . $msg['conversation_id']) ?>"
                           class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px">
                            #<?= $msg['conversation_id'] ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-4">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = max(1, $currentPage - 3); $p <= min($totalPages, $currentPage + 3); $p++): ?>
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
