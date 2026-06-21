<?php
/**
 * @var array $profile
 * @var array $errors
 */
$pageTitle = 'Edit Student Profile';
require_once __DIR__ . '/../partials/header.php';

$currentUser = current_user();
$isStudent = ($currentUser['role'] === 'student');
$isAdmin = in_array($currentUser['role'], ['admin', 'reviewer'], true);
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('student_profiles', 'index')) ?>">Student Profiles</a></li>
        <li class="breadcrumb-item active">Edit: <?= e($profile['student_code']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:640px;">
    <div class="card-header"><strong>✏️ Edit Student Profile</strong></div>
    <div class="card-body">
        <p class="text-muted small">Account: <strong><?= e($profile['email']) ?></strong> (user_id: <?= e($profile['user_id']) ?>)</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('student_profiles', 'update', ['id' => $profile['id']])) ?>" id="editProfileForm">
            <?= csrf_field() ?>
            
            <!-- Student Code (Read-only for students, editable for admins) -->
            <div class="mb-3">
                <label class="form-label">Student Code <span class="text-danger">*</span></label>
                <?php if ($isStudent): ?>
                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light"><code><?= e($profile['student_code']) ?></code></p>
                <?php else: ?>
                    <input type="text" name="student_code" class="form-control" value="<?= e($profile['student_code']) ?>" required>
                <?php endif; ?>
            </div>

            <!-- Full Name (Editable for everyone) -->
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($profile['full_name']) ?>" required>
            </div>

            <!-- Major (Editable for everyone) -->
            <div class="mb-3">
                <label class="form-label">Major</label>
                <input type="text" name="major" class="form-control" value="<?= e($profile['major']) ?>">
            </div>

            <!-- Academic fields (Read-only for students, editable for admins) -->
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">GPA <small class="text-muted">(0.00–4.00)</small></label>
                    <?php if ($isStudent): ?>
                        <p class="form-control-plaintext border rounded px-3 py-2 bg-light">
                            <?php
                            $gpa = (float)$profile['current_gpa'];
                            $gpaBadge = $gpa >= 3.6 ? 'success' : ($gpa >= 3.2 ? 'primary' : ($gpa >= 2.5 ? 'warning text-dark' : 'danger'));
                            ?>
                            <span class="badge bg-<?= $gpaBadge ?>"><?= e($profile['current_gpa']) ?></span>
                        </p>
                    <?php else: ?>
                        <input type="number" step="0.01" min="0" max="4" name="current_gpa" class="form-control"
                               value="<?= e($profile['current_gpa']) ?>">
                    <?php endif; ?>
                </div>

                <div class="col mb-3">
                    <label class="form-label">Accumulated Credits</label>
                    <?php if ($isStudent): ?>
                        <p class="form-control-plaintext border rounded px-3 py-2 bg-light"><?= e($profile['accumulated_credits']) ?></p>
                    <?php else: ?>
                        <input type="number" min="0" name="accumulated_credits" class="form-control"
                               value="<?= e($profile['accumulated_credits']) ?>">
                    <?php endif; ?>
                </div>

                <div class="col mb-3">
                    <label class="form-label">Conduct Score <small class="text-muted">(0–100)</small></label>
                    <?php if ($isStudent): ?>
                        <p class="form-control-plaintext border rounded px-3 py-2 bg-light"><?= e($profile['conduct_score']) ?></p>
                    <?php else: ?>
                        <input type="number" min="0" max="100" name="conduct_score" class="form-control"
                               value="<?= e($profile['conduct_score']) ?>">
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isStudent): ?>
                <div class="alert alert-info small">
                    <strong>Note:</strong> You can only edit your personal information (Full Name, Major). Academic records (GPA, Credits, Conduct Score) can only be updated by administrators.
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="<?= e(url('student_profiles', 'index')) ?>" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
