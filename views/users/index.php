<?php
/**
 * Template: users/index
 * @var array $users
 * @var string $currentRole
 * @var string $q
 * @var string $pagination
 */
$pageTitle = 'User List';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">👥 Users</h4>
    <?php if (is_logged_in() && current_user()['role'] === 'admin'): ?>
        <a href="<?= e(url('users', 'create')) ?>" class="btn btn-primary">+ Add User</a>
    <?php endif; ?>
</div>

<!-- [NEW] Search box -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="users">
    <input type="hidden" name="role" value="<?= e($currentRole ?? '') ?>">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Search by name or email..." value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<!-- Lọc theo role -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="<?= e(url('users', 'index', ['q' => $q])) ?>" class="btn btn-outline-secondary <?= !$currentRole ? 'active' : '' ?>">All</a>
        <a href="<?= e(url('users', 'index', ['role' => 'admin', 'q' => $q])) ?>" class="btn btn-outline-danger <?= $currentRole === 'admin' ? 'active' : '' ?>">Admin</a>
        <a href="<?= e(url('users', 'index', ['role' => 'reviewer', 'q' => $q])) ?>" class="btn btn-outline-warning <?= $currentRole === 'reviewer' ? 'active' : '' ?>">Reviewer</a>
        <a href="<?= e(url('users', 'index', ['role' => 'student', 'q' => $q])) ?>" class="btn btn-outline-success <?= $currentRole === 'student' ? 'active' : '' ?>">Student</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['id']) ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <?php
                            $badgeMap = ['admin' => 'danger', 'reviewer' => 'warning text-dark', 'student' => 'success'];
                            $badge = $badgeMap[$u['role']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= e(ucfirst($u['role'])) ?></span>
                        </td>
                        <td><?= e($u['created_at']) ?></td>
                        <td class="text-center">
                            <?php if (is_logged_in() && current_user()['role'] === 'admin'): ?>
                                <a href="<?= e(url('users', 'edit', ['id' => $u['id']])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-user-btn"
                                        data-user-id="<?= e($u['id']) ?>"
                                        data-user-name="<?= e($u['full_name']) ?>">Delete</button>
                            <?php else: ?>
                                <span class="text-muted small">Admin Only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?= $pagination /* [NEW] pagination controls */ ?></div>

<script>
// AJAX Delete User Implementation (Vanilla JS)
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-user-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            if (!confirm(`Delete user "${userName}" and all related data?`)) {
                return;
            }
            
            // Disable button during request
            this.disabled = true;
            this.textContent = 'Deleting...';
            
            // Prepare delete URL
            const deleteUrl = `index.php?controller=users&action=delete&id=${userId}&csrf_token=<?= e(csrf_token()) ?>`;
            
            // Send AJAX DELETE request
            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showFlashMessage('success', data.message);
                    
                    // Remove the row from DOM
                    const row = this.closest('tr');
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                } else {
                    // Show error message
                    showFlashMessage('danger', data.message);
                    this.disabled = false;
                    this.textContent = 'Delete';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFlashMessage('danger', 'An error occurred while deleting the user.');
                this.disabled = false;
                this.textContent = 'Delete';
            });
        });
    });
    
    // Helper function to display flash messages dynamically
    function showFlashMessage(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert at the top of the page content
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
