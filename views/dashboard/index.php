<?php
/**
 * [RE-DESIGNED] Dashboard Index View
 */
require_once __DIR__ . '/../partials/header.php'; // Đã có Sidebar xịn
?>

<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold text-dark">System Admin Dashboard</h1>
            <p class="text-muted">Overview of system performance and scholarship activities.</p>
        </div>
        <div>
            <a href="<?= e(url('dashboard', 'exportCsv')) ?>" class="btn btn-outline-success me-2">
                <i class="fa-solid fa-file-excel"></i> Export CSV
            </a>
            <a href="<?= e(url('dashboard', 'exportHtml')) ?>" class="btn btn-outline-primary">
                <i class="fa-solid fa-code"></i> Export HTML
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm rounded-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box icon-blue me-3"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Apps</span>
                        <h4 class="mb-0 fw-bold"><?= e($approvalRate['total']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm rounded-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box icon-green me-3"><i class="fa-solid fa-chart-line"></i></div>
                    <div>
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Approval Rate</span>
                        <h4 class="mb-0 fw-bold"><?= e($approvalRate['rate']) ?>%</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm rounded-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box icon-orange me-3"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div>
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Utilization</span>
                        <h4 class="mb-0 fw-bold"><?= e($utilizationRate['rate']) ?>%</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm rounded-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box icon-purple me-3"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Pending</span>
                        <h4 class="mb-0 fw-bold"><?= e($approvalRate['pending']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .chart-card { border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 24px; }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #f0fdf4; color: #10b981; }
        .icon-orange { background: #fff7ed; color: #ea580c; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; }
        
        .table-header {
            background: #F7F2EB;
        }

        .table-header th {
            color: #081F5C;
            font-weight: 700;
            border-bottom: 2px solid #BAD6EB;
        }

        .table-hover tbody tr:hover {
            background-color: #F8FBFF !important;
        }

        .status-approved {
            background-color: #198754 !important;
            color: white !important;
            border: none !important;
        }

        .status-pending,
        .status-waitlisted {
            background-color: #FFC107 !important;
            color: #000 !important;
            border: none !important;
        }

        .status-reviewing {
            background-color: #0DCAF0 !important;
            color: white !important;
            border: none !important;
        }

        .status-rejected {
            background-color: #DC3545 !important;
            color: white !important;
            border: none !important;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px !important;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>

    <div class="row">
        <div class="col-md-6"><div class="card chart-card"><div class="card-body"><canvas id="approvalChart"></canvas></div></div></div>
        <div class="col-md-6"><div class="card chart-card"><div class="card-body"><canvas id="utilizationChart"></canvas></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6"><div class="card chart-card"><div class="card-body"><canvas id="topResourcesChart"></canvas></div></div></div>
        <div class="col-md-6"><div class="card chart-card"><div class="card-body"><canvas id="peakSubmissionsChart"></canvas></div></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Recent Activity</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-header">
                        <tr><th>ID</th><th>Student</th><th>Program</th><th>Status</th><th>Submitted</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $activity): ?>
                        <tr>
                            <td>#<?= e($activity['application_id']) ?></td>
                            <td><strong><?= e($activity['student_name']) ?></strong><br><small class="text-muted"><?= e($activity['student_email']) ?></small></td>
                            <td><?= e($activity['program_name']) ?></td>
                            <td>
                                <?php
                                $statusClass = match(strtolower($activity['status'])) {
                                    'approved' => 'status-approved',
                                    'pending' => 'status-pending',
                                    'reviewing' => 'status-reviewing',
                                    'rejected' => 'status-rejected',
                                    default => 'bg-light text-dark'
                                };
                                ?>

                                <span class="badge rounded-pill <?= $statusClass ?>">
                                    <?= e($activity['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('M d, H:i', strtotime($activity['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Prepare data from PHP
const approvalData = <?= json_encode($approvalRate) ?>;
const utilizationData = <?= json_encode($utilizationRate) ?>;
const topResourcesData = <?= json_encode($topResources) ?>;
const peakSubmissionsData = <?= json_encode($peakSubmissions) ?>;

// Approval Rate Pie Chart
const approvalCtx = document.getElementById('approvalChart').getContext('2d');
new Chart(approvalCtx, {
    type: 'pie',
    data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{
            data: [approvalData.approved, approvalData.rejected, approvalData.pending],
            backgroundColor: ['#334EAC', '#081F5C', '#BAD6EB'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = approvalData.total;
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Utilization Rate Pie Chart
const utilizationCtx = document.getElementById('utilizationChart').getContext('2d');
const remainingAmount = Math.max(0, utilizationData.granted - utilizationData.disbursed);
new Chart(utilizationCtx, {
    type: 'pie',
    data: {
        labels: ['Disbursed', 'Remaining'],
        datasets: [{
            data: [utilizationData.disbursed, remainingAmount],
            backgroundColor: ['#334EAC', '#D0E3FF'],
            borderWidth: 2,
            borderColor: '#FFF9F0'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        return `${label}: $${value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
                    }
                }
            }
        }
    }
});

// Top Resources Bar Chart
const topResourcesCtx = document.getElementById('topResourcesChart').getContext('2d');
new Chart(topResourcesCtx, {
    type: 'bar',
    data: {
        labels: topResourcesData.map(r => r.program_name),
        datasets: [{
            label: 'Applications',
            data: topResourcesData.map(r => r.count),
            backgroundColor: ['#334EAC','#7096D1','#BAD6EB','#334EAC','#7096D1'],
            borderColor: '#0a58ca',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Peak Submissions Line Chart
const peakSubmissionsCtx = document.getElementById('peakSubmissionsChart').getContext('2d');
new Chart(peakSubmissionsCtx, {
    type: 'line',
    data: {
        labels: peakSubmissionsData.map(p => p.hour),
        datasets: [{
            label: 'Submissions',
            data: peakSubmissionsData.map(p => p.count),
            borderColor: '#081F5C',
            backgroundColor: 'rgba(112, 150, 209, 0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
