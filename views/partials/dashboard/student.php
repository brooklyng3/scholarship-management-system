<?php require_once __DIR__ . '/../header.php'; ?>

<style>
    .dashboard-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif; }
    
    /* Header và Banner đồng bộ */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
    .page-header h1 { font-size: 1.8rem; color: #112240; font-weight: 800; margin: 0; }
    .page-header p { color: #6b7280; margin: 5px 0 0 0; }
    
    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 30px; }
    .stat-card { background: #ffffff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); display: flex; align-items: center; }
    
    /* Icon Box */
    .icon-box { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 20px; }
    .icon-blue { background-color: #eff6ff; color: #2563eb; }
    .icon-green { background-color: #f0fdf4; color: #10b981; }
    .icon-orange { background-color: #fff7ed; color: #ea580c; }
    
    .stat-title { color: #6b7280; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .stat-number { font-size: 2rem; font-weight: 800; color: #112240; line-height: 1; }

    /* List Card */
    .list-card { background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .list-item { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
    .list-item:last-child { border-bottom: none; }
    .item-icon { width: 40px; height: 40px; background: #f3f4f6; color: #4b5563; display: flex; align-items: center; justify-content: center; border-radius: 8px; margin-right: 15px; }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Welcome back, <?= htmlspecialchars($student_name ?? 'Student') ?>! 👋</h1>
        <p>Track your scholarship application progress.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box icon-orange"><i class="fa-solid fa-clock"></i></div>
            <div><span class="stat-title">Pending</span><span class="stat-number"><?= $count_pending ?? 0 ?></span></div>
        </div>
        <div class="stat-card">
            <div class="icon-box icon-blue"><i class="fa-solid fa-spinner"></i></div>
            <div><span class="stat-title">Evaluating</span><span class="stat-number"><?= $count_evaluating ?? 0 ?></span></div>
        </div>
        <div class="stat-card">
            <div class="icon-box icon-green"><i class="fa-solid fa-award"></i></div>
            <div><span class="stat-title">Awarded</span><span class="stat-number"><?= $count_awarded ?? 0 ?></span></div>
        </div>
    </div>

    <div class="list-card">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 15px;">Your Recent Applications</h3>
        <?php if (empty($recent_apps)): ?>
            <p class="text-muted">No applications found.</p>
        <?php else: ?>
            <?php foreach ($recent_apps as $app): ?>
                <div class="list-item">
                    <div class="item-icon"><i class="fa-solid fa-file-invoice"></i></div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin:0;"><?= e($app['program_name']) ?></h4>
                        <p style="font-size: 0.8rem; color: #6b7280; margin:0;">Status: <strong><?= e($app['status']) ?></strong> • <?= date('M d, Y', strtotime($app['applied_date'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>