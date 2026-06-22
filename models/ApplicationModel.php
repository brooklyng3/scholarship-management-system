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
     * Get scholarship tiers with optional status filtering
     * @param bool $onlyOpen If true, only returns tiers from open programs
     * @return array
     */
    public function getAllTiers(bool $onlyOpen = false): array
    {
        $sql = "
            SELECT 
                st.id, 
                st.tier_name,
                sp.title as program_title
            FROM scholarship_tiers st
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
        ";
        
        // Enforce the 'open' status filter if requested
        if ($onlyOpen) {
            $sql .= " WHERE sp.status = 'active' ";
        }
        
        $sql .= " ORDER BY sp.title, st.tier_name";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verify if a specific tier belongs to an active/open program
     * @param int $tierId
     * @return bool
     */
    public function isTierAvailable(int $tierId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM scholarship_tiers st
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            WHERE st.id = ? AND sp.status = 'active'
        ");
        $stmt->execute([$tierId]);
        return (int)$stmt->fetchColumn() > 0;
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
                sp.gpa,
                sp.year_level,
                sp.major,
                sp.training_score,       -- UPDATED: Pulls student's actual training score
                st.tier_name,
                sch.title as program_title,
                sch.min_gpa,              -- UPDATED: Pulls program eligibility requirement
                sch.min_training_score    -- UPDATED: Pulls program eligibility requirement
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
     * Get existing review for an application
     * @param int $applicationId Application ID
     * @return array|false Review data or false if not found
     */
    public function getReviewByApplicationId(int $applicationId): array|false
    {
        // Ensure table exists
        $this->ensureReviewsTableExists();
        
        $stmt = $this->pdo->prepare("
            SELECT 
                ar.id,
                ar.application_id,
                ar.reviewer_id,
                ar.score,
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save a review for an application
     * @param array $data Review data (application_id, reviewer_id, score, comment)
     * @return bool True on success
     */
    public function saveReview(array $data): bool
    {
        // Ensure table exists
        $this->ensureReviewsTableExists();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO application_reviews 
            (application_id, reviewer_id, score, comment) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            score = VALUES(score),
            comment = VALUES(comment),
            created_at = NOW()
        ");
        
        return $stmt->execute([
            $data['application_id'],
            $data['reviewer_id'],
            $data['score'],
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
                score DECIMAL(5,2) NOT NULL,
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
            WHERE a.reviewer_id = ?
            ORDER BY a.id DESC
        ");
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
