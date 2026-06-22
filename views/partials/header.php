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
                <?php if (isset(current_user()['role']) && in_array(current_user()['role'], ['admin', 'staff'], true)): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('dashboard', 'index')) ?>">Dashboard</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('scholarship_programs', 'index')) ?>">Scholarship Programs</a></li>

                <?php if (isset(current_user()['role'])): ?>
                    <?php if (in_array(current_user()['role'], ['admin', 'reviewer', 'staff'], true)): ?>
                        <?php if (current_user()['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= e(url('users', 'index')) ?>">Users</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('scholarship_tiers', 'index')) ?>">Tiers</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('applications', 'index')) ?>">Applications</a></li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('student_profiles', 'edit', ['id' => current_user()['id']])) ?>">My Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('applications', 'index')) ?>">My Applications</a></li>
                    <?php endif; ?>
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