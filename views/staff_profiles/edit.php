<?php
/**
 * @var array $profile
 * @var array $errors
 */
$pageTitle = 'Edit Staff Profile';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('staff_profiles', 'index')) ?>">Staff Profiles</a></li>
        <li class="breadcrumb-item active">Edit: <?= e($profile['staff_code']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:560px;">
    <div class="card-header"><strong>✏️ Edit Staff Profile</strong></div>
    <div class="card-body">
        <p class="text-muted small">
            Account: <strong><?= e($profile['user_full_name']) ?></strong>
            &mdash; <?= e($profile['email']) ?>
            &mdash; <span class="badge bg-danger"><?= e(ucfirst($profile['role'])) ?></span>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('staff_profiles', 'update', ['id' => $profile['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">Staff Code <span class="text-danger">*</span></label>
                <input type="text" name="staff_code" class="form-control" value="<?= e($profile['staff_code']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control"
                       placeholder="E.g.: Training Dept., Student Affairs"
                       value="<?= e($profile['department']) ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?= e(url('staff_profiles', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
