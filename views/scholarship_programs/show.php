<?php
/**
 * Detail view for a single Scholarship Program
 * @var array $program - The scholarship program data
 * @var array $types - Available scholarship types
 * @var array $statuses - Available program statuses
 */
$pageTitle = 'Chi tiết Chương trình Học bổng';
require_once __DIR__ . '/../partials/header.php';

// Define badge color mappings for status
$statusBadgeMap = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏆 Chi tiết Chương trình Học bổng</h4>
    <a href="<?= e(url('scholarship_programs', 'index')) ?>" class="btn btn-secondary">← Quay lại danh sách</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= e($program['title']) ?></h5>
    </div>
    <div class="card-body">
        <?php
        // Render detail rows using component
        $label = 'ID:';
        $value = $program['id'];
        require __DIR__ . '/../partials/components/detail_row.php';

        $label = 'Tên chương trình:';
        $value = $program['title'];
        require __DIR__ . '/../partials/components/detail_row.php';

        // Scholarship Type with badge
        $label = 'Loại học bổng:';
        ob_start();
        ?>
        <span class="badge bg-info">
            <?= e($types[$program['scholarship_type']] ?? $program['scholarship_type']) ?>
        </span>
        <?php
        $value = ob_get_clean();
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // Start Date
        $label = 'Ngày bắt đầu:';
        $value = $program['start_date'] ?: '<span class="text-muted">Chưa xác định</span>';
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // End Date
        $label = 'Ngày kết thúc:';
        $value = $program['end_date'] ?: '<span class="text-muted">Chưa xác định</span>';
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        $escape = true;

        // Status with badge component
        $label = 'Trạng thái:';
        ob_start();
        $status = $program['status'];
        $statusMap = $statusBadgeMap;
        $labelMap = $statuses;
        require __DIR__ . '/../partials/components/status_badge.php';
        $value = ob_get_clean();
        $escape = false;
        require __DIR__ . '/../partials/components/detail_row.php';
        ?>
    </div>

    <?php
    // Render admin actions using component
    $controller = 'scholarship_programs';
    $id = $program['id'];
    $deleteConfirmMessage = 'Bạn có chắc muốn xóa chương trình học bổng này?';
    require __DIR__ . '/../partials/components/admin_actions.php';
    ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
