<?php
/**
 * @var array $applications
 * @var array $currentUser
 */
$pageTitle = 'Scholarship Applications';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">📝 Scholarship Applications</h4>
    
    <?php if (isset($currentUser) && $currentUser['role'] !== 'reviewer'): ?>
        <a href="index.php?controller=applications&action=create" class="btn btn-primary">+ New Application</a>
    <?php endif; ?>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Scholarship Program</th> <th>Tier Name</th>
                <th>Status</th>
                <th>Applied Date</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($applications)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #999; padding: 2rem;">
                    No applications found.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($applications as $app): ?>
            <tr id="row-<?= htmlspecialchars($app['id']) ?>">
                <td><?= htmlspecialchars($app['id']) ?></td>
                <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
                <td><?= htmlspecialchars($app['program_title']) ?></td> <td><?= htmlspecialchars($app['tier_name']) ?></td>
                <td>
                    <?php
                    $statusClass = 'status-draft';
                    switch ($app['status']) {
                        case 'pending':
                            $statusClass = 'status-draft';
                            break;
                        case 'reviewing':
                            $statusClass = 'status-draft';
                            break;
                        case 'approved':
                            $statusClass = 'status-active';
                            break;
                        case 'rejected':
                            $statusClass = 'status-closed';
                            break;
                    }
                    ?>
                    <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars(ucfirst($app['status'])) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($app['applied_date']) ?></td>
                <td style="text-align: center;">
                    <div class="actions d-flex gap-2 justify-content-center">
                        <a href="index.php?controller=applications&action=edit&id=<?= htmlspecialchars($app['id']) ?>" 
                        class="btn btn-secondary" 
                        style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Edit</a>
                        
                        <?php if (in_array($currentUser['role'], ['admin', 'reviewer'], true)): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-danger delete-application-btn" 
                                    style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                    data-id="<?= htmlspecialchars($app['id']) ?>">Delete</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// AJAX Delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            if (!confirm('Are you sure you want to delete this application?')) {
                return;
            }
            
            // Fetch API for AJAX delete
            fetch(`index.php?controller=applications&action=delete&id=${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.getElementById(`row-${id}`);
                    if (row) {
                        row.remove();
                    }
                    alert('Application deleted successfully.');
                } else {
                    alert('Failed to delete application: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the application.');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
