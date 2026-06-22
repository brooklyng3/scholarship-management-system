<?php
/**
 * @var array $errors
 * @var array $old
 * @var array $types
 * @var array $statuses
 */
$pageTitle = 'Add Scholarship Program';
require_once __DIR__ . '/../partials/header.php';
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('scholarship_programs', 'index')) ?>">Scholarship Programs</a></li>
        <li class="breadcrumb-item active">Add New</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:620px;">
    <div class="card-header"><strong>➕ Create Scholarship Program</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('scholarship_programs', 'store')) ?>">
            <?= csrf_field() /* [NEW] */ ?>

            <!-- title -->
            <div class="mb-3">
                <label class="form-label">Program Title (title) <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= e($old['title'] ?? '') ?>"
                       placeholder="e.g., Academic Encouragement Scholarship Fall Semester 2026"
                       required>
            </div>

            <!-- scholarship_type (ENUM) -->
            <div class="mb-3">
                <label class="form-label">Scholarship Type (scholarship_type) <span class="text-danger">*</span></label>
                <select name="scholarship_type" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= ($old['scholarship_type'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- start_date / end_date -->
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">Start Date (start_date)</label>
                    <input type="date" name="start_date" class="form-control" value="<?= e($old['start_date'] ?? '') ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">End Date (end_date)</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($old['end_date'] ?? '') ?>">
                </div>
            </div>

            <!-- status (ENUM) -->
            <div class="mb-3">
                <label class="form-label">Status (status)</label>
                <select name="status" class="form-select">
                    <?php foreach ($statuses as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= ($old['status'] ?? 'draft') === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?= e(url('scholarship_programs', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>