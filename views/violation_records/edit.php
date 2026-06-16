<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../controllers/ViolationRecordController.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$ctrl = new ViolationRecordController();
$vars = $ctrl->edit($id);
extract($vars);

$pageTitle = 'Sửa Vi phạm';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Vi phạm</a></li>
        <li class="breadcrumb-item active">Sửa #<?= e($record['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:600px;">
    <div class="card-header"><strong>✏️ Sửa Bản ghi Vi phạm</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Người dùng <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Chọn người dùng --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $record['user_id'] == $u['id'] ? 'selected' : '' ?>>
                            [<?= e(ucfirst($u['role'])) ?>] <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Loại vi phạm <span class="text-danger">*</span></label>
                <select name="violation_type" class="form-select" required>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $record['violation_type'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3"><?= e($record['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày ghi nhận</label>
                <input type="date" name="recorded_date" class="form-control" value="<?= e($record['recorded_date']) ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="index.php" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
