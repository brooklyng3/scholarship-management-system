<?php
require_once __DIR__ . '/../models/ScholarshipProgram.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW]

/**
 * Controller cho scholarship_programs.
 * Chỉ sử dụng đúng các cột thực tế: title, scholarship_type, start_date, end_date, status.
 * KHÔNG có name, description, budget.
 */
class ScholarshipProgramController
{
    private ScholarshipProgram $model;

    public function __construct()
    {
        $this->model = new ScholarshipProgram();
    }

    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']); // Students can view programs

        // [NEW] search by title + filter by status + pagination
        $q = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $status = array_key_exists($status, ScholarshipProgram::STATUSES) ? $status : '';
        $p = paginate_params(10);

        $programs = $this->model->search($q, $status, $p['perPage'], $p['offset']);
        $total = $this->model->countSearch($q, $status);

        $this->render('scholarship_programs/index', [
            'programs'   => $programs,
            'types'      => ScholarshipProgram::TYPES,
            'statuses'   => ScholarshipProgram::STATUSES,
            'q'          => $q,
            'status'     => $status,
            'pagination' => render_pagination($p['page'], $total, $p['perPage'], 'scholarship_programs', ['q' => $q, 'status' => $status]),
        ]);
    }

    public function show(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']); // Students can view program details

        $id = (int)($_GET['id'] ?? 0);
        $program = $this->model->getById($id);
        if (!$program) {
            set_flash('error', 'Không tìm thấy chương trình học bổng.');
            redirect(url('scholarship_programs', 'index'));
        }

        $this->render('scholarship_programs/show', [
            'program'  => $program,
            'types'    => ScholarshipProgram::TYPES,
            'statuses' => ScholarshipProgram::STATUSES,
        ]);
    }

    public function create(): void
    {
        require_role(['admin']); // [NEW] only Admin opens new scholarship programs

        $this->render('scholarship_programs/create', [
            'errors'   => [],
            'old'      => [],
            'types'    => ScholarshipProgram::TYPES,
            'statuses' => ScholarshipProgram::STATUSES,
        ]);
    }

    public function store(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'scholarship_type' => $_POST['scholarship_type'] ?? '',
            'start_date'       => trim($_POST['start_date'] ?? ''),
            'end_date'         => trim($_POST['end_date'] ?? ''),
            'status'           => $_POST['status'] ?? 'draft',
        ];

        $errors = $this->validate($data);

        if (empty($errors)) {
            if ($this->model->create($data)) {
                set_flash('success', 'Tạo chương trình học bổng thành công.');
                redirect(url('scholarship_programs', 'index'));
            }
            $errors[] = 'Không thể tạo chương trình. Vui lòng thử lại.';
        }

        $this->render('scholarship_programs/create', [
            'errors'   => $errors,
            'old'      => $data,
            'types'    => ScholarshipProgram::TYPES,
            'statuses' => ScholarshipProgram::STATUSES,
        ]);
    }

    public function edit(): void
    {
        require_role(['admin']); // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $program = $this->model->getById($id);
        if (!$program) {
            set_flash('error', 'Không tìm thấy chương trình học bổng.');
            redirect(url('scholarship_programs', 'index'));
        }

        $this->render('scholarship_programs/edit', [
            'program'  => $program,
            'errors'   => [],
            'types'    => ScholarshipProgram::TYPES,
            'statuses' => ScholarshipProgram::STATUSES,
        ]);
    }

    public function update(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $program = $this->model->getById($id);
        if (!$program) {
            set_flash('error', 'Không tìm thấy chương trình học bổng.');
            redirect(url('scholarship_programs', 'index'));
        }

        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'scholarship_type' => $_POST['scholarship_type'] ?? '',
            'start_date'       => trim($_POST['start_date'] ?? ''),
            'end_date'         => trim($_POST['end_date'] ?? ''),
            'status'           => $_POST['status'] ?? 'draft',
        ];

        $errors = $this->validate($data);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Cập nhật chương trình học bổng thành công.');
                redirect(url('scholarship_programs', 'index'));
            }
            $errors[] = 'Không thể cập nhật chương trình. Vui lòng thử lại.';
        }

        $program = array_merge($program, $data);
        $this->render('scholarship_programs/edit', [
            'program'  => $program,
            'errors'   => $errors,
            'types'    => ScholarshipProgram::TYPES,
            'statuses' => ScholarshipProgram::STATUSES,
        ]);
    }

    public function delete(): void
    {
        require_role(['admin']); // [NEW]
        verify_csrf();           // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $program = $this->model->getById($id);
        if (!$program) {
            set_flash('error', 'Không tìm thấy chương trình học bổng.');
            redirect(url('scholarship_programs', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Đã xóa chương trình "' . $program['title'] . '".');
        } else {
            set_flash('error', 'Không thể xóa chương trình này (có thể đang được tham chiếu bởi đợt học bổng con).');
        }

        redirect(url('scholarship_programs', 'index'));
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors[] = 'Tên chương trình (title) không được để trống.';
        }

        if (!array_key_exists($data['scholarship_type'], ScholarshipProgram::TYPES)) {
            $errors[] = 'Loại học bổng không hợp lệ.';
        }

        if (!array_key_exists($data['status'], ScholarshipProgram::STATUSES)) {
            $errors[] = 'Trạng thái không hợp lệ.';
        }

        // Business rule: end_date phải >= start_date
        $dateError = $this->model->validateDates(
            $data['start_date'] !== '' ? $data['start_date'] : null,
            $data['end_date'] !== '' ? $data['end_date'] : null
        );
        if ($dateError) {
            $errors[] = $dateError;
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
