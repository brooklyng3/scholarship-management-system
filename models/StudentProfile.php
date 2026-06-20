<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Model cho bảng `student_profiles` (Subtype của users, role = student)
 * Cột: id, user_id, student_code, full_name, major, current_gpa,
 *      accumulated_credits, conduct_score, updated_at
 */
class StudentProfile
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy danh sách hồ sơ sinh viên kèm email từ bảng users */
    public function getAll(): array
    {
        $sql = "SELECT sp.*, u.email
                FROM student_profiles sp
                INNER JOIN users u ON u.id = sp.user_id
                ORDER BY sp.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    // ============================================================
    // [NEW] Search + Pagination support
    // ============================================================

    public function search(string $keyword, int $limit, int $offset): array
    {
        $sql = "SELECT sp.*, u.email
                FROM student_profiles sp
                INNER JOIN users u ON u.id = sp.user_id
                WHERE sp.student_code LIKE :kw1 OR sp.full_name LIKE :kw2 OR sp.major LIKE :kw3
                ORDER BY sp.id ASC
                LIMIT :limit OFFSET :offset";
        $kw = '%' . $keyword . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kw1', $kw);
        $stmt->bindValue(':kw2', $kw);
        $stmt->bindValue(':kw3', $kw);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countSearch(string $keyword): int
    {
        $sql = "SELECT COUNT(*) FROM student_profiles
                WHERE student_code LIKE :kw1 OR full_name LIKE :kw2 OR major LIKE :kw3";
        $kw = '%' . $keyword . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kw1' => $kw, 'kw2' => $kw, 'kw3' => $kw]);
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT sp.*, u.email
                FROM student_profiles sp
                INNER JOIN users u ON u.id = sp.user_id
                WHERE sp.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM student_profiles WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function studentCodeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE student_code = :code AND id != :id");
            $stmt->execute(['code' => $code, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE student_code = :code");
            $stmt->execute(['code' => $code]);
        }
        return (bool) $stmt->fetch();
    }

    /**
     * Lấy các user có role = 'student' nhưng CHƯA có hồ sơ sinh viên.
     * Dùng để đổ vào dropdown khi tạo hồ sơ mới.
     */
    public function getStudentUsersWithoutProfile(): array
    {
        $sql = "SELECT u.id, u.email, u.full_name
                FROM users u
                WHERE u.role = 'student'
                  AND u.id NOT IN (SELECT user_id FROM student_profiles)
                ORDER BY u.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO student_profiles
                    (user_id, student_code, full_name, major, current_gpa, accumulated_credits, conduct_score, updated_at)
                VALUES
                    (:user_id, :student_code, :full_name, :major, :current_gpa, :accumulated_credits, :conduct_score, NOW())";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'user_id'             => $data['user_id'],
            'student_code'       => $data['student_code'],
            'full_name'           => $data['full_name'],
            'major'               => $data['major'],
            'current_gpa'         => $data['current_gpa'] !== '' ? $data['current_gpa'] : null,
            'accumulated_credits' => $data['accumulated_credits'] !== '' ? $data['accumulated_credits'] : null,
            'conduct_score'       => $data['conduct_score'] !== '' ? $data['conduct_score'] : null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE student_profiles
                SET student_code = :student_code,
                    full_name = :full_name,
                    major = :major,
                    current_gpa = :current_gpa,
                    accumulated_credits = :accumulated_credits,
                    conduct_score = :conduct_score,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'student_code'        => $data['student_code'],
            'full_name'           => $data['full_name'],
            'major'               => $data['major'],
            'current_gpa'         => $data['current_gpa'] !== '' ? $data['current_gpa'] : null,
            'accumulated_credits' => $data['accumulated_credits'] !== '' ? $data['accumulated_credits'] : null,
            'conduct_score'       => $data['conduct_score'] !== '' ? $data['conduct_score'] : null,
            'id'                  => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM student_profiles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
