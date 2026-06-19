<?php
// public/index.php

// Determine which controller to use
$controllerName = $_GET['controller'] ?? 'scholarship_programs';
$action = $_GET['action'] ?? 'index';

// Map controller names to controller classes
$controllerMap = [
    'scholarship_programs' => 'ScholarshipProgramController',
    'scholarship_tiers' => 'ScholarshipTierController',
    'scoring_criteria' => 'ScoringCriteriaController',
    'evaluation_scores' => 'EvaluationScoreController',
    'eligibility_rules' => 'EligibilityRuleController',
    'applications' => 'ApplicationController',
    'scholarship_decisions' => 'ScholarshipDecisionController',
];

// Check if controller exists
if (!isset($controllerMap[$controllerName])) {
    http_response_code(404);
    echo "Controller not found.";
    exit;
}

// Load the controller
$controllerClass = $controllerMap[$controllerName];
require_once __DIR__ . '/../controllers/' . $controllerClass . '.php';

// Instantiate controller
$controller = new $controllerClass();

// Map URL action to Controller method
$allowedActions = ['index', 'create', 'store', 'edit', 'update', 'delete'];

if (in_array($action, $allowedActions)) {
    $controller->$action();
} else {
    http_response_code(404);
    echo "Page not found.";
}
