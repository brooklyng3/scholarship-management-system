<?php
require_once __DIR__ . '/../models/ViolationRecord.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW]

class ViolationRecordController
{
    private ViolationRecord $model;

    public function __construct()
    {
        $this->model = new ViolationRecord();
    }

    public function index(): void
    {
        // [NEW] filter by type + pagination
        $type = $_GET['type'] ?? '';
        $type = array_key_exists($type, ViolationRecord::TYPES) ? $type : '';
        $p = paginate_params(10);

        $records = $this->model->search($type, $p['perPage'], $p['offset']);
        $total = $this->model->countSearch($type);

        $this->render('violation_records/index', [
            'records'    => $records,
            'types'      => ViolationRecord::TYPES,
            'type'       => $type,
            'pagination' => render_pagination($p['page'], $total, $p['perPage'], 'violation_records', ['type' => $type]),
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]

        $users = $this->model->getAllUsers();
        $this->render('violation_records/create', ['errors' => [], 'old' => [], 'users' => $users, 'types' => ViolationRecord::TYPES]);
    }

    public function store(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $data = [
            'user_id'        => (int)($_POST['user_id'] ?? 0),
            'violation_type' => $_POST['violation_type'] ?? '',
            'description'    => trim($_POST['description'] ?? ''),
            'recorded_date'  => trim($_POST['recorded_date'] ?? ''),
        ];

        $errors = $this->validate($data);

        if (empty($errors)) {
            if ($this->model->create($data)) {
                set_flash('success', 'Thêm bản ghi vi phạm thành công.');
                redirect(url('violation_records', 'index'));
            }
            $errors[] = 'Không thể thêm bản ghi. Vui lòng thử lại.';
        }

        $users = $this->model->getAllUsers();
        $this->render('violation_records/create', ['errors' => $errors, 'old' => $data, 'users' => $users, 'types' => ViolationRecord::TYPES]);
    }

    public function edit(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $record = $this->model->getById($id);
        if (!$record) {
            set_flash('error', 'Không tìm thấy bản ghi vi phạm.');
            redirect(url('violation_records', 'index'));
        }

        $users = $this->model->getAllUsers();
        $this->render('violation_records/edit', ['record' => $record, 'errors' => [], 'users' => $users, 'types' => ViolationRecord::TYPES]);
    }

    public function update(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $record = $this->model->getById($id);
        if (!$record) {
            set_flash('error', 'Không tìm thấy bản ghi vi phạm.');
            redirect(url('violation_records', 'index'));
        }

        $data = [
            'user_id'        => (int)($_POST['user_id'] ?? 0),
            'violation_type' => $_POST['violation_type'] ?? '',
            'description'    => trim($_POST['description'] ?? ''),
            'recorded_date'  => trim($_POST['recorded_date'] ?? ''),
        ];

        $errors = $this->validate($data);

        if (empty($errors)) {
            if ($this->model->update($id, $data)) {
                set_flash('success', 'Cập nhật bản ghi vi phạm thành công.');
                redirect(url('violation_records', 'index'));
            }
            $errors[] = 'Không thể cập nhật bản ghi. Vui lòng thử lại.';
        }

        $record = array_merge($record, $data);
        $users = $this->model->getAllUsers();
        $this->render('violation_records/edit', ['record' => $record, 'errors' => $errors, 'users' => $users, 'types' => ViolationRecord::TYPES]);
    }

    public function delete(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]
        verify_csrf();                       // [NEW]

        $id = (int)($_GET['id'] ?? 0);
        $record = $this->model->getById($id);
        if (!$record) {
            set_flash('error', 'Không tìm thấy bản ghi vi phạm.');
            redirect(url('violation_records', 'index'));
        }

        if ($this->model->delete($id)) {
            set_flash('success', 'Đã xóa bản ghi vi phạm.');
        } else {
            set_flash('error', 'Không thể xóa bản ghi này.');
        }

        redirect(url('violation_records', 'index'));
    }

    /**
     * [NEW FEATURE] Export toàn bộ "danh sách đen" ra file CSV.
     * GET index.php?controller=violation_records&action=export
     * Dùng cho Phòng Đào tạo / CTSV tải về đối chiếu offline.
     */
    public function export(): void
    {
        require_role(['admin', 'reviewer']); // [NEW]

        $records = $this->model->getAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=violation_records_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Họ tên', 'Email', 'Loại vi phạm', 'Mô tả', 'Ngày ghi nhận']);

        foreach ($records as $r) {
            fputcsv($out, [
                $r['id'],
                $r['full_name'],
                $r['email'],
                ViolationRecord::TYPES[$r['violation_type']] ?? $r['violation_type'],
                $r['description'],
                $r['recorded_date'],
            ]);
        }

        fclose($out);
        exit;
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['user_id'] <= 0) {
            $errors[] = 'Vui lòng chọn người dùng.';
        }

        if (!array_key_exists($data['violation_type'], ViolationRecord::TYPES)) {
            $errors[] = 'Loại vi phạm không hợp lệ.';
        }

        if ($data['recorded_date'] !== '' && !strtotime($data['recorded_date'])) {
            $errors[] = 'Ngày ghi nhận không đúng định dạng.';
        }

        return $errors;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
