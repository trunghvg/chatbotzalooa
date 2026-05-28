<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <h5 class="fw-bold mb-1">Cài đặt hệ thống</h5>
    <p class="text-muted small mb-0">Cấu hình hành vi chatbot và thông tin liên hệ</p>
</div>

<form method="POST" action="<?= base_url('admin/settings') ?>">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Bot settings -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-robot me-2 text-primary"></i>Cài đặt Bot
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Bot on/off -->
                    <div class="mb-4 p-3 rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Trả lời tự động</div>
                                <small class="text-muted">Bật/tắt bot Claude AI trả lời học viên</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="bot_enabled"
                                       value="1" id="botEnabled"
                                       <?= ($settings['bot_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                                       style="width:2.5em;height:1.25em">
                                <input type="hidden" name="bot_enabled" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Welcome message -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="welcomeMsg">
                            Tin nhắn chào mừng
                            <span class="text-muted fw-normal">(khi học viên quan tâm OA)</span>
                        </label>
                        <textarea class="form-control" name="welcome_message" id="welcomeMsg"
                                  rows="5"><?= esc($settings['welcome_message'] ?? '') ?></textarea>
                    </div>

                    <!-- Image reply -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trả lời khi nhận hình ảnh</label>
                        <textarea class="form-control" name="image_reply" rows="2"><?= esc($settings['image_reply'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer to human -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-person-lines-fill me-2 text-warning"></i>Chuyển sang nhân viên
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Từ khóa kích hoạt</label>
                        <input type="text" class="form-control" name="transfer_keywords"
                               value="<?= esc($settings['transfer_keywords'] ?? '') ?>"
                               placeholder="nhân viên, tư vấn viên, gặp người thật">
                        <small class="text-muted">Phân cách bằng dấu phẩy. Bot sẽ chuyển hội thoại khi nhận từ khóa này.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tin nhắn khi chuyển</label>
                        <textarea class="form-control" name="transfer_message" rows="5"><?= esc($settings['transfer_message'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact info -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-telephone-fill me-2 text-success"></i>Thông tin liên hệ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Hotline</label>
                            <input type="text" class="form-control" name="contact_hotline"
                                   value="<?= esc($settings['contact_hotline'] ?? '') ?>"
                                   placeholder="1800 xxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email hỗ trợ</label>
                            <input type="email" class="form-control" name="contact_email"
                                   value="<?= esc($settings['contact_email'] ?? '') ?>"
                                   placeholder="support@example.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Giờ làm việc</label>
                            <input type="text" class="form-control" name="contact_hours"
                                   value="<?= esc($settings['contact_hours'] ?? '') ?>"
                                   placeholder="8:00 - 22:00 mỗi ngày">
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Thông tin này được nhúng vào System Prompt để Claude AI có thể trả lời khi học viên hỏi về liên hệ.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>Lưu cài đặt
        </button>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Fix: checkbox với hidden field - chỉ submit giá trị checkbox khi checked
document.querySelector('form').addEventListener('submit', function(e) {
    const checkbox = document.getElementById('botEnabled');
    const hiddenInputs = document.querySelectorAll('input[type="hidden"][name="bot_enabled"]');
    if (checkbox.checked) {
        hiddenInputs.forEach(el => el.remove());
    } else {
        checkbox.remove();
    }
});
</script>
<?= $this->endSection() ?>
