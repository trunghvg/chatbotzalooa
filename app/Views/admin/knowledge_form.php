<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('admin/knowledge') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="fw-bold mb-0"><?= esc($title) ?></h5>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <?php
        $isEdit  = !empty($entry);
        $action  = $isEdit
            ? base_url('admin/knowledge/update/' . $entry['id'])
            : base_url('admin/knowledge/store');
        ?>
        <form method="POST" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Tiêu đề -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Tiêu đề <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="title" required
                                   value="<?= esc($entry['title'] ?? '') ?>"
                                   placeholder="VD: Đánh giá kết quả học tập - Bài thu hoạch (QĐ 1147, Điều 6)">
                        </div>

                        <!-- Category & Source -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <input type="text" class="form-control" name="category"
                                   value="<?= esc($entry['category'] ?? 'general') ?>"
                                   placeholder="VD: quy-che-boi-duong, to-chuc-bo-may...">
                            <small class="text-muted">Dùng dấu gạch ngang, không dấu. Cùng danh mục sẽ hiển thị gần nhau.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nguồn văn bản</label>
                            <input type="text" class="form-control" name="source_document"
                                   value="<?= esc($entry['source_document'] ?? '') ?>"
                                   placeholder="VD: QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025">
                        </div>

                        <!-- Nội dung -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Nội dung kiến thức <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control font-monospace" name="content" rows="14" required
                                      style="font-size:.85rem"
                                      placeholder="Nhập nội dung. Hỗ trợ Markdown: **in đậm**, *nghiêng*, - danh sách..."><?= esc($entry['content'] ?? '') ?></textarea>
                            <small class="text-muted">
                                Hỗ trợ Markdown. Trích dẫn số quyết định và điều khoản để bot trả lời chính xác hơn.
                            </small>
                        </div>

                        <!-- Keywords -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Từ khóa tìm kiếm</label>
                            <input type="text" class="form-control" name="keywords"
                                   value="<?= esc($entry['keywords'] ?? '') ?>"
                                   placeholder="bài thu hoạch, điểm số, xếp loại, 80%, hoàn thành, không đạt...">
                            <small class="text-muted">
                                Phân cách bằng dấu phẩy. Hệ thống dùng để tìm mục phù hợp khi học viên hỏi.
                            </small>
                        </div>

                        <!-- Sort order & Active -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" name="sort_order"
                                   value="<?= esc($entry['sort_order'] ?? 0) ?>"
                                   min="0" placeholder="0">
                            <small class="text-muted">Số nhỏ hơn → hiển thị trước.</small>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="p-3 rounded-3 border w-100">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           name="is_active" value="1" id="isActive"
                                           <?= ($entry['is_active'] ?? 1) ? 'checked' : '' ?>
                                           style="width:2.5em;height:1.25em">
                                    <label class="form-check-label fw-semibold ms-1" for="isActive">
                                        Kích hoạt mục kiến thức này
                                    </label>
                                </div>
                                <small class="text-muted">Khi tắt, bot sẽ không dùng mục này.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i><?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <a href="<?= base_url('admin/knowledge') ?>" class="btn btn-outline-secondary">
                    Hủy
                </a>
            </div>
        </form>
    </div>

    <!-- Gợi ý -->
    <div class="col-lg-3">
        <div class="card shadow-sm border-0 position-sticky" style="top:80px">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>Gợi ý
                </h6>

                <p class="small text-muted mb-2"><strong>Danh mục gợi ý:</strong></p>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <?php foreach (['tong-quan','to-chuc-bo-may','quy-che-boi-duong','de-an-boi-duong','ttct-le-chan','general'] as $cat): ?>
                    <span class="badge bg-light text-dark border" style="cursor:pointer;font-size:.75rem"
                          onclick="document.querySelector('[name=category]').value='<?= $cat ?>'">
                        <?= $cat ?>
                    </span>
                    <?php endforeach; ?>
                </div>

                <hr>
                <p class="small text-muted mb-0"><strong>Tips viết nội dung:</strong></p>
                <ul class="small text-muted ps-3 mt-2 mb-0">
                    <li>Trích số QĐ, điều khoản cụ thể</li>
                    <li>Dùng <code>**in đậm**</code> cho số liệu quan trọng</li>
                    <li>Liệt kê rõ ràng bằng <code>-</code> hoặc <code>1.</code></li>
                    <li>Keywords = những gì học viên hay hỏi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
