<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Model cho bảng `scholarship_programs`
 * Cột THỰC TẾ trong database.sql (KHÔNG có name/description/budget):
 *   id, title, scholarship_type (enum), start_date, end_date, status (enum)
 */
class ScholarshipProgram
{

    public const TYPES = [
        'internal_academic' => 'Internal Academic',
        'corporate_sponsor' => 'Corporate Sponsor',
        'social_support'    => 'Social Support',
    ];

    public const STATUSES = [
        'draft'  => 'Draft',
        'active' => 'Active',
        'closed' => 'Closed',
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM scholarship_programs ORDER BY id ASC")->fetchAll();
    }

    // ============================================================
    // [NEW] Search (by title/status) + Pagination support
    // ============================================================

    public function search(string $keyword, string $status, int $limit, int $offset): array
    {
        $sql = "SELECT * FROM scholarship_programs WHERE 1=1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND title LIKE :kw";
            $params['kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countSearch(string $keyword, string $status): int
    {
        $sql = "SELECT COUNT(*) FROM scholarship_programs WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $sql .= " AND title LIKE :kw";
            $params['kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM scholarship_programs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Validate business rule ngày: end_date phải sau (hoặc bằng) start_date.
     * Trả về chuỗi lỗi nếu có, hoặc null nếu hợp lệ.
     */
    public function validateDates(?string $startDate, ?string $endDate): ?string
    {
        if ($startDate && $endDate && strtotime($endDate) < strtotime($startDate)) {
            return 'Ngày kết thúc (end_date) phải sau hoặc bằng ngày bắt đầu (start_date).';
        }
        return null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO scholarship_programs 
                    (title, scholarship_type, start_date, end_date, status, min_gpa, min_training_score, created_at) 
                VALUES 
                    (:title, :scholarship_type, :start_date, :end_date, :status, :min_gpa, :min_training_score, NOW())";
                    
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'title'              => $data['title'],
            'scholarship_type'   => $data['scholarship_type'],
            // Handle empty date inputs gracefully as NULL values
            'start_date'         => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date'           => !empty($data['end_date']) ? $data['end_date'] : null,
            'status'             => $data['status'],
            
            // NEW: Bind parameters securely to neutralize injection vectors
            'min_gpa'            => $data['min_gpa'],
            'min_training_score' => $data['min_training_score']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        // Added min_gpa and min_training_score to the query criteria string
        $sql = "UPDATE scholarship_programs 
                SET title = :title,
                    scholarship_type = :scholarship_type,
                    start_date = :start_date,
                    end_date = :end_date,
                    status = :status,
                    min_gpa = :min_gpa,
                    min_training_score = :min_training_score
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'title'              => $data['title'],
            'scholarship_type'   => $data['scholarship_type'],
            'start_date'         => $data['start_date'] !== '' ? $data['start_date'] : null,
            'end_date'           => $data['end_date'] !== '' ? $data['end_date'] : null,
            'status'             => $data['status'],
            
            // Map the newly bound parameters to write safely to the database
            'min_gpa'            => $data['min_gpa'],
            'min_training_score' => $data['min_training_score'],
            'id'                 => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM scholarship_programs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
