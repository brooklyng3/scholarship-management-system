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
        
        // Pagination settings
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Search and filter parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        $programFilter = isset($_GET['program']) ? (int)$_GET['program'] : 0;
        
        // Get filtered applications based on role
        if ($currentUser['role'] === 'student') {
            $applications = $this->model->getByUserIdPaginated(
                (int)$currentUser['id'], 
                $offset, 
                $perPage, 
                $search, 
                $statusFilter, 
                $programFilter
            );
            $totalCount = $this->model->countByUserId((int)$currentUser['id'], $search, $statusFilter, $programFilter);
        } elseif ($currentUser['role'] === 'reviewer') {
            $applications = $this->model->getByReviewerIdPaginated(
                (int)$currentUser['id'], 
                $offset, 
                $perPage, 
                $search, 
                $statusFilter, 
                $programFilter
            );
            $totalCount = $this->model->countByReviewerId((int)$currentUser['id'], $search, $statusFilter, $programFilter);
        } else {
            $applications = $this->model->getAllPaginated($offset, $perPage, $search, $statusFilter, $programFilter);
            $totalCount = $this->model->countAll($search, $statusFilter, $programFilter);
        }
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $perPage);
        
        // Get all programs for filter dropdown
        $programs = $this->model->getAllPrograms();
        
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
        
        // FIX: Now fetch programs instead of tiers for selection
        $programs = $this->model->getAllPrograms($isStudent);
        
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
        
        // Read and sanitize input - now using program_id instead of tier_id
        $programId = (int)($_POST['program_id'] ?? 0);
        $userId = $isStudent ? (int)$currentUser['id'] : (int)($_POST['user_id'] ?? 0);

        // Validate required fields
        $errors = [];
        
        if ($userId === 0) {
            $errors[] = "Student is required.";
        }
        
        if ($programId === 0) {
            $errors[] = "Scholarship program is required.";
        }

        // Fetch student profile metrics
        $studentProfile = null;
        if (empty($errors)) {
            $studentProfile = $this->model->getStudentProfile($userId);
            if (!$studentProfile) {
                $errors[] = "Student profile not found.";
            }
        }

        // Fetch program entry requirements
        $program = null;
        if (empty($errors)) {
            $program = $this->model->getProgramById($programId);
            if (!$program) {
                $errors[] = "Scholarship program not found.";
            }
        }

        // AUTO-ROUTING LOGIC: Check eligibility and assign tier
        $assignedTierId = null;
        if (empty($errors)) {
            $studentGpa = (float)$studentProfile['current_gpa'];
            $studentTraining = (int)$studentProfile['training_score'];
            $programMinGpa = (float)$program['min_gpa'];
            $programMinTraining = (int)$program['min_training_score'];

            // First check: Does student meet program entry requirements?
            if ($studentGpa < $programMinGpa || $studentTraining < $programMinTraining) {
                $errors[] = "You do not meet the minimum requirements for this program. Required: GPA {$programMinGpa}, Training Score {$programMinTraining}. Your metrics: GPA {$studentGpa}, Training Score {$studentTraining}.";
            } else {
                // Fetch tiers for this program
                $tiers = $this->model->getTiersByProgramId($programId);
                
                if (count($tiers) === 0) {
                    $errors[] = "No tiers are configured for this program. Please contact an administrator.";
                } elseif (count($tiers) === 1) {
                    // Only one tier exists, assign to it
                    $assignedTierId = (int)$tiers[0]['id'];
                } else {
                    // Multiple tiers exist - sort and assign based on qualifications
                    // Sort to ensure Excellence Tier (highest requirements) comes first
                    usort($tiers, function($a, $b) {
                        return $b['min_gpa'] <=> $a['min_gpa']; // Descending order
                    });

                    // Find the best tier the student qualifies for
                    $assignedTierId = null;
                    foreach ($tiers as $tier) {
                        if ($studentGpa >= (float)$tier['min_gpa'] && 
                            $studentTraining >= (int)$tier['min_training_score']) {
                            $assignedTierId = (int)$tier['id'];
                            break;
                        }
                    }
                    
                    // If no tier matched, assign to the lowest tier (last in sorted array)
                    if ($assignedTierId === null) {
                        $assignedTierId = (int)$tiers[count($tiers) - 1]['id'];
                    }
                }
            }
        }

        // Check for duplicate application
        if (empty($errors) && $this->model->hasApplied($userId, $assignedTierId)) {
            $errors[] = "You have already applied to this scholarship tier.";
        }

        // Validate file upload
        if (!isset($_FILES['proof_documents']) || empty($_FILES['proof_documents']['name'][0])) {
            $errors[] = "At least one supporting document is required.";
        } else {
            // Validate each uploaded file
            $fileCount = count($_FILES['proof_documents']['name']);
            $maxSize = 5 * 1024 * 1024; // 5MB
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['proof_documents']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = "File upload failed for file: " . htmlspecialchars($_FILES['proof_documents']['name'][$i]);
                    continue;
                }
                
                $fileSize = $_FILES['proof_documents']['size'][$i];
                $fileTmpPath = $_FILES['proof_documents']['tmp_name'][$i];
                $fileName = $_FILES['proof_documents']['name'][$i];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileMimeType = mime_content_type($fileTmpPath);
                
                if ($fileSize > $maxSize) {
                    $errors[] = "File '" . htmlspecialchars($fileName) . "' exceeds 5MB limit.";
                }
                
                if (!in_array($fileExtension, $allowedExtensions, true)) {
                    $errors[] = "File '" . htmlspecialchars($fileName) . "' has invalid extension. Only PDF, JPG, and PNG files are allowed.";
                }
                
                if (!in_array($fileMimeType, $allowedMimeTypes, true)) {
                    $errors[] = "File '" . htmlspecialchars($fileName) . "' has invalid type. Only PDF, JPG, and PNG files are allowed.";
                }
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
            $programs = $this->model->getAllPrograms();
            $old = ['user_id' => $userId, 'program_id' => $programId];
            require __DIR__ . '/../views/applications/create.php';
            return;
        }

        // Save application with auto-assigned tier
        $data = [
            'user_id' => $userId,
            'tier_id' => $assignedTierId,
            'status' => $isStudent ? 'pending' : 'pending',
        ];

        $applicationId = $this->model->create($data);
        
        if (!$applicationId) {
            set_flash('error', 'Failed to create application.');
            header("Location: index.php?controller=applications&action=create");
            exit;
        }

        // Handle multiple file uploads
        $uploadedFiles = [];
        $fileCount = count($_FILES['proof_documents']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $_FILES['proof_documents']['name'][$i],
                'tmp_name' => $_FILES['proof_documents']['tmp_name'][$i],
                'size' => $_FILES['proof_documents']['size'][$i],
                'error' => $_FILES['proof_documents']['error'][$i]
            ];
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uniqueFileName = 'app_' . $applicationId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/../public/uploads/docs/';
            $uploadPath = $uploadDir . $uniqueFileName;
            
            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $uploadedFiles[] = [
                    'file_path' => $uploadPath,
                    'file_url' => 'uploads/docs/' . $uniqueFileName
                ];
            } else {
                // Rollback on failure
                foreach ($uploadedFiles as $uploadedFile) {
                    if (file_exists($uploadedFile['file_path'])) {
                        unlink($uploadedFile['file_path']);
                    }
                }
                $this->model->delete($applicationId);
                set_flash('error', 'File upload failed for: ' . htmlspecialchars($file['name']));
                header("Location: index.php?controller=applications&action=index");
                exit;
            }
        }
        
        // Save all document metadata
        $allDocumentsSaved = true;
        foreach ($uploadedFiles as $uploadedFile) {
            $documentData = [
                'application_id' => $applicationId,
                'document_type' => 'proof',
                'file_url' => $uploadedFile['file_url']
            ];
            
            if (!$this->documentModel->create($documentData)) {
                $allDocumentsSaved = false;
                break;
            }
        }
        
        if (!$allDocumentsSaved) {
            // Rollback: Clean up all uploaded files
            foreach ($uploadedFiles as $uploadedFile) {
                if (file_exists($uploadedFile['file_path'])) {
                    unlink($uploadedFile['file_path']);
                }
            }
            
            // Roll back the application record
            $this->model->delete($applicationId);
            
            set_flash('error', 'Application creation failed because document metadata could not be saved.');
            header("Location: index.php?controller=applications&action=index");
            exit;
        }
        
        set_flash('success', 'Application created successfully with ' . count($uploadedFiles) . ' document(s) uploaded.');
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
        
        // Fetch tier info for display (not for changing)
        $tierInfo = $this->model->getTierInfoById((int)$application['tier_id']);
        
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
        // NOTE: tier_id is PRESERVED from original application (no changes allowed)
        $data = [
            'user_id' => $isStudent ? (int)$application['user_id'] : (int)($_POST['user_id'] ?? 0), 
            'tier_id' => (int)$application['tier_id'], // LOCKED: Preserve original auto-assigned tier
            'status' => $isStudent ? $application['status'] : trim($_POST['status'] ?? 'pending'), 
        ];

        // Validate required fields
        $errors = [];
        
        if ($data['user_id'] === 0) {
            $errors[] = "Student is required.";
        }
        
        if ($data['tier_id'] === 0) {
            $errors[] = "Invalid tier assignment.";
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
            $tierInfo = $this->model->getTierInfoById((int)$application['tier_id']);
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
