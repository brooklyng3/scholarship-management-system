<?php
// models/ApplicationDocumentModel.php

require_once __DIR__ . '/../config/database.php';

class ApplicationDocumentModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all documents for a specific application
     * @param int $applicationId Application ID
     * @return array List of documents ordered by uploaded_at DESC
     */
    public function getByApplicationId(int $applicationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, application_id, document_type, file_url, uploaded_at
            FROM application_documents
            WHERE application_id = ?
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new document record
     * @param array $data Document data (application_id, document_type, file_url)
     * @return bool True on success
     */
    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO application_documents 
            (application_id, document_type, file_url) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['application_id'],
            $data['document_type'],
            $data['file_url']
        ]);
    }

    /**
     * Check if an application exists
     * @param int $applicationId Application ID
     * @return bool True if application exists
     */
    public function applicationExists(int $applicationId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM applications 
            WHERE id = ?
        ");
        $stmt->execute([$applicationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get application owner's user_id
     * @param int $applicationId Application ID
     * @return int|false User ID or false if not found
     */
    public function getApplicationOwnerId(int $applicationId): int|false
    {
        $stmt = $this->pdo->prepare("
            SELECT user_id 
            FROM applications 
            WHERE id = ?
        ");
        $stmt->execute([$applicationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['user_id'] : false;
    }

    /**
     * Delete a document record (for cleanup on storage failure)
     * @param int $id Document ID
     * @return bool True on success
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM application_documents 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    /**
     * Get a specific document by ID
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM application_documents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
