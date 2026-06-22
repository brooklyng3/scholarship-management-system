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
            <?= csrf_field() ?>

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
            <!-- NEW: Module 3 Eligibility Cutoffs Section -->
            <div class="bg-light p-3 rounded mb-3 border border-dashed">
                <h6 class="text-primary mb-2">🛡 Entry Requirements (Eligibility Thresholds)</h6>
                <div class="row">
                    <div class="col">
                        <label class="form-label small font-weight-bold">Minimum GPA</label>
                        <input type="number" name="min_gpa" class="form-control" 
                               step="0.01" min="0.00" max="4.00" 
                               value="<?= e($old['min_gpa'] ?? '0.00') ?>" 
                               placeholder="e.g., 3.20">
                    </div>
                    <div class="col">
                        <label class="form-label small font-weight-bold">Min Training Score (ĐRL)</label>
                        <input type="number" name="min_training_score" class="form-control" 
                               step="1" min="0" max="100" 
                               value="<?= e($old['min_training_score'] ?? '0') ?>" 
                               placeholder="e.g., 75">
                    </div>
                </div>
                <span class="text-muted extra-small d-block mt-1">Students falling below these criteria values will be automatically blocked upon application submission.</span>
            </div>
            <div class="bg-light p-3 rounded mb-3 border">
                <h6 class="text-success mb-3">📊 Evaluation Scoring Weights (Must equal exactly 100%)</h6>
                
                <div id="criteria-fixed-wrapper">
                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-7">
                            <label class="form-label mb-0 font-weight-bold text-secondary">Điểm Trung bình Tích lũy (GPA)</label>
                        </div>
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="weight_gpa" id="weight_gpa" class="form-control weight-input" value="40.00" step="0.01" min="0" max="100" required oninput="calculateFixedTotal()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-7">
                            <label class="form-label mb-0 font-weight-bold text-secondary">Điểm Rèn luyện (Training Score)</label>
                        </div>
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="weight_training" id="weight_training" class="form-control weight-input" value="30.00" step="0.01" min="0" max="100" required oninput="calculateFixedTotal()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-7">
                            <label class="form-label mb-0 font-weight-bold text-secondary">Chứng chỉ & Hồ sơ Minh chứng (Proof Upload)</label>
                        </div>
                        <div class="col-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="weight_proof" id="weight_proof" class="form-control weight-input" value="30.00" step="0.01" min="0" max="100" required oninput="calculateFixedTotal()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 pt-2 border-top text-end small">
                    <span class="text-muted">Running Balance Total:</span> 
                    <strong id="weight-total" class="text-success">100.00%</strong>
                </div>
            </div>

            <!-- NEW: 2-Tier Automated Architecture Configuration -->
            <div class="bg-light p-3 rounded mb-3 border border-primary">
                <h6 class="text-primary mb-3">🎯 2-Tier Automated Architecture</h6>
                <p class="small text-muted mb-3">Students will be automatically sorted into Excellence Tier or Standard Tier based on their GPA and Training Score.</p>
                
                <!-- Tier 1: Excellence Tier -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <strong>⭐ Tier 1: Excellence Tier</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small">Minimum GPA <span class="text-danger">*</span></label>
                                <input type="number" name="tier1_min_gpa" class="form-control form-control-sm" 
                                       step="0.01" min="0.00" max="4.00" value="3.50" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Min Training Score <span class="text-danger">*</span></label>
                                <input type="number" name="tier1_min_training_score" class="form-control form-control-sm" 
                                       step="1" min="0" max="100" value="85" required>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Reward Amount <span class="text-danger">*</span></label>
                                <input type="number" name="tier1_reward_amount" class="form-control form-control-sm" 
                                       step="0.01" min="0" value="15000000.00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Quota <span class="text-danger">*</span></label>
                                <input type="number" name="tier1_quota" class="form-control form-control-sm" 
                                       step="1" min="1" value="10" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Standard Tier -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <strong>📋 Tier 2: Standard Tier</strong>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2"><em>Inherits program entry requirements (min_gpa and min_training_score above)</em></p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Reward Amount <span class="text-danger">*</span></label>
                                <input type="number" name="tier2_reward_amount" class="form-control form-control-sm" 
                                       step="0.01" min="0" value="5000000.00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Quota <span class="text-danger">*</span></label>
                                <input type="number" name="tier2_quota" class="form-control form-control-sm" 
                                       step="1" min="1" value="50" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function calculateFixedTotal() {
                const gpa = parseFloat(document.getElementById('weight_gpa').value || 0);
                const training = parseFloat(document.getElementById('weight_training').value || 0);
                const proof = parseFloat(document.getElementById('weight_proof').value || 0);
                
                const total = gpa + training + proof;
                const totalEl = document.getElementById('weight-total');
                
                totalEl.textContent = total.toFixed(2) + '%';
                if (Math.abs(total - 100) < 0.001) {
                    totalEl.className = "text-success";
                } else {
                    totalEl.className = "text-danger";
                }
            }
            </script>
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