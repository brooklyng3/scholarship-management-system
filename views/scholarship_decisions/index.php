<?php
/**
 * Scholarship Decisions Index View
 * 
 * Displays list of all scholarship decisions with AJAX delete functionality
 */

require_once __DIR__ . '/../partials/header.php';
?>

<div class="card">
    <h2>Scholarship Decisions</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; color: #155724;">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; color: #721c24;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <a href="index.php?controller=scholarship_decisions&action=create" class="btn">Add New Decision</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Application ID</th>
                <th>Student Name</th>
                <th>Decision Status</th>
                <th>Awarded Amount</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($decisions)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #7f8c8d;">No scholarship decisions found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($decisions as $decision): ?>
                    <tr id="decision-row-<?= $decision['id'] ?>">
                        <td><?= htmlspecialchars($decision['id']) ?></td>
                        <td><?= htmlspecialchars($decision['application_id']) ?></td>
                        <td><?= htmlspecialchars($decision['student_name']) ?></td>
                        <td>
                            <span class="status-badge <?= $decision['decision_status'] === 'awarded' ? 'status-active' : 'status-inactive' ?>">
                                <?= ucfirst(htmlspecialchars($decision['decision_status'])) ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 500;">
                            $<?= number_format($decision['awarded_amount'], 2) ?>
                        </td>
                        <td><?= htmlspecialchars(substr($decision['notes'], 0, 50)) ?><?= strlen($decision['notes']) > 50 ? '...' : '' ?></td>
                        <td class="actions">
                            <a href="index.php?controller=scholarship_decisions&action=edit&id=<?= $decision['id'] ?>" class="btn btn-secondary" style="padding: 0.4rem 0.8rem;">Edit</a>
                            <button onclick="deleteDecision(<?= $decision['id'] ?>)" class="btn btn-danger" style="padding: 0.4rem 0.8rem;">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * Delete scholarship decision using AJAX
 * Removes row from table dynamically without page reload
 * 
 * @param {number} id - The decision ID to delete
 */
function deleteDecision(id) {
    if (!confirm('Are you sure you want to delete this scholarship decision?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('index.php?controller=scholarship_decisions&action=delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.getElementById('decision-row-' + id);
            if (row) {
                row.remove();
            }
            
            // Show success message
            alert('Scholarship decision deleted successfully.');
        } else {
            alert('Failed to delete scholarship decision: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the scholarship decision.');
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
