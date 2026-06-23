<?php require_once __DIR__ . '/../header.php'; ?>

<style>
    .dashboard-container { 
        max-width: 1200px; 
        margin: 30px auto; 
        padding: 0 20px; 
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* 1. KHU VỰC LỜI CHÀO (Giống ảnh mẫu) */
    .page-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-header h1 {
        font-size: 1.8rem;
        color: #112240; /* Cùng màu Navy với Sidebar */
        font-weight: 800;
        margin: 0;
    }
    .page-header p {
        color: #6b7280;
        margin: 5px 0 0 0;
        font-size: 0.95rem;
    }
    .date-widget {
        background: #fff; 
        padding: 10px 16px; 
        border-radius: 8px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.02); 
        color: #4b5563; 
        font-weight: 600; 
        font-size: 0.9rem;
    }

    /* 2. LƯỚI THỐNG KÊ (Dạng Thẻ Widget) */
    .stats-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
        gap: 24px; 
        margin-bottom: 40px; 
    }
    
    .stat-card { 
        background: #ffffff; 
        padding: 24px; 
        border-radius: 16px; 
        border: none; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        display: flex; 
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }

    /* Tạo ô vuông chứa Icon */
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 20px;
        flex-shrink: 0;
    }
    .icon-orange { background-color: #fff7ed; color: #ea580c; }
    .icon-green { background-color: #f0fdf4; color: #10b981; }

    /* Chữ số trong thẻ */
    .stat-content { flex-grow: 1; }
    .stat-title { 
        color: #6b7280; 
        font-size: 0.85rem; 
        font-weight: 700; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        margin-bottom: 8px; 
        display: block;
    }
    .stat-number { 
        font-size: 2rem; 
        font-weight: 800; 
        color: #112240; 
        line-height: 1;
    }

    /* 3. BẢNG HÀNH ĐỘNG (CTA Widget) */
    .action-panel { 
        background: linear-gradient(135deg, #112240, #1a365d); 
        border-radius: 16px; 
        padding: 30px; 
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(17, 34, 64, 0.15);
        color: white;
    }
    .action-text h3 { margin: 0 0 8px 0; font-size: 1.4rem; font-weight: 700; }
    .action-text p { margin: 0; color: #94a3b8; font-size: 0.95rem; }
    
    .btn-evaluate { 
        background: #f59e0b; /* Màu vàng cam điểm nhấn */
        color: white; 
        padding: 12px 28px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-weight: 600; 
        transition: all 0.2s;
        display: inline-block;
    }
    .btn-evaluate:hover { 
        background: #d97706; 
        color: white;
        transform: scale(1.05);
    }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1>Good Morning, <?= htmlspecialchars($reviewer_name ?? 'Evaluator') ?> 👋</h1>
            <p>Here is what's happening with your assigned scholarship applications today.</p>
        </div>
        <div class="date-widget">
            <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?= date('M d, Y') ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box icon-orange">
                <i class="fa-solid fa-file-circle-exclamation"></i>
            </div>
            <div class="stat-content">
                <span class="stat-title">To Review</span>
                <span class="stat-number"><?= $count_to_review ?? 0 ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon-box icon-green">
                <i class="fa-solid fa-check-to-slot"></i>
            </div>
            <div class="stat-content">
                <span class="stat-title">Evaluated</span>
                <span class="stat-number"><?= $count_reviewed ?? 0 ?></span>
            </div>
        </div>
    </div>

    <div class="action-panel">
        <div class="action-text">
            <h3>Ready to start grading?</h3>
            <p>Access the evaluation board to review applicant documents and submit your scores.</p>
        </div>
        <div>
            <a href="<?= e(url('applications', 'index')) ?>" class="btn-evaluate">
                Evaluation Board <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>