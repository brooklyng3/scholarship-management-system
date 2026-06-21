<?php
/**
 * @var array $students List of all students
 * @var array $tiers List of all scholarship tiers
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
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student['id']) ?>" 
                                <?= (isset($old['user_id']) && $old['user_id'] == $student['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="tier_id" class="form-label">Scholarship Tier <span class="text-danger">*</span></label>
                <select name="tier_id" id="tier_id" class="form-select" required>
                    <option value="">-- Select Tier --</option>
                    <?php foreach ($tiers as $tier): ?>
                        <option value="<?= htmlspecialchars($tier['id']) ?>" 
                                <?= (isset($old['tier_id']) && $old['tier_id'] == $tier['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tier['tier_name']) ?> - <?= htmlspecialchars($tier['program_title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="proof_document" class="form-label">Supporting Document <span class="text-danger">*</span></label>
                <input type="file" name="proof_document" id="proof_document" class="form-control" accept=".pdf,.jpg,.png" required>
                <div class="form-text">Upload supporting document (PDF, JPG, PNG, max 5MB)</div>
            </div>

            <?php if ($isStudent): ?>
                <input type="hidden" name="status" value="pending">
            <?php else: ?>
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending" <?= (!isset($old['status']) || $old['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="reviewing" <?= (isset($old['status']) && $old['status'] === 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                        <option value="approved" <?= (isset($old['status']) && $old['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= (isset($old['status']) && $old['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Save Application</button>
                <a href="index.php?controller=applications&action=index" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('applicationForm').addEventListener('submit', function(e) {
    const userId = document.getElementById('user_id').value;
    const tierId = document.getElementById('tier_id').value;
    const statusField = document.getElementById('status');
    const fileInput = document.getElementById('proof_document');
    
    if (!userId || !tierId) {
        e.preventDefault();
        alert('Please fill out all required fields.');
        return false;
    }
    
    if (statusField && !statusField.value) {
        e.preventDefault();
        alert('Please select a status.');
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