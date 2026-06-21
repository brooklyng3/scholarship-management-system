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
                                <a href="<?= e(url('users', 'delete', ['id' => $u['id'], 'csrf_token' => csrf_token()])) ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this user and all related data?')">Delete</a>
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
