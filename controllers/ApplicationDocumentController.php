<?php
// controllers/ApplicationDocumentController.php

require_once __DIR__ . '/../models/ApplicationDocumentModel.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/functions.php';

class ApplicationDocumentController
{
    private ApplicationDocumentModel $model;
    
    // Constants for file validation and document types
    private const UPLOAD_DIR = __DIR__ . '/../public/uploads/docs/';
    private const FILE_SIZE_LIMIT = 5242880; // 5 MB
    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'png'];
    private const ALLOWED_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    private const DOCUMENT_TYPES = [
        'transcript' => 'Academic Transcript',
        'ielts' => 'IELTS Certificate',
        'research_paper' => 'Research Paper',
        'ctf_certificate' => 'CTF Certificate',
        'security_plus' => 'Security+ Certification',
        'poverty_certificate' => 'Poverty Certificate'
    ];

    public function __construct()
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->model = new ApplicationDocumentModel();
    }

    /**
     * Display upload form (students only, for their own applications)
     */
    public function create(): void
    {
        require_login();
        
        $applicationId = (int)($_GET['application_id'] ?? 0);
        $currentUser = current_user();
        
        // Validate application ID is a positive integer
        if ($applicationId <= 0) {
            set_flash('error', 'Invalid application ID.');
            redirect(url('applications', 'index'));
            return;
        }
        
        // Validate application exists and belongs to current user (if student)
        $ownerId = $this->model->getApplicationOwnerId($applicationId);
        if ($ownerId === false) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
            return;
        }
        
        // Only the student who owns the application can upload documents
        if ($currentUser['role'] === 'student' && $ownerId !== (int)$currentUser['id']) {
            set_flash('error', 'You do not have permission to upload documents to this application.');
            redirect(url('applications', 'index'));
            return;
        }
        
        $documentTypes = self::DOCUMENT_TYPES;
        $errors = [];
        
        require __DIR__ . '/../views/application_documents/create.php';
    }

    /**
     * Handle file upload submission
     */
    public function store(): void
    {
        require_login();
        verify_csrf();
        
        $applicationId = (int)($_POST['application_id'] ?? 0);
        $documentType = trim($_POST['document_type'] ?? '');
        $currentUser = current_user();
        
        $errors = [];
        
        // Validate application ID is a positive integer
        if ($applicationId <= 0) {
            $errors[] = 'Invalid application ID.';
        }
        
        // Validate application exists
        if (empty($errors) && !$this->model->applicationExists($applicationId)) {
            $errors[] = 'Application not found.';
        }
        
        // Validate ownership (if student)
        if (empty($errors)) {
            $ownerId = $this->model->getApplicationOwnerId($applicationId);
            if ($currentUser['role'] === 'student' && $ownerId !== (int)$currentUser['id']) {
                $errors[] = 'You do not have permission to upload documents to this application.';
            }
        }
        
        // Validate document type
        if (empty($documentType) || !array_key_exists($documentType, self::DOCUMENT_TYPES)) {
            $errors[] = 'Please select a valid document type.';
        }
        
        // Validate file upload
        if (empty($errors)) {
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'No file was uploaded. Please select a file.';
            } else {
                $file = $_FILES['document'];
                
                // Validate file size
                if ($file['size'] > self::FILE_SIZE_LIMIT) {
                    $errors[] = 'File size exceeds maximum limit of 5 MB.';
                }
                
                // Validate file extension
                $fileName = $file['name'];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    $errors[] = 'Invalid file type. Allowed types: PDF, JPG, PNG.';
                } else {
                    // Validate MIME type
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);
                    
                    if ($mimeType !== self::ALLOWED_MIME_TYPES[$extension]) {
                        $errors[] = "File type does not match extension. Please upload a valid {$extension} file.";
                    }
                }
            }
        }
        
        // If validation errors, re-render form
        if (!empty($errors)) {
            $documentTypes = self::DOCUMENT_TYPES;
            require __DIR__ . '/../views/application_documents/create.php';
            return;
        }
        
        // Generate unique filename
        $uniqueFilename = uniqid('doc_', true) . '_' . time() . '.' . $extension;
        
        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
        
        $destinationPath = self::UPLOAD_DIR . $uniqueFilename;
        
        // Validate file path to prevent directory traversal
        if (!$this->validateFilePath($destinationPath)) {
            $errors[] = 'Invalid file path detected.';
            $documentTypes = self::DOCUMENT_TYPES;
            require __DIR__ . '/../views/application_documents/create.php';
            return;
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            $errors[] = 'Failed to save uploaded file. Please try again.';
            $documentTypes = self::DOCUMENT_TYPES;
            require __DIR__ . '/../views/application_documents/create.php';
            return;
        }
        
        // Set file permissions to read-only
        chmod($destinationPath, 0644);
        
        // Save to database
        $fileUrl = 'uploads/docs/' . $uniqueFilename;
        $data = [
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'file_url' => $fileUrl
        ];
        
        if (!$this->model->create($data)) {
            // Database insert failed, clean up uploaded file
            unlink($destinationPath);
            $errors[] = 'Failed to save document record. Please try again.';
            $documentTypes = self::DOCUMENT_TYPES;
            require __DIR__ . '/../views/application_documents/create.php';
            return;
        }
        
        // Success
        set_flash('success', 'Document uploaded successfully.');
        redirect(url('applications', 'index'));
    }

    /**
     * View documents for an application
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff', 'student']);
        
        $currentUser = current_user();
        $isStudent = ($currentUser['role'] === 'student');
        
        $applicationId = (int)($_GET['application_id'] ?? 0);
        
        // Validate application ID is a positive integer
        if ($applicationId <= 0) {
            set_flash('error', 'Invalid application ID.');
            redirect(url('applications', 'index'));
            return;
        }
        
        // Validate application exists
        if (!$this->model->applicationExists($applicationId)) {
            set_flash('error', 'Application not found.');
            redirect(url('applications', 'index'));
            return;
        }
        
        // Students can only view documents for their own applications
        if ($isStudent) {
            $ownerId = $this->model->getApplicationOwnerId($applicationId);
            if ($ownerId !== (int)$currentUser['id']) {
                set_flash('error', 'You do not have permission to view these documents.');
                redirect(url('applications', 'index'));
                return;
            }
        }
        
        $documents = $this->model->getByApplicationId($applicationId);
        $documentTypeLabels = self::DOCUMENT_TYPES;
        
        require __DIR__ . '/../views/application_documents/index.php';
    }

    /**
     * Validate file path is within upload directory (prevent directory traversal)
     * @param string $filePath File path to validate
     * @return bool True if path is safe
     */
    private function validateFilePath(string $filePath): bool
    {
        // Get the directory part of the file path
        $fileDir = dirname($filePath);
        
        // Resolve to absolute paths
        $uploadDirReal = realpath(self::UPLOAD_DIR);
        
        // If upload directory doesn't exist, create it first for validation
        if (!$uploadDirReal && !is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
            $uploadDirReal = realpath(self::UPLOAD_DIR);
        }
        
        // Resolve the file directory (it should exist after we ensure UPLOAD_DIR exists)
        $fileDirReal = realpath($fileDir);
        
        // If paths still can't be resolved, check string prefix as fallback
        if (!$fileDirReal) {
            // Normalize paths for comparison
            $normalizedFilePath = str_replace('\\', '/', $filePath);
            $normalizedUploadDir = str_replace('\\', '/', self::UPLOAD_DIR);
            return str_starts_with($normalizedFilePath, $normalizedUploadDir);
        }
        
        // Ensure file is within upload directory
        return $uploadDirReal && $fileDirReal && str_starts_with($fileDirReal, $uploadDirReal);
    }
    /**
     * Handle AJAX deletion
     */
    public function delete(): void
    {
        require_role(['admin', 'reviewer']);
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            return;
        }

        $document = $this->model->getById($id);
        if (!$document) {
            echo json_encode(['success' => false, 'error' => 'Document not found']);
            return;
        }

        // Delete physical file from the public directory
        $absolutePath = __DIR__ . '/../public/' . $document['file_url'];
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }

        // Delete DB record
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    }
}
