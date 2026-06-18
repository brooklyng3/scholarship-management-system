<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/StudentProfileController.php';

$ctrl = new StudentProfileController();
$vars = $ctrl->index();
extract($vars);

$pageTitle = 'Hồ sơ Sinh viên';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🎓 Hồ sơ Sinh viên (student_profiles)</h4>
    <a href="create.php" class="btn btn-primary">+ Thêm hồ sơ</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Ngành</th>
                        <th class="text-center">GPA</th>
                        <th class="text-center">Tín chỉ</th>
                        <th class="text-center">Rèn luyện</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($profiles)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Chưa có hồ sơ sinh viên nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($profiles as $p): ?>
                    <tr>
                        <td><?= e($p['id']) ?></td>
                        <td><code><?= e($p['student_code']) ?></code></td>
                        <td><?= e($p['full_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td><?= e($p['major']) ?></td>
                        <td class="text-center">
                            <?php
                            $gpa = (float)$p['current_gpa'];
                            $gpaBadge = $gpa >= 3.6 ? 'success' : ($gpa >= 3.2 ? 'primary' : ($gpa >= 2.5 ? 'warning text-dark' : 'danger'));
                            ?>
                            <span class="badge bg-<?= $gpaBadge ?>"><?= e($p['current_gpa']) ?></span>
                        </td>
                        <td class="text-center"><?= e($p['accumulated_credits']) ?></td>
                        <td class="text-center"><?= e($p['conduct_score']) ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <a href="delete.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Xóa hồ sơ sinh viên này?')">Xóa</a>
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
