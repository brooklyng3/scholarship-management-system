<?php
/**
 * DisbursementController
 * 
 * Handles all CRUD operations for disbursements.
 * Enforces RBAC and implements validation, CSRF protection, and AJAX delete.
 */

require_once __DIR__ . '/../models/DisbursementModel.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php';

class DisbursementController
{
    private DisbursementModel $model;

    public function __construct()
    {
        $this->model = new DisbursementModel();
    }

    /**
     * Display list of all disbursements with search and pagination
     */
    public function index(): void
    {
        // RBAC: View requires admin, reviewer, or staff role
        require_role(['admin', 'reviewer', 'staff']);

        // Get pagination parameters
        $pagination = paginate_params(10);
        
        // Get search filters
        $filters = [
            'student_name' => $_GET['student_name'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // Get filtered and paginated disbursements
        $disbursements = $this->model->search($filters, $pagination['perPage'], $pagination['offset']);
        $totalItems = $this->model->countSearch($filters);

        require_once __DIR__ . '/../views/disbursements/index.php';
    }

    /**
     * Display form to create a new disbursement
     */
    public function create(): void
    {
        // RBAC: Create requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        $decisions = $this->model->getAllApprovedDecisions();
        require_once __DIR__ . '/../views/disbursements/create.php';
    }

    /**
     * Store a new disbursement record
     * Validates amount, status, and enforces CSRF protection
     */
    public function store(): void
    {
        // RBAC: Store requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Method Not Allowed');
        }

        // CSRF protection
        verify_csrf();

        $decisionId = (int)$_POST['decision_id'];
        $amountPaid = (float)$_POST['amount_paid'];
        $paymentMethod = trim($_POST['payment_method']);
        $status = $_POST['status'];
        $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;

        // Validation: Amount must be greater than 0
        if ($amountPaid <= 0) {
            set_flash('error', 'Số tiền giải ngân phải lớn hơn 0.');
            redirect(url('disbursement', 'create'));
        }

        // Validation: Status must be valid ENUM value
        $validStatuses = ['processing', 'completed', 'failed'];
        if (!in_array($status, $validStatuses, true)) {
            set_flash('error', 'Trạng thái không hợp lệ.');
            redirect(url('disbursement', 'create'));
        }

        $data = [
            'decision_id' => $decisionId,
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'payment_date' => $paymentDate
        ];

        if ($this->model->create($data)) {
            set_flash('success', 'Giải ngân đã được tạo thành công.');
        } else {
            set_flash('error', 'Không thể tạo giải ngân.');
        }

        redirect(url('disbursement', 'index'));
    }

    /**
     * Display form to edit an existing disbursement
     */
    public function edit(): void
    {
        // RBAC: Edit requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        $id = (int)$_GET['id'];
        $disbursement = $this->model->getById($id);

        if (!$disbursement) {
            http_response_code(404);
            die('Giải ngân không tồn tại.');
        }

        $decisions = $this->model->getAllApprovedDecisions();
        require_once __DIR__ . '/../views/disbursements/edit.php';
    }

    /**
     * Update an existing disbursement record
     * Validates amount, status, and enforces CSRF protection
     */
    public function update(): void
    {
        // RBAC: Update requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Method Not Allowed');
        }

        // CSRF protection
        verify_csrf();

        $id = (int)$_POST['id'];
        $decisionId = (int)$_POST['decision_id'];
        $amountPaid = (float)$_POST['amount_paid'];
        $paymentMethod = trim($_POST['payment_method']);
        $status = $_POST['status'];
        $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;

        // Validation: Amount must be greater than 0
        if ($amountPaid <= 0) {
            set_flash('error', 'Số tiền giải ngân phải lớn hơn 0.');
            redirect(url('disbursement', 'edit', ['id' => $id]));
        }

        // Validation: Status must be valid ENUM value
        $validStatuses = ['processing', 'completed', 'failed'];
        if (!in_array($status, $validStatuses, true)) {
            set_flash('error', 'Trạng thái không hợp lệ.');
            redirect(url('disbursement', 'edit', ['id' => $id]));
        }

        $data = [
            'decision_id' => $decisionId,
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'payment_date' => $paymentDate
        ];

        if ($this->model->update($id, $data)) {
            set_flash('success', 'Giải ngân đã được cập nhật thành công.');
        } else {
            set_flash('error', 'Không thể cập nhật giải ngân.');
        }

        redirect(url('disbursement', 'index'));
    }

    /**
     * Delete a disbursement record
     * Returns JSON response for AJAX requests
     */
    public function delete(): void
    {
        // RBAC: Delete requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        // CSRF protection
        verify_csrf();

        $id = (int)$_POST['id'];

        if ($this->model->delete($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Giải ngân đã được xóa thành công.']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không thể xóa giải ngân.']);
        }
        exit;
    }
}
