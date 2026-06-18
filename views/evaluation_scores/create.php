<?php
/** @var array $applications */
/** @var array $criteria */
/** @var array $reviewers */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h1>Add New Evaluation Score</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="index.php?controller=evaluation_scores&action=store" id="evaluationForm">
        <div class="form-group">
            <label for="application_id">Application ID:</label>
            <select name="application_id" id="application_id" class="form-control" required>
                <option value="">-- Select Application --</option>
                <?php foreach ($applications as $app): ?>
                    <option value="<?= htmlspecialchars($app['id']) ?>">
                        Application #<?= htmlspecialchars($app['application_id'] ?? $app['id']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="criteria_id">Criteria:</label>
            <select name="criteria_id" id="criteria_id" class="form-control" required>
                <option value="">-- Select Criteria --</option>
                <?php foreach ($criteria as $criterion): ?>
                    <option value="<?= htmlspecialchars($criterion['id']) ?>">
                        <?= htmlspecialchars($criterion['criteria_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="reviewer_id">Reviewer:</label>
            <select name="reviewer_id" id="reviewer_id" class="form-control" required>
                <option value="">-- Select Reviewer --</option>
                <?php foreach ($reviewers as $reviewer): ?>
                    <option value="<?= htmlspecialchars($reviewer['id']) ?>">
                        <?= htmlspecialchars($reviewer['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="score">Score (0.00 - 10.00):</label>
            <input type="number" 
                   name="score" 
                   id="score" 
                   class="form-control" 
                   step="0.01" 
                   min="0" 
                   max="10" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="comments">Comments:</label>
            <textarea name="comments" 
                      id="comments" 
                      class="form-control" 
                      rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php?controller=evaluation_scores&action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    const scoreInput = document.getElementById('score');
    const score = parseFloat(scoreInput.value);
    
    if (isNaN(score) || score < 0 || score > 10) {
        e.preventDefault();
        alert('Score must be between 0.00 and 10.00');
        scoreInput.focus();
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
