<?php
/**
 * Disbursements Index View
 * * @var array $disbursements
 * @var array $pagination
 * @var array $filters
 * @var int $totalItems
 */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý Giải ngân</h2>
        <?php if (in_array(current_user()['role'], ['admin', 'reviewer'], true)): ?>
            <a href="<?= e(url('disbursement', 'create')) ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tạo Giải ngân Mới
            </a>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(url('disbursement', 'index')) ?>" class="row g-3">
                <input type="hidden" name="controller" value="disbursement">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-5">
                    <label for="student_name" class="form-label">Tìm kiếm Sinh viên</label>
                    <input type="text" 
                           class="form-control" 
                           id="student_name" 
                           name="student_name" 
                           placeholder="Tên hoặc Mã sinh viên" 
                           value="<?= e($filters['student_name']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">-- Tất cả --</option>
                        <option value="processing" <?= $filters['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="failed" <?= $filters['status'] === 'failed' ? 'selected' : '' ?>>Thất bại</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                    <a href="<?= e(url('disbursement', 'index')) ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Disbursements Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($disbursements)): ?>
                <div class="alert alert-info">Không có giải ngân nào.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Sinh viên</th>
                                <th>Mã SV</th>
                                <th>Số tiền quyết định</th>
                                <th>Số tiền đã trả</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Ngày thanh toán</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disbursements as $d): ?>
                                <tr data-disbursement-id="<?= e($d['id']) ?>">
                                    <td><?= e($d['id']) ?></td>
                                    <td><?= e($d['student_name']) ?></td>
                                    <td><?= e($d['student_code']) ?></td>
                                    <td><?= e(number_format($d['decision_amount'], 0, ',', '.')) ?> VNĐ</td>
                                    <td><?= e(number_format($d['amount_paid'], 0, ',', '.')) ?> VNĐ</td>
                                    <td><?= e($d['payment_method']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'processing' => 'badge bg-warning text-dark',
                                            'completed' => 'badge bg-success',
                                            'failed' => 'badge bg-danger'
                                        ];
                                        $statusText = [
                                            'processing' => 'Đang xử lý',
                                            'completed' => 'Hoàn thành',
                                            'failed' => 'Thất bại'
                                        ];
                                        ?>
                                        <span class="<?= $statusClass[$d['status']] ?>">
                                            <?= $statusText[$d['status']] ?>
                                        </span>
                                    </td>
                                    <td><?= $d['payment_date'] ? e(date('d/m/Y', strtotime($d['payment_date']))) : '<em>Chưa thanh toán</em>' ?></td>
                                    <td>
                                        <?php if (in_array(current_user()['role'], ['admin', 'reviewer'], true)): ?>
                                            <a href="<?= e(url('disbursement', 'edit', ['id' => $d['id']])) ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Chỉnh sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn" 
                                                    data-id="<?= e($d['id']) ?>"
                                                    title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?= render_pagination($pagination['page'], $totalItems, $pagination['perPage'], 'disbursement', $filters) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AJAX Delete Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const disbursementId = this.getAttribute('data-id');
            
            if (!confirm('Bạn có chắc chắn muốn xóa giải ngân này không?')) {
                return;
            }
            
            // Prepare form data with CSRF token
            const formData = new FormData();
            formData.append('id', disbursementId);
            formData.append('csrf_token', '<?= e(csrf_token()) ?>');
            
            // Send AJAX request
            fetch('<?= e(url('disbursement', 'delete')) ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from DOM
                    const row = document.querySelector(`tr[data-disbursement-id="${disbursementId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Show success message (optional: implement flash message display)
                    alert('Giải ngân đã được xóa thành công.');
                    
                    // Reload page if no rows left
                    const tbody = document.querySelector('tbody');
                    if (!tbody || tbody.children.length === 0) {
                        window.location.reload();
                    }
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa giải ngân.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi xóa giải ngân.');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
