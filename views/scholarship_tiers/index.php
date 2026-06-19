<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>Scholarship Tiers</h2>
        <a href="index.php?controller=scholarship_tiers&action=create" class="btn">+ Add New Tier</a>
    </div>

    <?php if (empty($tiers)): ?>
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">
            No scholarship tiers found. Click "Add New Tier" to create one.
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Program</th>
                    <th>Tier Name</th>
                    <th>Reward Amount</th>
                    <th>Quota</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tiers as $tier): ?>
                <tr>
                    <td><?= htmlspecialchars($tier['id']) ?></td>
                    <td><?= htmlspecialchars($tier['program_title']) ?></td>
                    <td><?= htmlspecialchars($tier['tier_name']) ?></td>
                    <td><?= htmlspecialchars(number_format($tier['reward_amount'], 2)) ?> VND</td>
                    <td><?= htmlspecialchars($tier['quota']) ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?controller=scholarship_tiers&action=edit&id=<?= $tier['id'] ?>" class="btn">Edit</a>
                            <button class="btn btn-danger" 
                                    onclick="deleteTier(<?= $tier['id'] ?>, this)">
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
/**
 * Delete a scholarship tier using AJAX
 * @param {number} tierId - The ID of the tier to delete
 * @param {HTMLElement} buttonElement - The delete button that was clicked
 */
function deleteTier(tierId, buttonElement) {
    if (!confirm('Are you sure you want to delete this tier?')) {
        return;
    }

    // Disable button during request
    buttonElement.disabled = true;
    buttonElement.textContent = 'Deleting...';

    fetch('index.php?controller=scholarship_tiers&action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(tierId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from DOM
            const row = buttonElement.closest('tr');
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
            
            alert('Tier deleted successfully');
        } else {
            alert('Error: ' + (data.message || 'Failed to delete tier'));
            buttonElement.disabled = false;
            buttonElement.textContent = 'Delete';
        }
    })
    .catch(error => {
        alert('Network error: ' + error.message);
        buttonElement.disabled = false;
        buttonElement.textContent = 'Delete';
    });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
