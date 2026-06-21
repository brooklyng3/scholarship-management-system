<?php
/** * Template: violation_records/index
 * * @var array $records
 * @var array $types
 * @var string $type
 * @var string $pagination
 */
$pageTitle = 'Violation List';
require_once __DIR__ . '/../partials/header.php';
$canManage = is_logged_in() && in_array(current_user()['role'], ['admin', 'reviewer'], true); // [NEW]

$typeBadge = [
    'fee_debt'     => 'danger',
    'discipline'   => 'dark',
    'library_debt' => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🚫 Violation List (violation_records)</h4>
    <div class="d-flex gap-2">
        <?php if ($canManage): ?>
            <a href="<?= e(url('violation_records', 'export')) ?>" class="btn btn-outline-success">⬇ Export CSV</a>
            <a href="<?= e(url('violation_records', 'create')) ?>" class="btn btn-primary">+ Add Violation</a>
        <?php endif; ?>
    </div>
</div>
<p class="text-muted small">Students with a record here will be automatically excluded from scholarship reviews.</p>

<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="<?= e(url('violation_records', 'index')) ?>" class="btn btn-outline-secondary <?= !$type ? 'active' : '' ?>">All</a>
        <?php foreach ($types as $val => $label): ?>
            <a href="<?= e(url('violation_records', 'index', ['type' => $val])) ?>"
               class="btn btn-outline-dark <?= $type === $val ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Violation Type</th>
                        <th>Description</th>
                        <th>Recorded Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No violation records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= e($r['id']) ?></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $typeBadge[$r['violation_type']] ?? 'secondary' ?>">
                                <?= e($types[$r['violation_type']] ?? $r['violation_type']) ?>
                            </span>
                        </td>
                        <td><?= e($r['description']) ?></td>
                        <td><?= e($r['recorded_date']) ?></td>
                        <td class="text-center">
                            <?php if ($canManage): ?>
                                <a href="<?= e(url('violation_records', 'edit', ['id' => $r['id']])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="<?= e(url('violation_records', 'delete', ['id' => $r['id'], 'csrf_token' => csrf_token()])) ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this violation record?')">Delete</a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
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

<div class="mt-3"><?= $pagination ?></div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>