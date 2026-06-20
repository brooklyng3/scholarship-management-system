<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW]

/**
 * UserController
 * Action methods match the Front Controller dispatcher (public/index.php):
 *   index, create, store, edit, update, delete
 */
class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /** GET index.php?controller=users&action=index */
    public function index(): void
    {
        $role = $_GET['role'] ?? null;
        $role = in_array($role, ['admin', 'student', 'reviewer'], true) ? $role : null;

        // [NEW] search keyword + pagination
        $q = trim($_GET['q'] ?? '');
        $p = paginate_params(10);

        $users = $this->userModel->search($role, $q, $p['perPage'], $p['offset']);
        $total = $this->userModel->countSearch($role, $q);

        $this->render('users/index', [
            'users'       => $users,
            'currentRole' => $role,
            'q'           => $q,
            'pagination'  => render_pagination($p['page'], $total, $p['perPage'], 'users', $role ? ['role' => $role, 'q' => $q] : ['q' => $q]),
        ]);
    }

    /** GET index.php?controller=users&action=create */
    public function create(): void
    {
        require_role(['admin']); // [NEW] only Admin can create accounts

        $this->render('users/create', ['errors' => [], 'old' => []]);
    }

    /** POST index.php?controller=users&action=store */
    public function store(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

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
                redirect(url('users', 'index'));
            }
            $errors[] = 'Không thể tạo người dùng. Vui lòng thử lại.';
        }

        $this->render('users/create', ['errors' => $errors, 'old' => $data]);
    }

    /** GET index.php?controller=users&action=edit&id=.. */
    public function edit(): void
    {
        require_role(['admin']); // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('error', 'Không tìm thấy người dùng.');
            redirect(url('users', 'index'));
        }

        $this->render('users/edit', ['user' => $user, 'errors' => []]);
    }

    /** POST index.php?controller=users&action=update&id=.. */
    public function update(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('error', 'Không tìm thấy người dùng.');
            redirect(url('users', 'index'));
        }

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
                redirect(url('users', 'index'));
            }
            $errors[] = 'Không thể cập nhật người dùng. Vui lòng thử lại.';
        }

        $user = array_merge($user, $data);
        $this->render('users/edit', ['user' => $user, 'errors' => $errors]);
    }

    /** GET index.php?controller=users&action=delete&id=..&csrf_token=.. */
    public function delete(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW] token passed as query string on the confirm link

        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);
        if (!$user) {
            set_flash('error', 'Không tìm thấy người dùng.');
            redirect(url('users', 'index'));
        }

        // users có FK ON DELETE CASCADE tới student_profiles/staff_profiles/violation_records
        if ($this->userModel->delete($id)) {
            set_flash('success', 'Đã xóa người dùng "' . $user['full_name'] . '" (và các hồ sơ liên quan).');
        } else {
            set_flash('error', 'Không thể xóa người dùng này.');
        }

        redirect(url('users', 'index'));
    }

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

    /** [NEW] Render a view template with data, replacing the old "view bootstraps itself" pattern */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
