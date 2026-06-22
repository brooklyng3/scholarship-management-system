-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 07:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scholarship_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tier_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `status` enum('pending','reviewing','approved','rejected','waitlisted') DEFAULT 'pending',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `tier_id`, `profile_id`, `reviewer_id`, `status`, `applied_date`) VALUES
(1, 6, 1, 1, 3, 'approved', '2026-06-22 07:53:50'),
(2, 8, 1, 3, 3, 'approved', '2026-06-22 07:53:50'),
(3, 10, 2, 5, 4, 'waitlisted', '2026-06-22 07:53:50'),
(5, 7, 3, 2, 4, 'rejected', '2026-06-22 07:53:50'),
(6, 9, 3, 4, 3, 'approved', '2026-06-22 07:53:50'),
(7, 11, 3, 6, 4, 'pending', '2026-06-22 07:53:50'),
(8, 16, 4, 11, 3, 'pending', '2026-06-22 07:53:50'),
(9, 20, 4, 15, 4, 'approved', '2026-06-22 07:53:50'),
(10, 14, 5, 9, 5, 'approved', '2026-06-22 07:53:50'),
(11, 17, 5, 12, 5, 'rejected', '2026-06-22 07:53:50'),
(12, 13, 6, 8, 5, 'reviewing', '2026-06-22 07:53:50'),
(13, 15, 6, 10, 5, 'waitlisted', '2026-06-22 07:53:50'),
(14, 18, 7, 13, 3, 'approved', '2026-06-22 07:53:50'),
(15, 19, 8, 14, 4, 'reviewing', '2026-06-22 07:53:50'),
(16, 7, 4, 2, 3, 'approved', '2026-06-22 07:53:50'),
(17, 13, 1, 8, 3, 'pending', '2026-06-22 07:53:50'),
(18, 14, 2, 9, 4, 'reviewing', '2026-06-22 07:53:50'),
(19, 15, 3, 10, 3, 'approved', '2026-06-22 07:53:50'),
(20, 18, 3, 13, 3, 'pending', '2026-06-22 07:53:50'),
(21, 16, 6, 11, 5, 'rejected', '2026-06-22 07:53:50'),
(22, 17, 1, 12, 3, 'reviewing', '2026-06-22 07:53:50'),
(24, 6, 4, 1, 4, 'rejected', '2026-06-22 07:53:50'),
(25, 8, 4, 3, 3, 'pending', '2026-06-22 07:53:50'),
(26, 9, 1, 4, 3, 'reviewing', '2026-06-22 07:53:50'),
(27, 10, 4, 5, 4, 'approved', '2026-06-22 07:53:50'),
(28, 19, 5, 14, 5, 'reviewing', '2026-06-22 07:53:50'),
(29, 20, 8, 15, 3, 'pending', '2026-06-22 07:53:50'),
(30, 11, 8, 6, 4, 'approved', '2026-06-22 07:53:50'),
(32, 12, 4, 7, NULL, 'approved', '2026-06-22 16:38:52'),
(33, 12, 1, 7, NULL, 'rejected', '2026-06-22 16:47:41');

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

CREATE TABLE `application_documents` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_documents`
--

INSERT INTO `application_documents` (`id`, `application_id`, `document_type`, `file_url`, `uploaded_at`) VALUES
(1, 1, 'transcript', '/uploads/docs/transcript_stu01.pdf', '2026-06-22 07:53:50'),
(2, 2, 'ielts', '/uploads/docs/ielts_stu03.pdf', '2026-06-22 07:53:50'),
(3, 8, 'poverty_proof', '/uploads/docs/proof_stu11.pdf', '2026-06-22 07:53:50'),
(4, 9, 'poverty_proof', '/uploads/docs/proof_stu15.pdf', '2026-06-22 07:53:50'),
(5, 10, 'cert_ctf', '/uploads/docs/ctf_stu09.pdf', '2026-06-22 07:53:50'),
(6, 14, 'national_prize', '/uploads/docs/prize_stu13.pdf', '2026-06-22 07:53:50'),
(7, 27, 'poverty_proof', '/uploads/docs/proof_stu05.pdf', '2026-06-22 07:53:50'),
(11, 32, 'proof', 'uploads/docs/app_32_1782146332_6a7332e79010a9a0.pdf', '2026-06-22 16:38:52'),
(12, 32, 'proof', 'uploads/docs/app_32_1782146332_0dd19a65ed25d661.jpg', '2026-06-22 16:38:52'),
(13, 33, 'proof', 'uploads/docs/app_33_1782146861_39fca92780f0845c.png', '2026-06-22 16:47:41');

-- --------------------------------------------------------

--
-- Table structure for table `application_reviews`
--

CREATE TABLE `application_reviews` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_reviews`
--

INSERT INTO `application_reviews` (`id`, `application_id`, `reviewer_id`, `comment`, `created_at`) VALUES
(1, 33, 4, 'Are you kidding me?', '2026-06-22 17:17:13'),
(4, 32, 4, 'Congrats my friend~', '2026-06-22 17:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `award_certificates`
--

CREATE TABLE `award_certificates` (
  `id` int(11) NOT NULL,
  `decision_id` int(11) NOT NULL,
  `certificate_code` varchar(50) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `pdf_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `award_certificates`
--

INSERT INTO `award_certificates` (`id`, `decision_id`, `certificate_code`, `issue_date`, `pdf_url`) VALUES
(1, 1, 'CERT-2026-EXC-001', '2026-06-25', '/uploads/certs/cert_stu01.pdf'),
(2, 2, 'CERT-2026-EXC-002', '2026-06-25', '/uploads/certs/cert_stu03.pdf'),
(3, 5, 'CERT-2026-VCS-001', '2026-06-25', '/uploads/certs/cert_stu09.pdf'),
(4, 6, 'CERT-2026-CVA-001', '2026-06-25', '/uploads/certs/cert_stu13.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `decision_id` int(11) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('processing','completed','failed') DEFAULT 'processing',
  `payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disbursements`
--

INSERT INTO `disbursements` (`id`, `decision_id`, `amount_paid`, `payment_method`, `status`, `payment_date`) VALUES
(1, 1, 15000000.00, 'bank_transfer', 'completed', '2026-06-22'),
(2, 2, 15000000.00, 'bank_transfer', 'completed', '2026-06-22'),
(3, 3, 5000000.00, 'bank_transfer', 'processing', NULL),
(4, 5, 30000000.00, 'cash', 'completed', '2026-06-24'),
(5, 6, 25000000.00, 'bank_transfer', 'processing', NULL),
(6, 9, 8000000.00, 'bank_transfer', 'completed', '2026-06-26');

-- --------------------------------------------------------

--
-- Table structure for table `eligibility_rules`
--

CREATE TABLE `eligibility_rules` (
  `id` int(11) NOT NULL,
  `tier_id` int(11) NOT NULL,
  `dynamic_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dynamic_rules`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eligibility_rules`
--

INSERT INTO `eligibility_rules` (`id`, `tier_id`, `dynamic_rules`) VALUES
(1, 1, '{\"requires_research\": true, \"min_english_score\": \"IELTS 7.0\"}'),
(2, 2, '{\"requires_research\": false, \"min_english_score\": \"IELTS 6.5\"}'),
(3, 3, '{\"requires_research\": false, \"min_english_score\": \"TOEIC 650\"}'),
(4, 4, '{\"requires_poverty_certificate\": true}'),
(5, 5, '{\"requires_ctf_prize\": true, \"interview_required\": true}'),
(6, 6, '{\"requires_ctf_prize\": false, \"interview_required\": true}'),
(7, 7, '{\"requires_national_prize\": true}'),
(8, 8, '{\"requires_university_prize\": true}');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_scores`
--

CREATE TABLE `evaluation_scores` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluation_scores`
--

INSERT INTO `evaluation_scores` (`id`, `application_id`, `criteria_id`, `reviewer_id`, `score`, `comments`) VALUES
(1, 1, 1, 3, 9.50, 'Outstanding GPA.'),
(2, 2, 1, 3, 9.80, 'Top of the class.'),
(3, 10, 5, 5, 9.50, 'Excellent CTF skills.'),
(4, 14, 7, 3, 9.00, 'Solid research methodology.'),
(5, 27, 3, 4, 8.50, 'Valid proofs submitted.');

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_decisions`
--

CREATE TABLE `scholarship_decisions` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `final_status` enum('approved','waitlisted','rejected') NOT NULL,
  `granted_amount` decimal(12,2) DEFAULT NULL,
  `decision_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarship_decisions`
--

INSERT INTO `scholarship_decisions` (`id`, `application_id`, `final_status`, `granted_amount`, `decision_date`) VALUES
(1, 1, 'approved', 15000000.00, '2026-06-20'),
(2, 2, 'approved', 15000000.00, '2026-06-20'),
(3, 6, 'approved', 5000000.00, '2026-06-21'),
(4, 9, 'approved', 8000000.00, '2026-06-21'),
(5, 10, 'approved', 30000000.00, '2026-06-22'),
(6, 14, 'approved', 25000000.00, '2026-06-23'),
(7, 16, 'approved', 8000000.00, '2026-06-23'),
(8, 19, 'approved', 5000000.00, '2026-06-24'),
(9, 27, 'approved', 8000000.00, '2026-06-25'),
(10, 30, 'approved', 15000000.00, '2026-06-25');

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_programs`
--

CREATE TABLE `scholarship_programs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `scholarship_type` enum('internal_academic','corporate_sponsor','social_support') NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','closed','draft') DEFAULT 'draft',
  `min_gpa` decimal(3,2) DEFAULT 0.00,
  `min_training_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarship_programs`
--

INSERT INTO `scholarship_programs` (`id`, `title`, `scholarship_type`, `start_date`, `end_date`, `status`, `min_gpa`, `min_training_score`) VALUES
(1, 'Academic Encouragement Scholarship Fall 2026', 'internal_academic', '2026-06-01', '2026-06-30', 'active', 3.20, 75),
(2, 'Social Support Grant 2026', 'social_support', '2026-07-01', '2026-07-31', 'active', 2.00, 60),
(3, 'Viettel Cyber Security Talent Grant', 'corporate_sponsor', '2026-05-15', '2026-06-15', 'active', 3.20, 75),
(4, 'Chu Van An Excellence Scholarship', 'internal_academic', '2026-08-01', '2026-08-30', 'draft', 3.50, 80);

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_tiers`
--

CREATE TABLE `scholarship_tiers` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `tier_name` varchar(100) NOT NULL,
  `reward_amount` decimal(12,2) NOT NULL,
  `quota` int(11) DEFAULT 0,
  `min_gpa` decimal(3,2) DEFAULT 0.00,
  `min_training_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarship_tiers`
--

INSERT INTO `scholarship_tiers` (`id`, `program_id`, `tier_name`, `reward_amount`, `quota`, `min_gpa`, `min_training_score`) VALUES
(1, 1, 'Excellence Rank 1', 15000000.00, 10, 3.50, 85),
(2, 1, 'Excellence Rank 2', 10000000.00, 20, 3.20, 75),
(3, 1, 'Good Rank', 5000000.00, 50, 3.20, 75),
(4, 2, 'Standard Financial Grant', 8000000.00, 100, 2.00, 60),
(5, 3, 'VCS First Prize', 30000000.00, 2, 3.20, 75),
(6, 3, 'VCS Second Prize', 15000000.00, 5, 3.20, 75),
(7, 4, 'Chu Van An Gold Medal', 25000000.00, 5, 3.50, 80),
(8, 4, 'Chu Van An Silver Medal', 15000000.00, 10, 3.50, 80);

-- --------------------------------------------------------

--
-- Table structure for table `scoring_criteria`
--

CREATE TABLE `scoring_criteria` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `criteria_name` varchar(150) NOT NULL,
  `weight` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scoring_criteria`
--

INSERT INTO `scoring_criteria` (`id`, `program_id`, `criteria_name`, `weight`) VALUES
(1, 1, 'Academic GPA Score', 60.00),
(2, 1, 'Extracurricular Activities', 40.00),
(3, 2, 'Financial Need Assessment', 80.00),
(4, 2, 'Academic Effort Score', 20.00),
(5, 3, 'Technical Skills Test (CTF)', 70.00),
(6, 3, 'Interview Performance', 30.00),
(7, 4, 'Scientific Research Quality', 50.00),
(8, 4, 'Overall GPA', 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_code` varchar(20) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `user_id`, `staff_code`, `department`, `updated_at`) VALUES
(1, 1, 'ADM-001', 'Student Affairs', '2026-06-22 07:53:50'),
(2, 2, 'ADM-002', 'Finance Department', '2026-06-22 07:53:50'),
(3, 3, 'REV-001', 'Faculty of IT', '2026-06-22 07:53:50'),
(4, 4, 'REV-002', 'Faculty of Business', '2026-06-22 07:53:50'),
(5, 5, 'REV-003', 'Cyber Security Center', '2026-06-22 07:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_code` varchar(20) NOT NULL,
  `major` varchar(100) DEFAULT NULL,
  `current_gpa` decimal(3,2) DEFAULT NULL,
  `accumulated_credits` int(11) DEFAULT NULL,
  `training_score` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `major`, `current_gpa`, `accumulated_credits`, `training_score`, `updated_at`) VALUES
(1, 6, 'STU-26-001', 'Information Systems', 3.90, 110, 95, '2026-06-22 07:53:50'),
(2, 7, 'STU-26-002', 'Information Systems', 3.20, 80, 85, '2026-06-22 07:53:50'),
(3, 8, 'STU-26-003', 'Computer Science', 3.95, 115, 90, '2026-06-22 07:53:50'),
(4, 9, 'STU-26-004', 'Computer Science', 2.80, 70, 75, '2026-06-22 07:53:50'),
(5, 10, 'STU-26-005', 'Business Admin', 3.85, 100, 92, '2026-06-22 07:53:50'),
(6, 11, 'STU-26-006', 'Business Admin', 3.10, 75, 80, '2026-06-22 07:53:50'),
(7, 12, 'STU-26-007', 'Cyber Security', 3.75, 105, 95, '2026-06-22 07:53:50'),
(8, 13, 'STU-26-008', 'Cyber Security', 3.60, 95, 88, '2026-06-22 07:53:50'),
(9, 14, 'STU-26-009', 'Data Science', 3.40, 90, 85, '2026-06-22 07:53:50'),
(10, 15, 'STU-26-010', 'Data Science', 3.65, 95, 90, '2026-06-22 07:53:50'),
(11, 16, 'STU-26-011', 'Information Systems', 2.50, 60, 70, '2026-06-22 07:53:50'),
(12, 17, 'STU-26-012', 'Computer Science', 3.88, 105, 94, '2026-06-22 07:53:50'),
(13, 18, 'STU-26-013', 'Business Admin', 3.35, 85, 82, '2026-06-22 07:53:50'),
(14, 19, 'STU-26-014', 'Cyber Security', 3.70, 98, 91, '2026-06-22 07:53:50'),
(15, 20, 'STU-26-015', 'Data Science', 2.20, 50, 65, '2026-06-22 07:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('admin','student','reviewer') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin_sys@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin', '2026-06-22 07:53:50'),
(2, 'admin_fin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Finance Manager', 'admin', '2026-06-22 07:53:50'),
(3, 'reviewer1@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Alan Turing', 'reviewer', '2026-06-22 07:53:50'),
(4, 'reviewer2@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Philip Kotler', 'reviewer', '2026-06-22 07:53:50'),
(5, 'reviewer3@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Kevin Mitnick', 'reviewer', '2026-06-22 07:53:50'),
(6, 'stu01@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex Johnson', 'student', '2026-06-22 07:53:50'),
(7, 'stu02@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Brian Smith', 'student', '2026-06-22 07:53:50'),
(8, 'stu03@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Catherine Lee', 'student', '2026-06-22 07:53:50'),
(9, 'stu04@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Miller', 'student', '2026-06-22 07:53:50'),
(10, 'stu05@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily Davis', 'student', '2026-06-22 07:53:50'),
(11, 'stu06@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fiona Wilson', 'student', '2026-06-22 07:53:50'),
(12, 'stu07@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'George Moore', 'student', '2026-06-22 07:53:50'),
(13, 'stu08@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hannah Taylor', 'student', '2026-06-22 07:53:50'),
(14, 'stu09@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ian Anderson', 'student', '2026-06-22 07:53:50'),
(15, 'stu10@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jessica Thomas', 'student', '2026-06-22 07:53:50'),
(16, 'stu11@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kevin Jackson', 'student', '2026-06-22 07:53:50'),
(17, 'stu12@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Laura White', 'student', '2026-06-22 07:53:50'),
(18, 'stu13@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael Harris', 'student', '2026-06-22 07:53:50'),
(19, 'stu14@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nina Martin', 'student', '2026-06-22 07:53:50'),
(20, 'stu15@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Oliver Thompson', 'student', '2026-06-22 07:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `violation_records`
--

CREATE TABLE `violation_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `violation_type` enum('fee_debt','discipline','library_debt') NOT NULL,
  `description` text DEFAULT NULL,
  `recorded_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_tier` (`user_id`,`tier_id`),
  ADD KEY `tier_id` (`tier_id`),
  ADD KEY `profile_id` (`profile_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `application_reviews`
--
ALTER TABLE `application_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_app_review` (`application_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `award_certificates`
--
ALTER TABLE `award_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `decision_id` (`decision_id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `decision_id` (`decision_id`);

--
-- Indexes for table `eligibility_rules`
--
ALTER TABLE `eligibility_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tier_id` (`tier_id`);

--
-- Indexes for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `criteria_id` (`criteria_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `scholarship_decisions`
--
ALTER TABLE `scholarship_decisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `scholarship_programs`
--
ALTER TABLE `scholarship_programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholarship_tiers`
--
ALTER TABLE `scholarship_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `scoring_criteria`
--
ALTER TABLE `scoring_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `staff_code` (`staff_code`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_code` (`student_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `violation_records`
--
ALTER TABLE `violation_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `application_reviews`
--
ALTER TABLE `application_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `award_certificates`
--
ALTER TABLE `award_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `eligibility_rules`
--
ALTER TABLE `eligibility_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scholarship_decisions`
--
ALTER TABLE `scholarship_decisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `scholarship_programs`
--
ALTER TABLE `scholarship_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scholarship_tiers`
--
ALTER TABLE `scholarship_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `scoring_criteria`
--
ALTER TABLE `scoring_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `violation_records`
--
ALTER TABLE `violation_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `scholarship_tiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_4` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_reviews`
--
ALTER TABLE `application_reviews`
  ADD CONSTRAINT `application_reviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `award_certificates`
--
ALTER TABLE `award_certificates`
  ADD CONSTRAINT `award_certificates_ibfk_1` FOREIGN KEY (`decision_id`) REFERENCES `scholarship_decisions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD CONSTRAINT `disbursements_ibfk_1` FOREIGN KEY (`decision_id`) REFERENCES `scholarship_decisions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eligibility_rules`
--
ALTER TABLE `eligibility_rules`
  ADD CONSTRAINT `eligibility_rules_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `scholarship_tiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD CONSTRAINT `evaluation_scores_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_scores_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `scoring_criteria` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_scores_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholarship_decisions`
--
ALTER TABLE `scholarship_decisions`
  ADD CONSTRAINT `scholarship_decisions_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholarship_tiers`
--
ALTER TABLE `scholarship_tiers`
  ADD CONSTRAINT `scholarship_tiers_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `scholarship_programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scoring_criteria`
--
ALTER TABLE `scoring_criteria`
  ADD CONSTRAINT `scoring_criteria_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `scholarship_programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `staff_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `violation_records`
--
ALTER TABLE `violation_records`
  ADD CONSTRAINT `violation_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
