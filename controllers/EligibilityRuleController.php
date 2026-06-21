<?php

require_once __DIR__ . '/../models/EligibilityRuleModel.php';
require_once __DIR__ . '/../helpers/auth.php';

/**
 * EligibilityRuleController
 * 
 * Handles HTTP requests for eligibility rules CRUD operations.
 * Implements RBAC (admin only), JSON validation, and 1-to-1 relationship enforcement.
 */
class EligibilityRuleController
{
    private EligibilityRuleModel $model;

    public function __construct()
    {
        $this->model = new EligibilityRuleModel();
    }

    /**
     * Display all eligibility rules
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff']);
        $rules = $this->model->getAll();
        require __DIR__ . '/../views/eligibility_rules/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        require_role(['admin', 'reviewer']);
        $tiers = $this->model->getAllTiers();
        require __DIR__ . '/../views/eligibility_rules/create.php';
    }

    /**
     * Store new eligibility rule
     */
    public function store(): void
    {
        require_role(['admin', 'reviewer']);
        $errors = [];

        // Validate tier_id
        if (empty($_POST['tier_id'])) {
            $errors[] = 'Tier is required.';
        }

        // Validate rules_json
        if (empty($_POST['rules_json'])) {
            $errors[] = 'Rules JSON is required.';
        } else {
            // JSON validation
            json_decode($_POST['rules_json']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Invalid JSON format: ' . json_last_error_msg();
            }
        }

        // Check 1-to-1 relationship
        if (!empty($_POST['tier_id'])) {
            $tierId = (int) $_POST['tier_id'];
            if ($this->model->hasRule($tierId)) {
                $errors[] = 'This tier already has eligibility rules configured.';
            }
        }

        if (!empty($errors)) {
            $tiers = $this->model->getAllTiers();
            require __DIR__ . '/../views/eligibility_rules/create.php';
            return;
        }

        // Create rule
        $success = $this->model->create(
            (int) $_POST['tier_id'],
            $_POST['rules_json']
        );

        if ($success) {
            header('Location: index.php?controller=eligibility_rules&action=index');
            exit;
        } else {
            $errors[] = 'Failed to create eligibility rule.';
            $tiers = $this->model->getAllTiers();
            require __DIR__ . '/../views/eligibility_rules/create.php';
        }
    }

    /**
     * Show edit form
     */
    public function edit(): void
    {
        require_role(['admin', 'reviewer', 'staff']);
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo "Invalid ID.";
            exit;
        }

        $rule = $this->model->getById($id);
        
        if (!$rule) {
            http_response_code(404);
            echo "Eligibility rule not found.";
            exit;
        }

        $tiers = $this->model->getAllTiers();
        require __DIR__ . '/../views/eligibility_rules/edit.php';
    }

    /**
     * Update eligibility rule
     */
    public function update(): void
    {
        require_role(['admin', 'reviewer']);
        $id = (int) ($_POST['id'] ?? 0);
        $errors = [];

        if ($id <= 0) {
            http_response_code(400);
            echo "Invalid ID.";
            exit;
        }

        // Validate tier_id
        if (empty($_POST['tier_id'])) {
            $errors[] = 'Tier is required.';
        }

        // Validate rules_json
        if (empty($_POST['rules_json'])) {
            $errors[] = 'Rules JSON is required.';
        } else {
            // JSON validation
            json_decode($_POST['rules_json']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Invalid JSON format: ' . json_last_error_msg();
            }
        }

        // Check 1-to-1 relationship (excluding current rule)
        if (!empty($_POST['tier_id'])) {
            $tierId = (int) $_POST['tier_id'];
            if ($this->model->hasRule($tierId, $id)) {
                $errors[] = 'This tier already has eligibility rules configured.';
            }
        }

        if (!empty($errors)) {
            $rule = $this->model->getById($id);
            $tiers = $this->model->getAllTiers();
            require __DIR__ . '/../views/eligibility_rules/edit.php';
            return;
        }

        // Update rule
        $success = $this->model->update(
            $id,
            (int) $_POST['tier_id'],
            $_POST['rules_json']
        );

        if ($success) {
            header('Location: index.php?controller=eligibility_rules&action=index');
            exit;
        } else {
            $errors[] = 'Failed to update eligibility rule.';
            $rule = $this->model->getById($id);
            $tiers = $this->model->getAllTiers();
            require __DIR__ . '/../views/eligibility_rules/edit.php';
        }
    }

    /**
     * Delete eligibility rule (AJAX)
     */
    public function delete(): void
    {
        require_role(['admin', 'reviewer']);
        header('Content-Type: application/json');
        
        $id = (int) ($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $success = $this->model->delete($id);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete rule']);
        }
        exit;
    }
}
