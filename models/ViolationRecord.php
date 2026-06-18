<?php
require_once __DIR__ . '/../config/database.php';

class ViolationRecord
{
    public const TYPES = [
        'fee_debt'     => 'Nợ học phí',
        'discipline'   => 'Vi phạm kỷ luật',
        'library_debt' => 'Nợ thư viện',
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT vr.*, u.full_name, u.email
                FROM violation_records vr
                INNER JOIN users u ON u.id = vr.user_id
                ORDER BY vr.recorded_date DESC, vr.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT vr.*, u.full_name, u.email
                FROM violation_records vr
                INNER JOIN users u ON u.id = vr.user_id
                WHERE vr.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /** Kiểm tra một user có đang nằm trong danh sách đen không (dùng cho việc loại hồ sơ tự động) */
    public function hasActiveViolation(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM violation_records WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return (bool) $stmt->fetch();
    }

    /** Lấy danh sách user (tất cả role) để chọn khi tạo bản ghi vi phạm */
    public function getAllUsers(): array
    {
        return $this->db->query("SELECT id, full_name, email, role FROM users ORDER BY full_name ASC")->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO violation_records (user_id, violation_type, description, recorded_date)
                VALUES (:user_id, :violation_type, :description, :recorded_date)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id'        => $data['user_id'],
            'violation_type' => $data['violation_type'],
            'description'    => $data['description'],
            'recorded_date'  => $data['recorded_date'] !== '' ? $data['recorded_date'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE violation_records
                SET user_id = :user_id,
                    violation_type = :violation_type,
                    description = :description,
                    recorded_date = :recorded_date
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id'        => $data['user_id'],
            'violation_type' => $data['violation_type'],
            'description'    => $data['description'],
            'recorded_date'  => $data['recorded_date'] !== '' ? $data['recorded_date'] : null,
            'id'             => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM violation_records WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
