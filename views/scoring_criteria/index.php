<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>Scoring Criteria</h2>
        <a href="index.php?controller=scoring_criteria&action=create" class="btn">+ Add New Criteria</a>
    </div>

    <?php if (empty($criteria)): ?>
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">
            No scoring criteria found. Click "Add New Criteria" to create one.
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Program</th>
                    <th>Criteria Name</th>
                    <th>Weight (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criteria as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['program_title'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['criteria_name']) ?></td>
                    <td><?= htmlspecialchars(number_format($item['weight'], 2)) ?>%</td>
                    <td>
                        <div class="actions">
                            <a href="index.php?controller=scoring_criteria&action=edit&id=<?= $item['id'] ?>" class="btn">Edit</a>
                            <button class="btn btn-danger" 
                                    onclick="deleteCriteria(<?= $item['id'] ?>, this)">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function deleteCriteria(id, buttonElement) {
    if (!confirm('Are you sure you want to delete this criteria?')) {
        return;
    }
    
    // Disable the button to prevent double-clicks
    buttonElement.disabled = true;
    buttonElement.textContent = 'Deleting...';
    
    fetch(`index.php?controller=scoring_criteria&action=delete&id=${id}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find and remove the table row
            const row = buttonElement.closest('tr');
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            
            setTimeout(() => {
                row.remove();
                
                // Check if table is now empty
                const tbody = document.querySelector('table tbody');
                if (tbody && tbody.children.length === 0) {
                    // Reload page to show "no criteria" message
                    location.reload();
                }
            }, 300);
        } else {
            alert('Error: ' + (data.message || 'Failed to delete criteria'));
            buttonElement.disabled = false;
            buttonElement.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the criteria');
        buttonElement.disabled = false;
        buttonElement.textContent = 'Delete';
    });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
