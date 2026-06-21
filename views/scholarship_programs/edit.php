<?php
/**
 * @var array $program
 * @var array $errors
 * @var array $types
 * @var array $statuses
 */
$pageTitle = 'Sửa Chương trình Học bổng';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('scholarship_programs', 'index')) ?>">Chương trình HB</a></li>
        <li class="breadcrumb-item active">Sửa #<?= e($program['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:620px;">
    <div class="card-header"><strong>✏️ Sửa Chương trình Học bổng</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('scholarship_programs', 'update', ['id' => $program['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>

            <!-- title -->
            <div class="mb-3">
                <label class="form-label">Tên chương trình (title) <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= e($program['title']) ?>" required>
            </div>

            <!-- scholarship_type (ENUM) -->
            <div class="mb-3">
                <label class="form-label">Loại học bổng (scholarship_type) <span class="text-danger">*</span></label>
                <select name="scholarship_type" class="form-select" required>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $program['scholarship_type'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- start_date / end_date -->
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">Ngày bắt đầu (start_date)</label>
                    <input type="date" name="start_date" class="form-control" value="<?= e($program['start_date']) ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">Ngày kết thúc (end_date)</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($program['end_date']) ?>">
                </div>
            </div>
            <p class="text-muted small mt-n2">⚠ end_date phải sau hoặc bằng start_date.</p>

            <!-- status (ENUM) -->
            <div class="mb-3">
                <label class="form-label">Trạng thái (status)</label>
                <select name="status" class="form-select">
                    <?php foreach ($statuses as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $program['status'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="<?= e(url('scholarship_programs', 'index')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
