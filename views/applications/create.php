<?php
/**
 * @var array $students List of all students
 * @var array $programs List of all scholarship programs
 * @var array $errors List of validation errors
 * @var array $old Previous form data
 */

$pageTitle = 'Create Application';
require_once __DIR__ . '/../partials/header.php';

$currentUser = current_user();
$isStudent = ($currentUser['role'] === 'student');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=applications&action=index">Applications</a></li>
        <li class="breadcrumb-item active">Create New</li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">➕ Create New Application</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=applications&action=store" id="applicationForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="user_id" class="form-label">Student <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select" required <?= $isStudent ? 'disabled' : '' ?>>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student['id']) ?>" 
                                <?= (isset($old['user_id']) && $old['user_id'] == $student['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isStudent): ?>
                    <input type="hidden" name="user_id" value="<?= $currentUser['id'] ?>">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="program_id" class="form-label">Scholarship Program <span class="text-danger">*</span></label>
                <select name="program_id" id="program_id" class="form-select" required>
                    <option value="">-- Select Program --</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= htmlspecialchars($program['id']) ?>" 
                                <?= (isset($old['program_id']) && $old['program_id'] == $program['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($program['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    <span class="badge bg-info text-dark">🎯 Auto-Sorting Enabled</span>
                    You will be automatically assigned to Excellence Tier or Standard Tier based on your GPA and Training Score.
                </div>
            </div>

            <div class="mb-3">
                <label for="proof_document" class="form-label">Supporting Document <span class="text-danger">*</span></label>
                <input type="file" name="proof_document" id="proof_document" class="form-control" accept=".pdf,.jpg,.png" required>
                <div class="form-text">Upload supporting document (PDF, JPG, PNG, max 5MB)</div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="index.php?controller=applications&action=index" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('applicationForm').addEventListener('submit', function(e) {
    const userId = document.getElementById('user_id').value;
    const programId = document.getElementById('program_id').value;
    const fileInput = document.getElementById('proof_document');
    
    if (!userId || !programId) {
        e.preventDefault();
        alert('Please fill out all required fields.');
        return false;
    }
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Please upload a supporting document.');
        return false;
    }
    
    const file = fileInput.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (file.size > maxSize) {
        e.preventDefault();
        alert('File size must be less than 5MB.');
        return false;
    }
    
    if (!allowedExtensions.includes(fileExtension)) {
        e.preventDefault();
        alert('Only PDF, JPG, and PNG files are allowed.');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>