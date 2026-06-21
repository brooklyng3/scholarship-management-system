<?php
/**
 * Reusable admin actions component for Edit and Delete buttons
 * @var string $controller - The controller name (e.g., 'scholarship_programs')
 * @var int $id - The record ID
 * @var string $deleteConfirmMessage - Custom confirmation message for delete (optional)
 */
$deleteConfirmMessage = $deleteConfirmMessage ?? 'Bạn có chắc muốn xóa mục này?';
$isAdmin = is_logged_in() && current_user()['role'] === 'admin';

if (!$isAdmin) {
    return;
}
?>

<div class="card-footer">
    <div class="d-flex gap-2">
        <a href="<?= e(url($controller, 'edit', ['id' => $id])) ?>" class="btn btn-warning">
            ✏️ Chỉnh sửa
        </a>
        <button type="button" class="btn btn-danger" onclick="deleteItem<?= e($controller) ?>(<?= e($id) ?>)">
            🗑️ Xóa
        </button>
    </div>
</div>

<script>
function deleteItem<?= e($controller) ?>(id) {
    if (!confirm('<?= e($deleteConfirmMessage) ?>')) {
        return;
    }

    // Create a form and submit it (to include CSRF token)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?controller=<?= e($controller) ?>&action=delete&id=' + id;

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
