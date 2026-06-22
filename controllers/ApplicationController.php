<?php
// controllers/ApplicationController.php

require_once __DIR__ . '/../models/ApplicationModel.php';
require_once __DIR__ . '/../models/ApplicationDocumentModel.php';
require_once __DIR__ . '/../helpers/auth.php';

class ApplicationController
{
    private ApplicationModel $model;
    private ApplicationDocumentModel $documentModel;

    public function __construct()
    {
        $this->model = new ApplicationModel();
        $this->documentModel = new ApplicationDocumentModel();
    }

    /**
     * Display list of all applications with role isolation rules
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']);
        
        $currentUser = current_user();
        
        if ($currentUser['role'] === 'student') {
            // RESTORED: Students can ONLY see what they submitted themselves
            $applications = $this->model->getByUserId((int)$currentUser['id']);
        } elseif ($currentUser['role'] === 'reviewer') {
            $applications = $this->model->getByReviewerId((int)$currentUser['id']);
        } else {
            $applications = $this->model->getAll();
        }
        
        require __DIR__ . '/../views/applications/index.php';
    }

    /**
     * NEW: Secure Read-Only Details Viewer for Students & Staff
     */
    public function view(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']);
        
        $currentUser = current_user();
        $id = (int)($_GET['id'] ?? 0);
        $application = $this->model->getApplicationWithDetails($id);
        
        if (!$application) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
        }
        
        // SECURITY GATE: Prevent URL tampering. Students cannot view other students' applications.
        if ($currentUser['role'] === 'student' && (int)$application['user_id'] !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to view this application.');
            redirect(url('applications', 'index'));
        }
        
        $documents = $this->documentModel->getByApplicationId($id);
        $existingReview = $this->model->getReviewByApplicationId($id);
        
        require __DIR__ . '/../views/applications/view.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_role(['admin', 'student']); 
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        if ($isStudent) {
            $students = [[
                'id' => $currentUser['id'],
                'full_name' => $currentUser['full_name'], 
                'email' => $currentUser['email']
            ]];
        } else {
            $students = $this->model->getAllStudents();
        }
        
        // FIX: Pass the $isStudent flag. 
        // Students only see 'open' programs, Admins/Reviewers can see all for management.
        $tiers = $this->model->getAllTiers($isStudent); 
        
        $errors = [];
        $old = [];
        
        require __DIR__ . '/../views/applications/create.php';
    }

    /**
     * Handle form submission for creating new application
     */
    public function store(): void
    {
        require_role(['admin', 'student']); // Students can create applications
        
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

        // FIX: Backend defense check against tampering parameter manipulation
        if (empty($errors) && !$this->model->isTierAvailable($data['tier_id'])) {
            $errors[] = "The selected scholarship program is currently draft or unavailable.";
        }

        // Validate file upload
        if (!isset($_FILES['proof_document']) || $_FILES['proof_document']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Supporting document is required.";
        } elseif ($_FILES['proof_document']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed. Please try again.";
        } else {
            $file = $_FILES['proof_document'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileMimeType = mime_content_type($file['tmp_name']);
            
            if ($file['size'] > $maxSize) {
                $errors[] = "File size must be less than 5MB.";
            }
            
            if (!in_array($fileExtension, $allowedExtensions, true)) {
                $errors[] = "Only PDF, JPG, and PNG files are allowed.";
            }
            
            if (!in_array($fileMimeType, $allowedMimeTypes, true)) {
                $errors[] = "Invalid file type. Only PDF, JPG, and PNG files are allowed.";
            }
        }

        // On error, reload form with messages
        if (!empty($errors)) {
            if ($isStudent) {
                $students = [[
                    'id' => $currentUser['id'],
                    'full_name' => $currentUser['full_name'],
                    'email' => $currentUser['email']
                ]];
            } else {
                $students = $this->model->getAllStudents();
            }
            $tiers = $this->model->getAllTiers();
            $old = $data;
            require __DIR__ . '/../views/applications/create.php';
            return;
        }

        // Save application
        $applicationId = $this->model->create($data);
        
        if (!$applicationId) {
            set_flash('error', 'Failed to create application.');
            header("Location: index.php?controller=applications&action=create");
            exit;
        }

        // Handle file upload
        $file = $_FILES['proof_document'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueFileName = 'app_' . $applicationId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $uploadDir = __DIR__ . '/../public/uploads/docs/';
        $uploadPath = $uploadDir . $uniqueFileName;
        
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            set_flash('error', 'Application created but file upload failed.');
            header("Location: index.php?controller=applications&action=index");
            exit;
        }
        
        // Save document metadata
        $documentData = [
            'application_id' => $applicationId,
            'document_type' => 'proof',
            'file_url' => 'uploads/docs/' . $uniqueFileName
        ];
        
        if (!$this->documentModel->create($documentData)) {
            // 1. Clean up uploaded file
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            // 2. Roll back the application record so we don't leave an orphan row
            $this->model->delete($applicationId); 
            
            set_flash('error', 'Application creation failed because the document metadata could not be saved.');
            header("Location: index.php?controller=applications&action=index");
            exit;
        }
        
        set_flash('success', 'Application created successfully with supporting document.');
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

        // SECURITY GUARD: Reject edits on processed records
        if ($isStudent && $application['status'] !== 'pending') {
            set_flash('error', 'You can only edit applications that are currently pending.');
            redirect(url('applications', 'index'));
        }
        
        $students = $this->model->getAllStudents();
        $tiers = $this->model->getAllTiers();
        
        // RELATIONAL LAYER BINDING: Query document metadata array for presentation view
        $documents = $this->documentModel->getByApplicationId($id);
        $errors = [];
        
        require __DIR__ . '/../views/applications/edit.php';
    }

    /**
     * Handle form submission for updating application
     */
    /**
     * Handle form submission for updating application
     */
    public function update(): void
    {
        require_role(['admin', 'reviewer', 'student']); 
        
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

        // SECURITY GUARD: Prevent POST request parameter manipulation on processed records
        if ($isStudent && $application['status'] !== 'pending') {
            set_flash('error', 'You can only update applications that are currently pending.');
            redirect(url('applications', 'index'));
        }
        
        // Read and sanitize input
        $data = [
            'user_id' => $isStudent ? (int)$application['user_id'] : (int)($_POST['user_id'] ?? 0), 
            'tier_id' => (int)($_POST['tier_id'] ?? 0),
            'status' => $isStudent ? $application['status'] : trim($_POST['status'] ?? 'pending'), 
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

        // PRE-VALIDATION SLOTS: Validate a newly loaded file if present inside buffer stream
        $hasNewFile = (isset($_FILES['proof_document']) && $_FILES['proof_document']['error'] !== UPLOAD_ERR_NO_FILE);
        if ($hasNewFile && empty($errors)) {
            if ($_FILES['proof_document']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "File upload failed. Please try again.";
            } else {
                $file = $_FILES['proof_document'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fileMimeType = mime_content_type($file['tmp_name']);
                
                if ($file['size'] > $maxSize) {
                    $errors[] = "File size must be less than 5MB.";
                }
                if (!in_array($fileExtension, $allowedExtensions, true) || !in_array($fileMimeType, $allowedMimeTypes, true)) {
                    $errors[] = "Invalid file type. Only PDF, JPG, and PNG files are allowed.";
                }
            }
        }

        // On validation errors, re-load views with error bounds
        if (!empty($errors)) {
            $students = $this->model->getAllStudents();
            $tiers = $this->model->getAllTiers();
            $documents = $this->documentModel->getByApplicationId($id);
            require __DIR__ . '/../views/applications/edit.php';
            return;
        }

        // 1. HARD DRIVE CLEANUP: Process checked deletions
        if (isset($_POST['delete_documents']) && is_array($_POST['delete_documents'])) {
            foreach ($_POST['delete_documents'] as $docId) {
                $docId = (int)$docId;
                $docRecord = $this->documentModel->getById($docId);
                // Security verification bounding checking to make sure it belongs to current context
                if ($docRecord && (int)$docRecord['application_id'] === $id) {
                    $absolutePath = __DIR__ . '/../public/' . $docRecord['file_url'];
                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }
                    $this->documentModel->delete($docId);
                }
            }
        }

        // 2. FILE INGESTION PROCESSING: Move validated multipart buffers onto server paths
        if ($hasNewFile) {
            $file = $_FILES['proof_document'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uniqueFileName = 'app_' . $id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/../public/uploads/docs/';
            $uploadPath = $uploadDir . $uniqueFileName;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $documentData = [
                    'application_id' => $id,
                    'document_type' => 'proof',
                    'file_url' => 'uploads/docs/' . $uniqueFileName
                ];
                $this->documentModel->create($documentData);
            }
        }

        // 3. BASE TRANSACTION RESOLUTION: Finalize update changes onto the application record
        $this->model->update($id, $data);
        set_flash('success', 'Application parameters synchronized successfully.');
        header("Location: index.php?controller=applications&action=index");
        exit;
    }

    /**
     * Delete an application (AJAX)
     */
    // controllers/ApplicationController.php

    public function delete(): void
    {
        require_role(['admin', 'reviewer']);
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        // 1. Fetch and physically remove all attached documents from disk
        $documents = $this->documentModel->getByApplicationId($id);
        foreach ($documents as $doc) {
            $absolutePath = __DIR__ . '/../public/' . $doc['file_url'];
            if (file_exists($absolutePath)) {
                unlink($absolutePath); // Delete the file from public/uploads/docs/
            }
            // Remove document record from the database
            $this->documentModel->delete((int)$doc['id']);
        }
        
        // 2. Now it is safe to delete the parent application record
        $success = $this->model->delete($id);
        
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Display review form for an application
     */
    public function review(): void
    {
        require_role(['reviewer', 'admin']);
        
        // FIX: Define the current user variable up front so the security guard can use it
        $currentUser = current_user();
        
        $id = (int)($_GET['id'] ?? 0);
        $application = $this->model->getApplicationWithDetails($id);
        
        if (!$application) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
        }
        
        // SECURITY GUARD: Stop reviewers from accessing processed records
        if ($currentUser['role'] === 'reviewer' && in_array($application['status'], ['approved', 'rejected'], true)) {
            set_flash('error', 'Reviewers are not permitted to modify finalized applications.');
            redirect(url('applications', 'index'));
        }
        
        // Get uploaded documents
        $documents = $this->documentModel->getByApplicationId($id);
        
        // Get existing review if any
        $existingReview = $this->model->getReviewByApplicationId($id);
        
        // REMOVED from here since it's now at the top
        $errors = [];
        
        require __DIR__ . '/../views/applications/review.php';
    }

    /**
     * Handle review submission
     */
    public function submitReview(): void
    {
        require_role(['reviewer', 'admin']);
        
        $currentUser = current_user();
        $applicationId = (int)($_POST['application_id'] ?? 0);
        $score = trim($_POST['score'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');
        
        // Validate
        $errors = [];
        
        if ($applicationId === 0) {
            $errors[] = "Invalid application ID.";
        } else {
            // SECURITY GUARD: Verify the running database status before changing elements
            $currentApp = $this->model->getById($applicationId);
            if (!$currentApp) {
                $errors[] = "Application not found.";
            } elseif ($currentUser['role'] === 'reviewer' && in_array($currentApp['status'], ['approved', 'rejected'], true)) {
                $errors[] = "This application has already been finalized and can no longer be updated.";
            }
        }
        
        if (!is_numeric($score) || $score < 0 || $score > 100) {
            $errors[] = "Score must be a number between 0 and 100.";
        }
        
        if (empty($comment)) {
            $errors[] = "Comment is required.";
        }
        
        $validStatuses = ['pending', 'reviewing', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses, true)) {
            $errors[] = "Invalid status selected.";
        }
        
        // If errors, reload form
        if (!empty($errors)) {
            $application = $this->model->getApplicationWithDetails($applicationId);
            $documents = $this->documentModel->getByApplicationId($applicationId);
            $existingReview = $this->model->getReviewByApplicationId($applicationId);
            require __DIR__ . '/../views/applications/review.php';
            return;
        }
        
        // Sanitize inputs
        $score = (float)$score;
        $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
        
        // Save review
        $reviewData = [
            'application_id' => $applicationId,
            'reviewer_id' => (int)$currentUser['id'],
            'score' => $score,
            'comment' => $comment
        ];
        
        $reviewSaved = $this->model->saveReview($reviewData);
        
        // Update application status
        $statusData = ['status' => $status];
        $statusUpdated = $this->model->update($applicationId, array_merge(
            $this->model->getById($applicationId),
            $statusData
        ));
        
        if ($reviewSaved && $statusUpdated) {
            set_flash('success', 'Review submitted successfully.');
        } else {
            set_flash('error', 'Failed to submit review.');
        }
        
        redirect(url('applications', 'index'));
    }
}
