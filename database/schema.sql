-- =======================================================
-- InternFlow: Internship Management Platform
-- Database Schema for MySQL 8.0+
-- Final Year Project (FYP) - UNITEN CCI
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS guidelines;
DROP TABLE IF EXISTS journey_stages;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS resource_categories;
DROP TABLE IF EXISTS email_log;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS final_grades;
DROP TABLE IF EXISTS final_reports;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS monthly_reports;
DROP TABLE IF EXISTS logbook_revisions;
DROP TABLE IF EXISTS logbook_reviews;
DROP TABLE IF EXISTS logbook_attachments;
DROP TABLE IF EXISTS logbooks;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS placements;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS vacancies;
DROP TABLE IF EXISTS company_users;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS invitation_tokens;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student', 'company', 'university_supervisor', 'coordinator') NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending', 'active', 'suspended', 'inactive') NOT NULL DEFAULT 'active',
    scope_placement_id INT NULL,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role),
    INDEX idx_user_status (status),
    INDEX idx_user_scope (scope_placement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. STUDENTS
CREATE TABLE students (
    user_id INT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    programme VARCHAR(100) NOT NULL,
    intake VARCHAR(50) NOT NULL,
    cgpa DECIMAL(3,2) NULL,
    skills TEXT NULL,
    interests TEXT NULL,
    emergency_contact VARCHAR(255) NULL,
    photo_path VARCHAR(255) NULL,
    verified TINYINT(1) NOT NULL DEFAULT 1,
    profile_complete TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PASSWORD RESETS
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pr_user (user_id),
    INDEX idx_pr_token (token_hash),
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. COMPANIES
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    registration_no VARCHAR(100) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    representative_name VARCHAR(255) NOT NULL,
    representative_email VARCHAR(255) NOT NULL,
    source ENUM('partner', 'self_sourced') NOT NULL DEFAULT 'partner',
    status ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'pending',
    registered_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company_status (status),
    INDEX idx_company_source (source),
    CONSTRAINT fk_comp_reg_by FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. COMPANY USERS
CREATE TABLE company_users (
    user_id INT NOT NULL,
    company_id INT NOT NULL,
    PRIMARY KEY (user_id, company_id),
    CONSTRAINT fk_cu_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cu_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. VACANCIES (Internship Listings)
CREATE TABLE vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    duties TEXT NOT NULL,
    requirements TEXT NOT NULL,
    slots INT NOT NULL DEFAULT 1,
    allowance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    location VARCHAR(255) NOT NULL,
    working_arrangement ENUM('onsite', 'remote', 'hybrid') NOT NULL DEFAULT 'onsite',
    duration_weeks INT NOT NULL DEFAULT 12,
    deadline DATE NOT NULL,
    status ENUM('draft', 'published', 'closed', 'flagged', 'deactivated') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vac_company (company_id),
    INDEX idx_vac_status (status),
    INDEX idx_vac_deadline (deadline),
    CONSTRAINT fk_vac_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. DOCUMENTS (Student CV, Verification letter, Transcript, etc.)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT NOT NULL,
    type ENUM('cv', 'verification_letter', 'transcript', 'other') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_doc_student (student_user_id),
    INDEX idx_doc_type (type),
    CONSTRAINT fk_doc_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. APPLICATIONS
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT NOT NULL,
    vacancy_id INT NOT NULL,
    cover_letter TEXT NULL,
    status ENUM('submitted', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'submitted',
    interview_details TEXT NULL,
    offer_issued TINYINT(1) NOT NULL DEFAULT 0,
    offer_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_app_student (student_user_id),
    INDEX idx_app_vac (vacancy_id),
    INDEX idx_app_status (status),
    CONSTRAINT fk_app_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_vac FOREIGN KEY (vacancy_id) REFERENCES vacancies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. PLACEMENTS
CREATE TABLE placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT NOT NULL,
    company_id INT NOT NULL,
    vacancy_id INT NULL,
    application_id INT NULL,
    source ENUM('platform', 'self_sourced') NOT NULL DEFAULT 'platform',
    position VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    supervisor_contact_name VARCHAR(255) NOT NULL,
    supervisor_contact_email VARCHAR(255) NOT NULL,
    company_supervisor_user_id INT NULL,
    university_supervisor_id INT NULL,
    status ENUM('placement_pending', 'university_approved', 'internship_active', 'completed', 'rejected') NOT NULL DEFAULT 'placement_pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plc_student (student_user_id),
    INDEX idx_plc_company (company_id),
    INDEX idx_plc_status (status),
    INDEX idx_plc_uni_sup (university_supervisor_id),
    INDEX idx_plc_comp_sup (company_supervisor_user_id),
    CONSTRAINT fk_plc_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_plc_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_plc_vac FOREIGN KEY (vacancy_id) REFERENCES vacancies(id) ON DELETE SET NULL,
    CONSTRAINT fk_plc_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    CONSTRAINT fk_plc_comp_sup FOREIGN KEY (company_supervisor_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_plc_uni_sup FOREIGN KEY (university_supervisor_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_plc_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. INVITATION TOKENS
CREATE TABLE invitation_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    placement_id INT NOT NULL,
    invited_email VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    revoked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_it_token (token_hash),
    INDEX idx_it_placement (placement_id),
    CONSTRAINT fk_it_placement FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ATTENDANCE
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL,
    work_date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    hours DECIMAL(4,2) NOT NULL DEFAULT 8.00,
    work_arrangement VARCHAR(50) NOT NULL DEFAULT 'onsite',
    status ENUM('present', 'absent', 'leave') NOT NULL DEFAULT 'present',
    absence_reason TEXT NULL,
    evidence_path VARCHAR(255) NULL,
    verification ENUM('pending_verification', 'verified', 'flagged') NOT NULL DEFAULT 'pending_verification',
    flag_comment TEXT NULL,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_plc_date (placement_id, work_date),
    INDEX idx_att_date (work_date),
    INDEX idx_att_ver (verification),
    CONSTRAINT fk_att_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. LOGBOOKS
CREATE TABLE logbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL,
    week_no INT NOT NULL,
    week_start DATE NOT NULL,
    week_end DATE NOT NULL,
    tasks TEXT NULL,
    skills TEXT NULL,
    challenges TEXT NULL,
    solutions TEXT NULL,
    reflection TEXT NULL,
    hours DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'submitted', 'company_reviewed', 'approved', 'returned', 'resubmitted') NOT NULL DEFAULT 'draft',
    grade DECIMAL(5,2) NULL,
    submitted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_plc_week (placement_id, week_no),
    INDEX idx_log_status (status),
    CONSTRAINT fk_log_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. LOGBOOK ATTACHMENTS
CREATE TABLE logbook_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logbook_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_la_log (logbook_id),
    CONSTRAINT fk_la_log FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. LOGBOOK REVIEWS
CREATE TABLE logbook_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logbook_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewer_role VARCHAR(50) NOT NULL,
    action ENUM('comment', 'approve', 'return') NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lr_log (logbook_id),
    INDEX idx_lr_rev (reviewer_id),
    CONSTRAINT fk_lr_log FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
    CONSTRAINT fk_lr_rev FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. LOGBOOK REVISIONS
CREATE TABLE logbook_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logbook_id INT NOT NULL,
    snapshot_json MEDIUMTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lrev_log (logbook_id),
    CONSTRAINT fk_lrev_log FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. MONTHLY REPORTS
CREATE TABLE monthly_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL,
    period_no INT NOT NULL,
    summary TEXT NOT NULL,
    stored_name VARCHAR(255) NULL,
    original_name VARCHAR(255) NULL,
    submitted_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_plc_period (placement_id, period_no),
    INDEX idx_mr_plc (placement_id),
    CONSTRAINT fk_mr_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. EVALUATIONS
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    type ENUM('host_monthly', 'host_final', 'visiting_lecturer') NOT NULL,
    period_no INT NULL,
    scores_json JSON NOT NULL,
    comments TEXT NULL,
    total_pct DECIMAL(5,2) NOT NULL,
    submitted_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eval_plc (placement_id),
    INDEX idx_eval_type (type),
    INDEX idx_evaluator (evaluator_id),
    CONSTRAINT fk_eval_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE,
    CONSTRAINT fk_eval_user FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. FINAL REPORTS
CREATE TABLE final_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL UNIQUE,
    stored_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    originality_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NOT NULL,
    mark DECIMAL(5,2) NULL,
    marked_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fr_marker (marked_by),
    CONSTRAINT fk_fr_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE,
    CONSTRAINT fk_fr_marker FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. FINAL GRADES
CREATE TABLE final_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL UNIQUE,
    host_score DECIMAL(5,2) NULL,
    visiting_score DECIMAL(5,2) NULL,
    report_score DECIMAL(5,2) NULL,
    logbook_score DECIMAL(5,2) NULL,
    total DECIMAL(5,2) NULL,
    locked TINYINT(1) NOT NULL DEFAULT 0,
    locked_by INT NULL,
    locked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fg_locked (locked),
    CONSTRAINT fk_fg_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE,
    CONSTRAINT fk_fg_locker FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. SETTINGS
CREATE TABLE settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings (Assessment weights & thresholds)
INSERT INTO settings (setting_key, setting_value) VALUES
('weight_host', '40'),
('weight_visiting', '30'),
('weight_report', '20'),
('weight_logbook', '10'),
('attendance_alert_pct', '80'),
('token_expiry_hours', '72'),
('session_timeout_min', '30'),
('max_upload_mb', '10');

-- 21. CONVERSATIONS (4-way Group Chat per Placement)
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conv_plc FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. MESSAGES
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_msg_conv (conversation_id),
    CONSTRAINT fk_msg_conv FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. NOTIFICATIONS
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    link VARCHAR(255) NULL,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_read (read_at),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. EMAIL LOG
CREATE TABLE email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    status ENUM('sent', 'logged', 'failed') NOT NULL DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_el_to (to_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. RESOURCE CATEGORIES (Module 14)
CREATE TABLE resource_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. RESOURCES (Module 14 Internship Kit Documents)
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    stored_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    version INT NOT NULL DEFAULT 1,
    status ENUM('draft', 'published', 'unpublished') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_res_cat (category_id),
    INDEX idx_res_status (status),
    CONSTRAINT fk_res_cat FOREIGN KEY (category_id) REFERENCES resource_categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_res_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. JOURNEY STAGES (Module 14 Timeline)
CREATE TABLE journey_stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stage_no INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_js_order (stage_no),
    INDEX idx_js_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. GUIDELINES (Module 14)
CREATE TABLE guidelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    kind ENUM('eligibility', 'procedure', 'responsibility', 'instruction') NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_guide_kind (kind),
    INDEX idx_guide_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. ANNOUNCEMENTS (Module 14)
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    deadline_date DATE NULL,
    status ENUM('draft', 'published', 'removed') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ann_status (status),
    INDEX idx_ann_published (published_at),
    CONSTRAINT fk_ann_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
