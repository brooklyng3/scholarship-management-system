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

        $q = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $status = array_key_exists($status, ScholarshipProgram::STATUSES) ? $status : '';
        $p = paginate_params(10);

        $programs = $this->model->search($q, $status, $p['perPage'], $p['offset']);
        $total = $this->model->countSearch($q, $status);

        $this->render('scholarship_programs/index', [
            'currentUser'=> current_user(), // 💡 FIX: Pass the current user session data here
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
        require_role(['admin', 'reviewer']);
        verify_csrf();

        // 1. Gather baseline parameters
        $data = [
            'title'              => trim($_POST['title'] ?? ''),
            'scholarship_type'   => trim($_POST['scholarship_type'] ?? ''),
            'start_date'         => trim($_POST['start_date'] ?? null),
            'end_date'           => trim($_POST['end_date'] ?? null),
            'status'             => trim($_POST['status'] ?? 'draft'),
            'min_gpa'            => isset($_POST['min_gpa']) && $_POST['min_gpa'] !== '' ? (float)$_POST['min_gpa'] : 0.00,
            'min_training_score' => isset($_POST['min_training_score']) && $_POST['min_training_score'] !== '' ? (int)$_POST['min_training_score'] : 0,
        ];

        $errors = [];

        // 2. Fetch the three dedicated hardcoded weights
        $weightGpa      = isset($_POST['weight_gpa']) ? (float)$_POST['weight_gpa'] : 0.00;
        $weightTraining = isset($_POST['weight_training']) ? (float)$_POST['weight_training'] : 0.00;
        $weightProof    = isset($_POST['weight_proof']) ? (float)$_POST['weight_proof'] : 0.00;

        $totalWeight = $weightGpa + $weightTraining + $weightProof;

        // Validate absolute mathematical parity
        if (abs($totalWeight - 100.00) > 0.001) {
            $errors[] = "Criteria weights total must equal exactly 100.00%. (Current math calculates to: " . number_format($totalWeight, 2) . "%)";
        }

        if ($data['min_gpa'] < 0.00 || $data['min_gpa'] > 4.00) {
            $errors[] = 'Minimum GPA threshold must fall between 0.00 and 4.00.';
        }

        // 3. Process database transactional commits if validation passes
        if (empty($errors)) {
            if ($this->model->create($data)) {
                $db = Database::getConnection();
                $newProgramId = (int)$db->lastInsertId();

                require_once __DIR__ . '/../models/ScoringCriteriaModel.php';
                $criteriaModel = new ScoringCriteriaModel();

                // Hardcode row item 1: GPA
                $criteriaModel->create([
                    'program_id'    => $newProgramId,
                    'criteria_name' => 'Điểm Trung bình Tích lũy (GPA)',
                    'weight'        => $weightGpa
                ]);

                // Hardcode row item 2: Training Score
                $criteriaModel->create([
                    'program_id'    => $newProgramId,
                    'criteria_name' => 'Điểm Rèn luyện',
                    'weight'        => $weightTraining
                ]);

                // Hardcode row item 3: Proof Submission
                $criteriaModel->create([
                    'program_id'    => $newProgramId,
                    'criteria_name' => 'Thành tích Ngoại khóa / Minh chứng (Upload)',
                    'weight'        => $weightProof
                ]);

                set_flash('success', 'Scholarship program built with normalized evaluation criteria profiles.');
                redirect(url('scholarship_programs', 'index'));
            }
            $errors[] = 'Database handling error encountered while saving structures.';
        }

        // Return to form upon validation error state
        $types = ScholarshipProgram::TYPES;
        $statuses = ScholarshipProgram::STATUSES;
        $this->render('scholarship_programs/create', ['errors' => $errors, 'old' => $data, 'types' => $types, 'statuses' => $statuses]);
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
        require_role(['admin', 'reviewer']);
        verify_csrf();

        $id = (int)($_GET['id'] ?? 0);
        $program = $this->model->getById($id);
        if (!$program) {
            set_flash('error', 'Scholarship program not found.');
            redirect(url('scholarship_programs', 'index'));
        }

        // 1. Gather all posted inputs from the view form
        $data = [
            'title'              => trim($_POST['title'] ?? ''),
            'scholarship_type'   => trim($_POST['scholarship_type'] ?? ''),
            'start_date'         => trim($_POST['start_date'] ?? null),
            'end_date'           => trim($_POST['end_date'] ?? null),
            'status'             => trim($_POST['status'] ?? 'draft'),
            'min_gpa'            => isset($_POST['min_gpa']) && $_POST['min_gpa'] !== '' ? (float)$_POST['min_gpa'] : 0.00,
            'min_training_score' => isset($_POST['min_training_score']) && $_POST['min_training_score'] !== '' ? (int)$_POST['min_training_score'] : 0,
        ];

        $weightGpa      = isset($_POST['weight_gpa']) ? (float)$_POST['weight_gpa'] : 0.00;
        $weightTraining = isset($_POST['weight_training']) ? (float)$_POST['weight_training'] : 0.00;
        $weightProof    = isset($_POST['weight_proof']) ? (float)$_POST['weight_proof'] : 0.00;

        $errors = [];

        // 2. Run mathematical boundary validations
        if (abs(($weightGpa + $weightTraining + $weightProof) - 100.00) > 0.001) {
            $errors[] = 'The sum of all evaluation criteria weights must equal exactly 100.00%.';
        }

        if ($data['min_gpa'] < 0.00 || $data['min_gpa'] > 4.00) {
            $errors[] = 'Minimum GPA threshold must fall between 0.00 and 4.00.';
        }

        // 3. If validation succeeds, commit updates to both tables
        if (empty($errors)) {
            // Update core scholarship program fields
            if ($this->model->update($id, $data)) {
                $db = Database::getConnection();

                // Explicitly update the weights for each of the 3 hardcoded metric names
                $updateWeightStmt = $db->prepare("UPDATE scoring_criteria SET weight = ? WHERE program_id = ? AND criteria_name = ?");
                
                $updateWeightStmt->execute([$weightGpa, $id, 'Điểm Trung bình Tích lũy (GPA)']);
                $updateWeightStmt->execute([$weightTraining, $id, 'Điểm Rèn luyện']);
                $updateWeightStmt->execute([$weightProof, $id, 'Thành tích Ngoại khóa / Minh chứng (Upload)']);

                set_flash('success', 'Scholarship program and scoring configurations updated successfully.');
                redirect(url('scholarship_programs', 'index'));
            }
            $errors[] = 'An error occurred while saving modifications to the database.';
        }

        // Fallback: If errors exist, re-render the edit form with adjustments preserved
        $types = ScholarshipProgram::TYPES;
        $statuses = ScholarshipProgram::STATUSES;
        $program = array_merge($program, $data);
        $this->render('scholarship_programs/edit', ['program' => $program, 'errors' => $errors, 'types' => $types, 'statuses' => $statuses]);
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
