<?php
/**
 * @var array $programs
 * @var array $types
 * @var array $statuses
 * @var string $status
 * @var string $q
 * @var string $pagination
 * @var array $currentUser
 */
$pageTitle = 'Chương trình Học bổng';
require_once __DIR__ . '/../partials/header.php';
$isAdmin = is_logged_in() && current_user()['role'] === 'admin'; // [NEW]

$statusBadge = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏆 Chương trình Học bổng (scholarship_programs)</h4>
    <?php if ($isAdmin): ?>
        <a href="<?= e(url('scholarship_programs', 'create')) ?>" class="btn btn-primary">+ Thêm chương trình</a>
    <?php endif; ?>
</div>

<!-- [NEW] Search + filter -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="scholarship_programs">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Tìm theo tên chương trình..." value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select">
            <option value="">-- Tất cả trạng thái --</option>
            <?php foreach ($statuses as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $status === $val ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Lọc</button>
    </div>
</form>

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
                        <td>
                            <a href="index.php?controller=scholarship_programs&action=show&id=<?= htmlspecialchars($p['id']) ?>" 
                            class="btn btn-sm btn-info">View</a>
                            
                            <?php if (isset($currentUser) && in_array($currentUser['role'], ['admin', 'staff'], true)): ?>
                                <a href="index.php?controller=scholarship_programs&action=edit&id=<?= htmlspecialchars($p['id']) ?>" 
                                class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger delete-program-btn" 
                                        data-id="<?= htmlspecialchars($p['id']) ?>">Delete</button>
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
