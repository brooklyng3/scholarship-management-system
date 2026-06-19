<?php
/**
 * ScholarshipDecisionModel
 * 
 * Handles all database operations for scholarship_decisions table.
 * Implements 1-to-1 relationship validation with applications.
 */

require_once __DIR__ . '/../config/database.php';

class ScholarshipDecisionModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all scholarship decisions with joined data
     * 
     * @return array Array of scholarship decisions with student names
     */
    public function getAll(): array
    {
        $sql = "SELECT 
                    sd.id,
                    sd.application_id,
                    sd.decision_status,
                    sd.awarded_amount,
                    sd.notes,
                    u.full_name as student_name
                FROM scholarship_decisions sd
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.student_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                ORDER BY sd.id DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a single scholarship decision by ID
     * 
     * @param int $id Decision ID
     * @return array|false Decision data or false if not found
     */
    public function getById(int $id): array|false
    {
        $sql = "SELECT * FROM scholarship_decisions WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new scholarship decision
     * 
     * @param array $data Decision data
     * @return bool Success status
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO scholarship_decisions 
                (application_id, decision_status, awarded_amount, notes) 
                VALUES (:application_id, :decision_status, :awarded_amount, :notes)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'application_id' => $data['application_id'],
            'decision_status' => $data['decision_status'],
            'awarded_amount' => $data['awarded_amount'],
            'notes' => $data['notes'] ?? ''
        ]);
    }

    /**
     * Update an existing scholarship decision
     * 
     * @param int $id Decision ID
     * @param array $data Updated decision data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE scholarship_decisions 
                SET application_id = :application_id,
                    decision_status = :decision_status,
                    awarded_amount = :awarded_amount,
                    notes = :notes
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'application_id' => $data['application_id'],
            'decision_status' => $data['decision_status'],
            'awarded_amount' => $data['awarded_amount'],
            'notes' => $data['notes'] ?? ''
        ]);
    }

    /**
     * Delete a scholarship decision
     * 
     * @param int $id Decision ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM scholarship_decisions WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get all applications for dropdown selection
     * 
     * @return array Array of applications with IDs and student names
     */
    public function getAllApplications(): array
    {
        $sql = "SELECT 
                    a.id,
                    u.full_name as student_name
                FROM applications a
                INNER JOIN student_profiles sp ON a.student_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                ORDER BY u.full_name ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Check if an application already has a decision (1-to-1 relationship)
     * 
     * @param int $applicationId Application ID to check
     * @param int $excludeId Decision ID to exclude from check (for updates)
     * @return bool True if application already has a decision
     */
    public function hasDecision(int $applicationId, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM scholarship_decisions 
                WHERE application_id = :application_id AND id != :exclude_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'application_id' => $applicationId,
            'exclude_id' => $excludeId
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
}
