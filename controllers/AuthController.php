<?php
/**
 * [NEW FEATURE] AuthController
 * Xử lý đăng nhập / đăng xuất cho toàn hệ thống, dùng bảng `users`.
 */
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php';

class AuthController
{
    /** GET: hiển thị form đăng nhập */
    public function login(): void
    {
        $errors = [];
        $this->render('auth/login', ['errors' => $errors]);
    }

    /** POST: xử lý đăng nhập */
    public function doLogin(): void
    {
        verify_csrf(); // [NEW] CSRF protection

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = attempt_login($email, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            set_flash('success', 'Đăng nhập thành công. Xin chào ' . $user['full_name'] . '!');
            redirect(url('scholarship_programs', 'index'));
        }

        $this->render('auth/login', ['errors' => ['Email hoặc mật khẩu không đúng.']]);
    }

    /** Đăng xuất: hủy session */
    public function logout(): void
    {
        unset($_SESSION['user']);
        set_flash('success', 'Bạn đã đăng xuất.');
        redirect(url('auth', 'login'));
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
