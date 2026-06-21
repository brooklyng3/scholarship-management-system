<?php
/**
 * @var array $profiles
 * @var string $q
 * @var string $pagination
 */
$pageTitle = 'Staff Profiles';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏢 Staff Profiles</h4>
    <a href="<?= e(url('staff_profiles', 'create')) ?>" class="btn btn-primary">+ Add Profile</a>
</div>

<!-- [NEW] Search box -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="controller" value="staff_profiles">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Search by staff code, name, department..." value="<?= e($q) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Staff Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Updated At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($profiles)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No staff profiles found.</td></tr>
                <?php else: ?>
                    <?php foreach ($profiles as $p): ?>
                    <tr>
                        <td><?= e($p['id']) ?></td>
                        <td><code><?= e($p['staff_code']) ?></code></td>
                        <td><?= e($p['user_full_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td>
                            <?php $badge = $p['role'] === 'admin' ? 'danger' : 'warning text-dark'; ?>
                            <span class="badge bg-<?= $badge ?>"><?= e(ucfirst($p['role'])) ?></span>
                        </td>
                        <td><?= e($p['department']) ?: '<span class="text-muted">—</span>' ?></td>
                        <td><?= e($p['updated_at']) ?></td>
                        <td class="text-center">
                            <a href="<?= e(url('staff_profiles', 'edit', ['id' => $p['id']])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="<?= e(url('staff_profiles', 'delete', ['id' => $p['id'], 'csrf_token' => csrf_token()])) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this staff profile?')">Delete</a>
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
