<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/StaffProfileController.php';

$ctrl = new StaffProfileController();
$vars = $ctrl->create();
extract($vars);

$pageTitle = 'Thêm Hồ sơ Cán bộ';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Hồ sơ Cán bộ</a></li>
        <li class="breadcrumb-item active">Thêm mới</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:560px;">
    <div class="card-header"><strong>➕ Tạo Hồ sơ Cán bộ</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (empty($availableUsers)): ?>
            <div class="alert alert-warning">Tất cả tài khoản admin/reviewer đều đã có hồ sơ cán bộ. <a href="../users/create.php">Tạo tài khoản mới</a> trước.</div>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tài khoản Admin / Reviewer <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Chọn tài khoản --</option>
                    <?php foreach ($availableUsers as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($old['user_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>>
                            [<?= e(ucfirst($u['role'])) ?>] <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mã cán bộ <span class="text-danger">*</span></label>
                <input type="text" name="staff_code" class="form-control" value="<?= e($old['staff_code'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phòng / Ban</label>
                <input type="text" name="department" class="form-control"
                       placeholder="VD: Phòng Đào tạo, Phòng CTSV"
                       value="<?= e($old['department'] ?? '') ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="index.php" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
