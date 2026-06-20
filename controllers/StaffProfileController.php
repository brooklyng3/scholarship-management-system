<?php
require_once __DIR__ . '/../models/StaffProfile.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW]

class StaffProfileController
{
    private StaffProfile $model;

    public function __construct()
    {
        $this->model = new StaffProfile();
    }

    public function index(): void
    {
        require_role(['admin']); // [NEW] staff records are admin-only to view

        $q = trim($_GET['q'] ?? '');
        $p = paginate_params(10);

        $profiles = $this->model->search($q, $p['perPage'], $p['offset']);
        $total = $this->model->countSearch($q);

        $this->render('staff_profiles/index', [
            'profiles'   => $profiles,
            'q'          => $q,
            'pagination' => render_pagination($p['page'], $total, $p['perPage'], 'staff_profiles', ['q' => $q]),
        ]);
    }

    public function create(): void
    {
        require_role(['admin']); // [NEW]

        $availableUsers = $this->model->getStaffUsersWithoutProfile();
        $this->render('staff_profiles/create', ['errors' => [], 'old' => [], 'availableUsers' => $availableUsers]);
    }

    public function store(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $data = [
            'user_id'    => (int)($_POST['user_id'] ?? 0),
            'staff_code' => trim($_POST['staff_code'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
        ];

        $errors = $this->validate($data, true);

        if (empty($errors)) {
            if ($this->model->create($data)) {
                set_flash('success', 'Tạo hồ sơ cán bộ thành công.');
                redirect(url('staff_profiles', 'index'));
            }
            $errors[] = 'Không thể tạo hồ sơ. Vui lòng thử lại.';
        }

        $availableUsers = $this->model->getStaffUsersWithoutProfile();
        $this->render('staff_profiles/create', ['errors' => $errors, 'old' => $data, 'availableUsers' => $availableUsers]);
    }

    public function edit(): void
    {
        require_role(['admin']); // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ cán bộ.');
            redirect(url('staff_profiles', 'index'));
        }

        $this->render('staff_profiles/edit', ['profile' => $profile, 'errors' => []]);
    }

    public function update(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ cán bộ.');
            redirect(url('staff_profiles', 'index'));
        }

        $data = [
            'staff_code' => trim($_POST['staff_code'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
        ];

        $errors = $this->validate($data, false, $id);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Cập nhật hồ sơ cán bộ thành công.');
                redirect(url('staff_profiles', 'index'));
            }
            $errors[] = 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
        }

        $profile = array_merge($profile, $data);
        $this->render('staff_profiles/edit', ['profile' => $profile, 'errors' => $errors]);
    }

    public function delete(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ cán bộ.');
            redirect(url('staff_profiles', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Đã xóa hồ sơ cán bộ mã "' . $profile['staff_code'] . '".');
        } else {
            set_flash('error', 'Không thể xóa hồ sơ này.');
        }

        redirect(url('staff_profiles', 'index'));
    }

    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($isCreate && $data['user_id'] <= 0) {
            $errors[] = 'Vui lòng chọn tài khoản cán bộ/admin/reviewer.';
        }

        if ($data['staff_code'] === '') {
            $errors[] = 'Mã cán bộ không được để trống.';
        } elseif ($this->model->staffCodeExists($data['staff_code'], $excludeId)) {
            $errors[] = 'Mã cán bộ này đã tồn tại.';
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
