<?php
/** Template: staff_profiles/create — biến: $errors, $old, $availableUsers */
$pageTitle = 'Add Staff Profile';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('staff_profiles', 'index')) ?>">Staff Profiles</a></li>
        <li class="breadcrumb-item active">Add New</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:560px;">
    <div class="card-header"><strong>➕ Create Staff Profile</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (empty($availableUsers)): ?>
            <div class="alert alert-warning">All admin/reviewer accounts already have staff profiles. <a href="<?= e(url('users', 'create')) ?>">Create a new account</a> first.</div>
        <?php else: ?>
        <form method="POST" action="<?= e(url('staff_profiles', 'store')) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">Admin / Reviewer Account <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Select Account --</option>
                    <?php foreach ($availableUsers as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($old['user_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>>
                            [<?= e(ucfirst($u['role'])) ?>] <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Staff Code <span class="text-danger">*</span></label>
                <input type="text" name="staff_code" class="form-control" value="<?= e($old['staff_code'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control"
                       placeholder="E.g.: Training Dept., Student Affairs"
                       value="<?= e($old['department'] ?? '') ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?= e(url('staff_profiles', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
