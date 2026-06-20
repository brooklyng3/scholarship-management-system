<?php
/** Template: student_profiles/edit — biến: $profile, $errors */
$pageTitle = 'Sửa Hồ sơ Sinh viên';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('student_profiles', 'index')) ?>">Hồ sơ Sinh viên</a></li>
        <li class="breadcrumb-item active">Sửa: <?= e($profile['student_code']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:640px;">
    <div class="card-header"><strong>✏️ Sửa Hồ sơ Sinh viên</strong></div>
    <div class="card-body">
        <p class="text-muted small">Tài khoản: <strong><?= e($profile['email']) ?></strong> (user_id: <?= e($profile['user_id']) ?>)</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('student_profiles', 'update', ['id' => $profile['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">Mã sinh viên <span class="text-danger">*</span></label>
                <input type="text" name="student_code" class="form-control" value="<?= e($profile['student_code']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($profile['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngành học</label>
                <input type="text" name="major" class="form-control" value="<?= e($profile['major']) ?>">
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">GPA <small class="text-muted">(0.00–4.00)</small></label>
                    <input type="number" step="0.01" min="0" max="4" name="current_gpa" class="form-control"
                           value="<?= e($profile['current_gpa']) ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">Tín chỉ tích lũy</label>
                    <input type="number" min="0" name="accumulated_credits" class="form-control"
                           value="<?= e($profile['accumulated_credits']) ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">Điểm rèn luyện <small class="text-muted">(0–100)</small></label>
                    <input type="number" min="0" max="100" name="conduct_score" class="form-control"
                           value="<?= e($profile['conduct_score']) ?>">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="<?= e(url('student_profiles', 'index')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
