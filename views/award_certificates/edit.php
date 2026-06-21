<?php
/**
 * Award Certificate Edit View
 * 
 * @var array $certificate Certificate data to edit
 * @var array $decisions List of approved scholarship decisions
 */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Chỉnh sửa Chứng chỉ Học bổng</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= e(url('award_certificate', 'update')) ?>" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($certificate['id']) ?>">

                        <!-- Decision Selection -->
                        <div class="mb-3">
                            <label for="decision_id" class="form-label">Quyết định Học bổng <span class="text-danger">*</span></label>
                            <select class="form-select" 
                                    id="decision_id" 
                                    name="decision_id" 
                                    required>
                                <option value="">-- Chọn Sinh viên --</option>
                                <?php foreach ($decisions as $decision): ?>
                                    <option value="<?= e($decision['id']) ?>" 
                                            data-amount="<?= e($decision['awarded_amount']) ?>"
                                            <?= $decision['id'] == $certificate['decision_id'] ? 'selected' : '' ?>>
                                        <?= e($decision['student_name']) ?> (<?= e($decision['student_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Vui lòng chọn quyết định học bổng.
                            </div>
                        </div>

                        <!-- Certificate Code -->
                        <div class="mb-3">
                            <label for="certificate_code" class="form-label">Mã Chứng chỉ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="certificate_code" 
                                   name="certificate_code" 
                                   value="<?= e($certificate['certificate_code']) ?>"
                                   placeholder="VD: CERT-26-0001"
                                   pattern="[A-Z0-9\-]+"
                                   maxlength="50"
                                   required>
                            <div class="invalid-feedback">
                                Mã chứng chỉ là bắt buộc và chỉ chứa chữ in hoa, số và dấu gạch ngang.
                            </div>
                            <small class="form-text text-muted">
                                Định dạng: CHỮ IN HOA, SỐ và dấu gạch ngang (-). Tối đa 50 ký tự.
                            </small>
                        </div>

                        <!-- Issue Date -->
                        <div class="mb-3">
                            <label for="issue_date" class="form-label">Ngày Phát hành</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="issue_date" 
                                   name="issue_date"
                                   value="<?= e($certificate['issue_date']) ?>">
                            <small class="form-text text-muted">
                                Để trống nếu chưa phát hành.
                            </small>
                        </div>

                        <!-- PDF URL -->
                        <div class="mb-3">
                            <label for="pdf_url" class="form-label">Đường dẫn File PDF <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="pdf_url" 
                                   name="pdf_url" 
                                   value="<?= e($certificate['pdf_url']) ?>"
                                   placeholder="/uploads/certs/cert_0001.pdf"
                                   maxlength="255"
                                   required>
                            <div class="invalid-feedback">
                                Đường dẫn file PDF là bắt buộc.
                            </div>
                            <small class="form-text text-muted">
                                Nhập đường dẫn đến file PDF chứng chỉ. Tối đa 255 ký tự.
                            </small>
                        </div>

                        <!-- Current PDF Link -->
                        <?php if (!empty($certificate['pdf_url'])): ?>
                            <div class="mb-3">
                                <label class="form-label">File PDF Hiện tại</label>
                                <div>
                                    <a href="<?= e($certificate['pdf_url']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-file-pdf"></i> Xem File PDF
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Awarded Amount Display (Read-only) -->
                        <div class="mb-3">
                            <label for="awarded_amount_display" class="form-label">Số tiền Học bổng</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="awarded_amount_display" 
                                   readonly
                                   value="<?= e(number_format($certificate['awarded_amount'], 0, ',', '.')) ?> VNĐ">
                            <small class="form-text text-muted">
                                Thông tin hiển thị từ quyết định học bổng đã chọn.
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= e(url('award_certificate', 'index')) ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật Chứng chỉ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side Validation and Amount Display Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Display awarded amount when decision is changed
    const decisionSelect = document.getElementById('decision_id');
    const amountDisplay = document.getElementById('awarded_amount_display');
    
    decisionSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const amount = selectedOption.getAttribute('data-amount');
        
        if (amount) {
            const formattedAmount = parseFloat(amount).toLocaleString('vi-VN') + ' VNĐ';
            amountDisplay.value = formattedAmount;
        } else {
            amountDisplay.value = '';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
