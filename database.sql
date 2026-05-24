-- 1. Tạo Database
CREATE DATABASE IF NOT EXISTS scholarship_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scholarship_system;

-- ==========================================
-- CỤM 1: HỆ THỐNG & USER (SUPERTYPE/SUBTYPE)
-- ==========================================

-- Bảng Supertype: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL COMMENT 'Lưu họ tên chung cho toàn bộ User',
    role ENUM('admin', 'student', 'reviewer') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng Subtype 1: student_profiles
CREATE TABLE student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- (Đã bỏ UNIQUE để 1 SV có nhiều profile các kỳ khác nhau)
    student_code VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    major VARCHAR(100),
    current_gpa DECIMAL(3,2),
    accumulated_credits INT,
    conduct_score INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Subtype 2: staff_profiles
CREATE TABLE staff_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    staff_code VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(100) COMMENT 'VD: Phòng Đào tạo, Phòng CTSV',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng violation_records
CREATE TABLE violation_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    violation_type ENUM('fee_debt', 'discipline', 'library_debt') NOT NULL,
    description TEXT,
    recorded_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng scholarship_programs
CREATE TABLE scholarship_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    scholarship_type ENUM('internal_academic', 'corporate_sponsor', 'social_support') NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'closed', 'draft') DEFAULT 'draft'
) ENGINE=InnoDB;

-- ==========================================
-- CỤM 2: QUY TẮC & ỨNG TUYỂN
-- ==========================================

-- Bảng scholarship_tiers
CREATE TABLE scholarship_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    tier_name VARCHAR(100) NOT NULL COMMENT 'Ex: Xuất sắc hạng 1',
    reward_amount DECIMAL(12,2) NOT NULL,
    quota INT DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES scholarship_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng eligibility_rules
CREATE TABLE eligibility_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tier_id INT UNIQUE NOT NULL,
    min_gpa DECIMAL(3,2) DEFAULT 0.00,
    dynamic_rules JSON COMMENT 'Lưu JSON các điều kiện mở rộng (NCKH, ĐKTA...)',
    FOREIGN KEY (tier_id) REFERENCES scholarship_tiers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng applications
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tier_id INT NOT NULL,
    profile_id INT NOT NULL, -- (Link trực tiếp đến profile snapshot của SV lúc nộp)
    status ENUM('pending', 'reviewing', 'evaluated') DEFAULT 'pending',
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tier_id) REFERENCES scholarship_tiers(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tier (user_id, tier_id)
) ENGINE=InnoDB;

-- Bảng application_documents
CREATE TABLE application_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- CỤM 3: CHẤM ĐIỂM & ĐẦU RA
-- ==========================================

-- Bảng scoring_criteria
CREATE TABLE scoring_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    criteria_name VARCHAR(150) NOT NULL,
    weight DECIMAL(5,2) NOT NULL COMMENT 'Trọng số phần trăm',
    FOREIGN KEY (program_id) REFERENCES scholarship_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng evaluation_scores
CREATE TABLE evaluation_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    criteria_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comments TEXT,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (criteria_id) REFERENCES scoring_criteria(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng scholarship_decisions
CREATE TABLE scholarship_decisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNIQUE NOT NULL,
    final_status ENUM('approved', 'waitlisted', 'rejected') NOT NULL,
    granted_amount DECIMAL(12,2),
    decision_date DATE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng disbursements
CREATE TABLE disbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    payment_date DATE,
    FOREIGN KEY (decision_id) REFERENCES scholarship_decisions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng award_certificates
CREATE TABLE award_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT UNIQUE NOT NULL,
    certificate_code VARCHAR(50) UNIQUE NOT NULL,
    issue_date DATE,
    pdf_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (decision_id) REFERENCES scholarship_decisions(id) ON DELETE CASCADE
) ENGINE=InnoDB;