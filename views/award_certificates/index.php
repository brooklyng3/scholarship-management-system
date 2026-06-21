<?php
/**
 * Award Certificates Index View
 * 
 * @var array $certificates List of award certificates
 * @var int $totalItems Total number of certificates
 * @var array $pagination Pagination parameters
 * @var array $filters Search filters
 */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý Chứng chỉ Học bổng</h2>
        <a href="<?= e(url('award_certificate', 'create')) ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tạo Chứng chỉ Mới
        </a>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(url('award_certificate', 'index')) ?>" class="row g-3">
                <input type="hidden" name="controller" value="award_certificate">
                <input type="hidden" name="action" value="index">
                
                <div class="col-md-4">
                    <label for="certificate_code" class="form-label">Mã Chứng chỉ</label>
                    <input type="text" 
                           class="form-control" 
                           id="certificate_code" 
                           name="certificate_code" 
                           value="<?= e($filters['certificate_code']) ?>"
                           placeholder="Nhập mã chứng chỉ">
                </div>
                
                <div class="col-md-4">
                    <label for="student_name" class="form-label">Tên Sinh viên / MSSV</label>
                    <input type="text" 
                           class="form-control" 
                           id="student_name" 
                           name="student_name" 
                           value="<?= e($filters['student_name']) ?>"
                           placeholder="Nhập tên hoặc mã sinh viên">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                    <a href="<?= e(url('award_certificate', 'index')) ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($certificates)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Không tìm thấy chứng chỉ nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Mã Chứng chỉ</th>
                                <th>Sinh viên</th>
                                <th>Mã SV</th>
                                <th>Số tiền</th>
                                <th>Ngày Phát hành</th>
                                <th>File PDF</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $cert): ?>
                                <tr data-certificate-id="<?= e($cert['id']) ?>">
                                    <td><?= e($cert['id']) ?></td>
                                    <td><strong><?= e($cert['certificate_code']) ?></strong></td>
                                    <td><?= e($cert['student_name']) ?></td>
                                    <td><?= e($cert['student_code']) ?></td>
                                    <td><?= e(number_format($cert['awarded_amount'], 0, ',', '.')) ?> VNĐ</td>
                                    <td><?= e($cert['issue_date'] ?? 'Chưa phát hành') ?></td>
                                    <td>
                                        <?php if (!empty($cert['pdf_url'])): ?>
                                            <a href="<?= e($cert['pdf_url']) ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="bi bi-file-pdf"></i> Xem
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= e(url('award_certificate', 'edit', ['id' => $cert['id']])) ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Sửa
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger btn-delete"
                                                data-id="<?= e($cert['id']) ?>"
                                                data-certificate-code="<?= e($cert['certificate_code']) ?>">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?= render_pagination($pagination['page'], $totalItems, $pagination['perPage'], 'award_certificate', $filters) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AJAX Delete Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const certificateId = this.getAttribute('data-id');
            const certificateCode = this.getAttribute('data-certificate-code');
            
            if (!confirm(`Bạn có chắc chắn muốn xóa chứng chỉ "${certificateCode}"?`)) {
                return;
            }
            
            // Prepare form data with CSRF token
            const formData = new FormData();
            formData.append('id', certificateId);
            formData.append('csrf_token', '<?= e(csrf_token()) ?>');
            
            // Send AJAX delete request
            fetch('<?= e(url('award_certificate', 'delete')) ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from DOM
                    const row = document.querySelector(`tr[data-certificate-id="${certificateId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Show success message
                    alert(data.message);
                    
                    // Reload page if no more rows
                    const remainingRows = document.querySelectorAll('tbody tr').length;
                    if (remainingRows === 0) {
                        window.location.reload();
                    }
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi xóa chứng chỉ.');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
