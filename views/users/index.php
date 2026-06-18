<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/UserController.php';

$ctrl = new UserController();
$vars = $ctrl->index();
extract($vars);

$pageTitle = 'Danh sách Người dùng';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">👥 Người dùng (Users)</h4>
    <a href="create.php" class="btn btn-primary">+ Thêm người dùng</a>
</div>

<!-- Lọc theo role -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="index.php" class="btn btn-outline-secondary <?= !$currentRole ? 'active' : '' ?>">Tất cả</a>
        <a href="index.php?role=admin" class="btn btn-outline-danger <?= $currentRole === 'admin' ? 'active' : '' ?>">Admin</a>
        <a href="index.php?role=reviewer" class="btn btn-outline-warning <?= $currentRole === 'reviewer' ? 'active' : '' ?>">Reviewer</a>
        <a href="index.php?role=student" class="btn btn-outline-success <?= $currentRole === 'student' ? 'active' : '' ?>">Student</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Không có người dùng nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['id']) ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <?php
                            $badgeMap = ['admin' => 'danger', 'reviewer' => 'warning text-dark', 'student' => 'success'];
                            $badge = $badgeMap[$u['role']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= e(ucfirst($u['role'])) ?></span>
                        </td>
                        <td><?= e($u['created_at']) ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <a href="delete.php?id=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Xóa người dùng này và toàn bộ dữ liệu liên quan?')">Xóa</a>
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
