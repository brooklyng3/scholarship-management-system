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
        require_role(['admin']);

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
                set_flash('success', 'User created successfully.');
                redirect(url('users', 'index'));
            }
            $errors[] = 'Unable to create user. Please try again.';
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
            set_flash('error', 'User not found.');
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
                set_flash('success', 'User updated successfully.');
                redirect(url('users', 'index'));
            }
            $errors[] = 'Unable to update user. Please try again.';
        }

        $user = array_merge($user, $data);
        $this->render('users/edit', ['user' => $user, 'errors' => $errors]);
    }

    /** POST/DELETE index.php?controller=users&action=delete&id=.. (AJAX) */
    public function delete(): void
    {
        require_role(['admin']);
        verify_csrf();

        // Return JSON for AJAX requests
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        // Prevent deleting the last admin account
        if ($user['role'] === 'admin' && $this->userModel->countAdmins() <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete the last admin account. The system must have at least one admin.']);
            exit;
        }

        // users có FK ON DELETE CASCADE tới student_profiles/staff_profiles/violation_records
        if ($this->userModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'User "' . $user['full_name'] . '" deleted (along with related records).']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete this user.']);
        }
        exit;
    }

    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($data['email'] === '') {
            $errors[] = 'Email cannot be empty.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email format is invalid.';
        } elseif ($this->userModel->emailExists($data['email'], $excludeId)) {
            $errors[] = 'This email is already in use.';
        }

        if ($data['full_name'] === '') {
            $errors[] = 'Full name cannot be empty.';
        }

        if (!in_array($data['role'], ['admin', 'student', 'reviewer'], true)) {
            $errors[] = 'Role is invalid.';
        }

        if ($isCreate && $data['password'] === '') {
            $errors[] = 'Password cannot be empty.';
        } elseif (!$isCreate && $data['password'] !== '' && strlen($data['password']) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($isCreate && strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
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
