<?php
/**
 * Seed Traffic Data Generator
 * Generates 100+ mock application records with realistic peak hours and financial tracking
 * 
 * Usage: Run from command line or browser: php seed_traffic.php
 */

require_once __DIR__ . '/../config/database.php';

echo "===== Starting Traffic Data Seeding =====\n\n";

try {
    $pdo = getDbConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Fetch all valid student user IDs
    echo "Fetching student users...\n";
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'student'");
    $studentUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($studentUserIds)) {
        die("Error: No student users found in the database. Please create student users first.\n");
    }
    echo "Found " . count($studentUserIds) . " student users.\n";

    // Step 2: Fetch all valid student profile IDs
    echo "Fetching student profiles...\n";
    $stmt = $pdo->query("SELECT id FROM student_profiles");
    $studentProfileIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($studentProfileIds)) {
        die("Error: No student profiles found in the database.\n");
    }
    echo "Found " . count($studentProfileIds) . " student profiles.\n";

    // Step 3: Fetch all valid scholarship tier IDs
    echo "Fetching scholarship tiers...\n";
    $stmt = $pdo->query("SELECT id FROM scholarship_tiers");
    $tierIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tierIds)) {
        die("Error: No scholarship tiers found in the database.\n");
    }
    echo "Found " . count($tierIds) . " scholarship tiers.\n\n";

    // Prepare status distribution
    $statuses = ['approved', 'rejected', 'reviewing', 'pending'];
    
    // Generate 150 application records for better visualization
    $totalRecords = 150;
    echo "Generating {$totalRecords} application records...\n";

    $insertedCount = 0;
    $approvedApplications = [];

    // Prepare insert statement for applications
    $insertAppStmt = $pdo->prepare("
        INSERT INTO applications 
        (user_id, student_profile_id, tier_id, status, applied_date, notes) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < $totalRecords; $i++) {
        // Random selections
        $userId = $studentUserIds[array_rand($studentUserIds)];
        $profileId = $studentProfileIds[array_rand($studentProfileIds)];
        $tierId = $tierIds[array_rand($tierIds)];
        $status = $statuses[array_rand($statuses)];

        // Generate timestamp with peak hours (40% morning, 40% evening, 20% other)
        $rand = mt_rand(1, 100);
        
        if ($rand <= 40) {
            // Morning peak: 09:00-11:00
            $hour = mt_rand(9, 10);
            $minute = mt_rand(0, 59);
        } elseif ($rand <= 80) {
            // Evening peak: 20:00-22:00
            $hour = mt_rand(20, 21);
            $minute = mt_rand(0, 59);
        } else {
            // Other hours
            $hour = mt_rand(0, 23);
            // Avoid peak hours
            while (($hour >= 9 && $hour <= 10) || ($hour >= 20 && $hour <= 21)) {
                $hour = mt_rand(0, 23);
            }
            $minute = mt_rand(0, 59);
        }

        // Random date within the last 90 days
        $daysAgo = mt_rand(0, 90);
        $timestamp = strtotime("-{$daysAgo} days");
        $appliedDate = date('Y-m-d', $timestamp) . ' ' . sprintf('%02d:%02d:%02d', $hour, $minute, mt_rand(0, 59));

        $notes = "Seeded application record #" . ($i + 1);

        // Insert application
        $insertAppStmt->execute([
            $userId,
            $profileId,
            $tierId,
            $status,
            $appliedDate,
            $notes
        ]);

        $applicationId = $pdo->lastInsertId();
        $insertedCount++;

        // Track approved applications for financial data generation
        if ($status === 'approved') {
            $approvedApplications[] = $applicationId;
        }

        // Progress indicator
        if (($i + 1) % 25 == 0) {
            echo "Progress: " . ($i + 1) . "/{$totalRecords} applications created...\n";
        }
    }

    echo "\nSuccessfully inserted {$insertedCount} application records.\n";
    echo "Approved applications: " . count($approvedApplications) . "\n\n";

    // Step 4: Generate financial tracking data for approved applications
    if (!empty($approvedApplications)) {
        echo "Generating financial tracking data...\n";

        // Prepare insert statements
        $insertDecisionStmt = $pdo->prepare("
            INSERT INTO scholarship_decisions 
            (application_id, decision_date, granted_amount, decision_notes) 
            VALUES (?, ?, ?, ?)
        ");

        $insertDisbursementStmt = $pdo->prepare("
            INSERT INTO disbursements 
            (decision_id, amount_paid, payment_date, payment_method, status, disbursement_notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $financialCount = 0;
        $paymentMethods = ['bank_transfer', 'check', 'cash', 'online_payment'];

        foreach ($approvedApplications as $appId) {
            // Generate random granted amount (5M - 20M VND)
            $grantedAmount = mt_rand(5000000, 20000000);
            
            // Decision date is a few days after application
            $decisionDate = date('Y-m-d H:i:s', strtotime('+' . mt_rand(3, 14) . ' days'));

            // Insert scholarship decision
            $insertDecisionStmt->execute([
                $appId,
                $decisionDate,
                $grantedAmount,
                "Approved - Seeded financial record"
            ]);

            $decisionId = $pdo->lastInsertId();

            // Insert corresponding disbursement (completed)
            $paymentDate = date('Y-m-d H:i:s', strtotime($decisionDate . ' +' . mt_rand(7, 30) . ' days'));
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            $insertDisbursementStmt->execute([
                $decisionId,
                $grantedAmount, // amount_paid matches granted_amount
                $paymentDate,
                $paymentMethod,
                'completed',
                "Payment completed - Seeded record"
            ]);

            $financialCount++;
        }

        echo "Successfully created {$financialCount} scholarship decisions and disbursements.\n";
    }

    echo "\n===== Seeding Completed Successfully! =====\n";
    echo "Summary:\n";
    echo "- Applications created: {$insertedCount}\n";
    echo "- Approved applications: " . count($approvedApplications) . "\n";
    echo "- Financial records created: " . (isset($financialCount) ? $financialCount : 0) . "\n";
    echo "\nYou can now view the dashboard with populated metrics!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
