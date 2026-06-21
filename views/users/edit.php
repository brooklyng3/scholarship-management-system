<?php
/**
 * Template: users/edit
 * @var array $user
 * @var array $errors
 */
$pageTitle = 'Edit User';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('users', 'index')) ?>">Users</a></li>
        <li class="breadcrumb-item active">Edit #<?= e($user['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:600px;">
    <div class="card-header"><strong>✏️ Edit User</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('users', 'update', ['id' => $user['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password <small class="text-muted">(leave blank to keep unchanged)</small></label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters">
            </div>
            <div class="mb-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select">
                    <option value="student"  <?= $user['role'] === 'student'  ? 'selected' : '' ?>>Student</option>
                    <option value="reviewer" <?= $user['role'] === 'reviewer' ? 'selected' : '' ?>>Reviewer</option>
                    <option value="admin"    <?= $user['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?= e(url('users', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
