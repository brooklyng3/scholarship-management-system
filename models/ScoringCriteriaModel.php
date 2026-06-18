<?php
// models/ScoringCriteriaModel.php

require_once __DIR__ . '/../config/database.php';

class ScoringCriteriaModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all scoring criteria with program titles
     * @return array List of all scoring criteria with joined program data
     */
    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT sc.*, sp.title as program_title
            FROM scoring_criteria sc
            LEFT JOIN scholarship_programs sp ON sc.program_id = sp.id
            ORDER BY sc.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single scoring criteria by ID
     * @param int $id Criteria ID
     * @return array|false Criteria data or false if not found
     */
    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT sc.*, sp.title as program_title
            FROM scoring_criteria sc
            LEFT JOIN scholarship_programs sp ON sc.program_id = sp.id
            WHERE sc.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new scoring criteria
     * @param array $data Criteria data (program_id, criteria_name, weight)
     * @return bool True on success
     */
    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO scoring_criteria 
            (program_id, criteria_name, weight) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['program_id'],
            $data['criteria_name'],
            $data['weight']
        ]);
    }

    /**
     * Update an existing scoring criteria
     * @param int $id Criteria ID
     * @param array $data Updated criteria data
     * @return bool True on success
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE scoring_criteria 
            SET program_id = ?, 
                criteria_name = ?, 
                weight = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['program_id'],
            $data['criteria_name'],
            $data['weight'],
            $id
        ]);
    }

    /**
     * Delete a scoring criteria
     * @param int $id Criteria ID
     * @return bool True on success
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM scoring_criteria 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get all scholarship programs for dropdown
     * @return array List of all programs with id and title
     */
    public function getAllPrograms(): array {
        $stmt = $this->pdo->query("
            SELECT id, title 
            FROM scholarship_programs 
            ORDER BY title ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the total weight currently assigned to a program
     */
    public function getTotalWeightForProgram(int $programId, int $excludeCriteriaId = 0): float {
        $sql = "SELECT SUM(weight) as total FROM scoring_criteria WHERE program_id = ?";
        $params = [$programId];

        if ($excludeCriteriaId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeCriteriaId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)($result['total'] ?? 0);
    }
}
