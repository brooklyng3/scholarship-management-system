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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* BẢNG MÀU THEO THEME ẢNH MẪU */
        :root {
            --sidebar-bg: #112240; /* Xanh Navy Đậm */
            --sidebar-text: #8892b0; /* Xám xanh nhạt */
            --sidebar-hover: #1a365d; 
            --accent-color: #f59e0b; /* Cam/Vàng điểm nhấn */
            --bg-main: #f0f2f5; /* Nền xám sáng */
        }
        
        body { 
            background-color: var(--bg-main); 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            /* THỦ THUẬT AN TOÀN: Đẩy toàn bộ nội dung web sang phải nhường chỗ cho Sidebar */
            padding-left: 260px; 
        }

        /* --- SIDEBAR CHÍNH --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: white;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }

        /* 1. KHU VỰC PROFILE TRÊN CÙNG */
        .sidebar-profile {
            padding: 40px 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: var(--sidebar-bg);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .profile-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .profile-role {
            font-size: 0.8rem;
            color: var(--accent-color);
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* 2. KHU VỰC MENU ĐIỀU HƯỚNG */
        .sidebar-menu {
            padding: 20px 0;
            flex-grow: 1;
        }
        .sidebar-menu .nav-link {
            color: var(--sidebar-text);
            padding: 12px 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        .sidebar-menu .nav-link:hover {
            color: white;
            background-color: var(--sidebar-hover);
            border-left: 4px solid var(--accent-color);
            padding-left: 21px; /* Trừ đi 4px border để text không bị giật */
        }
        .sidebar-menu .nav-link i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* 3. KHU VỰC LOGOUT DƯỚI CÙNG */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-footer .nav-link {
            color: #fca5a5;
        }
        .sidebar-footer .nav-link:hover {
            color: #ef4444;
            background-color: transparent;
            border-left: none;
        }

        /* --- VÙNG HIỂN THỊ CỦA CÁC THÀNH VIÊN KHÁC --- */
        .main-content {
            padding-top: 30px;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <?php if (is_logged_in()): $u = current_user(); ?>
        <div class="sidebar-profile">
            <div class="profile-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="profile-name"><?= e($u['full_name']) ?></div>
            <div class="profile-role"><?= e(ucfirst($u['role'])) ?></div>
        </div>
    <?php else: ?>
        <div class="sidebar-profile">
            <div class="profile-name" style="margin-top: 20px;">SMS SYSTEM</div>
        </div>
    <?php endif; ?>

    <div class="sidebar-menu">
        <?php if (isset(current_user()['role'])): ?>
            <?php 
                $myRole = current_user()['role'];
                if (in_array($myRole, ['admin', 'staff'], true)) {
                    $dashUrl = url('dashboard', 'index');
                } elseif ($myRole === 'reviewer') {
                    $dashUrl = url('dashboard', 'reviewer');
                } else {
                    $dashUrl = url('dashboard', 'student');
                }
            ?>
            <a class="nav-link" href="<?= e($dashUrl) ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <?php endif; ?>
        
        <a class="nav-link" href="<?= e(url('scholarship_programs', 'index')) ?>"><i class="fa-solid fa-graduation-cap"></i> Programs</a>

        <?php if (isset(current_user()['role'])): ?>
            <?php if (in_array(current_user()['role'], ['admin', 'reviewer', 'staff'], true)): ?>
                <?php if (current_user()['role'] === 'admin'): ?>
                    <a class="nav-link" href="<?= e(url('users', 'index')) ?>"><i class="fa-solid fa-users"></i> Users</a>
                <?php endif; ?>
                
                <a class="nav-link" href="<?= e(url('applications', 'index')) ?>"><i class="fa-solid fa-folder-open"></i> Applications</a>
            <?php else: ?>
                <a class="nav-link" href="<?= e(url('student_profiles', 'edit', ['id' => current_user()['id']])) ?>"><i class="fa-solid fa-address-card"></i> My Profile</a>
                <a class="nav-link" href="<?= e(url('applications', 'index')) ?>"><i class="fa-solid fa-file-signature"></i> My Applications</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <?php if (is_logged_in()): ?>
            <a class="nav-link" href="<?= e(url('auth', 'logout')) ?>"><i class="fa-solid fa-power-off"></i> Logout</a>
        <?php else: ?>
            <a class="nav-link" href="<?= e(url('auth', 'login')) ?>"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        <?php endif; ?>
    </div>
</aside>

<div class="main-content">
    <div class="container">
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="container">