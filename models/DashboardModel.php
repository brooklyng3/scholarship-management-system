<?php
/**
 * DashboardModel.php
 * Statistical queries for the dashboard metrics
 */

require_once __DIR__ . '/../config/database.php';

class DashboardModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getApprovalRate(): array
    {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM applications 
            GROUP BY status
        ");
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats = ['approved' => 0, 'rejected' => 0, 'pending' => 0, 'total' => 0, 'rate' => 0];

        foreach ($results as $row) {
            $status = strtolower($row['status']);
            $count = (int)$row['count'];
            $stats['total'] += $count;
            
            if ($status === 'approved') {
                $stats['approved'] = $count;
            } elseif ($status === 'rejected') {
                $stats['rejected'] = $count;
            } elseif (in_array($status, ['pending', 'reviewing'])) {
                $stats['pending'] += $count;
            }
        }

        if ($stats['total'] > 0) {
            $stats['rate'] = round(($stats['approved'] / $stats['total']) * 100, 2);
        }

        return $stats;
    }

    public function getUtilizationRate(): array
    {
        // Sum of completed disbursements (amount_paid)
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(amount_paid), 0) as total_disbursed
            FROM disbursements
            WHERE status = 'completed'
        ");
        $disbursed = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total_disbursed'];

        // Sum of granted amounts (final_status = approved)
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(granted_amount), 0) as total_granted
            FROM scholarship_decisions
            WHERE final_status = 'approved'
        ");
        $granted = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total_granted'];

        $rate = $granted > 0 ? round(($disbursed / $granted) * 100, 2) : 0;

        return [
            'disbursed' => $disbursed,
            'granted' => $granted,
            'rate' => $rate
        ];
    }

    public function getPeakSubmissions(): array
    {
        $stmt = $this->db->query("
            SELECT HOUR(applied_date) as hour, COUNT(*) as count
            FROM applications
            GROUP BY HOUR(applied_date)
            ORDER BY hour
        ");

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hourlyData = [];

        foreach ($results as $row) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', (int)$row['hour']),
                'count' => (int)$row['count']
            ];
        }

        return $hourlyData;
    }

    public function getTopResources(): array
    {
        // Join through tiers to get program applications
        $stmt = $this->db->query("
            SELECT sp.title as program_name, COUNT(a.id) as count
            FROM scholarship_programs sp
            LEFT JOIN scholarship_tiers st ON sp.id = st.program_id
            LEFT JOIN applications a ON st.id = a.tier_id
            GROUP BY sp.id, sp.title
            ORDER BY count DESC
            LIMIT 5
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentActivity(): array
    {
        $stmt = $this->db->query("
            SELECT 
                a.id as application_id,
                a.status,
                a.applied_date as created_at,
                sprog.title as program_name,
                u.full_name as student_name,
                u.email as student_email
            FROM applications a
            JOIN scholarship_tiers st ON a.tier_id = st.id
            JOIN scholarship_programs sprog ON st.program_id = sprog.id
            JOIN users u ON a.user_id = u.id
            ORDER BY a.applied_date DESC
            LIMIT 5
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * [NEW] Thống kê hồ sơ cho Student Dashboard
     */
    public function getStudentStats(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as total 
            FROM applications 
            WHERE user_id = :user_id 
            GROUP BY status
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * [NEW] Thống kê tiến độ chấm điểm cho Reviewer Dashboard
     */
    public function getReviewerStats(): array
    {
        // Đếm tổng số lượng hồ sơ dựa trên trạng thái để Reviewer biết tiến độ
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM applications 
            GROUP BY status
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
}