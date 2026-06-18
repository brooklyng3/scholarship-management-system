<?php
// models/ScholarshipTierModel.php

require_once __DIR__ . '/../config/database.php';

class ScholarshipTierModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all scholarship tiers with their program titles
     * @return array List of all scholarship tiers joined with program data
     */
    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT 
                st.id,
                st.program_id,
                st.tier_name,
                st.reward_amount,
                st.quota,
                sp.title AS program_title
            FROM scholarship_tiers st
            INNER JOIN scholarship_programs sp ON st.program_id = sp.id
            ORDER BY st.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single scholarship tier by ID
     * @param int $id Tier ID
     * @return array|false Tier data or false if not found
     */
    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT * FROM scholarship_tiers 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new scholarship tier
     * @param array $data Tier data (program_id, tier_name, reward_amount, quota)
     * @return bool True on success
     */
    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO scholarship_tiers 
            (program_id, tier_name, reward_amount, quota) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['program_id'],
            $data['tier_name'],
            $data['reward_amount'],
            $data['quota']
        ]);
    }

    /**
     * Update an existing scholarship tier
     * @param int $id Tier ID
     * @param array $data Updated tier data
     * @return bool True on success
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE scholarship_tiers 
            SET program_id = ?, 
                tier_name = ?, 
                reward_amount = ?, 
                quota = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['program_id'],
            $data['tier_name'],
            $data['reward_amount'],
            $data['quota'],
            $id
        ]);
    }

    /**
     * Delete a scholarship tier
     * @param int $id Tier ID
     * @return bool True on success
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM scholarship_tiers 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get all scholarship programs for dropdown menu
     * @return array List of programs with id and title
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
     * Check if a tier name already exists for a specific program
     * @param int $programId The scholarship program ID
     * @param string $tierName The tier name to check
     * @param int $excludeId Optional ID to exclude (for update operations)
     * @return bool True if tier name exists, false otherwise
     */
    public function isTierNameExists(int $programId, string $tierName, int $excludeId = 0): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM scholarship_tiers 
            WHERE program_id = ? 
            AND tier_name = ? 
            AND id != ?
        ");
        $stmt->execute([$programId, $tierName, $excludeId]);
        return $stmt->fetchColumn() > 0;
    }
}
