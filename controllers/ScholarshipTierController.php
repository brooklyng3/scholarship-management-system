<?php
// controllers/ScholarshipTierController.php

require_once __DIR__ . '/../models/ScholarshipTierModel.php';

class ScholarshipTierController {
    private ScholarshipTierModel $model;

    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // RBAC: Check if user is admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>You do not have permission to access this resource.</p>
</body>
</html>';
            exit;
        }

        $this->model = new ScholarshipTierModel();
    }

    /**
     * Display list of all scholarship tiers
     */
    public function index(): void {
        $tiers = $this->model->getAll();
        require __DIR__ . '/../views/scholarship_tiers/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void {
        $programs = $this->model->getAllPrograms();
        require __DIR__ . '/../views/scholarship_tiers/create.php';
    }

    /**
     * Handle form submission for creating new tier
     */
    public function store(): void {
        // Read and sanitize input
        $data = [
            'program_id' => (int)($_POST['program_id'] ?? 0),
            'tier_name' => trim($_POST['tier_name'] ?? ''),
            'reward_amount' => trim($_POST['reward_amount'] ?? ''),
            'quota' => (int)($_POST['quota'] ?? 0),
        ];

        // Validate required fields
        $errors = [];
        if (empty($data['program_id'])) {
            $errors[] = "Scholarship program is required.";
        }
        if (empty($data['tier_name'])) {
            $errors[] = "Tier name is required.";
        }
        if (empty($data['reward_amount']) || !is_numeric($data['reward_amount'])) {
            $errors[] = "Reward amount must be a valid number.";
        } elseif ((float)$data['reward_amount'] <= 0) {
            $errors[] = "Reward amount must be greater than 0.";
        }
        if ($data['quota'] < 1) {
            $errors[] = "Quota must be at least 1.";
        }

        // Check for duplicate tier name in the same program
        if ($this->model->isTierNameExists($data['program_id'], $data['tier_name'])) {
            $errors[] = "A tier with this name already exists for the selected program.";
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            $programs = $this->model->getAllPrograms();
            require __DIR__ . '/../views/scholarship_tiers/create.php';
            return;
        }

        // Save and redirect
        $this->model->create($data);
        header("Location: index.php?controller=scholarship_tiers&action=index");
        exit;
    }

    /**
     * Show edit form for a specific tier
     */
    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $tier = $this->model->getById($id);
        
        if (!$tier) {
            header("Location: index.php?controller=scholarship_tiers&action=index");
            exit;
        }
        
        $programs = $this->model->getAllPrograms();
        require __DIR__ . '/../views/scholarship_tiers/edit.php';
    }

    /**
     * Handle form submission for updating tier
     */
    public function update(): void {
        $id = (int)($_POST['id'] ?? 0);
        
        // Read and sanitize input
        $data = [
            'program_id' => (int)($_POST['program_id'] ?? 0),
            'tier_name' => trim($_POST['tier_name'] ?? ''),
            'reward_amount' => trim($_POST['reward_amount'] ?? ''),
            'quota' => (int)($_POST['quota'] ?? 0),
        ];

        // Validate required fields
        $errors = [];
        if (empty($data['program_id'])) {
            $errors[] = "Scholarship program is required.";
        }
        if (empty($data['tier_name'])) {
            $errors[] = "Tier name is required.";
        }
        if (empty($data['reward_amount']) || !is_numeric($data['reward_amount'])) {
            $errors[] = "Reward amount must be a valid number.";
        } elseif ((float)$data['reward_amount'] <= 0) {
            $errors[] = "Reward amount must be greater than 0.";
        }
        if ($data['quota'] < 1) {
            $errors[] = "Quota must be at least 1.";
        }

        // Check for duplicate tier name in the same program (excluding current tier)
        if ($this->model->isTierNameExists($data['program_id'], $data['tier_name'], $id)) {
            $errors[] = "A tier with this name already exists for the selected program.";
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            $tier = $this->model->getById($id);
            $programs = $this->model->getAllPrograms();
            require __DIR__ . '/../views/scholarship_tiers/edit.php';
            return;
        }

        // Update and redirect
        $this->model->update($id, $data);
        header("Location: index.php?controller=scholarship_tiers&action=index");
        exit;
    }

    /**
     * Delete a scholarship tier (AJAX endpoint)
     */
    public function delete(): void {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid tier ID']);
                return;
            }
            
            $result = $this->model->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Tier deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete tier']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
