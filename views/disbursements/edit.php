<?php
/**
 * Disbursements Edit View
 * 
 * Form to edit an existing disbursement with HTML5 validation and CSRF protection.
 * @var array $disbursement
 * @var array $decisions
 */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Chỉnh sửa Giải ngân #<?= e($disbursement['id']) ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" 
                          action="<?= e(url('disbursement', 'update')) ?>" 
                          novalidate>
                        
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($disbursement['id']) ?>">
                        
                        <!-- Current Student Info -->
                        <div class="alert alert-info mb-3">
                            <strong>Sinh viên:</strong> <?= e($disbursement['student_name']) ?> 
                            (<?= e($disbursement['student_code']) ?>)
                            <br>
                            <strong>Số tiền quyết định:</strong> <?= e(number_format($disbursement['decision_amount'], 0, ',', '.')) ?> VNĐ
                        </div>

                        <!-- Decision Selection -->
                        <div class="mb-3">
                            <label for="decision_id" class="form-label">
                                Quyết định Học bổng <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" 
                                    id="decision_id" 
                                    name="decision_id" 
                                    required>
                                <option value="">-- Chọn quyết định --</option>
                                <?php foreach ($decisions as $decision): ?>
                                    <option value="<?= e($decision['id']) ?>"
                                            <?= $decision['id'] == $disbursement['decision_id'] ? 'selected' : '' ?>>
                                        <?= e($decision['student_name']) ?> 
                                        (<?= e($decision['student_code']) ?>) - 
                                        <?= e(number_format($decision['awarded_amount'], 0, ',', '.')) ?> VNĐ
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Vui lòng chọn quyết định học bổng.
                            </div>
                        </div>

                        <!-- Amount Paid -->
                        <div class="mb-3">
                            <label for="amount_paid" class="form-label">
                                Số tiền giải ngân <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="amount_paid" 
                                   name="amount_paid" 
                                   step="0.01" 
                                   min="0.01" 
                                   value="<?= e($disbursement['amount_paid']) ?>"
                                   placeholder="Nhập số tiền" 
                                   required>
                            <div class="invalid-feedback">
                                Số tiền phải lớn hơn 0.
                            </div>
                            <small class="form-text text-muted">
                                Đơn vị: VNĐ (Ví dụ: 15000000)
                            </small>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">
                                Phương thức thanh toán <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="payment_method" 
                                   name="payment_method" 
                                   value="<?= e($disbursement['payment_method']) ?>"
                                   placeholder="Ví dụ: Chuyển khoản ngân hàng, Tiền mặt" 
                                   maxlength="50" 
                                   required>
                            <div class="invalid-feedback">
                                Vui lòng nhập phương thức thanh toán.
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Trạng thái <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="">-- Chọn trạng thái --</option>
                                <option value="processing" <?= $disbursement['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="completed" <?= $disbursement['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                <option value="failed" <?= $disbursement['status'] === 'failed' ? 'selected' : '' ?>>Thất bại</option>
                            </select>
                            <div class="invalid-feedback">
                                Vui lòng chọn trạng thái.
                            </div>
                        </div>

                        <!-- Payment Date -->
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">
                                Ngày thanh toán
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="payment_date" 
                                   name="payment_date"
                                   value="<?= e($disbursement['payment_date']) ?>">
                            <small class="form-text text-muted">
                                Để trống nếu chưa thanh toán
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= e(url('disbursement', 'index')) ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Cập nhật Giải ngân
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side Validation Script -->
<script>
(function() {
    'use strict';
    
    // Fetch form element
    const form = document.querySelector('form');
    
    // Add Bootstrap validation on submit
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);

    // Real-time validation for amount field
    const amountField = document.getElementById('amount_paid');
    amountField.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value <= 0 || isNaN(value)) {
            this.setCustomValidity('Số tiền phải lớn hơn 0');
        } else {
            this.setCustomValidity('');
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
