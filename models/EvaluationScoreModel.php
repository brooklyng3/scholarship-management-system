<?php

require_once __DIR__ . '/../config/database.php';

class EvaluationScoreModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all evaluation scores with JOINs to fetch related data
     */
    public function getAll(): array
    {
        $sql = "SELECT 
                    es.id,
                    es.application_id,
                    es.criteria_id,
                    es.reviewer_id,
                    es.score,
                    es.comments,
                    sc.criteria_name,
                    u.full_name AS reviewer_name
                FROM evaluation_scores es
                LEFT JOIN scoring_criteria sc ON es.criteria_id = sc.id
                LEFT JOIN users u ON es.reviewer_id = u.id
                ORDER BY es.id DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a single evaluation score by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM evaluation_scores WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Create a new evaluation score
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO evaluation_scores (application_id, criteria_id, reviewer_id, score, comments)
                VALUES (:application_id, :criteria_id, :reviewer_id, :score, :comments)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'application_id' => $data['application_id'],
            'criteria_id' => $data['criteria_id'],
            'reviewer_id' => $data['reviewer_id'],
            'score' => $data['score'],
            'comments' => $data['comments'] ?? null
        ]);
    }

    /**
     * Update an existing evaluation score
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE evaluation_scores 
                SET application_id = :application_id,
                    criteria_id = :criteria_id,
                    reviewer_id = :reviewer_id,
                    score = :score,
                    comments = :comments
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'application_id' => $data['application_id'],
            'criteria_id' => $data['criteria_id'],
            'reviewer_id' => $data['reviewer_id'],
            'score' => $data['score'],
            'comments' => $data['comments'] ?? null
        ]);
    }

    /**
     * Delete an evaluation score
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM evaluation_scores WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get all applications for dropdown with student info
     */
    public function getAllApplications(): array
    {
        $sql = "SELECT a.id, a.user_id, u.full_name 
                FROM applications a
                JOIN users u ON a.user_id = u.id
                ORDER BY a.id DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get all scoring criteria for dropdown
     */
    public function getAllCriteria(): array
    {
        $sql = "SELECT id, criteria_name FROM scoring_criteria ORDER BY criteria_name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get all reviewers (users with role 'reviewer' or 'admin') for dropdown
     */
    public function getAllReviewers(): array
    {
        $sql = "SELECT id, full_name FROM users 
                WHERE role IN ('reviewer', 'admin') 
                ORDER BY full_name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
