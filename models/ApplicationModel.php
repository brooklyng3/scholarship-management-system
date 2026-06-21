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
            WHERE st.id = ? AND sp.status = 'open'
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
}
