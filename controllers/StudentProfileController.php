<?php
require_once __DIR__ . '/../models/StudentProfile.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW]

class StudentProfileController
{
    private StudentProfile $model;

    public function __construct()
    {
        $this->model = new StudentProfile();
    }

    public function index(): void
    {
        // [NEW] search + pagination
        $q = trim($_GET['q'] ?? '');
        $p = paginate_params(10);

        $profiles = $this->model->search($q, $p['perPage'], $p['offset']);
        $total = $this->model->countSearch($q);

        $this->render('student_profiles/index', [
            'profiles'   => $profiles,
            'q'          => $q,
            'pagination' => render_pagination($p['page'], $total, $p['perPage'], 'student_profiles', ['q' => $q]),
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'reviewer']); // [NEW] only staff manage student records

        $availableUsers = $this->model->getStudentUsersWithoutProfile();
        $this->render('student_profiles/create', ['errors' => [], 'old' => [], 'availableUsers' => $availableUsers]);
    }

    public function store(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $data = [
            'user_id'             => (int)($_POST['user_id'] ?? 0),
            'student_code'        => trim($_POST['student_code'] ?? ''),
            'full_name'           => trim($_POST['full_name'] ?? ''),
            'major'               => trim($_POST['major'] ?? ''),
            'current_gpa'         => trim($_POST['current_gpa'] ?? ''),
            'accumulated_credits' => trim($_POST['accumulated_credits'] ?? ''),
            'conduct_score'       => trim($_POST['conduct_score'] ?? ''),
        ];

        $errors = $this->validate($data, true);

        if (empty($errors)) {
            if ($this->model->create($data)) {
                set_flash('success', 'Tạo hồ sơ sinh viên thành công.');
                redirect(url('student_profiles', 'index'));
            }
            $errors[] = 'Không thể tạo hồ sơ. Vui lòng thử lại.';
        }

        $availableUsers = $this->model->getStudentUsersWithoutProfile();
        $this->render('student_profiles/create', ['errors' => $errors, 'old' => $data, 'availableUsers' => $availableUsers]);
    }

    public function edit(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ sinh viên.');
            redirect(url('student_profiles', 'index'));
        }

        $this->render('student_profiles/edit', ['profile' => $profile, 'errors' => []]);
    }

    public function update(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ sinh viên.');
            redirect(url('student_profiles', 'index'));
        }

        $data = [
            'student_code'        => trim($_POST['student_code'] ?? ''),
            'full_name'           => trim($_POST['full_name'] ?? ''),
            'major'               => trim($_POST['major'] ?? ''),
            'current_gpa'         => trim($_POST['current_gpa'] ?? ''),
            'accumulated_credits' => trim($_POST['accumulated_credits'] ?? ''),
            'conduct_score'       => trim($_POST['conduct_score'] ?? ''),
        ];

        $errors = $this->validate($data, false, $id);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Cập nhật hồ sơ sinh viên thành công.');
                redirect(url('student_profiles', 'index'));
            }
            $errors[] = 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
        }

        $profile = array_merge($profile, $data);
        $this->render('student_profiles/edit', ['profile' => $profile, 'errors' => $errors]);
    }

    public function delete(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Không tìm thấy hồ sơ sinh viên.');
            redirect(url('student_profiles', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Đã xóa hồ sơ sinh viên "' . $profile['full_name'] . '".');
        } else {
            set_flash('error', 'Không thể xóa hồ sơ này.');
        }

        redirect(url('student_profiles', 'index'));
    }

    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($isCreate && $data['user_id'] <= 0) {
            $errors[] = 'Vui lòng chọn tài khoản sinh viên.';
        }

        if ($data['student_code'] === '') {
            $errors[] = 'Mã sinh viên không được để trống.';
        } elseif ($this->model->studentCodeExists($data['student_code'], $excludeId)) {
            $errors[] = 'Mã sinh viên này đã tồn tại.';
        }

        if ($data['full_name'] === '') {
            $errors[] = 'Họ tên không được để trống.';
        }

        if ($data['current_gpa'] !== '' && (!is_numeric($data['current_gpa']) || $data['current_gpa'] < 0 || $data['current_gpa'] > 4)) {
            $errors[] = 'GPA phải là số từ 0.00 đến 4.00.';
        }

        if ($data['accumulated_credits'] !== '' && (!ctype_digit($data['accumulated_credits']) || (int)$data['accumulated_credits'] < 0)) {
            $errors[] = 'Số tín chỉ tích lũy phải là số nguyên không âm.';
        }

        if ($data['conduct_score'] !== '' && (!ctype_digit($data['conduct_score']) || (int)$data['conduct_score'] < 0 || (int)$data['conduct_score'] > 100)) {
            $errors[] = 'Điểm rèn luyện phải từ 0 đến 100.';
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
