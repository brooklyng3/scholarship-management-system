<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/ScholarshipProgramController.php';

$ctrl = new ScholarshipProgramController();
$vars = $ctrl->index();
extract($vars);

$pageTitle = 'Chương trình Học bổng';
require_once __DIR__ . '/../partials/header.php';

$statusBadge = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏆 Chương trình Học bổng (scholarship_programs)</h4>
    <a href="create.php" class="btn btn-primary">+ Thêm chương trình</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tên chương trình</th>
                        <th>Loại học bổng</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có chương trình học bổng nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($programs as $p): ?>
                    <tr>
                        <td><?= e($p['id']) ?></td>
                        <td><strong><?= e($p['title']) ?></strong></td>
                        <td><?= e($types[$p['scholarship_type']] ?? $p['scholarship_type']) ?></td>
                        <td><?= e($p['start_date']) ?></td>
                        <td><?= e($p['end_date']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $statusBadge[$p['status']] ?? 'secondary' ?>">
                                <?= e($statuses[$p['status']] ?? $p['status']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <a href="delete.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Xóa chương trình học bổng này?')">Xóa</a>
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
