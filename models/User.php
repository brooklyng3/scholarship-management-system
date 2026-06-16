<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy toàn bộ user, có thể lọc theo role */
    public function getAll(?string $role = null): array
    {
        if ($role) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE role = :role ORDER BY id ASC");
            $stmt->execute(['role' => $role]);
        } else {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY id ASC");
        }
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
        }
        return (bool) $stmt->fetch();
    }

    /**
     * Tạo user mới.
     * $data['password'] là mật khẩu thô, sẽ được hash trước khi lưu.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO users (email, password_hash, full_name, role, created_at)
                VALUES (:email, :password_hash, :full_name, :role, NOW())";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'full_name'     => $data['full_name'],
            'role'          => $data['role'],
        ]);
    }
    /**
     * Cập nhật user.
     * Nếu $data['password'] không rỗng thì cập nhật lại password_hash.
     */
    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $sql = "UPDATE users
                    SET email = :email, password_hash = :password_hash, full_name = :full_name, role = :role
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'email'         => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'full_name'     => $data['full_name'],
                'role'          => $data['role'],
                'id'            => $id,
            ]);
        }

        $sql = "UPDATE users SET email = :email, full_name = :full_name, role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'role'      => $data['role'],
            'id'        => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Đếm số bản ghi liên quan (profile) - dùng để cảnh báo khi xóa */
    public function hasProfile(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = :id
                                    UNION SELECT id FROM staff_profiles WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        return (bool) $stmt->fetch();
    }
}
