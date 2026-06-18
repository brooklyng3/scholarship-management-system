<?php
/** @var array $score */
/** @var array $applications */
/** @var array $criteria */
/** @var array $reviewers */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h1>Edit Evaluation Score</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="index.php?controller=evaluation_scores&action=update" id="evaluationForm">
        <input type="hidden" name="id" value="<?= htmlspecialchars($score['id']) ?>">
        
        <div class="form-group">
            <label for="application_id">Application ID:</label>
            <select name="application_id" id="application_id" class="form-control" required>
                <option value="">-- Select Application --</option>
                <?php foreach ($applications as $app): ?>
                    <option value="<?= htmlspecialchars($app['id']) ?>"
                            <?= $app['id'] == $score['application_id'] ? 'selected' : '' ?>>
                        App #<?= htmlspecialchars($app['id']) ?> - <?= htmlspecialchars($app['full_name'] ?? 'Unknown Student') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="criteria_id">Criteria:</label>
            <select name="criteria_id" id="criteria_id" class="form-control" required>
                <option value="">-- Select Criteria --</option>
                <?php foreach ($criteria as $criterion): ?>
                    <option value="<?= htmlspecialchars($criterion['id']) ?>"
                            <?= $criterion['id'] == $score['criteria_id'] ? 'selected' : '' ?>>
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
                    <option value="<?= htmlspecialchars($reviewer['id']) ?>"
                            <?= $reviewer['id'] == $score['reviewer_id'] ? 'selected' : '' ?>>
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
                   value="<?= htmlspecialchars($score['score']) ?>"
                   required>
        </div>
        
        <div class="form-group">
            <label for="comments">Comments:</label>
            <textarea name="comments" 
                      id="comments" 
                      class="form-control" 
                      rows="4"><?= htmlspecialchars($score['comments'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
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
