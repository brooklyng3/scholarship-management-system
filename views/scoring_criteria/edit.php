<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
/**
 * @var array $criteria
 * @var array $programs
 * @var array $errors
 * @var array $data
 */
?>

<div class="card">
    <h2>Edit Scoring Criteria</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-list">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=scoring_criteria&action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($criteria['id']) ?>">

        <div class="form-group">
            <label for="program_id">Scholarship Program *</label>
            <select id="program_id" name="program_id" required>
                <option value="">-- Select Program --</option>
                <?php foreach ($programs as $program): ?>
                    <option value="<?= htmlspecialchars($program['id']) ?>" 
                            <?= ($data['program_id'] ?? $criteria['program_id']) == $program['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($program['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="criteria_name">Criteria Name *</label>
            <input type="text" 
                   id="criteria_name" 
                   name="criteria_name" 
                   value="<?= htmlspecialchars($data['criteria_name'] ?? $criteria['criteria_name']) ?>" 
                   placeholder="e.g., Academic Performance, Research Contribution"
                   required>
        </div>

        <div class="form-group">
            <label for="weight">Weight (%) *</label>
            <input type="number" 
                   id="weight" 
                   name="weight" 
                   value="<?= htmlspecialchars($data['weight'] ?? $criteria['weight']) ?>" 
                   step="0.01" 
                   min="0.01" 
                   max="100.00"
                   placeholder="Enter value between 0.01 and 100.00"
                   required>
            <small style="color: #7f8c8d; display: block; margin-top: 0.25rem;">
                Must be between 0.01 and 100.00
            </small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Update Criteria</button>
            <a href="index.php?controller=scoring_criteria&action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const weightInput = document.getElementById('weight').value;
    if (weightInput < 0.01 || weightInput > 100) {
        e.preventDefault();
        alert("Client-side validation failed: Weight must be between 0.01 and 100.");
    }
});
</script>