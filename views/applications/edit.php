<?php
/**
 * @var array $application The current application data being edited
 * @var array $students List of all students (for admin choice)
 * @var array $tiers List of all scholarship tiers
 * @var array $errors List of validation errors
 * @var array $documents List of attached files for this application
 */

$pageTitle = 'Edit Application #' . $application['id'];
require_once __DIR__ . '/../partials/header.php';

$currentUser = current_user();
$isStudent = ($currentUser['role'] === 'student');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=applications&action=index">Applications</a></li>
        <li class="breadcrumb-item active">Edit Application #<?= htmlspecialchars($application['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-dark text-white d-flex align-items-center">
        <h5 class="mb-0">✏️ Edit Application #<?= htmlspecialchars($application['id']) ?></h5>
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

        <form method="POST" action="index.php?controller=applications&action=update" id="editApplicationForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($application['id']) ?>">

            <div class="mb-3">
                <label class="form-label">Student</label>
                <?php if ($isStudent): ?>
                    <?php 
                        $currentStudentLabel = $currentUser['full_name'] . ' (' . $currentUser['email'] . ')';
                    ?>
                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light">
                        <strong><?= htmlspecialchars($currentStudentLabel) ?></strong>
                    </p>
                <?php else: ?>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['id']) ?>" 
                                    <?= ($application['user_id'] == $student['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="tier_id" class="form-label">Scholarship Tier <span class="text-danger">*</span></label>
                <select name="tier_id" id="tier_id" class="form-select" required>
                    <?php foreach ($tiers as $tier): ?>
                        <option value="<?= htmlspecialchars($tier['id']) ?>" 
                                <?= ($application['tier_id'] == $tier['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tier['tier_name']) ?> - <?= htmlspecialchars($tier['program_title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Application Status</label>
                <?php if ($isStudent): ?>
                    <p class="form-control-plaintext border rounded px-3 py-2 bg-light">
                        <?php 
                        $statusClass = $application['status'] === 'approved' ? 'success' : 
                                       ($application['status'] === 'rejected' ? 'danger' : 
                                       ($application['status'] === 'reviewing' ? 'info' : 'warning text-dark'));
                        ?>
                        <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($application['status'])) ?></span>
                    </p>
                <?php else: ?>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending" <?= ($application['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="reviewing" <?= ($application['status'] === 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                        <option value="approved" <?= ($application['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($application['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Attached Supporting Documents</label>
                <?php if (!empty($documents)): ?>
                    <div class="list-group mb-2">
                        <?php foreach ($documents as $doc): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                <a href="<?= htmlspecialchars($doc['file_url']) ?>" target="_blank" class="text-decoration-none">
                                    📄 <?= htmlspecialchars(basename($doc['file_url'])) ?>
                                </a>
                                <div class="form-check">
                                    <input class="form-check-input text-danger" type="checkbox" name="delete_documents[]" value="<?= htmlspecialchars($doc['id']) ?>" id="del_doc_<?= $doc['id'] ?>">
                                    <label class="form-check-label text-danger small font-weight-bold" for="del_doc_<?= $doc['id'] ?>">
                                        Remove file
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small border rounded p-2 bg-light">No documents uploaded yet.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="proof_document" class="form-label">Upload Additional / Replacement Document</label>
                <input type="file" name="proof_document" id="proof_document" class="form-control">
                <div class="form-text text-muted small">Max file size: 5MB. Permitted types: PDF, JPG, JPEG, PNG.</div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Update Application</button>
                <a href="index.php?controller=applications&action=index" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>