<?php
/**
 * [NEW FEATURE] helpers/auth.php
 *
 * Hệ thống đăng nhập & phân quyền (Role-Based Access Control) dựa trên
 * bảng `users` (cụm 1). Đây là tính năng MỚI bổ sung thêm cho module:
 *   - Đăng nhập bằng email + mật khẩu (đối chiếu password_hash trong DB)
 *   - Lưu user đang đăng nhập vào session
 *   - Hàm require_login() / require_role() dùng để bảo vệ các action
 *     nhạy cảm (vd: chỉ Admin mới được xóa user, thêm vi phạm...)
 */

require_once __DIR__ . '/../config/database.php';

/** Trả về user hiện đang đăng nhập (mảng) hoặc null nếu chưa đăng nhập */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

/** Chặn truy cập nếu chưa đăng nhập -> đẩy về trang login */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please login to continue.');
        redirect(url('auth', 'login'));
    }
}

/**
 * Chặn truy cập nếu user hiện tại không có role phù hợp.
 * Dùng: require_role(['admin'])  hoặc  require_role(['admin', 'reviewer'])
 */
function require_role(array $allowedRoles): void
{
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $allowedRoles, true)) {
        set_flash('error', 'You do not have permission to perform this action (required role: ' . implode(', ', $allowedRoles) . ').');
        redirect(url('scholarship_programs', 'index'));
    }
}

/** Thử đăng nhập với email + mật khẩu. Trả về user array nếu đúng, false nếu sai. */
function attempt_login(string $email, string $password): array|false
{
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']); // không lưu hash vào session
        return $user;
    }

    return false;
}
