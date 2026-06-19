<?php
/**
 * Scholarship Decisions Create View
 * 
 * Form to create a new scholarship decision with client-side validation
 * 
 * @var array $applications List of applications for the dropdown
 */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="card">
    <h2>Create Scholarship Decision</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; color: #721c24;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form id="decisionForm" method="POST" action="index.php?controller=scholarship_decisions&action=store">
        <div class="form-group">
            <label for="application_id">Application <span style="color: red;">*</span></label>
            <select name="application_id" id="application_id" required>
                <option value="">-- Select Application --</option>
                <?php foreach ($applications as $app): ?>
                    <option value="<?= $app['id'] ?>">
                        App #<?= $app['id'] ?> - <?= htmlspecialchars($app['student_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="decision_status">Decision Status <span style="color: red;">*</span></label>
            <select name="decision_status" id="decision_status" required>
                <option value="">-- Select Status --</option>
                <option value="awarded">Awarded</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="form-group">
            <label for="awarded_amount">Awarded Amount <span style="color: red;">*</span></label>
            <input type="number" name="awarded_amount" id="awarded_amount" step="0.01" min="0" value="0" required>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" placeholder="Optional notes about this decision..."></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Create Decision</button>
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
