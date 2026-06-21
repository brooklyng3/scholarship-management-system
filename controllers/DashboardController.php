<?php
/**
 * DashboardController.php
 * Handles dashboard display and export actions
 */

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController
{
    private DashboardModel $model;

    public function __construct()
    {
        $this->model = new DashboardModel();
    }

    /**
     * Display the dashboard with all metrics
     */
    public function index(): void
    {
        require_role(['admin', 'reviewer', 'staff']);

        $approvalRate = $this->model->getApprovalRate();
        $utilizationRate = $this->model->getUtilizationRate();
        $peakSubmissions = $this->model->getPeakSubmissions();
        $topResources = $this->model->getTopResources();
        $recentActivity = $this->model->getRecentActivity();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    /**
     * Export dashboard data to CSV format
     */
    public function exportCsv(): void
    {
        require_role(['admin', 'reviewer', 'staff']);

        $topResources = $this->model->getTopResources();
        $peakSubmissions = $this->model->getPeakSubmissions();
        $approvalRate = $this->model->getApprovalRate();
        $utilizationRate = $this->model->getUtilizationRate();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dashboard_report_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Dashboard Summary
        fputcsv($output, ['DASHBOARD SUMMARY REPORT']);
        fputcsv($output, ['Generated at', date('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Approval Rate Section
        fputcsv($output, ['APPROVAL RATE']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Applications', $approvalRate['total']]);
        fputcsv($output, ['Approved', $approvalRate['approved']]);
        fputcsv($output, ['Rejected', $approvalRate['rejected']]);
        fputcsv($output, ['Pending', $approvalRate['pending']]);
        fputcsv($output, ['Approval Rate (%)', $approvalRate['rate']]);
        fputcsv($output, []);

        // Utilization Rate Section
        fputcsv($output, ['UTILIZATION RATE']);
        fputcsv($output, ['Metric', 'Amount']);
        fputcsv($output, ['Total Granted', number_format($utilizationRate['granted'], 2)]);
        fputcsv($output, ['Total Disbursed', number_format($utilizationRate['disbursed'], 2)]);
        fputcsv($output, ['Utilization Rate (%)', $utilizationRate['rate']]);
        fputcsv($output, []);

        // Top Resources Section
        fputcsv($output, ['TOP 5 SCHOLARSHIP PROGRAMS']);
        fputcsv($output, ['Program Name', 'Application Count']);
        foreach ($topResources as $resource) {
            fputcsv($output, [$resource['program_name'], $resource['count']]);
        }
        fputcsv($output, []);

        // Peak Submissions Section
        fputcsv($output, ['PEAK SUBMISSION TIMES']);
        fputcsv($output, ['Hour', 'Submission Count']);
        foreach ($peakSubmissions as $submission) {
            fputcsv($output, [$submission['hour'], $submission['count']]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export dashboard data to HTML format
     */
    public function exportHtml(): void
    {
        require_role(['admin', 'reviewer', 'staff']);

        $topResources = $this->model->getTopResources();
        $peakSubmissions = $this->model->getPeakSubmissions();
        $approvalRate = $this->model->getApprovalRate();
        $utilizationRate = $this->model->getUtilizationRate();
        $recentActivity = $this->model->getRecentActivity();

        // Set headers for HTML download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="dashboard_report_' . date('Y-m-d_His') . '.html"');
        header('Pragma: no-cache');
        header('Expires: 0');

        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Report - <?= date('Y-m-d H:i:s') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
        }
        h2 {
            color: #198754;
            margin-top: 30px;
            border-bottom: 2px solid #198754;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #0d6efd;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .stat-box {
            display: inline-block;
            padding: 15px 25px;
            margin: 10px;
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            border-radius: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>Dashboard Summary Report</h1>
    <p><strong>Generated:</strong> <?= date('Y-m-d H:i:s') ?></p>

    <h2>Approval Rate Statistics</h2>
    <div class="stat-box">
        <strong>Total Applications:</strong> <?= $approvalRate['total'] ?>
    </div>
    <div class="stat-box">
        <strong>Approved:</strong> <?= $approvalRate['approved'] ?>
    </div>
    <div class="stat-box">
        <strong>Rejected:</strong> <?= $approvalRate['rejected'] ?>
    </div>
    <div class="stat-box">
        <strong>Pending:</strong> <?= $approvalRate['pending'] ?>
    </div>
    <div class="stat-box">
        <strong>Approval Rate:</strong> <?= $approvalRate['rate'] ?>%
    </div>

    <h2>Utilization Rate</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Granted</td>
            <td>$<?= number_format($utilizationRate['granted'], 2) ?></td>
        </tr>
        <tr>
            <td>Total Disbursed</td>
            <td>$<?= number_format($utilizationRate['disbursed'], 2) ?></td>
        </tr>
        <tr>
            <td><strong>Utilization Rate</strong></td>
            <td><strong><?= $utilizationRate['rate'] ?>%</strong></td>
        </tr>
    </table>

    <h2>Top 5 Scholarship Programs</h2>
    <table>
        <tr>
            <th>Program Name</th>
            <th>Application Count</th>
        </tr>
        <?php foreach ($topResources as $resource): ?>
        <tr>
            <td><?= htmlspecialchars($resource['program_name']) ?></td>
            <td><?= $resource['count'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Peak Submission Times</h2>
    <table>
        <tr>
            <th>Hour</th>
            <th>Submission Count</th>
        </tr>
        <?php foreach ($peakSubmissions as $submission): ?>
        <tr>
            <td><?= htmlspecialchars($submission['hour']) ?></td>
            <td><?= $submission['count'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Recent Activity (Last 5 Applications)</h2>
    <table>
        <tr>
            <th>Application ID</th>
            <th>Student</th>
            <th>Program</th>
            <th>Status</th>
            <th>Submitted At</th>
        </tr>
        <?php foreach ($recentActivity as $activity): ?>
        <tr>
            <td><?= $activity['application_id'] ?></td>
            <td><?= htmlspecialchars($activity['student_name']) ?></td>
            <td><?= htmlspecialchars($activity['program_name']) ?></td>
            <td><?= htmlspecialchars($activity['status']) ?></td>
            <td><?= htmlspecialchars($activity['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="footer">
        <p>Scholarship Management System - Dashboard Export</p>
    </div>
</body>
</html>
        <?php
        exit;
    }
}
