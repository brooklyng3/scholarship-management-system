<?php
/**
 * @var array $application
 * @var array $documents
 * @var array|false $existingReview
 */
$pageTitle = 'Application Details';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="mb-3">
    <a href="<?= e(url('applications', 'index')) ?>" class="btn btn-secondary">← Back to Applications</a>
</div>

<h4 class="mb-4">📄 Application Summary Records (ID #<?= e($application['id']) ?>)</h4>

<div class="row">
    <!-- Profile & Metrics Breakdown Summary Card Info -->
    <div class="col-md-6">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Student Profile Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width: 40%;">Full Name:</th><td><strong><?= e($application['student_name']) ?></strong></td></tr>
                    <tr><th>Email Address:</th><td><?= e($application['student_email']) ?></td></tr>
                    <tr><th>Current GPA:</th><td><span class="badge bg-primary"><?= e($application['gpa'] ?? 'N/A') ?></span></td></tr>
                    <tr><th>Training Score (ĐRL):</th><td><strong><?= e($application['training_score'] ?? '0') ?></strong> / 100</td></tr>
                    <tr><th>Academic Major:</th><td><?= e($application['major'] ?? 'N/A') ?></td></tr>
                    <tr><th>Submission Date:</th><td><small class="text-muted"><?= e($application['applied_date']) ?></small></td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Program & Lifecycle Tracking Status</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width: 40%;">Scholarship Program:</th><td><?= e($application['program_title']) ?></td></tr>
                    <tr><th>Configured Tier Class:</th><td><span class="text-muted"><?= e($application['tier_name']) ?></span></td></tr>
                    <tr><th>Current Decision:</th><td>
                        <?php
                        $badge = 'bg-warning text-dark';
                        if ($application['status'] === 'approved') $badge = 'bg-success';
                        if ($application['status'] === 'rejected') $badge = 'bg-danger';
                        if ($application['status'] === 'reviewing') $badge = 'bg-info';
                        ?>
                        <span class="badge <?= $badge ?>"><?= e(ucfirst($application['status'])) ?></span>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Side: Official Committee Assessment Grades & Feedback Notes -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">🏆 Official Academic Review Assessment</h6>
            </div>
            <div class="card-body">
                <?php if ($existingReview): ?>
                    <div class="mb-4 text-center p-3 bg-light rounded border">
                        <span class="text-muted d-block small text-uppercase font-weight-bold">Total Weighted Evaluation Score</span>
                        <?php if (isset($existingReview['id']) && $existingReview['id'] === 0): ?>
                            <h2 class="text-secondary font-weight-bold my-1">N/A</h2>
                        <?php else: ?>
                            <h2 class="text-success font-weight-bold my-1"><?= number_format($existingReview['score'], 2) ?> <span class="fs-5 text-muted">/ 100</span></h2>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold d-block">Reviewer Feedback & Comments</label>
                        <div class="p-3 bg-white border rounded text-secondary" style="white-space: pre-wrap; min-height: 140px; font-size: 0.95rem;"><?= e($existingReview['comment']) ?></div>
                    </div>
                    
                    <div class="text-end text-muted small border-top pt-2">
                        Reviewed by: <strong><?= e($existingReview['reviewer_name']) ?></strong> on <?= date('M d, Y', strtotime($existingReview['created_at'])) ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 my-4 text-muted">
                        <h5 class="text-secondary">🔄 Under Evaluation</h5>
                        <p class="small mb-0 px-3">Your credentials and uploads are currently being processed by the evaluation board. Grades and quantitative notes will post here instantly once finalized.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>