<?php
/**
 * @var array $record
 * @var array $errors
 * @var array $users
 * @var array $types
 */
$pageTitle = 'Edit Violation';
require_once __DIR__ . '/../partials/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('violation_records', 'index')) ?>">Violations</a></li>
        <li class="breadcrumb-item active">Edit #<?= e($record['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:600px;">
    <div class="card-header"><strong>✏️ Edit Violation Record</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('violation_records', 'update', ['id' => $record['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>
            <div class="mb-3">
                <label class="form-label">User <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $record['user_id'] == $u['id'] ? 'selected' : '' ?>>
                            [<?= e(ucfirst($u['role'])) ?>] <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Violation Type <span class="text-danger">*</span></label>
                <select name="violation_type" class="form-select" required>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $record['violation_type'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($record['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Recorded Date</label>
                <input type="date" name="recorded_date" class="form-control" value="<?= e($record['recorded_date']) ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?= e(url('violation_records', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>