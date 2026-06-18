<?php

require_once __DIR__ . '/../models/EvaluationScoreModel.php';

class EvaluationScoreController
{
    private EvaluationScoreModel $model;

    public function __construct()
    {
        session_start();
        
        // RBAC: Check if user is admin or reviewer
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'reviewer'])) {
            http_response_code(403);
            echo '<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>You do not have permission to access this resource.</p>
    <p>Only administrators and reviewers can access evaluation scores.</p>
</body>
</html>';
            exit;
        }

        $this->model = new EvaluationScoreModel();
    }

    /**
     * Display list of all evaluation scores
     */
    public function index(): void
    {
        $scores = $this->model->getAll();
        require_once __DIR__ . '/../views/evaluation_scores/index.php';
    }

    /**
     * Show form to create new evaluation score
     */
    public function create(): void
    {
        $applications = $this->model->getAllApplications();
        $criteria = $this->model->getAllCriteria();
        $reviewers = $this->model->getAllReviewers();
        
        require_once __DIR__ . '/../views/evaluation_scores/create.php';
    }

    /**
     * Store new evaluation score
     */
    public function store(): void
    {
        // Validate score
        if (!isset($_POST['score']) || !is_numeric($_POST['score'])) {
            $_SESSION['error'] = 'Score must be a numeric value.';
            header('Location: index.php?controller=evaluation_scores&action=create');
            exit;
        }

        $score = (float)$_POST['score'];
        
        // Business Logic: Validate score range (0.00 to 10.00)
        if ($score < 0.00 || $score > 10.00) {
            $_SESSION['error'] = 'Score must be between 0.00 and 10.00.';
            header('Location: index.php?controller=evaluation_scores&action=create');
            exit;
        }

        $data = [
            'application_id' => $_POST['application_id'],
            'criteria_id' => $_POST['criteria_id'],
            'reviewer_id' => $_POST['reviewer_id'],
            'score' => $score,
            'comments' => $_POST['comments'] ?? ''
        ];

        if ($this->model->create($data)) {
            $_SESSION['success'] = 'Evaluation score created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create evaluation score.';
        }

        header('Location: index.php?controller=evaluation_scores&action=index');
        exit;
    }

    /**
     * Show form to edit evaluation score
     */
    public function edit(): void
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Invalid evaluation score ID.';
            header('Location: index.php?controller=evaluation_scores&action=index');
            exit;
        }

        $score = $this->model->getById((int)$id);
        
        if (!$score) {
            $_SESSION['error'] = 'Evaluation score not found.';
            header('Location: index.php?controller=evaluation_scores&action=index');
            exit;
        }

        $applications = $this->model->getAllApplications();
        $criteria = $this->model->getAllCriteria();
        $reviewers = $this->model->getAllReviewers();
        
        require_once __DIR__ . '/../views/evaluation_scores/edit.php';
    }

    /**
     * Update evaluation score
     */
    public function update(): void
    {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Invalid evaluation score ID.';
            header('Location: index.php?controller=evaluation_scores&action=index');
            exit;
        }

        // Validate score
        if (!isset($_POST['score']) || !is_numeric($_POST['score'])) {
            $_SESSION['error'] = 'Score must be a numeric value.';
            header('Location: index.php?controller=evaluation_scores&action=edit&id=' . $id);
            exit;
        }

        $score = (float)$_POST['score'];
        
        // Business Logic: Validate score range (0.00 to 10.00)
        if ($score < 0.00 || $score > 10.00) {
            $_SESSION['error'] = 'Score must be between 0.00 and 10.00.';
            header('Location: index.php?controller=evaluation_scores&action=edit&id=' . $id);
            exit;
        }

        $data = [
            'application_id' => $_POST['application_id'],
            'criteria_id' => $_POST['criteria_id'],
            'reviewer_id' => $_POST['reviewer_id'],
            'score' => $score,
            'comments' => $_POST['comments'] ?? ''
        ];

        if ($this->model->update((int)$id, $data)) {
            $_SESSION['success'] = 'Evaluation score updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update evaluation score.';
        }

        header('Location: index.php?controller=evaluation_scores&action=index');
        exit;
    }

    /**
     * Delete evaluation score (AJAX)
     */
    public function delete(): void
    {
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        if ($this->model->delete((int)$id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete']);
        }
        exit;
    }
}
