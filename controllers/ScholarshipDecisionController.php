<?php
/**
 * ScholarshipDecisionController
 * 
 * Handles all CRUD operations for scholarship decisions.
 * Enforces RBAC (admin only) and business logic validation.
 */

require_once __DIR__ . '/../models/ScholarshipDecisionModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class ScholarshipDecisionController
{
    private ScholarshipDecisionModel $model;

    public function __construct()
    {
        $this->model = new ScholarshipDecisionModel();
    }

    /**
     * Display list of all scholarship decisions
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff']);
        $decisions = $this->model->getAll();
        require_once __DIR__ . '/../views/scholarship_decisions/index.php';
    }

    /**
     * Display form to create a new scholarship decision
     */
    public function create(): void
    {
        require_role(['admin', 'reviewer']);
        $applications = $this->model->getAllApplications();
        require_once __DIR__ . '/../views/scholarship_decisions/create.php';
    }

    /**
     * Store a new scholarship decision
     * Validates status/amount relationship and 1-to-1 constraint
     */
    public function store(): void
    {
        require_role(['admin', 'reviewer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $applicationId = (int)$_POST['application_id'];
        $decisionStatus = $_POST['decision_status'];
        $awardedAmount = (float)$_POST['awarded_amount'];
        $notes = $_POST['notes'] ?? '';

        // Business Logic: Status/Amount Validation
        if ($decisionStatus === 'awarded' && $awardedAmount <= 0) {
            $_SESSION['error'] = "Awarded decisions must have an amount greater than 0.";
            header('Location: index.php?controller=scholarship_decisions&action=create');
            exit;
        }

        if ($decisionStatus === 'rejected' && $awardedAmount != 0) {
            $_SESSION['error'] = "Rejected decisions must have an awarded amount of exactly 0.";
            header('Location: index.php?controller=scholarship_decisions&action=create');
            exit;
        }

        // Business Logic: 1-to-1 Relationship Check
        if ($this->model->hasDecision($applicationId)) {
            $_SESSION['error'] = "This application already has a decision. Each application can only have one decision.";
            header('Location: index.php?controller=scholarship_decisions&action=create');
            exit;
        }

        $data = [
            'application_id' => $applicationId,
            'decision_status' => $decisionStatus,
            'awarded_amount' => $awardedAmount,
            'notes' => $notes
        ];

        if ($this->model->create($data)) {
            $_SESSION['success'] = "Scholarship decision created successfully.";
        } else {
            $_SESSION['error'] = "Failed to create scholarship decision.";
        }

        header('Location: index.php?controller=scholarship_decisions&action=index');
        exit;
    }

    /**
     * Display form to edit an existing scholarship decision
     */
    public function edit(): void
    {
        require_role(['admin', 'reviewer', 'staff']);
        $id = (int)$_GET['id'];
        $decision = $this->model->getById($id);

        if (!$decision) {
            http_response_code(404);
            echo "Decision not found.";
            exit;
        }

        $applications = $this->model->getAllApplications();
        require_once __DIR__ . '/../views/scholarship_decisions/edit.php';
    }

    /**
     * Update an existing scholarship decision
     * Validates status/amount relationship and 1-to-1 constraint
     */
    public function update(): void
    {
        require_role(['admin', 'reviewer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $id = (int)$_POST['id'];
        $applicationId = (int)$_POST['application_id'];
        $decisionStatus = $_POST['decision_status'];
        $awardedAmount = (float)$_POST['awarded_amount'];
        $notes = $_POST['notes'] ?? '';

        // Business Logic: Status/Amount Validation
        if ($decisionStatus === 'awarded' && $awardedAmount <= 0) {
            $_SESSION['error'] = "Awarded decisions must have an amount greater than 0.";
            header("Location: index.php?controller=scholarship_decisions&action=edit&id=$id");
            exit;
        }

        if ($decisionStatus === 'rejected' && $awardedAmount != 0) {
            $_SESSION['error'] = "Rejected decisions must have an awarded amount of exactly 0.";
            header("Location: index.php?controller=scholarship_decisions&action=edit&id=$id");
            exit;
        }

        // Business Logic: 1-to-1 Relationship Check (exclude current decision)
        if ($this->model->hasDecision($applicationId, $id)) {
            $_SESSION['error'] = "This application already has a decision. Each application can only have one decision.";
            header("Location: index.php?controller=scholarship_decisions&action=edit&id=$id");
            exit;
        }

        $data = [
            'application_id' => $applicationId,
            'decision_status' => $decisionStatus,
            'awarded_amount' => $awardedAmount,
            'notes' => $notes
        ];

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = "Scholarship decision updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update scholarship decision.";
        }

        header('Location: index.php?controller=scholarship_decisions&action=index');
        exit;
    }

    /**
     * Delete a scholarship decision
     * Returns JSON response for AJAX requests
     */
    public function delete(): void
    {
        require_role(['admin', 'reviewer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $id = (int)$_POST['id'];

        if ($this->model->delete($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to delete decision']);
        }
        exit;
    }
}
