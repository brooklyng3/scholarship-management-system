<?php
/**
 * AwardCertificate Model
 * 
 * Handles all database operations for award_certificates table.
 * Implements pagination, search, and filtering capabilities.
 */

require_once __DIR__ . '/../config/database.php';

class AwardCertificate
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Search award certificates with filtering and pagination
     * 
     * @param array $filters Search filters (certificate_code, student_name, status)
     * @param int $limit Number of records per page
     * @param int $offset Starting offset for pagination
     * @return array Array of certificates with joined data
     */
    public function search(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT 
                    ac.id,
                    ac.decision_id,
                    ac.certificate_code,
                    ac.issue_date,
                    ac.pdf_url,
                    u.full_name as student_name,
                    sp.student_code,
                    sd.granted_amount as awarded_amount,
                    sd.decision_status as decision_status
                FROM award_certificates ac
                INNER JOIN scholarship_decisions sd ON ac.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        // Filter by certificate code
        if (!empty($filters['certificate_code'])) {
            $sql .= " AND ac.certificate_code LIKE :certificate_code";
            $params['certificate_code'] = '%' . $filters['certificate_code'] . '%';
        }

        // Filter by student name or student code
        if (!empty($filters['student_name'])) {
            $sql .= " AND (u.full_name LIKE :student_name OR sp.student_code LIKE :student_code)";
            $params['student_name'] = '%' . $filters['student_name'] . '%';
            $params['student_code'] = '%' . $filters['student_name'] . '%';
        }

        $sql .= " ORDER BY ac.id DESC LIMIT :limit OFFSET :offset";
        
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
     * Count total certificates matching search filters
     * 
     * @param array $filters Search filters (certificate_code, student_name, status)
     * @return int Total count of matching records
     */
    public function countSearch(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) 
                FROM award_certificates ac
                INNER JOIN scholarship_decisions sd ON ac.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        // Filter by certificate code
        if (!empty($filters['certificate_code'])) {
            $sql .= " AND ac.certificate_code LIKE :certificate_code";
            $params['certificate_code'] = '%' . $filters['certificate_code'] . '%';
        }

        // Filter by student name or student code
        if (!empty($filters['student_name'])) {
            $sql .= " AND (u.full_name LIKE :student_name OR sp.student_code LIKE :student_code)";
            $params['student_name'] = '%' . $filters['student_name'] . '%';
            $params['student_code'] = '%' . $filters['student_name'] . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get a single certificate by ID with joined data
     * 
     * @param int $id Certificate ID
     * @return array|false Certificate data or false if not found
     */
    public function getById(int $id): array|false
    {
        $sql = "SELECT 
                    ac.*,
                    u.full_name as student_name,
                    sp.student_code,
                    sd.granted_amount as awarded_amount
                FROM award_certificates ac
                INNER JOIN scholarship_decisions sd ON ac.decision_id = sd.id
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE ac.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Check if certificate code already exists (for uniqueness validation)
     * 
     * @param string $certificateCode Certificate code to check
     * @param int|null $excludeId Exclude this ID from check (for update validation)
     * @return bool True if code exists, false otherwise
     */
    public function certificateCodeExists(string $certificateCode, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM award_certificates WHERE certificate_code = :code";
        $params = ['code' => $certificateCode];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new certificate record
     * 
     * @param array $data Certificate data
     * @return bool Success status
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO award_certificates 
                (decision_id, certificate_code, issue_date, pdf_url) 
                VALUES (:decision_id, :certificate_code, :issue_date, :pdf_url)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'decision_id' => $data['decision_id'],
            'certificate_code' => $data['certificate_code'],
            'issue_date' => $data['issue_date'] ?? null,
            'pdf_url' => $data['pdf_url']
        ]);
    }

    /**
     * Update an existing certificate record
     * 
     * @param int $id Certificate ID
     * @param array $data Updated certificate data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE award_certificates 
                SET decision_id = :decision_id,
                    certificate_code = :certificate_code,
                    issue_date = :issue_date,
                    pdf_url = :pdf_url
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'decision_id' => $data['decision_id'],
            'certificate_code' => $data['certificate_code'],
            'issue_date' => $data['issue_date'] ?? null,
            'pdf_url' => $data['pdf_url']
        ]);
    }

    /**
     * Delete a certificate record
     * 
     * @param int $id Certificate ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM award_certificates WHERE id = :id";
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
                    sd.granted_amount as awarded_amount,
                    u.full_name as student_name,
                    sp.student_code
                FROM scholarship_decisions sd
                INNER JOIN applications a ON sd.application_id = a.id
                INNER JOIN student_profiles sp ON a.profile_id = sp.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sd.decision_status = 'approved'
                ORDER BY u.full_name ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
