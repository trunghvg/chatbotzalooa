<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1">Cơ sở kiến thức (Knowledge Base)</h5>
        <p class="text-muted small mb-0">
            Các kiến thức này được tự động đưa vào cuộc trò chuyện để Claude AI trả lời chính xác.
            Tổng: <strong><?= number_format($total) ?></strong> mục
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <!-- Import Excel -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
        </button>
        <a href="<?= base_url('admin/knowledge/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Thêm mục mới
        </a>
    </div>
</div>

<!-- Category filter chips -->
<?php if (!empty($categories)): ?>
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= base_url('admin/knowledge') ?>"
       class="badge rounded-pill text-decoration-none py-2 px-3"
       style="background:#e8f0fe;color:#0068ff;border:1px solid #bfdbfe">
        Tất cả
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="?category=<?= urlencode($cat['category']) ?>"
       class="badge rounded-pill text-decoration-none py-2 px-3"
       style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0">
        <?= esc($cat['category']) ?>
        <span class="ms-1 opacity-75">(<?= $cat['count'] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Giải thích cơ chế -->
<div class="alert alert-info border-0 mb-4" style="background:#eff6ff">
    <div class="d-flex gap-3">
        <i class="bi bi-info-circle-fill text-primary mt-1" style="font-size:1.1rem"></i>
        <div class="small">
            <strong>Cơ chế hoạt động:</strong> Khi học viên gửi tin nhắn, hệ thống tự động tìm kiếm các mục kiến thức liên quan và đưa vào System Prompt của Claude AI.
            Bot sẽ dựa vào đó để trả lời chính xác dựa trên văn bản pháp lý thực tế.
            <br>Keyword matching tìm trong: <em>tiêu đề</em>, <em>nội dung</em> và <em>từ khóa</em>.
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($entries)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-database-slash" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3 mb-1">Chưa có mục kiến thức nào</p>
            <small>Import từ Excel hoặc thêm thủ công để bắt đầu</small>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Tiêu đề & Nội dung</th>
                        <th width="130">Danh mục</th>
                        <th width="180">Nguồn văn bản</th>
                        <th width="70" class="text-center">Thứ tự</th>
                        <th width="90" class="text-center">Trạng thái</th>
                        <th width="110" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                <tr>
                    <td class="text-muted small"><?= $entry['id'] ?></td>
                    <td>
                        <div class="fw-semibold small mb-1"><?= esc($entry['title']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;max-width:400px">
                            <div class="text-truncate"><?= esc(mb_substr(strip_tags($entry['content']), 0, 120)) ?>...</div>
                        </div>
                        <?php if (!empty($entry['keywords'])): ?>
                        <div class="mt-1">
                            <?php foreach (array_slice(explode(',', $entry['keywords']), 0, 4) as $kw): ?>
                            <?php $kw = trim($kw); if (empty($kw)) continue; ?>
                            <span class="badge bg-light text-muted" style="font-size:.65rem"><?= esc($kw) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:#f1f5f9;color:#475569;font-size:.75rem">
                            <?= esc($entry['category']) ?>
                        </span>
                    </td>
                    <td class="text-muted small">
                        <?= esc($entry['source_document'] ?? '—') ?>
                    </td>
                    <td class="text-center text-muted small"><?= $entry['sort_order'] ?></td>
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input toggle-active" type="checkbox"
                                   data-id="<?= $entry['id'] ?>"
                                   <?= $entry['is_active'] ? 'checked' : '' ?>
                                   style="width:2em;height:1em;cursor:pointer">
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="<?= base_url('admin/knowledge/edit/' . $entry['id']) ?>"
                               class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteEntry(<?= $entry['id'] ?>, this)" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
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

<!-- Import Excel Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Import từ Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/knowledge/import') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-warning small border-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Cấu trúc Excel được hỗ trợ:</strong><br>
                        • Mỗi sheet = một danh mục<br>
                        • Cột A: Tiêu đề / Trường thông tin<br>
                        • Cột B: Nội dung / Giá trị<br>
                        • Hỗ trợ cả file từ Zalo OA lẫn file tự soạn
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn file Excel (.xlsx)</label>
                        <input type="file" class="form-control" name="excel_file"
                               accept=".xlsx,.xls" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="replace_all" value="1" id="replaceAll">
                        <label class="form-check-label small" for="replaceAll">
                            Xóa toàn bộ kiến thức cũ trước khi import
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Toggle active/inactive
document.querySelectorAll('.toggle-active').forEach(cb => {
    cb.addEventListener('change', async function() {
        const id = this.dataset.id;
        const resp = await fetch('<?= base_url('admin/knowledge/toggle/') ?>' + id, { method: 'POST' });
        const data = await resp.json();
        if (!data.status) {
            this.checked = !this.checked; // revert
            alert('Lỗi cập nhật!');
        }
    });
});

// Delete entry
async function deleteEntry(id, btn) {
    if (!confirm('Xóa mục kiến thức này?')) return;
    btn.disabled = true;

    const resp = await fetch('<?= base_url('admin/knowledge/') ?>' + id, { method: 'DELETE' });
    const data = await resp.json();

    if (data.status === 'ok') {
        btn.closest('tr').remove();
    } else {
        alert('Lỗi xóa!');
        btn.disabled = false;
    }
}
</script>
<?= $this->endSection() ?>
