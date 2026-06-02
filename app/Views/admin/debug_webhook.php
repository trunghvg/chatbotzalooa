<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0">Debug Webhook Zalo</h5>
    <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i>Reload
    </a>
</div>

<div class="alert alert-info small mb-3">
    <strong>Cách dùng:</strong> Nhắn 1 tin từ Zalo tới OA, rồi reload trang này để xem dữ liệu mà Zalo gửi về.
</div>

<!-- Webhook Payload -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white pt-3 pb-2">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-cloud-download text-primary me-2"></i>
            Webhook Payload (dữ liệu Zalo gửi tới server)
        </h6>
    </div>
    <div class="card-body p-0">
        <pre class="m-0 p-4" style="background:#1e1e1e;color:#d4d4d4;font-size:.8rem;overflow-x:auto;max-height:50vh"><?= esc($payload) ?></pre>
    </div>
</div>

<!-- getUserProfile API Result -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white pt-3 pb-2">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-person-badge text-success me-2"></i>
            Kết quả API getUserProfile (/v3.0/oa/user/detail)
        </h6>
    </div>
    <div class="card-body p-0">
        <pre class="m-0 p-4" style="background:#1e1e1e;color:#d4d4d4;font-size:.8rem;overflow-x:auto;max-height:30vh"><?= esc($userProfileResult) ?></pre>
    </div>
</div>

<!-- Diagnosis -->
<?php if ($senderIdFromPayload): ?>
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white pt-3 pb-2">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-clipboard-check text-warning me-2"></i>
            Chẩn đoán
        </h6>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered mb-0">
            <tr>
                <th style="width:220px">sender.id (Zalo social ID)</th>
                <td><code><?= esc($senderIdGlobal) ?></code>
                    <small class="text-muted ms-2">Không dùng cho OA API</small></td>
            </tr>
            <tr>
                <th>user_id_by_app <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Dùng cho API</span></th>
                <td><code><?= esc($userIdByApp) ?></code>
                    <small class="text-success ms-2">Đây là ID đúng để gọi getUserProfile</small></td>
            </tr>
            <tr>
                <th>display_name (trong webhook)</th>
                <td>
                    <?php if ($senderNameFromPayload): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= esc($senderNameFromPayload) ?></span>
                        <small class="text-muted ms-2">Zalo GỬI tên trong webhook</small>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Không có</span>
                        <small class="text-muted ms-2">Cần gọi API để lấy tên</small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>display_name (từ API)</th>
                <td>
                    <?php if ($nameFromApi): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= esc($nameFromApi) ?></span>
                        <small class="text-muted ms-2">API hoạt động bình thường</small>
                    <?php elseif ($apiError): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Lỗi: <?= esc($apiError) ?></span>
                        <?php if (str_contains($apiError, '-201')): ?>
                        <div class="mt-2 small bg-warning-subtle border border-warning-subtle rounded p-2">
                            <strong>Lỗi -201 "param is empty"</strong> — có 2 nguyên nhân:<br>
                            <strong>1.</strong> Người dùng này chưa nhấn <strong>Quan tâm OA</strong>, nên không có trong danh sách liên hệ.<br>
                            <strong>2.</strong> OA chưa được cấp quyền <em>Quản lý danh sách người quan tâm</em>.<br><br>
                            <strong>Cách khắc phục:</strong><br>
                            &nbsp;• Vào <a href="https://oa.zalo.me/manage/permission" target="_blank">OA Manager &gt; Quyền &amp; cấp phép</a> → bật quyền "Quản lý danh sách người quan tâm"<br>
                            &nbsp;• Sau đó vào <a href="<?= base_url('zalo/authorize') ?>">Zalo Authorize</a> để cấp lại token<br>
                            &nbsp;• Hoặc <strong>sửa tên thủ công</strong> trong <a href="<?= base_url('admin/conversations') ?>">màn hình hội thoại</a> (click icon bút chì cạnh tên)
                        </div>
                        <?php elseif (str_contains($apiError, '-403') || str_contains($apiError, 'permission') || str_contains($apiError, '-14')): ?>
                        <div class="mt-2 small text-danger">
                            <strong>OA chưa có quyền đọc thông tin người dùng.</strong><br>
                            Vào <a href="https://oa.zalo.me/manage/permission" target="_blank">Zalo OA Manager &gt; Quyền &amp; cấp phép</a>
                            để kiểm tra và bật quyền <strong>Quản lý danh sách người quan tâm</strong>.
                        </div>
                        <?php elseif (str_contains($apiError, '-216')): ?>
                        <div class="mt-2 small text-danger">
                            <strong>Access token hết hạn hoặc không hợp lệ.</strong>
                            Vào <a href="<?= base_url('zalo/authorize') ?>">Zalo Authorize</a> để cấp lại quyền.
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Chưa có dữ liệu</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
