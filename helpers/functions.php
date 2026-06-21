<?php
/**
 * Các hàm hỗ trợ chung: flash message, escape HTML, URL builder, CSRF, pagination.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Đặt flash message để hiển thị ở trang kế tiếp */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Lấy và xóa flash message (chỉ hiển thị 1 lần) */
// In helpers/functions.php

function get_flash(?string $type = null) {
    // If no type is provided, check for 'error' first, then 'success'
    if ($type === null) {
        if (isset($_SESSION['flash']['error'])) {
            $msg = $_SESSION['flash']['error'];
            unset($_SESSION['flash']['error']);
            return ['type' => 'error', 'message' => $msg];
        }
        if (isset($_SESSION['flash']['success'])) {
            $msg = $_SESSION['flash']['success'];
            unset($_SESSION['flash']['success']);
            return ['type' => 'success', 'message' => $msg];
        }
        return null;
    }

    // If a type IS provided, handle it normally
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return ['type' => $type, 'message' => $msg];
    }
    return null;
}

/** Escape chuỗi để hiển thị an toàn trong HTML */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect tới URL chỉ định và dừng thực thi */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

// ============================================================
// [NEW] URL builder cho kiến trúc Front Controller (public/index.php)
// Thay vì link sang từng file riêng (vd: users/index.php), toàn bộ
// link trong app giờ build qua index.php?controller=...&action=...
// ============================================================
function url(string $controller, string $action = 'index', array $params = []): string
{
    $query = array_merge(['controller' => $controller, 'action' => $action], $params);
    return 'index.php?' . http_build_query($query);
}

// ============================================================
// [NEW] CSRF protection
// Mỗi form POST (create/store, edit/update) đều phải mang theo token này.
// verify_csrf() được gọi ở đầu mỗi action store()/update()/delete().
// ============================================================
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** In ra input ẩn chứa CSRF token, dùng trong <form> */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Kiểm tra CSRF token gửi lên từ form. Nếu sai -> chặn request. */
function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        die('Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ (CSRF token mismatch). Vui lòng quay lại và thử lại.');
    }
}

// ============================================================
// [NEW] Pagination helper dùng chung cho các trang index (users,
// student_profiles, violation_records, scholarship_programs...)
// ============================================================
function paginate_params(int $defaultPerPage = 10): array
{
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = $defaultPerPage;
    $offset  = ($page - 1) * $perPage;

    return ['page' => $page, 'perPage' => $perPage, 'offset' => $offset];
}

/** Render thanh phân trang (Bootstrap) */
function render_pagination(int $currentPage, int $totalItems, int $perPage, string $controller, array $extraParams = []): string
{
    $totalPages = (int)ceil($totalItems / $perPage);
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav><ul class="pagination justify-content-center">';
    for ($p = 1; $p <= $totalPages; $p++) {
        $active = $p === $currentPage ? ' active' : '';
        $href = e(url($controller, 'index', array_merge($extraParams, ['page' => $p])));
        $html .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$href}\">{$p}</a></li>";
    }
    $html .= '</ul></nav>';

    return $html;
}
