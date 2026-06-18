<?php
/**
 * @var array $rule Current eligibility rule data
 * @var array $tiers List of scholarship tiers for dropdown
 * @var array $errors List of validation errors (optional)
 */
?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-4">
    <h2>Edit Eligibility Rule</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=eligibility_rules&action=update" id="ruleForm">
        <input type="hidden" name="id" value="<?= $rule['id'] ?>">
        
        <div class="form-group">
            <label for="tier_id">Scholarship Tier <span class="text-danger">*</span></label>
            <select class="form-control" id="tier_id" name="tier_id" required>
                <option value="">-- Select Tier --</option>
                <?php foreach ($tiers as $tier): ?>
                    <option value="<?= $tier['id'] ?>" <?= (isset($_POST['tier_id']) ? $_POST['tier_id'] : $rule['tier_id']) == $tier['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tier['tier_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="rules_json">Rules JSON <span class="text-danger">*</span></label>
            <textarea class="form-control" id="rules_json" name="rules_json" rows="8" required placeholder='{"english_level": "IELTS 6.5", "gpa_min": 3.2}'><?= htmlspecialchars($_POST['rules_json'] ?? $rule['rules_json']) ?></textarea>
            <small class="form-text text-muted">Enter valid JSON format for eligibility rules.</small>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="index.php?controller=eligibility_rules&action=index" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
document.getElementById('ruleForm').addEventListener('submit', function(e) {
    const rulesJsonTextarea = document.getElementById('rules_json');
    const rulesJsonValue = rulesJsonTextarea.value.trim();
    
    if (rulesJsonValue === '') {
        alert('Rules JSON cannot be empty!');
        e.preventDefault();
        return;
    }
    
    // Client-side JSON validation
    try {
        JSON.parse(rulesJsonValue);
    } catch (error) {
        e.preventDefault();
        alert('Invalid JSON format! Please check your JSON syntax.');
        rulesJsonTextarea.focus();
        return;
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
