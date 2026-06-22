<?php
/**
 * @var array $programs
 * @var array $types
 * @var array $statuses
 * @var string $status
 * @var string $q
 * @var string $pagination
 * @var array $currentUser
 */
$pageTitle = 'Scholarship Programs';
require_once __DIR__ . '/../partials/header.php';
$isAdmin = is_logged_in() && current_user()['role'] === 'admin';

$statusBadge = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏆 Scholarship Programs Management</h4>
    <?php if ($isAdmin): ?>
        <a href="<?= e(url('scholarship_programs', 'create')) ?>" class="btn btn-primary">+ Add New Program</a>
    <?php endif; ?>
</div>

<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="scholarship_programs">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Search by program title..." value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select">
            <option value="">-- All Statuses --</option>
            <?php foreach ($statuses as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $status === $val ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Program Title</th>
                        <th>Scholarship Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No scholarship programs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($programs as $p): ?>
                    <tr>
                        <td><?= e($p['id']) ?></td>
                        <td><strong><?= e($p['title']) ?></strong></td>
                        <td><?= e($types[$p['scholarship_type']] ?? $p['scholarship_type']) ?></td>
                        <td><?= $p['start_date'] ? date('M d, Y', strtotime($p['start_date'])) : '—' ?></td>
                        <td><?= $p['end_date'] ? date('M d, Y', strtotime($p['end_date'])) : '—' ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $statusBadge[$p['status']] ?? 'secondary' ?>">
                                <?= e($statuses[$p['status']] ?? $p['status']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-sm">
                                <a href="index.php?controller=scholarship_programs&action=show&id=<?= htmlspecialchars($p['id']) ?>" 
                                   class="btn btn-sm btn-info text-white">View</a>
                                
                                <?php if (isset($currentUser) && in_array($currentUser['role'], ['admin', 'staff'], true)): ?>
                                    <a href="index.php?controller=scholarship_programs&action=edit&id=<?= htmlspecialchars($p['id']) ?>" 
                                       class="btn btn-sm btn-warning text-dark">Edit</a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-program-btn" 
                                            data-id="<?= htmlspecialchars($p['id']) ?>">Delete</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?= $pagination ?></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all program delete elements globally
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const programId = this.getAttribute('data-id');
            
            // Native UI confirmation query block
            if (confirm('Are you completely sure you want to permanently delete this scholarship program and all of its associated criteria rules?')) {
                // Route execution command securely passed along with CSRF anti-hijacking validation token
                window.location.href = `index.php?controller=scholarship_programs&action=delete&id=${programId}&csrf_token=<?= csrf_token() ?>`;
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>