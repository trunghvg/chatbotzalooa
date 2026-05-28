<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <h5 class="fw-bold mb-1">System Prompt Claude AI</h5>
    <p class="text-muted small mb-0">
        Đây là "hướng dẫn nghề nghiệp" cho Claude AI. Định nghĩa vai trò, phong cách, giới hạn và kiến thức của bot.
    </p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="<?= base_url('admin/prompt') ?>">
            <?= csrf_field() ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <textarea class="form-control font-monospace" name="system_prompt"
                              id="systemPrompt" rows="28"
                              style="font-size:.85rem;resize:vertical"><?= esc($prompt) ?></textarea>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <small class="text-muted" id="charCount">0 ký tự</small>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Lưu System Prompt
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>Gợi ý viết prompt
                </h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-2">Định nghĩa rõ <strong>vai trò</strong> của bot (VD: tư vấn viên khóa học lập trình)</li>
                    <li class="mb-2">Liệt kê <strong>nguyên tắc</strong> trả lời (ngắn gọn, lịch sự, bằng tiếng Việt)</li>
                    <li class="mb-2">Cung cấp <strong>thông tin khóa học</strong> cụ thể nếu có</li>
                    <li class="mb-2">Định nghĩa <strong>ranh giới</strong> (không trả lời gì)</li>
                    <li class="mb-2">Cho biết <strong>thông tin liên hệ</strong> khi cần chuyển tiếp</li>
                </ul>

                <hr>

                <h6 class="fw-semibold mb-2">Cấu trúc gợi ý</h6>
                <div class="small font-monospace p-2 rounded" style="background:#f8fafc;font-size:.75rem">
## Vai trò<br>
Bạn là...<br><br>
## Kiến thức<br>
- Khóa học: ...<br>
- Lịch học: ...<br><br>
## Nguyên tắc<br>
1. Luôn tiếng Việt<br>
2. Thân thiện<br><br>
## Giới hạn<br>
Không làm...
                </div>

                <hr>

                <h6 class="fw-semibold mb-2">
                    <i class="bi bi-cpu me-1 text-primary"></i>Model đang dùng
                </h6>
                <div class="p-2 rounded border text-center">
                    <code class="small"><?= esc(env('CLAUDE_MODEL', 'claude-opus-4-7')) ?></code>
                </div>
                <small class="text-muted d-block mt-1">Thay đổi trong file .env → CLAUDE_MODEL</small>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const textarea = document.getElementById('systemPrompt');
const charCount = document.getElementById('charCount');

function updateCount() {
    charCount.textContent = textarea.value.length.toLocaleString('vi-VN') + ' ký tự';
}

textarea.addEventListener('input', updateCount);
updateCount();
</script>
<?= $this->endSection() ?>
