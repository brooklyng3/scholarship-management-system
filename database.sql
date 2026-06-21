-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 12:32 AM
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
  `status` enum('pending','reviewing','approved','rejected') DEFAULT 'pending',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `tier_id`, `profile_id`, `reviewer_id`, `status`, `applied_date`) VALUES
(1, 6, 1, 1, 3, '', '2026-06-04 21:07:08'),
(2, 8, 1, 3, 3, '', '2026-06-04 21:07:08'),
(3, 13, 1, 8, 3, '', '2026-06-04 21:07:08'),
(4, 17, 1, 12, 3, '', '2026-06-04 21:07:08'),
(5, 12, 2, 7, 3, '', '2026-06-04 21:07:08'),
(6, 15, 2, 10, 3, '', '2026-06-04 21:07:08'),
(7, 19, 2, 14, 3, 'reviewing', '2026-06-04 21:07:08'),
(8, 20, 2, 15, 3, 'reviewing', '2026-06-04 21:07:08'),
(9, 7, 3, 2, 3, 'pending', '2026-06-04 21:07:08'),
(10, 10, 3, 5, 3, '', '2026-06-04 21:07:08'),
(11, 14, 3, 9, 3, '', '2026-06-04 21:07:08'),
(12, 18, 3, 13, 3, 'reviewing', '2026-06-04 21:07:08'),
(13, 11, 4, 6, 3, 'pending', '2026-06-04 21:07:08'),
(14, 8, 4, 3, 3, '', '2026-06-04 21:07:08'),
(15, 6, 5, 1, 3, '', '2026-06-04 21:07:08'),
(16, 8, 6, 3, 3, 'pending', '2026-06-21 20:31:39'),
(17, 8, 7, 3, 3, 'pending', '2026-06-21 20:53:44');

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
(1, 1, 'transcript', '/uploads/docs/transcript_sv001.pdf', '2026-06-04 21:07:09'),
(2, 1, 'ielts', '/uploads/docs/ielts_sv001.pdf', '2026-06-04 21:07:09'),
(3, 2, 'transcript', '/uploads/docs/transcript_sv003.pdf', '2026-06-04 21:07:09'),
(4, 2, 'research_paper', '/uploads/docs/paper_sv003.pdf', '2026-06-04 21:07:09'),
(5, 3, 'transcript', '/uploads/docs/transcript_sv008.pdf', '2026-06-04 21:07:09'),
(6, 4, 'transcript', '/uploads/docs/transcript_sv012.pdf', '2026-06-04 21:07:09'),
(7, 4, 'ielts', '/uploads/docs/ielts_sv012.pdf', '2026-06-04 21:07:09'),
(8, 5, 'transcript', '/uploads/docs/transcript_sv007.pdf', '2026-06-04 21:07:09'),
(9, 6, 'transcript', '/uploads/docs/transcript_sv010.pdf', '2026-06-04 21:07:09'),
(10, 13, 'ctf_certificate', '/uploads/docs/ctf_sv006.pdf', '2026-06-04 21:07:09'),
(11, 14, 'ctf_certificate', '/uploads/docs/ctf_sv003.pdf', '2026-06-04 21:07:09'),
(12, 15, 'security_plus', '/uploads/docs/sec_sv001.pdf', '2026-06-04 21:07:09'),
(15, 17, 'proof', 'uploads/docs/app_17_1782075224_5b7b8b15c8347b12.jpg', '2026-06-21 20:53:44');

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
(1, 1, 'CERT-26-0001', '2026-06-25', '/uploads/certs/cert_0001.pdf'),
(2, 2, 'CERT-26-0002', '2026-06-25', '/uploads/certs/cert_0002.pdf'),
(3, 3, 'CERT-26-0003', '2026-06-25', '/uploads/certs/cert_0003.pdf'),
(4, 4, 'CERT-26-0004', '2026-06-25', '/uploads/certs/cert_0004.pdf'),
(5, 5, 'CERT-26-0005', '2026-06-25', '/uploads/certs/cert_0005.pdf'),
(6, 8, 'VCS-26-0001', '2026-06-30', '/uploads/certs/vcs_0001.pdf'),
(7, 9, 'VCS-26-0002', '2026-06-30', '/uploads/certs/vcs_0002.pdf');

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
(3, 3, 15000000.00, 'bank_transfer', 'completed', '2026-06-22'),
(4, 4, 15000000.00, 'bank_transfer', 'processing', NULL),
(5, 5, 10000000.00, 'bank_transfer', 'processing', NULL),
(6, 8, 25000000.00, 'cash', 'completed', '2026-06-25'),
(7, 9, 15000000.00, 'cash', 'completed', '2026-06-25');

-- --------------------------------------------------------

--
-- Table structure for table `eligibility_rules`
--

CREATE TABLE `eligibility_rules` (
  `id` int(11) NOT NULL,
  `tier_id` int(11) NOT NULL,
  `min_gpa` decimal(3,2) DEFAULT 0.00,
  `dynamic_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Lưu JSON các điều kiện mở rộng (NCKH, ĐKTA...)' CHECK (json_valid(`dynamic_rules`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eligibility_rules`
--

INSERT INTO `eligibility_rules` (`id`, `tier_id`, `min_gpa`, `dynamic_rules`) VALUES
(1, 1, 3.60, '{\"requires_research\": true, \"min_english_score\": \"IELTS 6.5\"}'),
(2, 2, 3.20, '{\"requires_research\": false, \"min_english_score\": \"TOEIC 600\"}'),
(3, 3, 2.50, '{\"requires_research\": false}'),
(4, 4, 3.50, '{\"requires_ctf_prize\": true}'),
(5, 5, 3.00, '{\"requires_ctf_prize\": false, \"min_security_cert\": \"Security+\"}'),
(6, 6, 2.00, '{\"requires_poverty_certificate\": true}'),
(7, 7, 2.00, '{\"requires_poverty_certificate\": true}');

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
(1, 1, 1, 3, 9.50, 'GPA Rất cao.'),
(2, 1, 2, 3, 9.00, 'Điểm rèn luyện tốt.'),
(3, 1, 3, 3, 8.50, 'Có tham gia NCKH cấp trường.'),
(4, 2, 1, 4, 9.75, 'GPA xuất sắc.'),
(5, 2, 2, 4, 9.50, 'Thái độ tốt.'),
(6, 2, 3, 4, 9.50, 'Có bài báo quốc tế.'),
(7, 3, 1, 3, 9.80, 'Thủ khoa ngành.'),
(8, 3, 2, 3, 9.80, 'Xuất sắc.'),
(9, 3, 3, 3, 9.00, 'Tốt.'),
(10, 14, 4, 5, 9.00, 'Kỹ năng CTF rất vững.'),
(11, 14, 5, 5, 8.50, 'Phỏng vấn lưu loát, hiểu sâu.'),
(12, 15, 4, 5, 8.00, 'Test kỹ thuật đạt yêu cầu.'),
(13, 15, 5, 5, 7.50, 'Phỏng vấn hơi rụt rè.');

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
(1, 1, 'approved', 15000000.00, '2026-06-15'),
(2, 2, 'approved', 15000000.00, '2026-06-15'),
(3, 3, 'approved', 15000000.00, '2026-06-15'),
(4, 4, 'approved', 15000000.00, '2026-06-15'),
(5, 5, 'approved', 10000000.00, '2026-06-15'),
(6, 6, 'waitlisted', NULL, '2026-06-15'),
(7, 10, 'rejected', NULL, '2026-06-15'),
(8, 14, 'approved', 25000000.00, '2026-06-20'),
(9, 15, 'approved', 15000000.00, '2026-06-20');

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
  `status` enum('active','closed','draft') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarship_programs`
--

INSERT INTO `scholarship_programs` (`id`, `title`, `scholarship_type`, `start_date`, `end_date`, `status`) VALUES
(1, 'Học bổng Khuyến khích Học tập Kỳ Thu 2026', 'internal_academic', '2026-06-01', '2026-06-30', 'active'),
(2, 'Học bổng Tài năng Viettel Cyber Security', 'corporate_sponsor', '2026-05-15', '2026-06-15', 'active'),
(3, 'Hỗ trợ Sinh viên Vượt khó 2026', 'social_support', '2026-07-01', '2026-07-31', 'draft'),
(4, 'Học bổng Nghiên cứu Khoa học Trẻ', 'internal_academic', '2026-08-01', '2026-08-30', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_tiers`
--

CREATE TABLE `scholarship_tiers` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `tier_name` varchar(100) NOT NULL COMMENT 'Ex: Xuất sắc hạng 1',
  `reward_amount` decimal(12,2) NOT NULL,
  `quota` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarship_tiers`
--

INSERT INTO `scholarship_tiers` (`id`, `program_id`, `tier_name`, `reward_amount`, `quota`) VALUES
(1, 1, 'Học bổng Xuất sắc (Loại 1)', 15000000.00, 5),
(2, 1, 'Học bổng Giỏi (Loại 2)', 10000000.00, 10),
(3, 1, 'Học bổng Khá (Loại 3)', 5000000.00, 20),
(4, 2, 'Giải Nhất VCS', 25000000.00, 1),
(5, 2, 'Giải Nhì VCS', 15000000.00, 3),
(6, 3, 'Trợ cấp Toàn phần', 12000000.00, 10),
(7, 3, 'Trợ cấp Bán phần', 6000000.00, 20);

-- --------------------------------------------------------

--
-- Table structure for table `scoring_criteria`
--

CREATE TABLE `scoring_criteria` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `criteria_name` varchar(150) NOT NULL,
  `weight` decimal(5,2) NOT NULL COMMENT 'Trọng số phần trăm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scoring_criteria`
--

INSERT INTO `scoring_criteria` (`id`, `program_id`, `criteria_name`, `weight`) VALUES
(1, 1, 'Điểm Trung bình Tích lũy (GPA)', 60.00),
(2, 1, 'Điểm Rèn luyện', 20.00),
(3, 1, 'Thành tích Ngoại khóa / NCKH', 20.00),
(4, 2, 'Kỹ năng Chuyên môn (Test/CTF)', 70.00),
(5, 2, 'Phỏng vấn Trực tiếp', 30.00),
(6, 3, 'Mức độ Hoàn cảnh Gia đình', 60.00),
(7, 3, 'Nỗ lực Học tập (GPA)', 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_code` varchar(20) NOT NULL,
  `department` varchar(100) DEFAULT NULL COMMENT 'VD: Phòng Đào tạo, Phòng CTSV',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `user_id`, `staff_code`, `department`, `updated_at`) VALUES
(1, 1, 'ADM_001', 'Phòng Công tác Sinh viên', '2026-06-04 21:07:07'),
(2, 2, 'ADM_002', 'Phòng Đào tạo', '2026-06-04 21:07:07'),
(3, 3, 'REV_001', 'Hội đồng Khoa học', '2026-06-04 21:07:07'),
(4, 4, 'REV_002', 'Hội đồng Khoa học', '2026-06-04 21:07:07'),
(5, 5, 'REV_003', 'Hội đồng Khoa học', '2026-06-04 21:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `student_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `major` varchar(100) DEFAULT NULL,
  `current_gpa` decimal(3,2) DEFAULT NULL,
  `accumulated_credits` int(11) DEFAULT NULL,
  `conduct_score` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `gpa`, `year_level`, `student_code`, `full_name`, `major`, `current_gpa`, `accumulated_credits`, `conduct_score`, `updated_at`) VALUES
(1, 6, NULL, NULL, 'SV2026001', 'Nguyễn Văn Một', 'Information Technology', 3.85, 95, 90, '2026-06-04 21:07:08'),
(2, 7, NULL, NULL, 'SV2026002', 'Trần Thị Hai', 'Computer Science', 3.20, 80, 85, '2026-06-04 21:07:08'),
(3, 8, NULL, NULL, 'SV2026003', 'Lê Văn Ba', 'Information Systems', 3.90, 110, 95, '2026-06-04 21:07:08'),
(4, 9, NULL, NULL, 'SV2026004', 'Phạm Thị Bốn', 'Software Engineering', 2.80, 70, 75, '2026-06-04 21:07:08'),
(5, 10, NULL, NULL, 'SV2026005', 'Hoàng Văn Năm', 'Data Science', 3.55, 85, 88, '2026-06-04 21:07:08'),
(6, 11, NULL, NULL, 'SV2026006', 'Vũ Thị Sáu', 'Cyber Security', 3.10, 75, 80, '2026-06-04 21:07:08'),
(7, 12, NULL, NULL, 'SV2026007', 'Đặng Văn Bảy', 'Information Technology', 3.75, 100, 92, '2026-06-04 21:07:08'),
(8, 13, NULL, NULL, 'SV2026008', 'Bùi Thị Tám', 'Computer Science', 3.95, 115, 98, '2026-06-04 21:07:08'),
(9, 14, NULL, NULL, 'SV2026009', 'Đỗ Văn Chín', 'Software Engineering', 3.40, 90, 85, '2026-06-04 21:07:08'),
(10, 15, NULL, NULL, 'SV2026010', 'Hồ Thị Mười', 'Data Science', 3.65, 95, 90, '2026-06-04 21:07:08'),
(11, 16, NULL, NULL, 'SV2026011', 'Ngô Văn Mười Một', 'Information Systems', 2.50, 60, 70, '2026-06-04 21:07:08'),
(12, 17, NULL, NULL, 'SV2026012', 'Dương Thị Mười Hai', 'Cyber Security', 3.88, 105, 94, '2026-06-04 21:07:08'),
(13, 18, NULL, NULL, 'SV2026013', 'Lý Văn Mười Ba', 'Information Technology', 3.35, 85, 82, '2026-06-04 21:07:08'),
(14, 19, NULL, NULL, 'SV2026014', 'Mai Thị Mười Bốn', 'Computer Science', 3.70, 98, 91, '2026-06-04 21:07:08'),
(15, 20, NULL, NULL, 'SV2026015', 'Trịnh Văn Mười Lăm', 'Software Engineering', 3.60, 92, 88, '2026-06-04 21:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL COMMENT 'Lưu họ tên chung cho toàn bộ User',
  `role` enum('admin','student','reviewer') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin1@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Quản Trị 1', 'admin', '2026-06-04 21:07:07'),
(2, 'admin2@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Quản Trị 2', 'admin', '2026-06-04 21:07:07'),
(3, 'reviewer1@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Giám Khảo 1', 'reviewer', '2026-06-04 21:07:07'),
(4, 'reviewer2@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Giám Khảo 2', 'reviewer', '2026-06-04 21:07:07'),
(5, 'reviewer3@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Giám Khảo 3', 'reviewer', '2026-06-04 21:07:07'),
(6, 'sv01@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Một', 'student', '2026-06-04 21:07:07'),
(7, 'sv02@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Hai', 'student', '2026-06-04 21:07:07'),
(8, 'sv03@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn Ba', 'student', '2026-06-04 21:07:07'),
(9, 'sv04@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị Bốn', 'student', '2026-06-04 21:07:07'),
(10, 'sv05@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoàng Văn Năm', 'student', '2026-06-04 21:07:07'),
(11, 'sv06@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vũ Thị Sáu', 'student', '2026-06-04 21:07:07'),
(12, 'sv07@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Đặng Văn Bảy', 'student', '2026-06-04 21:07:07'),
(13, 'sv08@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bùi Thị Tám', 'student', '2026-06-04 21:07:07'),
(14, 'sv09@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Đỗ Văn Chín', 'student', '2026-06-04 21:07:07'),
(15, 'sv10@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hồ Thị Mười', 'student', '2026-06-04 21:07:07'),
(16, 'sv11@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ngô Văn Mười Một', 'student', '2026-06-04 21:07:07'),
(17, 'sv12@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dương Thị Mười Hai', 'student', '2026-06-04 21:07:07'),
(18, 'sv13@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lý Văn Mười Ba', 'student', '2026-06-04 21:07:07'),
(19, 'sv14@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mai Thị Mười Bốn', 'student', '2026-06-04 21:07:07'),
(20, 'sv15@ischool.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trịnh Văn Mười Lăm', 'student', '2026-06-04 21:07:07');

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
-- Dumping data for table `violation_records`
--

INSERT INTO `violation_records` (`id`, `user_id`, `violation_type`, `description`, `recorded_date`) VALUES
(1, 9, 'fee_debt', 'Nợ học phí kỳ Thu 2025', '2025-11-15'),
(2, 11, 'library_debt', 'Quá hạn trả 3 sách thư viện', '2026-04-10'),
(3, 16, 'discipline', 'Vi phạm quy chế thi', '2025-12-20'),
(4, 7, 'library_debt', 'Chưa trả sách mượn', '2026-05-01');

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
  ADD KEY `fk_applications_reviewer` (`reviewer_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `award_certificates`
--
ALTER TABLE `award_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `eligibility_rules`
--
ALTER TABLE `eligibility_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `scholarship_decisions`
--
ALTER TABLE `scholarship_decisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `scholarship_programs`
--
ALTER TABLE `scholarship_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scholarship_tiers`
--
ALTER TABLE `scholarship_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `scoring_criteria`
--
ALTER TABLE `scoring_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `fk_applications_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

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
