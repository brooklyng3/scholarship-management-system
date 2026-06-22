<?php
// models/ApplicationModel.php

require_once __DIR__ . '/../config/database.php';

class ApplicationModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all applications with JOINs to fetch student name, tier name, and scholarship program title
     * @return array List of all applications
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            ORDER BY a.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated applications with optional search and filters
     * @param int $offset Starting position
     * @param int $limit Number of records
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return array
     */
    public function getAllPaginated(int $offset, int $limit, string $search = '', string $statusFilter = '', int $programFilter = 0): array
    {
        $sql = "
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR sp.title LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $sql .= " ORDER BY a.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all applications with optional filters
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return int
     */
    public function countAll(string $search = '', string $statusFilter = '', int $programFilter = 0): int
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR sp.title LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

    /**
     * Get a single application by ID
     * @param int $id Application ID
     * @return array|false Application data or false if not found
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM applications 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all applications for a specific user (student) with program title details
     * @param int $userId User ID
     * @return array List of applications for the user
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE a.user_id = ?
            ORDER BY a.id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated applications for a specific user with filters
     * @param int $userId User ID
     * @param int $offset Starting position
     * @param int $limit Number of records
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return array
     */
    public function getByUserIdPaginated(int $userId, int $offset, int $limit, string $search = '', string $statusFilter = '', int $programFilter = 0): array
    {
        $sql = "
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE a.user_id = ?
        ";
        
        $params = [$userId];
        
        if (!empty($search)) {
            $sql .= " AND sp.title LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $sql .= " ORDER BY a.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count applications for a specific user with filters
     * @param int $userId User ID
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return int
     */
    public function countByUserId(int $userId, string $search = '', string $statusFilter = '', int $programFilter = 0): int
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM applications a
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE a.user_id = ?
        ";
        
        $params = [$userId];
        
        if (!empty($search)) {
            $sql .= " AND sp.title LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

    /**
     * Create a new application
     * @param array $data Application data (user_id, tier_id, status)
     * @return int|false Last insert ID on success or false on failure
     */
    public function create(array $data): int|false
    {
        // Get profile_id for the student
        $profileId = $this->getProfileIdByUserId($data['user_id']);
        
        if (!$profileId) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO applications 
            (user_id, tier_id, profile_id, status) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([
            $data['user_id'],
            $data['tier_id'],
            $profileId,
            $data['status']
        ])) {
            return (int)$this->pdo->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update an existing application
     * @param int $id Application ID
     * @param array $data Updated application data
     * @return bool True on success
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE applications 
            SET user_id = ?, 
                tier_id = ?, 
                status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['user_id'],
            $data['tier_id'],
            $data['status'],
            $id
        ]);
    }

    /**
     * Delete an application
     * @param int $id Application ID
     * @return bool True on success
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM applications 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get all students (users with role 'student')
     * @return array List of students
     */
    public function getAllStudents(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, full_name, email 
            FROM users 
            WHERE role = 'student'
            ORDER BY full_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all scholarship programs with optional status filtering
     * @param bool $onlyOpen If true, only returns active programs
     * @return array
     */
    public function getAllPrograms(bool $onlyOpen = false): array
    {
        $sql = "
            SELECT 
                sp.id, 
                sp.title,
                sp.min_gpa,
                sp.min_training_score
            FROM scholarship_programs sp
        ";
        
        if ($onlyOpen) {
            $sql .= " WHERE sp.status = 'active' ";
        }
        
        $sql .= " ORDER BY sp.title";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get program by ID
     * @param int $programId
     * @return array|false
     */
    public function getProgramById(int $programId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, min_gpa, min_training_score, status 
            FROM scholarship_programs 
            WHERE id = ?
        ");
        $stmt->execute([$programId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get tiers for a specific program
     * @param int $programId
     * @return array
     */
    public function getTiersByProgramId(int $programId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, tier_name, min_gpa, min_training_score, reward_amount, quota
            FROM scholarship_tiers
            WHERE program_id = ?
            ORDER BY min_gpa DESC
        ");
        $stmt->execute([$programId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get student profile with metrics
     * @param int $userId
     * @return array|false
     */
    public function getStudentProfile(int $userId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, current_gpa, training_score, major, accumulated_credits
            FROM student_profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get tier information by tier ID
     * @param int $tierId
     * @return array|false
     */
    public function getTierInfoById(int $tierId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                st.id,
                st.tier_name,
                st.reward_amount,
                sp.title as program_title,
                sp.id as program_id
            FROM scholarship_tiers st
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE st.id = ?
        ");
        $stmt->execute([$tierId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a student has already applied to this specific tier
     * @param int $userId User ID of the student
     * @param int $tierId Scholarship tier ID
     * @param int $excludeId Application ID to exclude (for update operations)
     * @return bool True if the student has already applied
     */
    public function hasApplied(int $userId, int $tierId, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM applications 
            WHERE user_id = ? 
            AND tier_id = ? 
            AND id != ?
        ");
        $stmt->execute([$userId, $tierId, $excludeId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get profile_id for a user (student)
     * @param int $userId User ID
     * @return int|false Profile ID or false if not found
     */
    private function getProfileIdByUserId(int $userId): int|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM student_profiles 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : false;
    }

    /**
     * Get application with full details including student profile and thresholds
     * @param int $id Application ID
     * @return array|false Application with student and profile data
     */
    public function getApplicationWithDetails(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                u.email as student_email,
                sp.current_gpa as gpa,
                sp.major,
                sp.training_score,
                sp.accumulated_credits,
                st.tier_name,
                sch.title as program_title,
                sch.min_gpa,
                sch.min_training_score
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN student_profiles sp ON a.profile_id = sp.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sch ON st.program_id = sch.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get existing review for an application with a multi-table status fallback engine
     * @param int $applicationId Application ID
     * @return array|false Review data or false if not found/under evaluation
     */
    public function getReviewByApplicationId(int $applicationId): array|false
    {
        // Ensure table exists
        $this->ensureReviewsTableExists();
        
        // 1. Primary Check: Look into explicit feedback reviews table
        $stmt = $this->pdo->prepare("
            SELECT 
                ar.id,
                ar.application_id,
                ar.reviewer_id,
                ar.comment,
                ar.created_at,
                u.full_name as reviewer_name
            FROM application_reviews ar
            INNER JOIN users u ON ar.reviewer_id = u.id
            WHERE ar.application_id = ?
            ORDER BY ar.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$applicationId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($review) {
            return $review;
        }

        // 2. Fallback Engine: If no explicit feedback exists, check if application state is finalized
        $stmtApp = $this->pdo->prepare("
            SELECT 
                a.status,
                a.reviewer_id,
                a.applied_date,
                u.full_name as reviewer_name,
                sd.decision_date
            FROM applications a
            LEFT JOIN users u ON a.reviewer_id = u.id
            LEFT JOIN scholarship_decisions sd ON a.id = sd.application_id
            WHERE a.id = ?
        ");
        $stmtApp->execute([$applicationId]);
        $appData = $stmtApp->fetch(PDO::FETCH_ASSOC);

        // If the application is officially Approved or Rejected, display the evaluation card with the actual fields left blank
        if ($appData && in_array($appData['status'], ['approved', 'rejected'], true)) {
            return [
                'id'             => 0,
                'application_id' => $applicationId,
                'reviewer_id'    => $appData['reviewer_id'] ?? 0,
                'comment'        => '', // Left blank as requested
                'reviewer_name'  => $appData['reviewer_name'] ?? '', // Shows reviewer name if linked, otherwise blank
                'created_at'     => $appData['decision_date'] ?? $appData['applied_date'] ?? date('Y-m-d H:i:s')
            ];
        }

        return false;
    }

    /**
     * Save a review for an application
     * @param array $data Review data (application_id, reviewer_id, comment)
     * @return bool True on success
     */
    public function saveReview(array $data): bool
    {
        // Ensure table exists
        $this->ensureReviewsTableExists();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO application_reviews 
            (application_id, reviewer_id, comment) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            comment = VALUES(comment),
            created_at = NOW()
        ");
        
        return $stmt->execute([
            $data['application_id'],
            $data['reviewer_id'],
            $data['comment']
        ]);
    }

    /**
     * Ensure the application_reviews table exists
     * @return void
     */
    private function ensureReviewsTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS application_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                application_id INT NOT NULL,
                reviewer_id INT NOT NULL,
                comment TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_app_review (application_id),
                FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }
    /**
     * RE-01 FIX: Fetch only the applications explicitly assigned to a specific reviewer
     * @param int $reviewerId
     * @return array
     */
    public function getByReviewerId(int $reviewerId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE (a.reviewer_id = ? OR a.reviewer_id IS NULL)
            AND a.status IN ('pending', 'reviewing')
            ORDER BY a.id DESC
        ");
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated applications for a specific reviewer with filters
     * @param int $reviewerId Reviewer ID
     * @param int $offset Starting position
     * @param int $limit Number of records
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return array
     */
    public function getByReviewerIdPaginated(int $reviewerId, int $offset, int $limit, string $search = '', string $statusFilter = '', int $programFilter = 0): array
    {
        $sql = "
            SELECT 
                a.id,
                a.user_id,
                a.tier_id,
                a.status,
                a.applied_date,
                u.full_name as student_name,
                st.tier_name,
                sp.title as program_title
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE (a.reviewer_id = ? OR a.reviewer_id IS NULL)
            AND a.status IN ('pending', 'reviewing')
        ";
        
        $params = [$reviewerId];
        
        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR sp.title LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $sql .= " ORDER BY a.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count applications for a specific reviewer with filters
     * @param int $reviewerId Reviewer ID
     * @param string $search Search term
     * @param string $statusFilter Status filter
     * @param int $programFilter Program ID filter
     * @return int
     */
    public function countByReviewerId(int $reviewerId, string $search = '', string $statusFilter = '', int $programFilter = 0): int
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM applications a
            INNER JOIN users u ON a.user_id = u.id
            INNER JOIN scholarship_tiers st ON a.tier_id = st.id
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE (a.reviewer_id = ? OR a.reviewer_id IS NULL)
            AND a.status IN ('pending', 'reviewing')
        ";
        
        $params = [$reviewerId];
        
        if (!empty($search)) {
            $sql .= " AND (u.full_name LIKE ? OR sp.title LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($programFilter > 0) {
            $sql .= " AND sp.id = ?";
            $params[] = $programFilter;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }
}
