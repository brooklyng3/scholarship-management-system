<?php
// public/index.php
// Front Controller (single entry point) cho toàn bộ ứng dụng.
// URL pattern: index.php?controller=<ten_controller>&action=<ten_action>

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/auth.php'; // [NEW] session/login helpers (require_login, require_role, current_user)

// Determine which controller to use
$controllerName = $_GET['controller'] ?? 'scholarship_programs';
$action = $_GET['action'] ?? 'index';

// Map controller names to controller classes
$controllerMap = [
    // ===== Cụm 1: Hệ thống & User (module của tôi) =====
    'users'                => 'UserController',
    'student_profiles'     => 'StudentProfileController',
    'staff_profiles'       => 'StaffProfileController',
    'violation_records'    => 'ViolationRecordController',
    'scholarship_programs' => 'ScholarshipProgramController',
    'auth'                 => 'AuthController', // [NEW] login / logout

    // ===== Cụm 2 & 3: module của thành viên khác (controller nằm ở repo của họ) =====
    'scholarship_tiers'    => 'ScholarshipTierController',
    'scoring_criteria'     => 'ScoringCriteriaController',
    'evaluation_scores'    => 'EvaluationScoreController',
    'eligibility_rules'    => 'EligibilityRuleController',
    'scholarship_decisions' => 'ScholarshipDecisionController',
    'applications'          => 'ApplicationController',
    'application_documents' => 'ApplicationDocumentController',
];

// Check if controller exists
if (!isset($controllerMap[$controllerName])) {
    http_response_code(404);
    echo "Controller not found.";
    exit;
}

// Load the controller
$controllerClass = $controllerMap[$controllerName];
$controllerFile = __DIR__ . '/../controllers/' . $controllerClass . '.php';

// [NEW] Friendly error instead of a fatal "file not found" if a teammate's
// controller (Cụm 2/3) hasn't been merged into this folder yet.
if (!file_exists($controllerFile)) {
    http_response_code(503);
    echo "Module '{$controllerName}' chưa sẵn sàng (controller chưa được merge vào dự án).";
    exit;
}
require_once $controllerFile;

// Instantiate controller
$controller = new $controllerClass();

// Map URL action to Controller method
$allowedActions = ['index', 'create', 'store', 'edit', 'update', 'delete', 'login', 'doLogin', 'logout', 'export'];
// 'login'/'doLogin'/'logout' -> AuthController, 'export' -> CSV export (violation_records) [NEW]

if (in_array($action, $allowedActions) && method_exists($controller, $action)) {
    $controller->$action();
} else {
    http_response_code(404);
    echo "Page not found.";
}
