<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/ViolationRecordController.php';

$ctrl = new ViolationRecordController();
$vars = $ctrl->index();
extract($vars);

$pageTitle = 'Danh sách Vi phạm';
require_once __DIR__ . '/../partials/header.php';

$typeBadge = [
    'fee_debt'     => 'danger',
    'discipline'   => 'dark',
    'library_debt' => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🚫 Danh sách Vi phạm (violation_records)</h4>
    <a href="create.php" class="btn btn-primary">+ Thêm vi phạm</a>
</div>
<p class="text-muted small">Sinh viên có bản ghi tại đây sẽ bị loại tự động khỏi các đợt xét học bổng.</p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Loại vi phạm</th>
                        <th>Mô tả</th>
                        <th>Ngày ghi nhận</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Không có bản ghi vi phạm nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= e($r['id']) ?></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $typeBadge[$r['violation_type']] ?? 'secondary' ?>">
                                <?= e($types[$r['violation_type']] ?? $r['violation_type']) ?>
                            </span>
                        </td>
                        <td><?= e($r['description']) ?></td>
                        <td><?= e($r['recorded_date']) ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <a href="delete.php?id=<?= $r['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Xóa bản ghi vi phạm này?')">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
