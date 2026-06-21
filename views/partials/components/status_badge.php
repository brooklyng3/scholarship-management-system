<?php
/**
 * Reusable status badge component
 * @var string $status - The status key (e.g., 'draft', 'active', 'closed')
 * @var array $statusMap - Mapping of status keys to badge colors (e.g., ['draft' => 'secondary', 'active' => 'success'])
 * @var array $labelMap - Mapping of status keys to display labels (e.g., ['draft' => 'Bản nháp', 'active' => 'Đang mở'])
 */
$badgeColor = $statusMap[$status] ?? 'secondary';
$statusLabel = $labelMap[$status] ?? $status;
?>
<span class="badge bg-<?= e($badgeColor) ?>">
    <?= e($statusLabel) ?>
</span>
