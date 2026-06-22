<?php
/**
 * Detail view for a single Scholarship Program
 * @var array $program - The scholarship program data
 * @var array $types - Available scholarship types
 * @var array $statuses - Available program statuses
 */
$pageTitle = 'Scholarship Program Details';
require_once __DIR__ . '/../partials/header.php';

// Define badge color mappings for status
$statusBadgeMap = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];

// Dynamically pull the hardcoded scoring weights for this specific program
$db = Database::getConnection();
$stmt = $db->prepare("SELECT criteria_name, weight FROM scoring_criteria WHERE program_id = ? ORDER BY id ASC");
$stmt->execute([$program['id']]);
$scoringCriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏆 Scholarship Program Details</h4>
    <a href="<?= e(url('scholarship_programs', 'index')) ?>" class="btn btn-secondary">← Back to List</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= e($program['title']) ?></h5>
    </div>
    <div class="card-body">
        <?php
        // Core Program Details
        $label = 'Program ID:';
        $value = $program['id'];
        require __DIR__ . '/../partials/components/detail_row.php';

        $label = 'Program Title:';
        $value = $program['title'];
        require __DIR__ . '/../partials/components/detail_row.php';

        // Scholarship Type Badge
        $label = 'Scholarship Type:';
        ob_start();
        ?>
        <span class="badge bg-info">
            <?= e($types[$program['scholarship_type']] ?? $program['scholarship_type']) ?>
        </span>
        <?php
        $value = ob_get_clean();
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // Start Date
        $label = 'Start Date:';
        $value = $program['start_date'] ? date('M d, Y', strtotime($program['start_date'])) : '<span class="text-muted">Not specified</span>';
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // End Date
        $label = 'End Date:';
        $value = $program['end_date'] ? date('M d, Y', strtotime($program['end_date'])) : '<span class="text-muted">Not specified</span>';
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // Status Badge Component
        $label = 'Status:';
        ob_start();
        $status = $program['status'];
        $statusMap = $statusBadgeMap;
        $labelMap = $statuses;
        require __DIR__ . '/../partials/components/status_badge.php';
        $value = ob_get_clean();
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        ?>
    </div>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-primary">🛡️ Entry Requirements (Eligibility Thresholds)</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 p-2 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-secondary">Minimum Required GPA:</span>
                    <span class="badge bg-dark fs-6"><?= number_format($program['min_gpa'] ?? 0.00, 2) ?> / 4.00</span>
                </div>
                <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-secondary">Min Training Score (ĐRL):</span>
                    <span class="badge bg-dark fs-6"><?= e($program['min_training_score'] ?? 0) ?> / 100</span>
                </div>
                <small class="text-muted d-block mt-3 px-1">
                    ℹ️ Students who do not meet both metrics on their profile will be restricted from submitting applications.
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-success">📊 Evaluation Scoring Weights Breakdown</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($scoringCriteria)): ?>
                    <div class="p-4 text-center text-muted">
                        No scoring criteria weights configured for this program.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Evaluation Metric Component</th>
                                    <th class="text-end pe-4" style="width: 150px;">Weight Factor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scoringCriteria as $criteria): ?>
                                <tr>
                                    <td class="ps-3 text-secondary font-weight-bold"><?= e($criteria['criteria_name']) ?></td>
                                    <td class="text-end pe-4 text-primary"><strong><?= number_format($criteria['weight'], 2) ?>%</strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card p-3 shadow-sm bg-light">
    <?php
    // Core administrative execution controls block
    $controller = 'scholarship_programs';
    $id = $program['id'];
    $deleteConfirmMessage = 'Are you sure you want to permanently delete this scholarship program?';
    require __DIR__ . '/../partials/components/admin_actions.php';
    ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>