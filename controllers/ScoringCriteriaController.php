<?php
// controllers/ScoringCriteriaController.php

require_once __DIR__ . '/../models/ScoringCriteriaModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class ScoringCriteriaController {
    private ScoringCriteriaModel $model;

    public function __construct() {
        $this->model = new ScoringCriteriaModel();
    }

    /**
     * Display list of all scoring criteria
     */
    public function index(): void {
        require_role(['admin', 'reviewer', 'staff']);
        $criteria = $this->model->getAll();
        require __DIR__ . '/../views/scoring_criteria/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void {
        require_role(['admin', 'reviewer']);
        $programs = $this->model->getAllPrograms();
        require __DIR__ . '/../views/scoring_criteria/create.php';
    }

    /**
     * Handle form submission for creating new criteria
     */
    public function store(): void {
        require_role(['admin', 'reviewer']);
        // Read and sanitize input
        $data = [
            'program_id' => trim($_POST['program_id'] ?? ''),
            'criteria_name' => trim($_POST['criteria_name'] ?? ''),
            'weight' => trim($_POST['weight'] ?? ''),
        ];

        // Validate required fields
        $errors = [];
        if (empty($data['program_id'])) {
            $errors[] = "Program is required.";
        }
        if (empty($data['criteria_name'])) {
            $errors[] = "Criteria name is required.";
        }
        if (empty($data['weight'])) {
            $errors[] = "Weight is required.";
        } else {
            // Validate weight is a number between 0.01 and 100.00
            if (!is_numeric($data['weight'])) {
                $errors[] = "Weight must be a valid number.";
            } else {
                $weight = (float)$data['weight'];
                if ($weight < 0.01 || $weight > 100.00) {
                    $errors[] = "Weight must be between 0.01 and 100.00.";
                } else {
                    // Business Logic Check: Ensure total weight doesn't exceed 100%
                    $currentTotal = $this->model->getTotalWeightForProgram((int)$data['program_id'], 0);
                    if (($currentTotal + $weight) > 100.00) {
                        $available = 100.00 - $currentTotal;
                        $errors[] = "Adding this criteria exceeds 100% for the program. You can only add up to {$available}%.";
                    }
                }
            }
        }
        

        // On error, reload form with messages
        if (!empty($errors)) {
            $programs = $this->model->getAllPrograms();
            require __DIR__ . '/../views/scoring_criteria/create.php';
            return;
        }

        // Save and redirect
        $this->model->create($data);
        header("Location: index.php?controller=scoring_criteria&action=index");
        exit;
    }

    /**
     * Show edit form for a specific criteria
     */
    public function edit(): void {
        require_role(['admin', 'reviewer', 'staff']);
        $id = (int)($_GET['id'] ?? 0);
        $criteria = $this->model->getById($id);
        
        if (!$criteria) {
            header("Location: index.php?controller=scoring_criteria&action=index");
            exit;
        }
        
        $programs = $this->model->getAllPrograms();
        require __DIR__ . '/../views/scoring_criteria/edit.php';
    }

    /**
     * Handle form submission for updating criteria
     */
    public function update(): void {
        require_role(['admin', 'reviewer']);
        $id = (int)($_POST['id'] ?? 0);
        
        // Read and sanitize input
        $data = [
            'program_id' => trim($_POST['program_id'] ?? ''),
            'criteria_name' => trim($_POST['criteria_name'] ?? ''),
            'weight' => trim($_POST['weight'] ?? ''),
        ];

        // Validate required fields
        $errors = [];
        if (empty($data['program_id'])) {
            $errors[] = "Program is required.";
        }
        if (empty($data['criteria_name'])) {
            $errors[] = "Criteria name is required.";
        }
        if (empty($data['weight'])) {
            $errors[] = "Weight is required.";
        } else {
            // Validate weight is a number between 0.01 and 100.00
            if (!is_numeric($data['weight'])) {
                $errors[] = "Weight must be a valid number.";
            } else {
                $weight = (float)$data['weight'];
                if ($weight < 0.01 || $weight > 100.00) {
                    $errors[] = "Weight must be between 0.01 and 100.00.";
                } else {
                    // Business Logic Check: Ensure total weight doesn't exceed 100%
                    $currentTotal = $this->model->getTotalWeightForProgram((int)$data['program_id'], $id ?? 0);
                    if (($currentTotal + $weight) > 100.00) {
                        $available = 100.00 - $currentTotal;
                        $errors[] = "Adding this criteria exceeds 100% for the program. You can only add up to {$available}%.";
                    }
                }
            }
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            $criteria = $this->model->getById($id);
            $programs = $this->model->getAllPrograms();
            require __DIR__ . '/../views/scoring_criteria/edit.php';
            return;
        }

        // Update and redirect
        $this->model->update($id, $data);
        header("Location: index.php?controller=scoring_criteria&action=index");
        exit;
    }

    /**
     * Delete a scoring criteria
     */
    public function delete(): void {
        require_role(['admin', 'reviewer']);
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        
        try {
            $this->model->delete($id);
            echo json_encode(['success' => true, 'message' => 'Criteria deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete criteria']);
        }
        
        exit;
    }
}
