<?php
/** Template: student_profiles/create — biến: $errors, $old, $availableUsers */
$pageTitle = 'Thêm Hồ sơ Sinh viên';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('student_profiles', 'index')) ?>">Hồ sơ Sinh viên</a></li>
        <li class="breadcrumb-item active">Thêm mới</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:640px;">
    <div class="card-header"><strong>➕ Tạo Hồ sơ Sinh viên</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (empty($availableUsers)): ?>
            <div class="alert alert-warning">Tất cả tài khoản student đều đã có hồ sơ. <a href="<?= e(url('users', 'create')) ?>">Tạo tài khoản mới</a> trước.</div>
        <?php else: ?>
        <form method="POST" action="<?= e(url('student_profiles', 'store')) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">Tài khoản sinh viên <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Chọn tài khoản --</option>
                    <?php foreach ($availableUsers as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($old['user_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>>
                            [#<?= e($u['id']) ?>] <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                <input type="text" name="student_code" class="form-control" value="<?= e($old['student_code'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($old['full_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngành học</label>
                <input type="text" name="major" class="form-control" value="<?= e($old['major'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">GPA hiện tại <small class="text-muted">(0.00 – 4.00)</small></label>
                    <input type="number" step="0.01" min="0" max="4" name="current_gpa" class="form-control"
                           value="<?= e($old['current_gpa'] ?? '') ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">Tín chỉ tích lũy</label>
                    <input type="number" min="0" name="accumulated_credits" class="form-control"
                           value="<?= e($old['accumulated_credits'] ?? '') ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">Điểm rèn luyện <small class="text-muted">(0–100)</small></label>
                    <input type="number" min="0" max="100" name="conduct_score" class="form-control"
                           value="<?= e($old['conduct_score'] ?? '') ?>">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="<?= e(url('student_profiles', 'index')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
