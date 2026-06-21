<?php
/**
 * AwardCertificateController
 * 
 * Handles all CRUD operations for award certificates.
 * Enforces RBAC and implements validation, CSRF protection, and AJAX delete.
 */

require_once __DIR__ . '/../models/AwardCertificate.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php';

class AwardCertificateController
{
    private AwardCertificate $model;

    public function __construct()
    {
        $this->model = new AwardCertificate();
    }

    /**
     * Display list of all certificates with search and pagination
     */
    public function index(): void
    {
        // RBAC: View requires admin, reviewer, or staff role
        require_role(['admin', 'reviewer', 'staff']);

        // Get pagination parameters
        $pagination = paginate_params(10);
        
        // Get search filters
        $filters = [
            'certificate_code' => $_GET['certificate_code'] ?? '',
            'student_name' => $_GET['student_name'] ?? ''
        ];

        // Get filtered and paginated certificates
        $certificates = $this->model->search($filters, $pagination['perPage'], $pagination['offset']);
        $totalItems = $this->model->countSearch($filters);

        require_once __DIR__ . '/../views/award_certificates/index.php';
    }

    /**
     * Display form to create a new certificate
     */
    public function create(): void
    {
        // RBAC: Create requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        $decisions = $this->model->getAllApprovedDecisions();
        require_once __DIR__ . '/../views/award_certificates/create.php';
    }

    /**
     * Store a new certificate record
     * Validates certificate code uniqueness and enforces CSRF protection
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
        $certificateCode = trim($_POST['certificate_code']);
        $issueDate = !empty($_POST['issue_date']) ? $_POST['issue_date'] : null;
        $pdfUrl = trim($_POST['pdf_url']);

        // Validation: Certificate code is required
        if (empty($certificateCode)) {
            set_flash('error', 'Mã chứng chỉ là bắt buộc.');
            redirect(url('award_certificate', 'create'));
        }

        // Validation: Certificate code must be unique
        if ($this->model->certificateCodeExists($certificateCode)) {
            set_flash('error', 'Mã chứng chỉ đã tồn tại. Vui lòng sử dụng mã khác.');
            redirect(url('award_certificate', 'create'));
        }

        // Validation: PDF URL is required
        if (empty($pdfUrl)) {
            set_flash('error', 'Đường dẫn file PDF là bắt buộc.');
            redirect(url('award_certificate', 'create'));
        }

        $data = [
            'decision_id' => $decisionId,
            'certificate_code' => $certificateCode,
            'issue_date' => $issueDate,
            'pdf_url' => $pdfUrl
        ];

        if ($this->model->create($data)) {
            set_flash('success', 'Chứng chỉ đã được tạo thành công.');
        } else {
            set_flash('error', 'Không thể tạo chứng chỉ.');
        }

        redirect(url('award_certificate', 'index'));
    }

    /**
     * Display form to edit an existing certificate
     */
    public function edit(): void
    {
        // RBAC: Edit requires admin or reviewer role
        require_role(['admin', 'reviewer']);

        $id = (int)$_GET['id'];
        $certificate = $this->model->getById($id);

        if (!$certificate) {
            http_response_code(404);
            die('Chứng chỉ không tồn tại.');
        }

        $decisions = $this->model->getAllApprovedDecisions();
        require_once __DIR__ . '/../views/award_certificates/edit.php';
    }

    /**
     * Update an existing certificate record
     * Validates certificate code uniqueness and enforces CSRF protection
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
        $certificateCode = trim($_POST['certificate_code']);
        $issueDate = !empty($_POST['issue_date']) ? $_POST['issue_date'] : null;
        $pdfUrl = trim($_POST['pdf_url']);

        // Validation: Certificate code is required
        if (empty($certificateCode)) {
            set_flash('error', 'Mã chứng chỉ là bắt buộc.');
            redirect(url('award_certificate', 'edit', ['id' => $id]));
        }

        // Validation: Certificate code must be unique (excluding current record)
        if ($this->model->certificateCodeExists($certificateCode, $id)) {
            set_flash('error', 'Mã chứng chỉ đã tồn tại. Vui lòng sử dụng mã khác.');
            redirect(url('award_certificate', 'edit', ['id' => $id]));
        }

        // Validation: PDF URL is required
        if (empty($pdfUrl)) {
            set_flash('error', 'Đường dẫn file PDF là bắt buộc.');
            redirect(url('award_certificate', 'edit', ['id' => $id]));
        }

        $data = [
            'decision_id' => $decisionId,
            'certificate_code' => $certificateCode,
            'issue_date' => $issueDate,
            'pdf_url' => $pdfUrl
        ];

        if ($this->model->update($id, $data)) {
            set_flash('success', 'Chứng chỉ đã được cập nhật thành công.');
        } else {
            set_flash('error', 'Không thể cập nhật chứng chỉ.');
        }

        redirect(url('award_certificate', 'index'));
    }

    /**
     * Delete a certificate record
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
            echo json_encode(['success' => true, 'message' => 'Chứng chỉ đã được xóa thành công.']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không thể xóa chứng chỉ.']);
        }
        exit;
    }
}
