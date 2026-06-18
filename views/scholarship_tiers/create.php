<?php 
/**
 * @var array $programs List of available scholarship programs
 * @var array $data Form data (populated on validation errors)
 * @var array $errors List of validation error messages
 */
require __DIR__ . '/../partials/header.php'; 
?>

<div class="card">
    <h2>Add New Scholarship Tier</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-list">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=scholarship_tiers&action=store">
        <div class="form-group">
            <label for="program_id">Scholarship Program *</label>
            <select id="program_id" name="program_id" required>
                <option value="">-- Select Program --</option>
                <?php foreach ($programs as $program): ?>
                    <option value="<?= htmlspecialchars($program['id']) ?>" 
                            <?= ($data['program_id'] ?? 0) == $program['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($program['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tier_name">Tier Name *</label>
            <input type="text" 
                   id="tier_name" 
                   name="tier_name" 
                   placeholder="e.g., Xuất sắc hạng 1"
                   value="<?= htmlspecialchars($data['tier_name'] ?? '') ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="reward_amount">Reward Amount (VND) *</label>
            <input type="number" 
                   id="reward_amount" 
                   name="reward_amount" 
                   step="0.01"
                   min="0.01"
                   placeholder="e.g., 5000000"
                   value="<?= htmlspecialchars($data['reward_amount'] ?? '') ?>" 
                   required>
            <small style="color: #7f8c8d;">Must be greater than 0</small>
        </div>

        <div class="form-group">
            <label for="quota">Quota *</label>
            <input type="number" 
                   id="quota" 
                   name="quota" 
                   min="1"
                   placeholder="e.g., 10"
                   value="<?= htmlspecialchars($data['quota'] ?? '') ?>" 
                   required>
            <small style="color: #7f8c8d;">Must be at least 1</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Save Tier</button>
            <a href="index.php?controller=scholarship_tiers&action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
/**
 * Client-side form validation for scholarship tier creation
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const rewardAmount = parseFloat(document.getElementById('reward_amount').value);
        const quota = parseInt(document.getElementById('quota').value);
        
        // Validate reward amount
        if (isNaN(rewardAmount) || rewardAmount <= 0) {
            e.preventDefault();
            alert('Error: Reward amount must be greater than 0');
            document.getElementById('reward_amount').focus();
            return false;
        }
        
        // Validate quota
        if (isNaN(quota) || quota < 1) {
            e.preventDefault();
            alert('Error: Quota must be at least 1');
            document.getElementById('quota').focus();
            return false;
        }
        
        return true;
    });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
