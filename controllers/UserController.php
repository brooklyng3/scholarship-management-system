<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /** Trả về danh sách user cho trang index, hỗ trợ lọc theo role qua ?role=... */
    public function index(): array
    {
        $role = $_GET['role'] ?? null;
        $role = in_array($role, ['admin', 'student', 'reviewer'], true) ? $role : null;

        return [
            'users'        => $this->userModel->getAll($role),
            'currentRole'  => $role,
        ];
    }

    /**
     * Xử lý tạo mới user.
     * Trả về ['errors' => [...]] nếu có lỗi, hoặc redirect nếu thành công.
     */
    public function create(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email'     => trim($_POST['email'] ?? ''),
                'password'  => $_POST['password'] ?? '',
                'full_name' => trim($_POST['full_name'] ?? ''),
                'role'      => $_POST['role'] ?? 'student',
            ];

            $errors = $this->validate($data, true);

            if (empty($errors)) {
                if ($this->userModel->create($data)) {
                    set_flash('success', 'Tạo người dùng thành công.');
                    redirect('index.php');
                }
                $errors[] = 'Không thể tạo người dùng. Vui lòng thử lại.';
            }

            return ['errors' => $errors, 'old' => $data];
        }

        return ['errors' => [], 'old' => []];
    }

    /**
     * Xử lý cập nhật user theo $id.
     */
    public function edit(int $id): array
    {
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('error', 'Không tìm thấy người dùng.');
            redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email'     => trim($_POST['email'] ?? ''),
                'password'  => $_POST['password'] ?? '',
                'full_name' => trim($_POST['full_name'] ?? ''),
                'role'      => $_POST['role'] ?? $user['role'],
            ];

            $errors = $this->validate($data, false, $id);

            if (empty($errors)) {
                if ($this->userModel->update($id, $data)) {
                    set_flash('success', 'Cập nhật người dùng thành công.');
                    redirect('index.php');
                }
                $errors[] = 'Không thể cập nhật người dùng. Vui lòng thử lại.';
            }

            // Giữ lại dữ liệu vừa nhập để hiển thị lại form
            $user = array_merge($user, $data);
            return ['user' => $user, 'errors' => $errors];
        }

        return ['user' => $user, 'errors' => []];
    }

    /** Xóa user theo id (kiểm tra ràng buộc profile trước khi xóa) */
    public function delete(int $id): void
    {
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('error', 'Không tìm thấy người dùng.');
            redirect('index.php');
        }

        // users có FK ON DELETE CASCADE tới student_profiles/staff_profiles/violation_records
        // nên có thể xóa được, nhưng cảnh báo để người dùng biết hệ quả.
        if ($this->userModel->delete($id)) {
            set_flash('success', 'Đã xóa người dùng "' . $user['full_name'] . '" (và các hồ sơ liên quan).');
        } else {
            set_flash('error', 'Không thể xóa người dùng này.');
        }

        redirect('index.php');
    }

    /** Validate dữ liệu đầu vào cho create/update */
    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($data['email'] === '') {
            $errors[] = 'Email không được để trống.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không đúng định dạng.';
        } elseif ($this->userModel->emailExists($data['email'], $excludeId)) {
            $errors[] = 'Email này đã được sử dụng.';
        }

        if ($data['full_name'] === '') {
            $errors[] = 'Họ tên không được để trống.';
        }

        if (!in_array($data['role'], ['admin', 'student', 'reviewer'], true)) {
            $errors[] = 'Vai trò (role) không hợp lệ.';
        }

        if ($isCreate && $data['password'] === '') {
            $errors[] = 'Mật khẩu không được để trống.';
        } elseif (!$isCreate && $data['password'] !== '' && strlen($data['password']) < 6) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($isCreate && strlen($data['password']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        return $errors;
    }
}
