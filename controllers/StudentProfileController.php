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
        require_role(['admin', 'reviewer', 'staff', 'student']);

        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');

        // Students can only view their own profile
        if ($isStudent) {
            $profile = $this->model->getByUserId((int)$currentUser['id']);
            if (!$profile) {
                set_flash('error', 'No student profile found for your account.');
                redirect(url('dashboard', 'index'));
            }
            $this->render('student_profiles/index', ['profile' => $profile]);
        } else {
            // Staff/Admin/Reviewer view all profiles with search + pagination
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
                set_flash('success', 'Student profile created successfully.');
                redirect(url('student_profiles', 'index'));
            }
            $errors[] = 'Unable to create profile. Please try again.';
        }

        $availableUsers = $this->model->getStudentUsersWithoutProfile();
        $this->render('student_profiles/create', ['errors' => $errors, 'old' => $data, 'availableUsers' => $availableUsers]);
    }

    public function edit(): void
    {
        require_role(['admin', 'reviewer', 'student']); // Students can edit their own profile

        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Student profile not found.');
            redirect(url('student_profiles', 'index'));
        }

        // Students can only edit their own profile
        if ($isStudent && (int)$profile['user_id'] !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to edit this profile.');
            redirect(url('student_profiles', 'index'));
        }

        $this->render('student_profiles/edit', ['profile' => $profile, 'errors' => []]);
    }

    public function update(): void
    {
        require_role(['admin', 'reviewer', 'student']); // Students can update their own profile
        verify_csrf();

        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        $isAdmin = in_array($currentUser['role'], ['admin', 'reviewer'], true);

        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->model->getById($id);
        if (!$profile) {
            set_flash('error', 'Student profile not found.');
            redirect(url('student_profiles', 'index'));
        }

        // Students can only update their own profile
        if ($isStudent && (int)$profile['user_id'] !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to update this profile.');
            redirect(url('student_profiles', 'index'));
        }

        // Strict whitelist: students can only edit full_name and major
        if ($isStudent) {
            $data = [
                'student_code'        => $profile['student_code'], // Preserve existing
                'full_name'           => trim($_POST['full_name'] ?? ''),
                'major'               => trim($_POST['major'] ?? ''),
                'current_gpa'         => $profile['current_gpa'], // Preserve existing
                'accumulated_credits' => $profile['accumulated_credits'], // Preserve existing
                'conduct_score'       => $profile['conduct_score'], // Preserve existing
            ];
        } else {
            // Admins can edit all fields
            $data = [
                'student_code'        => trim($_POST['student_code'] ?? ''),
                'full_name'           => trim($_POST['full_name'] ?? ''),
                'major'               => trim($_POST['major'] ?? ''),
                'current_gpa'         => trim($_POST['current_gpa'] ?? ''),
                'accumulated_credits' => trim($_POST['accumulated_credits'] ?? ''),
                'conduct_score'       => trim($_POST['conduct_score'] ?? ''),
            ];
        }

        $errors = $this->validate($data, false, $id);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Student profile updated successfully.');
                redirect(url('student_profiles', 'index'));
            }
            $errors[] = 'Unable to update profile. Please try again.';
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
            set_flash('error', 'Student profile not found.');
            redirect(url('student_profiles', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Student profile "' . $profile['full_name'] . '" deleted.');
        } else {
            set_flash('error', 'Unable to delete this profile.');
        }

        redirect(url('student_profiles', 'index'));
    }

    private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
    {
        $errors = [];

        if ($isCreate && $data['user_id'] <= 0) {
            $errors[] = 'Please select a student account.';
        }

        if ($data['student_code'] === '') {
            $errors[] = 'Student code cannot be empty.';
        } elseif ($this->model->studentCodeExists($data['student_code'], $excludeId)) {
            $errors[] = 'This student code already exists.';
        }

        if ($data['full_name'] === '') {
            $errors[] = 'Full name cannot be empty.';
        }

        if ($data['current_gpa'] !== '' && (!is_numeric($data['current_gpa']) || $data['current_gpa'] < 0 || $data['current_gpa'] > 4)) {
            $errors[] = 'GPA must be a number from 0.00 to 4.00.';
        }

        if ($data['accumulated_credits'] !== '' && (!ctype_digit($data['accumulated_credits']) || (int)$data['accumulated_credits'] < 0)) {
            $errors[] = 'Accumulated credits must be a non-negative integer.';
        }

        if ($data['conduct_score'] !== '' && (!ctype_digit($data['conduct_score']) || (int)$data['conduct_score'] < 0 || (int)$data['conduct_score'] > 100)) {
            $errors[] = 'Conduct score must be from 0 to 100.';
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
