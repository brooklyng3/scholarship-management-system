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
                set_flash('success', 'Staff profile created successfully.');
                redirect(url('staff_profiles', 'index'));
            }
            $errors[] = 'Unable to create profile. Please try again.';
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
            set_flash('error', 'Staff profile not found.');
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
            set_flash('error', 'Staff profile not found.');
            redirect(url('staff_profiles', 'index'));
        }

        $data = [
            'staff_code' => trim($_POST['staff_code'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
        ];

        $errors = $this->validate($data, false, $id);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Staff profile updated successfully.');
                redirect(url('staff_profiles', 'index'));
            }
            $errors[] = 'Unable to update profile. Please try again.';
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
            set_flash('error', 'Staff profile not found.');
            redirect(url('staff_profiles', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Staff profile "' . $profile['staff_code'] . '" deleted.');
        } else {
            set_flash('error', 'Unable to delete this profile.');
        }

        redirect(url('staff_profiles', 'index'));
    }

    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($isCreate && $data['user_id'] <= 0) {
            $errors[] = 'Please select a staff/admin/reviewer account.';
        }

        if ($data['staff_code'] === '') {
            $errors[] = 'Staff code cannot be empty.';
        } elseif ($this->model->staffCodeExists($data['staff_code'], $excludeId)) {
            $errors[] = 'This staff code already exists.';
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
