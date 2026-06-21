<?php
/**
 * Disbursements Create View
 * 
 * Form to create a new disbursement with HTML5 validation and CSRF protection.
 * @var array $decisions
 */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tạo Giải ngân Mới</h4>
                </div>
                <div class="card-body">
                    <form method="POST" 
                          action="<?= e(url('disbursement', 'store')) ?>" 
                          novalidate>
                        
                        <?= csrf_field() ?>
                        
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
                                    <option value="<?= e($decision['id']) ?>">
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
                                <option value="processing">Đang xử lý</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="failed">Thất bại</option>
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
                                   name="payment_date">
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Tạo Giải ngân
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
