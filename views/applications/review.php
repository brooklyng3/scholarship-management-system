<?php
/**
 * @var array $application
 * @var array $documents
 * @var array|false $existingReview
 * @var array $currentUser
 * @var array $errors
 */
$pageTitle = 'Review Application';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="mb-3">
    <a href="<?= e(url('applications', 'index')) ?>" class="btn btn-secondary">← Back to Applications</a>
</div>

<h4 class="mb-4">📋 Review Application #<?= e($application['id']) ?></h4>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Left Side: Student Information & Documents -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Student Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 40%;">Student Name:</th>
                        <td><strong><?= e($application['student_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= e($application['student_email']) ?></td>
                    </tr>
                    <tr>
                        <th>GPA:</th>
                        <td><?= e($application['gpa'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Year Level:</th>
                        <td><?= e($application['year_level'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Major:</th>
                        <td><?= e($application['major'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Applied Date:</th>
                        <td><?= e($application['applied_date']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Scholarship Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 40%;">Program:</th>
                        <td><strong><?= e($application['program_title']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Tier:</th>
                        <td><?= e($application['tier_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Current Status:</th>
                        <td>
                            <?php
                            $statusClass = 'status-draft';
                            switch ($application['status']) {
                                case 'pending':
                                    $statusClass = 'status-draft';
                                    break;
                                case 'reviewing':
                                    $statusClass = 'status-draft';
                                    break;
                                case 'approved':
                                    $statusClass = 'status-active';
                                    break;
                                case 'rejected':
                                    $statusClass = 'status-closed';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= e(ucfirst($application['status'])) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Supporting Documents</h5>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <p class="text-muted mb-0">No documents uploaded.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= e(ucfirst($doc['document_type'])) ?></strong>
                                        <br>
                                        <small class="text-muted">Uploaded: <?= e($doc['uploaded_at']) ?></small>
                                    </div>
                                    <a href="<?= e($doc['file_url']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        📄 View/Download
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($existingReview): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Previous Review</h5>
                </div>
                <div class="card-body">
                    <p><strong>Reviewer:</strong> <?= e($existingReview['reviewer_name']) ?></p>
                    <p><strong>Score:</strong> <?= e($existingReview['score']) ?></p>
                    <p><strong>Comment:</strong></p>
                    <p class="border p-3 bg-light"><?= e($existingReview['comment']) ?></p>
                    <p class="text-muted mb-0"><small>Reviewed on: <?= e($existingReview['created_at']) ?></small></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Side: Evaluation Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Evaluation Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= e(url('applications', 'submitReview')) ?>" id="reviewForm">
                    <input type="hidden" name="application_id" value="<?= e($application['id']) ?>">

                    <div class="mb-3">
                        <label for="score" class="form-label">Score (0-100) <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            class="form-control" 
                            id="score" 
                            name="score" 
                            min="0" 
                            max="100" 
                            step="0.01"
                            value="<?= e($existingReview['score'] ?? '') ?>"
                            required>
                        <small class="form-text text-muted">Enter a numeric score between 0 and 100.</small>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Review Comments <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="comment" 
                            name="comment" 
                            rows="8" 
                            required
                            placeholder="Provide detailed feedback on the application..."><?= e($existingReview['comment'] ?? '') ?></textarea>
                        <small class="form-text text-muted">Provide qualitative feedback and justification for your evaluation.</small>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Application Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?= ($application['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="reviewing" <?= ($application['status'] === 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                            <option value="approved" <?= ($application['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= ($application['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                        <small class="form-text text-muted">Update the application status based on your review.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-fill">
                            ✓ Submit Evaluation
                        </button>
                        <a href="<?= e(url('applications', 'index')) ?>" class="btn btn-secondary flex-fill">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4 border-warning">
            <div class="card-body">
                <h6 class="card-title">⚠️ Review Guidelines</h6>
                <ul class="mb-0 small">
                    <li>Review all supporting documents thoroughly</li>
                    <li>Ensure the score reflects the application quality</li>
                    <li>Provide constructive and specific feedback</li>
                    <li>Update the status appropriately based on your evaluation</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    const score = parseFloat(document.getElementById('score').value);
    const comment = document.getElementById('comment').value.trim();
    
    if (isNaN(score) || score < 0 || score > 100) {
        e.preventDefault();
        alert('Please enter a valid score between 0 and 100.');
        return false;
    }
    
    if (comment.length < 10) {
        e.preventDefault();
        alert('Please provide a meaningful comment (at least 10 characters).');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
