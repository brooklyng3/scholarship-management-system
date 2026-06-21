<?php
/**
 * Seed Traffic Data Generator - Unique Constraint Safe Edition
 * Generates mock application records matching the database constraints perfectly.
 */

require_once __DIR__ . '/../config/database.php';

echo "<pre>";
echo "===== Starting Traffic Data Seeding =====\n\n";

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Fetch profiles and tiers
    echo "Fetching student profiles and tracking references...\n";
    $stmt = $pdo->query("SELECT id, user_id FROM student_profiles");
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profiles)) {
        die("Error: No student profiles found. Please register student accounts first.\n");
    }

    echo "Fetching scholarship tiers...\n";
    $stmt = $pdo->query("SELECT id FROM scholarship_tiers");
    $tierIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tierIds)) {
        die("Error: No scholarship tiers found in the system.\n");
    }

    // 2. Map out what combinations ALREADY exist in the database to avoid them
    $stmt = $pdo->query("SELECT user_id, tier_id FROM applications");
    $existingApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingPairs = [];
    foreach ($existingApps as $app) {
        $existingPairs[$app['user_id'] . '-' . $app['tier_id']] = true;
    }

    // 3. Find an evaluation account to auto-assign tasks
    $reviewerStmt = $pdo->query("SELECT id FROM users WHERE role = 'reviewer' LIMIT 1");
    $reviewerId = $reviewerStmt->fetchColumn();
    $reviewerId = $reviewerId ? (int)$reviewerId : null;

    // 4. Generate every single possible unique remaining combination
    $availablePairs = [];
    foreach ($profiles as $profile) {
        foreach ($tierIds as $tierId) {
            $pairKey = $profile['user_id'] . '-' . $tierId;
            if (!isset($existingPairs[$pairKey])) {
                $availablePairs[] = [
                    'user_id'    => $profile['user_id'],
                    'profile_id' => $profile['id'],
                    'tier_id'    => $tierId
                ];
            }
        }
    }

    // Shuffle the deck so the insertions are completely randomized across tiers/students
    shuffle($availablePairs);

    // Determine how many we can safely insert
    $totalPossible = count($availablePairs);
    echo "Found {$totalPossible} valid new unique student-tier combinations available.\n";
    
    // We will cap it at the maximum available pairs to avoid constraint crashes
    $insertLimit = min($totalPossible, 90); 
    echo "Preparing to safely insert {$insertLimit} application history blocks...\n";

    $statuses = ['approved', 'rejected', 'reviewing', 'pending'];
    $insertedCount = 0;
    $approvedApplications = [];

    $insertAppStmt = $pdo->prepare("
        INSERT INTO applications 
        (user_id, profile_id, tier_id, reviewer_id, status, applied_date) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < $insertLimit; $i++) {
        $pair = $availablePairs[$i];
        $status = $statuses[array_rand($statuses)];

        // Force time splits to create distinct visual data spikes on line charts
        $rand = mt_rand(1, 100);
        if ($rand <= 40) {
            $hour = mt_rand(9, 10); // 40% Morning peak
        } elseif ($rand <= 80) {
            $hour = mt_rand(20, 21); // 40% Evening peak
        } else {
            $hour = mt_rand(0, 23); // 20% Baseline spread
            while (($hour >= 9 && $hour <= 10) || ($hour >= 20 && $hour <= 21)) {
                $hour = mt_rand(0, 23);
            }
        }
        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);

        $daysAgo = mt_rand(0, 90);
        $appliedDate = date('Y-m-d', strtotime("-{$daysAgo} days")) . ' ' . sprintf('%02d:%02d:%02d', $hour, $minute, $second);

        $insertAppStmt->execute([
            $pair['user_id'],
            $pair['profile_id'],
            $pair['tier_id'],
            $reviewerId,
            $status,
            $appliedDate
        ]);

        $applicationId = $pdo->lastInsertId();
        $insertedCount++;

        if ($status === 'approved') {
            $approvedApplications[] = [
                'id' => $applicationId,
                'date' => $appliedDate
            ];
        }
    }

    echo "Successfully generated {$insertedCount} history logs without a single conflict.\n";

    // 5. Populate matching programmatic records to unlock funding graph calculations
    if (!empty($approvedApplications)) {
        echo "Generating financial tracking indexes (decisions & disbursements)...\n";

        $pdo->exec("CREATE TABLE IF NOT EXISTS scholarship_decisions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            granted_amount DECIMAL(15,2) NOT NULL,
            final_status VARCHAR(50) DEFAULT 'approved',
            decision_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS disbursements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            decision_id INT NOT NULL,
            amount_paid DECIMAL(15,2) NOT NULL,
            status VARCHAR(50) DEFAULT 'completed',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $insertDecisionStmt = $pdo->prepare("
            INSERT INTO scholarship_decisions 
            (application_id, granted_amount, final_status, decision_date) 
            VALUES (?, ?, 'approved', ?)
        ");

        $insertDisbursementStmt = $pdo->prepare("
            INSERT INTO disbursements 
            (decision_id, amount_paid, status, payment_date) 
            VALUES (?, ?, 'completed', ?)
        ");

        $financialCount = 0;

        foreach ($approvedApplications as $app) {
            $grantedAmount = mt_rand(5000000, 20000000);
            $decisionDate = date('Y-m-d H:i:s', strtotime($app['date'] . ' +' . mt_rand(3, 10) . ' days'));
            
            $insertDecisionStmt->execute([
                $app['id'],
                $grantedAmount,
                $decisionDate
            ]);

            $decisionId = $pdo->lastInsertId();
            $paymentDate = date('Y-m-d H:i:s', strtotime($decisionDate . ' +' . mt_rand(5, 15) . ' days'));

            $insertDisbursementStmt->execute([
                $decisionId,
                $grantedAmount,
                $paymentDate
            ]);

            $financialCount++;
        }

        echo "Successfully integrated {$financialCount} financial records.\n";
    }

    echo "\n===== Seeding Completed Successfully! =====\n";
    echo "Head back to the dashboard page to check out the metrics charts!";

} catch (PDOException $e) {
    echo "Database Execution Blocked: " . $e->getMessage() . "\n";
}
echo "</pre>";