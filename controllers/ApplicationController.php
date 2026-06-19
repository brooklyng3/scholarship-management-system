<?php
// controllers/ApplicationController.php

require_once __DIR__ . '/../models/ApplicationModel.php';

class ApplicationController
{
    private ApplicationModel $model;

    public function __construct()
    {
        // Start session and check RBAC
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo "Access Denied: Admin privileges required.";
            exit;
        }

        $this->model = new ApplicationModel();
    }

    /**
     * Display list of all applications
     */
    public function index(): void
    {
        $applications = $this->model->getAll();
        require __DIR__ . '/../views/applications/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $students = $this->model->getAllStudents();
        $tiers = $this->model->getAllTiers();
        $errors = [];
        $old = [];
        
        require __DIR__ . '/../views/applications/create.php';
    }

    /**
     * Handle form submission for creating new application
     */
    public function store(): void
    {
        // Read and sanitize input
        $data = [
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'tier_id' => (int)($_POST['tier_id'] ?? 0),
            'status' => trim($_POST['status'] ?? 'pending'),
        ];

        // Validate required fields
        $errors = [];
        
        if ($data['user_id'] === 0) {
            $errors[] = "Student is required.";
        }
        
        if ($data['tier_id'] === 0) {
            $errors[] = "Scholarship tier is required.";
        }
        
        // Validate status
        $validStatuses = ['pending', 'reviewing', 'approved', 'rejected'];
        if (!in_array($data['status'], $validStatuses, true)) {
            $errors[] = "Status must be one of: pending, reviewing, approved, rejected.";
        }

        // Check for duplicate application
        if (empty($errors) && $this->model->hasApplied($data['user_id'], $data['tier_id'])) {
            $errors[] = "This student has already applied to this scholarship tier.";
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            $students = $this->model->getAllStudents();
            $tiers = $this->model->getAllTiers();
            $old = $data;
            require __DIR__ . '/../views/applications/create.php';
            return;
        }

        // Save and redirect
        $this->model->create($data);
        header("Location: index.php?controller=applications&action=index");
        exit;
    }

    /**
     * Show edit form for a specific application
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $application = $this->model->getById($id);
        
        if (!$application) {
            header("Location: index.php?controller=applications&action=index");
            exit;
        }
        
        $students = $this->model->getAllStudents();
        $tiers = $this->model->getAllTiers();
        $errors = [];
        
        require __DIR__ . '/../views/applications/edit.php';
    }

    /**
     * Handle form submission for updating application
     */
    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        
        // Read and sanitize input
        $data = [
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'tier_id' => (int)($_POST['tier_id'] ?? 0),
            'status' => trim($_POST['status'] ?? 'pending'),
        ];

        // Validate required fields
        $errors = [];
        
        if ($data['user_id'] === 0) {
            $errors[] = "Student is required.";
        }
        
        if ($data['tier_id'] === 0) {
            $errors[] = "Scholarship tier is required.";
        }
        
        // Validate status
        $validStatuses = ['pending', 'reviewing', 'approved', 'rejected'];
        if (!in_array($data['status'], $validStatuses, true)) {
            $errors[] = "Status must be one of: pending, reviewing, approved, rejected.";
        }

        // Check for duplicate application (exclude current application)
        if (empty($errors) && $this->model->hasApplied($data['user_id'], $data['tier_id'], $id)) {
            $errors[] = "This student has already applied to this scholarship tier.";
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            $application = $this->model->getById($id);
            $students = $this->model->getAllStudents();
            $tiers = $this->model->getAllTiers();
            require __DIR__ . '/../views/applications/edit.php';
            return;
        }

        // Update and redirect
        $this->model->update($id, $data);
        header("Location: index.php?controller=applications&action=index");
        exit;
    }

    /**
     * Delete an application (AJAX)
     */
    public function delete(): void
    {
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        $success = $this->model->delete($id);
        
        echo json_encode(['success' => $success]);
        exit;
    }
}
