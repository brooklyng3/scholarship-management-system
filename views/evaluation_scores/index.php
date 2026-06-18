<?php
/** @var array $scores */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h1>Evaluation Scores</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="index.php?controller=evaluation_scores&action=create" class="btn btn-primary">
            Add New Evaluation Score
        </a>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Application ID</th>
                <th>Criteria Name</th>
                <th>Reviewer Name</th>
                <th>Score</th>
                <th>Comments</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($scores)): ?>
                <tr>
                    <td colspan="7" class="text-center">No evaluation scores found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($scores as $score): ?>
                    <tr id="score-row-<?= $score['id'] ?>">
                        <td><?= htmlspecialchars($score['id']) ?></td>
                        <td><?= htmlspecialchars($score['application_id']) ?></td>
                        <td><?= htmlspecialchars($score['criteria_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($score['reviewer_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(number_format($score['score'], 2)) ?></td>
                        <td><?= htmlspecialchars($score['comments'] ?? '') ?></td>
                        <td>
                            <a href="index.php?controller=evaluation_scores&action=edit&id=<?= $score['id'] ?>" 
                               class="btn btn-sm btn-warning">Edit</a>
                            <button onclick="deleteScore(<?= $score['id'] ?>)" 
                                    class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function deleteScore(id) {
    if (!confirm('Are you sure you want to delete this evaluation score?')) {
        return;
    }
    
    fetch('index.php?controller=evaluation_scores&action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.getElementById('score-row-' + id);
            if (row) {
                row.remove();
            }
            alert('Evaluation score deleted successfully.');
        } else {
            alert('Failed to delete evaluation score: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the evaluation score.');
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
