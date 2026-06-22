<?php
/**
 * @var array $program
 * @var array $errors
 * @var array $types
 * @var array $statuses
 */
$pageTitle = 'Edit Scholarship Program';
require_once __DIR__ . '/../partials/header.php';

// Fetch the existing three hardcoded weights for this program to pre-populate the inputs
$db = Database::getConnection();
$stmt = $db->prepare("SELECT criteria_name, weight FROM scoring_criteria WHERE program_id = ?");
$stmt->execute([$program['id']]);
$existingCriteria = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fall back to defaults if the rows do not exist yet for this older record
$weightGpa      = isset($existingCriteria['Điểm Trung bình Tích lũy (GPA)']) ? (float)$existingCriteria['Điểm Trung bình Tích lũy (GPA)'] : 40.00;
$weightTraining = isset($existingCriteria['Điểm Rèn luyện']) ? (float)$existingCriteria['Điểm Rèn luyện'] : 30.00;
$weightProof    = isset($existingCriteria['Thành tích Ngoại khóa / Minh chứng (Upload)']) ? (float)$existingCriteria['Thành tích Ngoại khóa / Minh chứng (Upload)'] : 30.00;
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('scholarship_programs', 'index')) ?>">Scholarship Programs</a></li>
        <li class="breadcrumb-item active">Edit #<?= e($program['id']) ?></li>
    </ol>
</nav>

<div class="card shadow-sm" style="max-width:620px;">
    <div class="card-header"><strong>✏️ Edit Scholarship Program</strong></div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('scholarship_programs', 'update', ['id' => $program['id']])) ?>">
            <?= csrf_field() /* [NEW] */ ?>

            <div class="mb-3">
                <label class="form-label">Program Title (title) <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= e($program['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Scholarship Type (scholarship_type) <span class="text-danger">*</span></label>
                <select name="scholarship_type" class="form-select" required>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $program['scholarship_type'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">Start Date (start_date)</label>
                    <input type="date" name="start_date" class="form-control" value="<?= e($program['start_date']) ?>">
                </div>
                <div class="col mb-3">
                    <label class="form-label">End Date (end_date)</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($program['end_date']) ?>">
                </div>
            </div>
            <p class="text-muted small mt-n2 mb-3">⚠ end_date must be after or equal to start_date.</p>

            <div class="bg-light p-3 rounded mb-3 border">
                <h6 class="text-primary mb-2">🛡️ Entry Requirements (Eligibility Thresholds)</h6>
                <div class="row">
                    <div class="col">
                        <label class="form-label small font-weight-bold">Minimum GPA</label>
                        <input type="number" name="min_gpa" class="form-control" 
                               step="0.01" min="0.00" max="4.00" 
                               value="<?= e(number_format($program['min_gpa'] ?? 0.00, 2)) ?>">
                    </div>
                    <div class="col">
                        <label class="form-label small font-weight-bold">Min Training Score (ĐRL)</label>
                        <input type="number" name="min_training_score" class="form-control" 
                               step="1" min="0" max="100" 
                               value="<?= e($program['min_training_score'] ?? 0) ?>">
                    </div>
                </div>
            </div>

            <div class="bg-light p-3 rounded mb-3 border">
                <h6 class="text-success mb-3">📊 Evaluation Scoring Weights (Must equal exactly 100%)</h6>
                
                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-7">
                        <label class="form-label mb-0 small text-secondary font-weight-bold">Điểm Trung bình Tích lũy (GPA)</label>
                    </div>
                    <div class="col-5">
                        <div class="input-group input-group-sm">
                            <input type="number" name="weight_gpa" id="weight_gpa" class="form-control weight-input" 
                                   value="<?= e(number_format($weightGpa, 2)) ?>" step="0.01" min="0" max="100" required oninput="calculateEditTotal()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-7">
                        <label class="form-label mb-0 small text-secondary font-weight-bold">Điểm Rèn luyện (Training Score)</label>
                    </div>
                    <div class="col-5">
                        <div class="input-group input-group-sm">
                            <input type="number" name="weight_training" id="weight_training" class="form-control weight-input" 
                                   value="<?= e(number_format($weightTraining, 2)) ?>" step="0.01" min="0" max="100" required oninput="calculateEditTotal()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-7">
                        <label class="form-label mb-0 small text-secondary font-weight-bold">Chứng chỉ & Minh chứng (Proof Upload)</label>
                    </div>
                    <div class="col-5">
                        <div class="input-group input-group-sm">
                            <input type="number" name="weight_proof" id="weight_proof" class="form-control weight-input" 
                                   value="<?= e(number_format($weightProof, 2)) ?>" step="0.01" min="0" max="100" required oninput="calculateEditTotal()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top text-end small">
                    <span class="text-muted">Running Balance Total:</span> 
                    <strong id="edit-weight-total" class="text-success">100.00%</strong>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status (status)</label>
                <select name="status" class="form-select">
                    <?php foreach ($statuses as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $program['status'] === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Program</button>
                <a href="<?= e(url('scholarship_programs', 'index')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function calculateEditTotal() {
    const gpa = parseFloat(document.getElementById('weight_gpa').value || 0);
    const training = parseFloat(document.getElementById('weight_training').value || 0);
    const proof = parseFloat(document.getElementById('weight_proof').value || 0);
    
    const total = gpa + training + proof;
    const totalEl = document.getElementById('edit-weight-total');
    
    totalEl.textContent = total.toFixed(2) + '%';
    if (Math.abs(total - 100) < 0.001) {
        totalEl.className = "text-success";
    } else {
        totalEl.className = "text-danger";
    }
}
// Run the initialization check on load
document.addEventListener("DOMContentLoaded", calculateEditTotal);
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>