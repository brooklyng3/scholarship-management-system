<?php

require_once __DIR__ . '/../config/database.php';

/**
 * EligibilityRuleModel
 * 
 * Handles database operations for eligibility_rules table.
 * Implements 1-to-1 relationship with scholarship_tiers.
 */
class EligibilityRuleModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all eligibility rules with tier information
     * 
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT er.id, er.tier_id, er.rules_json, st.tier_name 
                FROM eligibility_rules er
                INNER JOIN scholarship_tiers st ON er.tier_id = st.id
                ORDER BY er.id DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get eligibility rule by ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getById(int $id)
    {
        $sql = "SELECT er.id, er.tier_id, er.rules_json, st.tier_name 
                FROM eligibility_rules er
                INNER JOIN scholarship_tiers st ON er.tier_id = st.id
                WHERE er.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create new eligibility rule
     * 
     * @param int $tierId
     * @param string $rulesJson
     * @return bool
     */
    public function create(int $tierId, string $rulesJson): bool
    {
        $sql = "INSERT INTO eligibility_rules (tier_id, rules_json) 
                VALUES (:tier_id, :rules_json)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'tier_id' => $tierId,
            'rules_json' => $rulesJson
        ]);
    }

    /**
     * Update existing eligibility rule
     * 
     * @param int $id
     * @param int $tierId
     * @param string $rulesJson
     * @return bool
     */
    public function update(int $id, int $tierId, string $rulesJson): bool
    {
        $sql = "UPDATE eligibility_rules 
                SET tier_id = :tier_id, rules_json = :rules_json 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'tier_id' => $tierId,
            'rules_json' => $rulesJson
        ]);
    }

    /**
     * Delete eligibility rule
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM eligibility_rules WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get all scholarship tiers for dropdown
     * 
     * @return array
     */
    public function getAllTiers(): array
    {
        $sql = "SELECT id, tier_name FROM scholarship_tiers ORDER BY tier_name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Check if a tier already has an eligibility rule assigned
     * Enforces 1-to-1 relationship between tiers and rules
     * 
     * @param int $tierId
     * @param int $excludeId Optional ID to exclude from check (for updates)
     * @return bool
     */
    public function hasRule(int $tierId, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM eligibility_rules 
                WHERE tier_id = :tier_id";
        
        if ($excludeId > 0) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = ['tier_id' => $tierId];
        
        if ($excludeId > 0) {
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
