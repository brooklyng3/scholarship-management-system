<?php
/**
 * Dashboard Index View
 * Displays statistical metrics with Chart.js visualizations
 * @var array $approvalRate
 * @var array $utilizationRate
 * @var array $topResources
 * @var array $peakSubmissions
 * @var array $recentActivity
 */
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard</h1>
        <div>
            <a href="<?= e(url('dashboard', 'exportCsv')) ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
            </a>
            <a href="<?= e(url('dashboard', 'exportHtml')) ?>" class="btn btn-info text-white">
                <i class="bi bi-file-earmark-code"></i> Export to HTML
            </a>
        </div>
    </div>

    <!-- Key Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Total Applications</h6>
                    <h2 class="mb-0"><?= e($approvalRate['total']) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Approval Rate</h6>
                    <h2 class="mb-0"><?= e($approvalRate['rate']) ?>%</h2>
                    <small><?= e($approvalRate['approved']) ?> approved / <?= e($approvalRate['total']) ?> total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Utilization Rate</h6>
                    <h2 class="mb-0"><?= e($utilizationRate['rate']) ?>%</h2>
                    <small>$<?= e(number_format($utilizationRate['disbursed'], 2)) ?> / $<?= e(number_format($utilizationRate['granted'], 2)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Pending Applications</h6>
                    <h2 class="mb-0"><?= e($approvalRate['pending']) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Approval Rate Pie Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Application Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="approvalChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Utilization Rate Pie Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Fund Utilization</h5>
                </div>
                <div class="card-body">
                    <canvas id="utilizationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Top Resources Bar Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 5 Scholarship Programs</h5>
                </div>
                <div class="card-body">
                    <canvas id="topResourcesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Peak Submissions Line Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Peak Submission Times</h5>
                </div>
                <div class="card-body">
                    <canvas id="peakSubmissionsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivity)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No recent activity</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td><?= e($activity['application_id']) ?></td>
                                        <td><?= e($activity['student_name']) ?></td>
                                        <td><?= e($activity['student_email']) ?></td>
                                        <td><?= e($activity['program_name']) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = match(strtolower($activity['status'])) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'pending' => 'warning',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>"><?= e($activity['status']) ?></span>
                                        </td>
                                        <td><?= e(date('Y-m-d H:i', strtotime($activity['created_at']))) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
            backgroundColor: ['#198754', '#dc3545', '#ffc107'],
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
            backgroundColor: ['#0dcaf0', '#e9ecef'],
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
            backgroundColor: '#0d6efd',
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
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
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
