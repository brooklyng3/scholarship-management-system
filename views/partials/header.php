<?php
/**
 * Common header for the entire System interface
 * Optional variable: $pageTitle (string)
 */
$pageTitle = $pageTitle ?? 'System & Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Scholarship Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-brand { font-weight: 700; }
        .badge-role { text-transform: capitalize; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= e(url('scholarship_programs', 'index')) ?>">SMS - System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('users', 'index')) ?>">Users</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('student_profiles', 'index')) ?>">Student Profiles</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('staff_profiles', 'index')) ?>">Staff Profiles</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('violation_records', 'index')) ?>">Violation Records</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('scholarship_programs', 'index')) ?>">Scholarship Programs</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('scholarship_tiers', 'index')) ?>">Tiers</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('eligibility_rules', 'index')) ?>">Rules</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('applications', 'index')) ?>">Applications</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('scholarship_decisions', 'index')) ?>">Decisions</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('scoring_criteria', 'index')) ?>">Criteria</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('evaluation_scores', 'index')) ?>">Scores</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('application_documents', 'index')) ?>">App Documents</a></li>
                <?php if (isset(current_user()['role']) && in_array(current_user()['role'], ['admin', 'reviewer', 'staff'], true)): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['controller']) && $_GET['controller'] === 'dashboard') ? 'active' : '' ?>" 
                        href="<?= e(url('dashboard', 'index')) ?>">
                            <i class="bi bi-graph-up-arrow"></i> Statistics Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_logged_in()): $u = current_user(); ?>
                    <li class="nav-item d-flex align-items-center text-light me-3">
                        <span class="badge bg-secondary"><?= e(ucfirst($u['role'])) ?></span>&nbsp;<?= e($u['full_name']) ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('auth', 'logout')) ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('auth', 'login')) ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>
<div class="container">