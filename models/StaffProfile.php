<?php
require_once __DIR__ . '/../config/database.php';

class StaffProfile
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT sp.*, u.email, u.full_name AS user_full_name, u.role
                FROM staff_profiles sp
                INNER JOIN users u ON u.id = sp.user_id
                ORDER BY sp.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT sp.*, u.email, u.full_name AS user_full_name, u.role
                FROM staff_profiles sp
                INNER JOIN users u ON u.id = sp.user_id
                WHERE sp.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function staffCodeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM staff_profiles WHERE staff_code = :code AND id != :id");
            $stmt->execute(['code' => $code, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM staff_profiles WHERE staff_code = :code");
            $stmt->execute(['code' => $code]);
        }
        return (bool) $stmt->fetch();
    }

    /**
     * Lấy các user có role = 'admin' hoặc 'reviewer' nhưng CHƯA có hồ sơ nhân viên.
     * Dùng để đổ vào dropdown khi tạo hồ sơ mới.
     */
    public function getStaffUsersWithoutProfile(): array
    {
        $sql = "SELECT u.id, u.email, u.full_name, u.role
                FROM users u
                WHERE u.role IN ('admin', 'reviewer')
                  AND u.id NOT IN (SELECT user_id FROM staff_profiles)
                ORDER BY u.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO staff_profiles (user_id, staff_code, department, updated_at)
                VALUES (:user_id, :staff_code, :department, NOW())";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id'    => $data['user_id'],
            'staff_code' => $data['staff_code'],
            'department' => $data['department'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE staff_profiles
                SET staff_code = :staff_code, department = :department, updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'staff_code' => $data['staff_code'],
            'department' => $data['department'],
            'id'         => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM staff_profiles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
