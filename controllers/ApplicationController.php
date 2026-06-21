<?php
// controllers/ApplicationController.php

require_once __DIR__ . '/../models/ApplicationModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class ApplicationController
{
    private ApplicationModel $model;

    public function __construct()
    {
        $this->model = new ApplicationModel();
    }

    /**
     * Display list of all applications
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']);
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        // Students can only view their own applications
        if ($isStudent) {
            $applications = $this->model->getByUserId((int)$currentUser['id']);
        } else {
            $applications = $this->model->getAll();
        }
        
        require __DIR__ . '/../views/applications/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_role(['admin', 'reviewer', 'student']); // Students can create their own applications
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        // For students, pre-populate with their own user_id
        if ($isStudent) {
            $students = []; // Students don't need to see other students
        } else {
            $students = $this->model->getAllStudents();
        }
        
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
        require_role(['admin', 'reviewer', 'student']); // Students can create applications
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        // Read and sanitize input
        $data = [
            'user_id' => $isStudent ? (int)$currentUser['id'] : (int)($_POST['user_id'] ?? 0), // Force student's own ID
            'tier_id' => (int)($_POST['tier_id'] ?? 0),
            'status' => $isStudent ? 'pending' : trim($_POST['status'] ?? 'pending'), // Students can only create pending applications
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
            if ($isStudent) {
                $students = [];
            } else {
                $students = $this->model->getAllStudents();
            }
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
        require_role(['admin', 'reviewer', 'staff', 'student']);
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        $id = (int)($_GET['id'] ?? 0);
        $application = $this->model->getById($id);
        
        if (!$application) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
        }
        
        // Students can only edit their own applications
        if ($isStudent && (int)$application['user_id'] !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to edit this application.');
            redirect(url('applications', 'index'));
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
        require_role(['admin', 'reviewer', 'student']); // Students can update their own applications (limited fields)
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        $id = (int)($_POST['id'] ?? 0);
        
        // Verify ownership for students
        $application = $this->model->getById($id);
        if (!$application) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
        }
        
        if ($isStudent && (int)$application['user_id'] !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to update this application.');
            redirect(url('applications', 'index'));
        }
        
        // Read and sanitize input
        $data = [
            'user_id' => $isStudent ? (int)$application['user_id'] : (int)($_POST['user_id'] ?? 0), // Students cannot change user_id
            'tier_id' => (int)($_POST['tier_id'] ?? 0),
            'status' => $isStudent ? $application['status'] : trim($_POST['status'] ?? 'pending'), // Students cannot change status
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
        require_role(['admin', 'reviewer']);
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
