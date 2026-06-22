<?php
/**
 * @var array $applications
 * @var array $currentUser
 * @var int $page Current page number
 * @var int $totalPages Total number of pages
 * @var int $totalCount Total number of records
 * @var array $programs List of programs for filter
 * @var string $search Current search term
 * @var string $statusFilter Current status filter
 * @var int $programFilter Current program filter
 */
$pageTitle = 'Scholarship Applications';
require_once __DIR__ . '/../partials/header.php';

// Default values if not set
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$search = $search ?? '';
$statusFilter = $statusFilter ?? '';
$programFilter = $programFilter ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">📝 Scholarship Applications</h4>
    
    <?php if (isset($currentUser) && $currentUser['role'] !== 'reviewer'): ?>
        <a href="index.php?controller=applications&action=create" class="btn btn-primary">+ New Application</a>
    <?php endif; ?>
</div>

<!-- Search and Filter Section -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="controller" value="applications">
            <input type="hidden" name="action" value="index">
            
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Search by student name or program..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="reviewing" <?= $statusFilter === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="waitlisted" <?= $statusFilter === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="program" class="form-label">Program</label>
                <select class="form-select" id="program" name="program">
                    <option value="0">All Programs</option>
                    <?php foreach ($programs as $prog): ?>
                        <option value="<?= htmlspecialchars($prog['id']) ?>" 
                                <?= $programFilter == $prog['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prog['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
        
        <?php if (!empty($search) || !empty($statusFilter) || $programFilter > 0): ?>
            <div class="mt-3">
                <a href="index.php?controller=applications&action=index" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                <span class="text-muted ms-2">Showing <?= $totalCount ?> result(s)</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%; text-align: center;">ID</th>
                    <th style="width: 25%;">Student Name</th>
                    <th style="width: 30%;">Scholarship Program</th> 
                    <th style="width: 12%;">Status</th>
                    <th style="width: 18%;">Applied Date</th>
                    <th style="width: 10%; text-align: center;">Actions</th>
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
                    <td style="text-align: center; font-weight: 500;"><?= htmlspecialchars($app['id']) ?></td>
                    
                    <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
                    
                    <td><?= htmlspecialchars($app['program_title']) ?></td>
                    
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
                        <div class="actions d-flex gap-2 justify-content-center">
                            <?php if ($currentUser['role'] === 'student'): ?>
                                <!-- Student Option 1: Can ALWAYS view their own grades & feedback -->
                                <a href="index.php?controller=applications&action=view&id=<?= htmlspecialchars($app['id']) ?>" 
                                   class="btn btn-sm btn-info text-white">View</a>
                                
                                <!-- Student Option 2: Can ONLY edit if the file is still pending -->
                                <?php if ($app['status'] === 'pending'): ?>
                                    <a href="index.php?controller=applications&action=edit&id=<?= htmlspecialchars($app['id']) ?>" 
                                       class="btn btn-sm btn-secondary">Edit</a>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <!-- ADMINISTRATIVE & REVIEWER ACTIONS GRID -->
                                <?php if (in_array($currentUser['role'], ['admin', 'reviewer'], true)): ?>
                                    <?php 
                                    $isFinalized = in_array($app['status'], ['approved', 'rejected'], true);
                                    $canReview = ($currentUser['role'] === 'admin') || ($currentUser['role'] === 'reviewer' && !$isFinalized);

                                    if ($canReview):
                                    ?>
                                        <a href="index.php?controller=applications&action=review&id=<?= htmlspecialchars($app['id']) ?>" 
                                           class="btn btn-sm btn-info text-white">Review</a>
                                    <?php else:
                                    ?>
                                        <span class="badge bg-secondary p-2 small">Locked</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (in_array($currentUser['role'], ['admin', 'staff'], true)): ?>
                                    <a href="index.php?controller=applications&action=edit&id=<?= htmlspecialchars($app['id']) ?>" 
                                       class="btn btn-sm btn-secondary">Edit</a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-application-btn" 
                                            data-id="<?= htmlspecialchars($app['id']) ?>">Delete</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <!-- Previous Button -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="index.php?controller=applications&action=index&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&program=<?= $programFilter ?>"
                           <?= $page <= 1 ? 'tabindex="-1"' : '' ?>>
                            Previous
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?controller=applications&action=index&page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&program=<?= $programFilter ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="index.php?controller=applications&action=index&page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&program=<?= $programFilter ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?controller=applications&action=index&page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&program=<?= $programFilter ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="index.php?controller=applications&action=index&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&program=<?= $programFilter ?>"
                           <?= $page >= $totalPages ? 'tabindex="-1"' : '' ?>>
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="text-center text-muted small mt-2">
                Showing page <?= $page ?> of <?= $totalPages ?> (<?= $totalCount ?> total records)
            </div>
        </div>
    <?php endif; ?>
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