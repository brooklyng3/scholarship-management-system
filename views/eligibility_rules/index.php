<?php
/**
 * @var array $rules List of eligibility rules with tier information
 */
?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Eligibility Rules</h2>
        <a href="index.php?controller=eligibility_rules&action=create" class="btn btn-primary">Add New Rule</a>
    </div>

    <?php if (empty($rules)): ?>
        <div class="alert alert-info">No eligibility rules found.</div>
    <?php else: ?>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tier Name</th>
                    <th>Rules JSON</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $rule): ?>
                    <tr id="row-<?= $rule['id'] ?>">
                        <td><?= htmlspecialchars($rule['id']) ?></td>
                        <td><?= htmlspecialchars($rule['tier_name']) ?></td>
                        <td>
                            <code><?= htmlspecialchars(substr($rule['rules_json'], 0, 100)) ?><?= strlen($rule['rules_json']) > 100 ? '...' : '' ?></code>
                        </td>
                        <td>
                            <a href="index.php?controller=eligibility_rules&action=edit&id=<?= $rule['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $rule['id'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ruleId = this.getAttribute('data-id');
            
            if (!confirm('Are you sure you want to delete this eligibility rule?')) {
                return;
            }
            
            // AJAX delete request
            fetch('index.php?controller=eligibility_rules&action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(ruleId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    const row = document.getElementById('row-' + ruleId);
                    if (row) {
                        row.remove();
                    }
                    
                    // Show success message
                    alert('Eligibility rule deleted successfully.');
                    
                    // Check if table is empty
                    const tbody = document.querySelector('tbody');
                    if (tbody && tbody.children.length === 0) {
                        location.reload();
                    }
                } else {
                    alert('Failed to delete eligibility rule: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the eligibility rule.');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
