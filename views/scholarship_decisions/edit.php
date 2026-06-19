<?php
/**
 * Scholarship Decisions Edit View
 * 
 * Form to edit an existing scholarship decision with client-side validation
 * 
 * @var array $decision The current decision data to edit
 * @var array $applications List of applications for the dropdown
 */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="card">
    <h2>Edit Scholarship Decision</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; color: #721c24;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form id="decisionForm" method="POST" action="index.php?controller=scholarship_decisions&action=update">
        <input type="hidden" name="id" value="<?= $decision['id'] ?>">
        
        <div class="form-group">
            <label for="application_id">Application <span style="color: red;">*</span></label>
            <select name="application_id" id="application_id" required>
                <option value="">-- Select Application --</option>
                <?php foreach ($applications as $app): ?>
                    <option value="<?= $app['id'] ?>" <?= $app['id'] == $decision['application_id'] ? 'selected' : '' ?>>
                        App #<?= $app['id'] ?> - <?= htmlspecialchars($app['student_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="decision_status">Decision Status <span style="color: red;">*</span></label>
            <select name="decision_status" id="decision_status" required>
                <option value="">-- Select Status --</option>
                <option value="awarded" <?= $decision['decision_status'] === 'awarded' ? 'selected' : '' ?>>Awarded</option>
                <option value="rejected" <?= $decision['decision_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div class="form-group">
            <label for="awarded_amount">Awarded Amount <span style="color: red;">*</span></label>
            <input type="number" name="awarded_amount" id="awarded_amount" step="0.01" min="0" value="<?= htmlspecialchars($decision['awarded_amount']) ?>" required>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" placeholder="Optional notes about this decision..."><?= htmlspecialchars($decision['notes']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Update Decision</button>
            <a href="index.php?controller=scholarship_decisions&action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
/**
 * Client-Side Validation Script
 * 
 * - Enforces business rules: awarded status requires amount > 0, rejected requires amount = 0
 * - Auto-adjusts amount field based on status selection
 * - Validates form on submit
 */

const statusSelect = document.getElementById('decision_status');
const amountInput = document.getElementById('awarded_amount');
const form = document.getElementById('decisionForm');

// Initialize readonly state on page load
if (statusSelect.value === 'rejected') {
    amountInput.readOnly = true;
    amountInput.style.backgroundColor = '#e9ecef';
}

// Handle status change - auto-adjust amount field
statusSelect.addEventListener('change', function() {
    if (this.value === 'rejected') {
        amountInput.value = '0';
        amountInput.readOnly = true;
        amountInput.style.backgroundColor = '#e9ecef';
    } else {
        amountInput.readOnly = false;
        amountInput.style.backgroundColor = '';
    }
});

// Validate form on submit
form.addEventListener('submit', function(e) {
    const status = statusSelect.value;
    const amount = parseFloat(amountInput.value);

    if (status === 'awarded' && amount <= 0) {
        e.preventDefault();
        alert('Error: Awarded decisions must have an amount greater than 0.');
        amountInput.focus();
        return false;
    }

    if (status === 'rejected' && amount !== 0) {
        e.preventDefault();
        alert('Error: Rejected decisions must have an awarded amount of exactly 0.');
        amountInput.focus();
        return false;
    }

    return true;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
