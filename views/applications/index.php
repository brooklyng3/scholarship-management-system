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

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%; text-align: center;">ID</th>
                    <th style="width: 20%;">Student Name</th>
                    <th style="width: 25%;">Scholarship Program</th> 
                    <th style="width: 15%;">Tier Name</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%;">Applied Date</th>
                    <th style="width: 10%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 2rem;">
                        No applications found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                <tr id="row-<?= htmlspecialchars($app['id']) ?>">
                    <td style="text-align: center; font-weight: 500;"><?= htmlspecialchars($app['id']) ?></td>
                    
                    <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
                    
                    <td><?= htmlspecialchars($app['program_title']) ?></td>
                    
                    <td><span class="text-muted"><?= htmlspecialchars($app['tier_name']) ?></span></td>
                    
                    <td>
                        <?php
                        $statusClass = 'badge bg-warning text-dark';
                        if ($app['status'] === 'approved') $statusClass = 'badge bg-success';
                        if ($app['status'] === 'rejected') $statusClass = 'badge bg-danger';
                        if ($app['status'] === 'reviewing') $statusClass = 'badge bg-info';
                        ?>
                        <span class="<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($app['status'])) ?></span>
                    </td>
                    
                    <td class="small text-secondary"><?= htmlspecialchars($app['applied_date']) ?></td>
                    
                    <td class="text-center">
                        <?php 
                        // Admins retain master override access, but reviewers are blocked if the application is finalized
                        $isFinalized = in_array($app['status'], ['approved', 'rejected'], true);
                        $canReview = ($currentUser['role'] === 'admin') || ($currentUser['role'] === 'reviewer' && !$isFinalized);

                        if ($canReview): 
                        ?>
                            <a href="index.php?controller=applications&action=review&id=<?= htmlspecialchars($app['id']) ?>" 
                            class="btn btn-sm btn-info text-white">Review</a>
                        <?php else: ?>
                            <span class="badge bg-secondary p-2 small">Locked</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// AJAX Delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-application-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            if (!confirm('Are you sure you want to delete this application?')) {
                return;
            }
            
            fetch(`index.php?controller=applications&action=delete&id=${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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