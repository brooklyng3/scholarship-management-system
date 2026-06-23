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
            set_flash('success', 'Login successful. Welcome ' . $user['full_name'] . '!');

            if ($user['role'] === 'admin') {
                redirect(url('dashboard', 'index')); 
            } elseif ($user['role'] === 'reviewer') {
                redirect(url('dashboard', 'reviewer')); 
            } else {
                redirect(url('dashboard', 'student')); 
            }
            exit; // Đảm bảo dừng thực thi sau khi chuyển hướng
        }

        // Dòng số 45 từng bị lỗi giờ đã nằm ngoan ngoãn BÊN TRONG hàm doLogin()
        $this->render('auth/login', ['errors' => ['Email or password is incorrect.']]);
    }

    /** Đăng xuất: hủy session */
    public function logout(): void
    {
        unset($_SESSION['user']);
        set_flash('success', 'You have been logged out.');
        redirect(url('auth', 'login'));
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}