<?php
/**
 * @var array $profiles
 * @var string $q
 * @var string $pagination
 */
$pageTitle = 'Hồ sơ Cán bộ';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏢 Hồ sơ Cán bộ (staff_profiles)</h4>
    <a href="<?= e(url('staff_profiles', 'create')) ?>" class="btn btn-primary">+ Thêm hồ sơ</a>
</div>

<!-- [NEW] Search box -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="staff_profiles">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Tìm theo mã CB, tên, phòng ban..." value="<?= e($q) ?>">
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
                        <th>Mã cán bộ</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Phòng/Ban</th>
                        <th>Cập nhật lúc</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($profiles)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có hồ sơ cán bộ nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($profiles as $p): ?>
                    <tr>
                        <td><?= e($p['id']) ?></td>
                        <td><code><?= e($p['staff_code']) ?></code></td>
                        <td><?= e($p['user_full_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td>
                            <?php $badge = $p['role'] === 'admin' ? 'danger' : 'warning text-dark'; ?>
                            <span class="badge bg-<?= $badge ?>"><?= e(ucfirst($p['role'])) ?></span>
                        </td>
                        <td><?= e($p['department']) ?: '<span class="text-muted">—</span>' ?></td>
                        <td><?= e($p['updated_at']) ?></td>
                        <td class="text-center">
                            <a href="<?= e(url('staff_profiles', 'edit', ['id' => $p['id']])) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <a href="<?= e(url('staff_profiles', 'delete', ['id' => $p['id'], 'csrf_token' => csrf_token()])) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Xóa hồ sơ cán bộ này?')">Xóa</a>
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
