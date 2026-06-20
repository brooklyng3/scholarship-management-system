<?php
/** Template: student_profiles/index — biến: $profiles, $q, $pagination */
$pageTitle = 'Hồ sơ Sinh viên';
require_once __DIR__ . '/../partials/header.php';
$canManage = is_logged_in() && in_array(current_user()['role'], ['admin', 'reviewer'], true); // [NEW]
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🎓 Hồ sơ Sinh viên (student_profiles)</h4>
    <?php if ($canManage): ?>
        <a href="<?= e(url('student_profiles', 'create')) ?>" class="btn btn-primary">+ Thêm hồ sơ</a>
    <?php endif; ?>
</div>

<!-- [NEW] Search box -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="student_profiles">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Tìm theo mã SV, tên, ngành..." value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Tìm</button>
    </div>
</form>

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
                            <?php if ($canManage): ?>
                                <a href="<?= e(url('student_profiles', 'edit', ['id' => $p['id']])) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <a href="<?= e(url('student_profiles', 'delete', ['id' => $p['id'], 'csrf_token' => csrf_token()])) ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Xóa hồ sơ sinh viên này?')">Xóa</a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?= $pagination ?></div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
