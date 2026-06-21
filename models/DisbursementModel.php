<?php
/**
 * Disbursement Model
 * 
 * Handles all database operations for disbursements table.
 * Implements pagination, search, and filtering capabilities.
 */

require_once __DIR__ . '/../config/database.php';

class DisbursementModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Search disbursements with filtering and pagination
     * 
     * @param array $filters Search filters (student_name, status)
     * @param int $limit Number of records per page
     * @param int $offset Starting offset for pagination
     * @return array Array of disbursements with joined data
     */
    public function search(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT 
                    d.id,
                    d.decision_id,
                    d.amount_paid,
                    d.payment_method,
                    d.status,
                    d.payment_date,
                    u.full_name as student_name,
                    sp.student_code,
                    sd.awarded_amount as decision_amount,
                    sd.final_status
                FROM disbursements d
                INNER JOIN scholarship_decisions sd ON d.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        // Filter by student name or student code
        if (!empty($filters['student_name'])) {
            $sql .= " AND (u.full_name LIKE :student_name OR sp.student_code LIKE :student_code)";
            $params['student_name'] = '%' . $filters['student_name'] . '%';
            $params['student_code'] = '%' . $filters['student_name'] . '%';
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY d.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind search parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        // Bind pagination parameters
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total disbursements matching search filters
     * 
     * @param array $filters Search filters (student_name, status)
     * @return int Total count of matching records
     */
    public function countSearch(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) 
                FROM disbursements d
                INNER JOIN scholarship_decisions sd ON d.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        // Filter by student name or student code
        if (!empty($filters['student_name'])) {
            $sql .= " AND (u.full_name LIKE :student_name OR sp.student_code LIKE :student_code)";
            $params['student_name'] = '%' . $filters['student_name'] . '%';
            $params['student_code'] = '%' . $filters['student_name'] . '%';
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params['status'] = $filters['status'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get a single disbursement by ID with joined data
     * 
     * @param int $id Disbursement ID
     * @return array|false Disbursement data or false if not found
     */
    public function getById(int $id): array|false
    {
        $sql = "SELECT 
                    d.*,
                    u.full_name as student_name,
                    sp.student_code,
                    sd.awarded_amount as decision_amount
                FROM disbursements d
                INNER JOIN scholarship_decisions sd ON d.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE d.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new disbursement record
     * 
     * @param array $data Disbursement data
     * @return bool Success status
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO disbursements 
                (decision_id, amount_paid, payment_method, status, payment_date) 
                VALUES (:decision_id, :amount_paid, :payment_method, :status, :payment_date)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'decision_id' => $data['decision_id'],
            'amount_paid' => $data['amount_paid'],
            'payment_method' => $data['payment_method'],
            'status' => $data['status'],
            'payment_date' => $data['payment_date'] ?? null
        ]);
    }

    /**
     * Update an existing disbursement record
     * 
     * @param int $id Disbursement ID
     * @param array $data Updated disbursement data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE disbursements 
                SET decision_id = :decision_id,
                    amount_paid = :amount_paid,
                    payment_method = :payment_method,
                    status = :status,
                    payment_date = :payment_date
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'decision_id' => $data['decision_id'],
            'amount_paid' => $data['amount_paid'],
            'payment_method' => $data['payment_method'],
            'status' => $data['status'],
            'payment_date' => $data['payment_date'] ?? null
        ]);
    }

    /**
     * Delete a disbursement record
     * 
     * @param int $id Disbursement ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM disbursements WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get all approved scholarship decisions for dropdown selection
     * 
     * @return array Array of decisions with IDs and student names
     */
    public function getAllApprovedDecisions(): array
    {
        $sql = "SELECT 
                    sd.id,
                    sd.awarded_amount,
                    u.full_name as student_name,
                    sp.student_code
                FROM scholarship_decisions sd
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sd.final_status = 'approved'
                ORDER BY u.full_name ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
