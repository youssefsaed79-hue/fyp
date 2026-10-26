<?php
declare(strict_types=1);

/**
 * InternFlow Database Seeder Script
 * Seeds realistic demonstration data for UNITEN CCI Industrial Training
 */

require dirname(__DIR__) . '/app/core/Database.php';
require dirname(__DIR__) . '/app/core/FileStore.php';

use App\Core\Database;

$config = require dirname(__DIR__) . '/app/config/config.php';
Database::setConfig($config['db']);
$pdo = Database::getConnection();

echo "Starting InternFlow Database Seeding...\n";

// Ensure upload folders exist
$storagePath = dirname(__DIR__) . '/storage/uploads';
foreach (['documents', 'resources', 'evidence', 'reports'] as $folder) {
    $dir = $storagePath . '/' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Helper to create a dummy PDF file
function createSamplePdf(string $filePath, string $title, string $description): void {
    if (file_exists($filePath)) {
        return;
    }
    // Minimal valid PDF binary structure
    $pdfContent = "%PDF-1.4\n" .
        "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n" .
        "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n" .
        "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n" .
        "4 0 obj << /Length 200 >> stream\n" .
        "BT\n" .
        "/F1 18 Tf\n" .
        "50 720 Td\n" .
        "(" . addcslashes($title, "()\n\r") . ") Tj\n" .
        "/F1 12 Tf\n" .
        "0 -30 Td\n" .
        "(" . addcslashes($description, "()\n\r") . ") Tj\n" .
        "0 -20 Td\n" .
        "(Universiti Tenaga Nasional - College of Computing and Informatics) Tj\n" .
        "0 -20 Td\n" .
        "(Sample File - Replace with Official University Document) Tj\n" .
        "ET\n" .
        "endstream\nendobj\n" .
        "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n" .
        "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000261 00000 n \n0000000514 00000 n \n" .
        "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n594\n%%EOF\n";
    file_put_contents($filePath, $pdfContent);
}

// Default bcrypt hash for 'Password@123'
$demoPasswordHash = password_hash('Password@123', PASSWORD_BCRYPT);

// Disable FK checks during clean seed
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = [
    'announcements', 'guidelines', 'journey_stages', 'resources', 'resource_categories',
    'email_log', 'notifications', 'messages', 'conversations', 'settings',
    'final_grades', 'final_reports', 'evaluations', 'monthly_reports',
    'logbook_revisions', 'logbook_reviews', 'logbook_attachments', 'logbooks',
    'attendance', 'invitation_tokens', 'placements', 'applications', 'documents',
    'vacancies', 'company_users', 'companies', 'password_resets', 'students', 'users'
];
foreach ($tables as $t) {
    $pdo->exec("TRUNCATE TABLE `{$t}`");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// 1. SETTINGS
$pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES
    ('weight_host', '40'),
    ('weight_visiting', '30'),
    ('weight_report', '20'),
    ('weight_logbook', '10'),
    ('attendance_alert_pct', '80'),
    ('token_expiry_hours', '72'),
    ('session_timeout_min', '30'),
    ('max_upload_mb', '10')");

// 2. COORDINATOR
$stmt = $pdo->prepare("INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
VALUES ('coordinator', 'Dr. Norhaslinda Kamaruddin', 'coordinator@internflow.test', :pw, 0, 'active', NOW())");
$stmt->execute(['pw' => $demoPasswordHash]);
$coordId = (int) $pdo->lastInsertId();

// 3. UNIVERSITY SUPERVISORS
$supervisors = [
    ['Dr. Ahmed Mubarak Ahmed Al-Haiqi', 'dr.ahmed@uniten.edu.my'],
    ['Dr. Loo Yim Ling', 'dr.loo@uniten.edu.my'],
    ['Ts. Dr. Zulkifli bin Ishak', 'dr.zulkifli@uniten.edu.my'],
];
$supIds = [];
foreach ($supervisors as $s) {
    $stmt = $pdo->prepare("INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
        VALUES ('university_supervisor', :name, :email, :pw, 0, 'active', NOW())");
    $stmt->execute(['name' => $s[0], 'email' => $s[1], 'pw' => $demoPasswordHash]);
    $supIds[] = (int) $pdo->lastInsertId();
}

// 4. PARTNER COMPANIES
$companiesData = [
    [
        'name' => 'Petronas Digital Sdn Bhd',
        'registration_no' => '199801012345 (456789-A)',
        'industry' => 'Energy & Oil/Gas Tech',
        'address' => 'Tower 1, PETRONAS Twin Towers, Kuala Lumpur City Centre, 50088 Kuala Lumpur',
        'rep_name' => 'Siti Aminah binti Mansor',
        'rep_email' => 'hr@petronas.test',
        'status' => 'approved',
        'user_name' => 'Siti Aminah (Petronas HR)',
        'user_email' => 'hr@petronas.test'
    ],
    [
        'name' => 'Tenaga Nasional Berhad (TNB IT & Cloud)',
        'registration_no' => '199001009876 (200866-W)',
        'industry' => 'Utilities & Cloud Computing',
        'address' => 'No. 129, Jalan Bangsar, 59200 Kuala Lumpur',
        'rep_name' => 'Faizal bin Osman',
        'rep_email' => 'intern@tnb.com.my',
        'status' => 'approved',
        'user_name' => 'Faizal Osman (TNB Supervisor)',
        'user_email' => 'intern@tnb.com.my'
    ],
    [
        'name' => 'CIMB Group Technologies',
        'registration_no' => '197401002647 (18417-M)',
        'industry' => 'Fintech & Banking',
        'address' => 'Menara CIMB, Jalan Stesen Sentral 2, 50470 Kuala Lumpur',
        'rep_name' => 'Marcus Wong Wei Loon',
        'rep_email' => 'techintern@cimb.com',
        'status' => 'approved',
        'user_name' => 'Marcus Wong (CIMB Lead)',
        'user_email' => 'techintern@cimb.com'
    ],
    [
        'name' => 'Aerodyne Group',
        'registration_no' => '201401033456 (1109542-K)',
        'industry' => 'Drone Tech & Artificial Intelligence',
        'address' => 'Aerodyne Campus, Cyberjaya, 63000 Selangor',
        'rep_name' => 'Azlan Shah bin Zain',
        'rep_email' => 'careers@aerodyne.group',
        'status' => 'approved',
        'user_name' => 'Azlan Shah (Aerodyne HR)',
        'user_email' => 'careers@aerodyne.group'
    ],
    [
        'name' => 'CyberSecurity Malaysia',
        'registration_no' => '200601007890 (726048-U)',
        'industry' => 'Information Security',
        'address' => 'Menara Cyber Axis, Jalan Impact, 63000 Cyberjaya',
        'rep_name' => 'Nadiah binti Yusof',
        'rep_email' => 'hr@cybersecurity.my',
        'status' => 'pending',
        'user_name' => 'Nadiah Yusof',
        'user_email' => 'hr@cybersecurity.my'
    ],
    [
        'name' => 'Grab Malaysia Technology Hub',
        'registration_no' => '201101019876 (948010-T)',
        'industry' => 'E-Commerce & Mobility',
        'address' => '1 Powerhouse, Bandar Utama, 47800 Petaling Jaya',
        'rep_name' => 'Kevin Tan',
        'rep_email' => 'talent@grab.com',
        'status' => 'suspended',
        'user_name' => 'Kevin Tan',
        'user_email' => 'talent@grab.com'
    ],
];

$companyIds = [];
$compUserIds = [];
foreach ($companiesData as $c) {
    $stmt = $pdo->prepare("INSERT INTO companies (name, registration_no, industry, address, representative_name, representative_email, source, status, created_at)
        VALUES (:name, :reg, :ind, :addr, :rname, :remail, 'partner', :status, NOW())");
    $stmt->execute([
        'name' => $c['name'],
        'reg' => $c['registration_no'],
        'ind' => $c['industry'],
        'addr' => $c['address'],
        'rname' => $c['rep_name'],
        'remail' => $c['rep_email'],
        'status' => $c['status'],
    ]);
    $compId = (int) $pdo->lastInsertId();
    $companyIds[] = $compId;

    // Create user for company
    $stmtU = $pdo->prepare("INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
        VALUES ('company', :name, :email, :pw, 0, 'active', NOW())");
    $stmtU->execute([
        'name' => $c['user_name'],
        'email' => $c['user_email'],
        'pw' => $demoPasswordHash
    ]);
    $uId = (int) $pdo->lastInsertId();
    $compUserIds[$compId] = $uId;

    $pdo->prepare("INSERT INTO company_users (user_id, company_id) VALUES (?, ?)")->execute([$uId, $compId]);
}

// 5. VACANCIES (12 Vacancies)
$vacanciesData = [
    [
        'company_id' => $companyIds[0], // Petronas
        'title' => 'Software Engineer Intern (Full-Stack Web & Cloud)',
        'duties' => "Assist the Digital Engineering team in building enterprise web portals.\nDevelop REST APIs in PHP/Python and front-end modules.\nParticipate in daily Agile scrums and code reviews.",
        'requirements' => 'Proficiency in PHP, JavaScript, SQL. Knowledge of Git version control. CGPA 3.00 and above.',
        'slots' => 3,
        'allowance' => 1200.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'hybrid',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[0], // Petronas
        'title' => 'Data Analyst Intern (Energy Insights)',
        'duties' => 'Analyse telemetry and process data from offshore operations. Build interactive dashboards.',
        'requirements' => 'Python, SQL, Tableau/PowerBI knowledge. Strong numerical reasoning.',
        'slots' => 2,
        'allowance' => 1200.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+20 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[1], // TNB
        'title' => 'Cloud Infrastructure & DevOps Intern',
        'duties' => 'Support grid automation and server migration to AWS cloud. Maintain Linux virtual machines and monitoring alerts.',
        'requirements' => 'Linux shell, basic networking, Docker concepts.',
        'slots' => 2,
        'allowance' => 1000.00,
        'location' => 'Bangsar, Kuala Lumpur',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+25 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[1], // TNB
        'title' => 'Cybersecurity Analyst Intern (SOC Monitoring)',
        'duties' => 'Monitor security incident logs, assist vulnerability scanning and participate in threat hunting drills.',
        'requirements' => 'Cybersecurity coursework, Wireshark, SIEM tools familiarity.',
        'slots' => 2,
        'allowance' => 1000.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'hybrid',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+15 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[2], // CIMB
        'title' => 'Mobile App Developer Intern (iOS/Android)',
        'duties' => 'Contribute to next-gen retail banking applications. Write clean unit tests and UI components.',
        'requirements' => 'Mobile development experience, Flutter/React Native/Swift, RESTful APIs.',
        'slots' => 4,
        'allowance' => 1500.00,
        'location' => 'KL Sentral',
        'working_arrangement' => 'hybrid',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+35 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[2], // CIMB
        'title' => 'Fintech QA Automation Intern',
        'duties' => 'Design automated test scripts for payment processing gateways.',
        'requirements' => 'Selenium, Cypress or Postman test scripting.',
        'slots' => 2,
        'allowance' => 1400.00,
        'location' => 'KL Sentral',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+10 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[3], // Aerodyne
        'title' => 'AI & Computer Vision Intern',
        'duties' => 'Develop object detection pipelines for drone aerial imagery.',
        'requirements' => 'PyTorch/TensorFlow, OpenCV, Python.',
        'slots' => 2,
        'allowance' => 1300.00,
        'location' => 'Cyberjaya',
        'working_arrangement' => 'remote',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+40 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[3], // Aerodyne
        'title' => 'Drone IoT Systems Engineering Intern',
        'duties' => 'Telemetry protocols and embedded controller firmware integration.',
        'requirements' => 'C/C++, Raspberry Pi/Arduino, IoT protocols (MQTT).',
        'slots' => 1,
        'allowance' => 1200.00,
        'location' => 'Cyberjaya',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+14 days')),
        'status' => 'published'
    ],
    [
        'company_id' => $companyIds[0], // Draft vacancy
        'title' => 'Enterprise Architecture Research Intern',
        'duties' => 'Evaluate modern microservices architectures and legacy systems.',
        'requirements' => 'Software engineering principles and UML diagrams.',
        'slots' => 1,
        'allowance' => 1100.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'remote',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+60 days')),
        'status' => 'draft'
    ],
    [
        'company_id' => $companyIds[1], // Closed vacancy
        'title' => 'IT Helpdesk Support Specialist Intern',
        'duties' => 'Provide level 1 technical support for internal staff across headquarters.',
        'requirements' => 'Good communication, Windows Active Directory, troubleshooting.',
        'slots' => 2,
        'allowance' => 900.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('-5 days')),
        'status' => 'closed'
    ],
    [
        'company_id' => $companyIds[2], // Flagged vacancy
        'title' => 'Unpaid Marketing & Coffee Delivery Intern',
        'duties' => 'Run personal errands and deliver beverages.',
        'requirements' => 'Own transport.',
        'slots' => 1,
        'allowance' => 0.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+10 days')),
        'status' => 'flagged'
    ],
    [
        'company_id' => $companyIds[2], // Deactivated vacancy
        'title' => 'Cryptocurrency Miner Operations Intern',
        'duties' => 'Oversee high-voltage mining rigs.',
        'requirements' => 'Crypto trading experience.',
        'slots' => 1,
        'allowance' => 500.00,
        'location' => 'Kuala Lumpur',
        'working_arrangement' => 'onsite',
        'duration_weeks' => 12,
        'deadline' => date('Y-m-d', strtotime('+5 days')),
        'status' => 'deactivated'
    ]
];

$vacancyIds = [];
foreach ($vacanciesData as $v) {
    $stmt = $pdo->prepare("INSERT INTO vacancies (company_id, title, duties, requirements, slots, allowance, location, working_arrangement, duration_weeks, deadline, status, created_at)
        VALUES (:cid, :title, :duties, :req, :slots, :allow, :loc, :arr, :dur, :dead, :status, NOW())");
    $stmt->execute([
        'cid' => $v['company_id'],
        'title' => $v['title'],
        'duties' => $v['duties'],
        'req' => $v['requirements'],
        'slots' => $v['slots'],
        'allow' => $v['allowance'],
        'loc' => $v['location'],
        'arr' => $v['working_arrangement'],
        'dur' => $v['duration_weeks'],
        'dead' => $v['deadline'],
        'status' => $v['status'],
    ]);
    $vacancyIds[] = (int) $pdo->lastInsertId();
}

// 6. FEATURED STUDENT: Aly Youssef Said Mahmoud (SW01084546)
$stmt = $pdo->prepare("INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
    VALUES ('student', 'Aly Youssef Said Mahmoud', 'aly.youssef@student.uniten.edu.my', :pw, 0, 'active', NOW())");
$stmt->execute(['pw' => $demoPasswordHash]);
$alyUserId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO students (user_id, student_id, programme, intake, cgpa, skills, interests, emergency_contact, verified, profile_complete, created_at)
    VALUES (:uid, 'SW01084546', 'Bachelor of Computer Science (Software Engineering)', '2023/2024-01', 3.82, 'PHP, MySQL, JavaScript, HTML5, CSS3, Git, Software Architecture', 'Web Platforms, Distributed Systems, Cloud Architecture', 'Said Mahmoud (+60123456789 - Father)', 1, 1, NOW())");
$stmt->execute(['uid' => $alyUserId]);

// Create CV document for Aly
$alyCvPath = $storagePath . '/documents/cv_aly_youssef.pdf';
createSamplePdf($alyCvPath, 'Curriculum Vitae - Aly Youssef Said Mahmoud', 'Software Engineering Student, UNITEN CCI (CGPA 3.82)');
$pdo->prepare("INSERT INTO documents (student_user_id, type, original_name, stored_name, mime, size, created_at)
    VALUES (?, 'cv', 'CV_Aly_Youssef.pdf', 'documents/cv_aly_youssef.pdf', 'application/pdf', ?, NOW())")
    ->execute([$alyUserId, filesize($alyCvPath)]);

// 7. SEED COHORT STUDENTS FROM database/sample_cohort.csv
$csvFile = dirname(__DIR__) . '/database/sample_cohort.csv';
$studentUserIds = [$alyUserId];
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle); // skip header
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 5) continue;
        [$sid, $sname, $semail, $sprog, $sintake] = array_map('trim', $row);
        if (empty($sid) || empty($semail) || !filter_var($semail, FILTER_VALIDATE_EMAIL)) {
            continue; // Skip invalid rows in seed
        }
        // Check duplicate email or student_id
        $stmtChk = $pdo->prepare("SELECT u.id FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.email = ? OR s.student_id = ?");
        $stmtChk->execute([$semail, $sid]);
        if ($stmtChk->fetch()) continue;

        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
            VALUES ('student', :name, :email, :pw, 0, 'active', NOW())");
        $stmt->execute(['name' => $sname, 'email' => $semail, 'pw' => $demoPasswordHash]);
        $uid = (int) $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO students (user_id, student_id, programme, intake, cgpa, verified, profile_complete, created_at)
            VALUES (?, ?, ?, ?, 3.45, 1, 1, NOW())")
            ->execute([$uid, $sid, $sprog, $sintake]);
        $studentUserIds[] = $uid;

        // Give each student a sample CV
        $cvFile = $storagePath . "/documents/cv_{$sid}.pdf";
        createSamplePdf($cvFile, "Curriculum Vitae - {$sname}", "Student ID: {$sid} | Programme: {$sprog}");
        $pdo->prepare("INSERT INTO documents (student_user_id, type, original_name, stored_name, mime, size, created_at)
            VALUES (?, 'cv', 'CV_{$sid}.pdf', ?, 'application/pdf', ?, NOW())")
            ->execute([$uid, "documents/cv_{$sid}.pdf", filesize($cvFile)]);
    }
    fclose($handle);
}

// 8. APPLICATIONS IN DIFFERENT PIPELINE STATUSES
$appStatuses = ['submitted', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected'];
$i = 0;
foreach ($studentUserIds as $stUid) {
    if ($stUid === $alyUserId) continue;
    $vId = $vacancyIds[$i % 5];
    $status = $appStatuses[$i % count($appStatuses)];
    $interviewDetails = ($status === 'interview' || $status === 'accepted') ? "Online Technical Interview scheduled on Zoom: Meeting ID 889 012 3456" : null;
    $offerIssued = ($status === 'accepted') ? 1 : 0;
    $offerText = ($offerIssued) ? "We are delighted to offer you the internship position starting in Special Semester." : null;

    $stmt = $pdo->prepare("INSERT INTO applications (student_user_id, vacancy_id, cover_letter, status, interview_details, offer_issued, offer_text, created_at)
        VALUES (?, ?, 'Dear Hiring Manager, I am eager to apply for this internship opportunity.', ?, ?, ?, ?, NOW())");
    $stmt->execute([$stUid, $vId, $status, $interviewDetails, $offerIssued, $offerText]);
    $i++;
    if ($i > 15) break;
}

// 9. PLACEMENT 1: COMPLETED PLACEMENT WITH ALL GRADES (Placement #1 - Nurul Huda)
$p1Student = $studentUserIds[1];
$p1Start = date('Y-m-d', strtotime('-14 weeks'));
$p1End = date('Y-m-d', strtotime('-2 weeks'));
$stmt = $pdo->prepare("INSERT INTO placements (student_user_id, company_id, vacancy_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, company_supervisor_user_id, university_supervisor_id, status, approved_by, approved_at, created_at)
    VALUES (?, ?, ?, 'platform', 'Full-Stack Software Engineering Intern', ?, ?, 'Siti Aminah', 'hr@petronas.test', ?, ?, 'completed', ?, NOW(), NOW())");
$stmt->execute([$p1Student, $companyIds[0], $vacancyIds[0], $p1Start, $p1End, $compUserIds[$companyIds[0]], $supIds[0], $coordId]);
$p1Id = (int) $pdo->lastInsertId();

// Conversation for Placement 1
$pdo->prepare("INSERT INTO conversations (placement_id, created_at) VALUES (?, NOW())")->execute([$p1Id]);
$conv1Id = (int) $pdo->lastInsertId();
$pdo->prepare("INSERT INTO messages (conversation_id, sender_id, body, created_at) VALUES
    (?, ?, 'Welcome to Petronas Digital! Please check the orientation guide.', NOW()),
    (?, ?, 'Thank you madam. I have reported for duty today.', NOW()),
    (?, ?, 'Great. I will conduct the university supervision visit around week 9.', NOW())")
    ->execute([$conv1Id, $compUserIds[$companyIds[0]], $conv1Id, $p1Student, $conv1Id, $supIds[0]]);

// Final Grades for Placement 1 (Host: 92%, Visiting: 88%, Report: 85%, Logbook: 90%)
// Total = (92*0.40) + (88*0.30) + (85*0.20) + (90*0.10) = 36.8 + 26.4 + 17.0 + 9.0 = 89.20%
$pdo->prepare("INSERT INTO final_grades (placement_id, host_score, visiting_score, report_score, logbook_score, total, locked, locked_by, locked_at, created_at)
    VALUES (?, 92.00, 88.00, 85.00, 90.00, 89.20, 1, ?, NOW(), NOW())")
    ->execute([$p1Id, $coordId]);

// Final Report for Placement 1
$p1ReportFile = $storagePath . '/reports/final_report_p1.pdf';
createSamplePdf($p1ReportFile, 'Industrial Training Final Report - Nurul Huda', 'Final Report submitted for evaluation');
$pdo->prepare("INSERT INTO final_reports (placement_id, stored_name, original_name, originality_confirmed, submitted_at, mark, marked_by, created_at)
    VALUES (?, 'reports/final_report_p1.pdf', 'Industrial_Training_Final_Report_NurulHuda.pdf', 1, NOW(), 85.00, ?, NOW())")
    ->execute([$p1Id, $supIds[0]]);

// Host Evaluation for Placement 1
$scoresP1 = json_encode([
    'attendance' => 5, 'punctuality' => 5, 'communication' => 4,
    'technical_knowledge' => 5, 'work_quality' => 5, 'problem_solving' => 4,
    'teamwork' => 5, 'professional_behaviour' => 5, 'training_objectives' => 4
]);
$pdo->prepare("INSERT INTO evaluations (placement_id, evaluator_id, type, period_no, scores_json, comments, total_pct, submitted_at, created_at)
    VALUES (?, ?, 'host_final', NULL, ?, 'Outstanding technical performance and initiative.', 92.00, NOW(), NOW())")
    ->execute([$p1Id, $compUserIds[$companyIds[0]], $scoresP1]);

// Visiting Evaluation for Placement 1
$scoresUniP1 = json_encode([
    'presentation' => 5, 'comprehension' => 4, 'academic_reflection' => 4, 'host_feedback' => 5
]);
$pdo->prepare("INSERT INTO evaluations (placement_id, evaluator_id, type, scores_json, comments, total_pct, submitted_at, created_at)
    VALUES (?, ?, 'visiting_lecturer', ?, 'Satisfactory visit conducted. Student demonstrated deep understanding.', 88.00, NOW(), NOW())")
    ->execute([$p1Id, $supIds[0], $scoresUniP1]);


// 10. PLACEMENT 2: ACTIVE INTERNSHIP (Aly Youssef Said Mahmoud - Placement #2)
// 6 weeks completed with attendance and weekly logbooks!
$p2Start = date('Y-m-d', strtotime('-6 weeks'));
$p2End = date('Y-m-d', strtotime('+6 weeks'));
$stmt = $pdo->prepare("INSERT INTO placements (student_user_id, company_id, vacancy_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, company_supervisor_user_id, university_supervisor_id, status, approved_by, approved_at, created_at)
    VALUES (?, ?, ?, 'platform', 'Software Engineering Intern', ?, ?, 'Faizal bin Osman', 'intern@tnb.com.my', ?, ?, 'internship_active', ?, NOW(), NOW())");
$stmt->execute([$alyUserId, $companyIds[1], $vacancyIds[2], $p2Start, $p2End, $compUserIds[$companyIds[1]], $supIds[0], $coordId]);
$alyPlacementId = (int) $pdo->lastInsertId();

// Conversation for Aly
$pdo->prepare("INSERT INTO conversations (placement_id, created_at) VALUES (?, NOW())")->execute([$alyPlacementId]);
$convAlyId = (int) $pdo->lastInsertId();
$pdo->prepare("INSERT INTO messages (conversation_id, sender_id, body, created_at) VALUES
    (?, ?, 'Welcome to the TNB Cloud Engineering team, Aly! Let us know if you need access permissions.', NOW()),
    (?, ?, 'Thank you Dr. Faizal. I have set up my local dev environment and Docker containers.', NOW()),
    (?, ?, 'Dr. Ahmed here. Remember to submit your weekly logbooks on time every Friday.', NOW()),
    (?, ?, 'Meeting link for our Week 8 Mid-Term Supervision Sync: https://meet.google.com/xyz-internflow', NOW())")
    ->execute([
        $convAlyId, $compUserIds[$companyIds[1]],
        $convAlyId, $alyUserId,
        $convAlyId, $supIds[0],
        $convAlyId, $supIds[0]
    ]);

// Generate 12 Weekly Logbooks for Aly
for ($w = 1; $w <= 12; $w++) {
    $wStart = date('Y-m-d', strtotime($p2Start . " +" . (($w - 1) * 7) . " days"));
    $wEnd = date('Y-m-d', strtotime($wStart . " +6 days"));
    $status = 'draft';
    $grade = null;
    $submittedAt = null;
    $tasks = null; $skills = null; $challenges = null; $solutions = null; $reflection = null; $hours = 0.00;

    if ($w <= 5) {
        $status = 'approved';
        $grade = 90.00 + ($w * 1.5);
        $submittedAt = date('Y-m-d H:i:s', strtotime($wEnd . " 17:00:00"));
        $tasks = "Completed microservices modules and API endpoints for Week {$w}.\nConducted automated unit testing.";
        $skills = "PHP 8, Docker, MySQL indexing, Git flow";
        $challenges = "Resolved query optimization bottlenecks on large dataset.";
        $solutions = "Added composite indexing on foreign keys and refactored SQL joins.";
        $reflection = "Improved technical confidence working with enterprise infrastructure.";
        $hours = 40.00;
    } elseif ($w === 6) {
        $status = 'company_reviewed';
        $submittedAt = date('Y-m-d H:i:s', strtotime($wEnd . " 16:30:00"));
        $tasks = "Deployed CI/CD pipeline stage on staging server. Configured automated lint checks.";
        $skills = "DevOps, GitLab CI, Shell Scripting";
        $challenges = "Pipeline failed due to permission rights in runner container.";
        $solutions = "Adjusted Docker runner non-root user permissions.";
        $reflection = "Learned how automated deployment safeguards production integrity.";
        $hours = 40.00;
    }

    $stmt = $pdo->prepare("INSERT INTO logbooks (placement_id, week_no, week_start, week_end, tasks, skills, challenges, solutions, reflection, hours, status, grade, submitted_at, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$alyPlacementId, $w, $wStart, $wEnd, $tasks, $skills, $challenges, $solutions, $reflection, $hours, $status, $grade, $submittedAt]);
    $logbookId = (int) $pdo->lastInsertId();

    if ($w <= 5) {
        // Log review
        $pdo->prepare("INSERT INTO logbook_reviews (logbook_id, reviewer_id, reviewer_role, action, comment, created_at)
            VALUES (?, ?, 'company', 'approve', 'Excellent progress this week. Well documented.', NOW())")
            ->execute([$logbookId, $compUserIds[$companyIds[1]]]);
        $pdo->prepare("INSERT INTO logbook_reviews (logbook_id, reviewer_id, reviewer_role, action, comment, created_at)
            VALUES (?, ?, 'university_supervisor', 'approve', 'Good technical depth and clear reflection.', NOW())")
            ->execute([$logbookId, $supIds[0]]);
    }
}

// Generate Attendance records for Aly (Week 1 to Week 6: 30 working days)
$currentDate = new DateTime($p2Start);
$today = new DateTime();
$dayCount = 0;
while ($currentDate <= $today && $dayCount < 30) {
    $dayOfWeek = (int) $currentDate->format('N');
    if ($dayOfWeek <= 5) { // Monday to Friday
        $dateStr = $currentDate->format('Y-m-d');
        // Mostly verified, one flagged day for demonstration (FR-ATT-02)
        $attStatus = 'present';
        $verStatus = 'verified';
        $flagComment = null;
        if ($dayCount === 12) {
            $attStatus = 'present';
            $verStatus = 'flagged';
            $flagComment = 'Check-in time discrepancy noted. Please submit supervisor sign-off confirmation.';
        } elseif ($dayCount === 18) {
            $attStatus = 'leave';
            $verStatus = 'verified';
        }

        $stmt = $pdo->prepare("INSERT INTO attendance (placement_id, work_date, check_in, check_out, hours, work_arrangement, status, verification, flag_comment, verified_by, verified_at, created_at)
            VALUES (?, ?, '08:30:00', '17:30:00', 8.00, 'onsite', ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$alyPlacementId, $dateStr, $attStatus, $verStatus, $flagComment, $compUserIds[$companyIds[1]]]);
        $dayCount++;
    }
    $currentDate->modify('+1 day');
}

// Monthly Report 1 for Aly
$pdo->prepare("INSERT INTO monthly_reports (placement_id, period_no, summary, original_name, stored_name, submitted_at, created_at)
    VALUES (?, 1, 'Completed Months 1 training objectives covering server migration, Docker containerization, and unit test suites.', 'Month1_Summary_Aly.pdf', 'reports/month1_aly.pdf', NOW(), NOW())")
    ->execute([$alyPlacementId]);


// 11. PLACEMENT 3: UNIVERSITY APPROVED (Ready for activation)
$p3Student = $studentUserIds[2];
$pdo->prepare("INSERT INTO placements (student_user_id, company_id, vacancy_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, university_supervisor_id, status, approved_by, approved_at, created_at)
    VALUES (?, ?, ?, 'platform', 'Fintech Mobile Engineer Intern', '2026-11-01', '2027-01-24', 'Marcus Wong', 'techintern@cimb.com', ?, 'university_approved', ?, NOW(), NOW())")
    ->execute([$p3Student, $companyIds[2], $vacancyIds[4], $supIds[1], $coordId]);

// 12. PLACEMENT 4: PENDING APPROVAL QUEUE (Coordinator action needed)
$p4Student = $studentUserIds[3];
$pdo->prepare("INSERT INTO placements (student_user_id, company_id, vacancy_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, status, created_at)
    VALUES (?, ?, ?, 'platform', 'AI & Computer Vision Intern', '2026-11-01', '2027-01-24', 'Azlan Shah', 'careers@aerodyne.group', 'placement_pending', NOW())")
    ->execute([$p4Student, $companyIds[3], $vacancyIds[6]]);

// 13. PLACEMENT 5: SELF-SOURCED WITH SYSTEM INVITATION TOKEN (FR-UCM-02)
$p5Student = $studentUserIds[4];
// Insert self-sourced company
$stmt = $pdo->prepare("INSERT INTO companies (name, registration_no, industry, address, representative_name, representative_email, source, status, created_at)
    VALUES ('NexTech Innovations Sdn Bhd', '202001099887 (139988-X)', 'Cloud Robotics', 'Technology Park Malaysia, Bukit Jalil, 57000 Kuala Lumpur', 'Engr. David Tan', 'david.tan@nextech.test', 'self_sourced', 'approved', NOW())");
$stmt->execute();
$selfCompId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO placements (student_user_id, company_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, status, created_at)
    VALUES (?, ?, 'self_sourced', 'Robotics Systems Engineering Intern', '2026-11-15', '2027-02-07', 'Engr. David Tan', 'david.tan@nextech.test', 'placement_pending', NOW())");
$stmt->execute([$p5Student, $selfCompId]);
$p5Id = (int) $pdo->lastInsertId();

// Create active invitation token for David Tan
$rawToken = 'demo_token_nextech_2026';
$tokenHash = hash('sha256', $rawToken);
$pdo->prepare("INSERT INTO invitation_tokens (token_hash, placement_id, invited_email, expires_at, created_at)
    VALUES (?, ?, 'david.tan@nextech.test', DATE_ADD(NOW(), INTERVAL 72 HOUR), NOW())")
    ->execute([$tokenHash, $p5Id]);


// 14. MODULE 14: INTERNSHIP RESOURCES SEED CONTENT
// Resource Categories
$categories = [
    ['Forms', 1],
    ['Templates', 2],
    ['Guidelines and Rubrics', 3],
    ['Briefing Slides', 4]
];
$catIds = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("INSERT INTO resource_categories (name, sort_order, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$cat[0], $cat[1]]);
    $catIds[$cat[0]] = (int) $pdo->lastInsertId();
}

// 12 Official Kit Documents (Creating sample PDFs)
$kitDocs = [
    ['1st Briefing Slide Presentation', 'Official slides for 1st briefing semester.', 'Briefing Slides'],
    ['Final Briefing Slide Presentation', 'Final briefing slides before reporting to duty.', 'Briefing Slides'],
    ['Form 1 (Offer Letter Form)', 'Official F1 offer letter confirmation form.', 'Forms'],
    ['Form 2 (Report for Duty Form)', 'Official F2 report for duty form to be submitted within week 1.', 'Forms'],
    ['Industrial Training Application Letter', 'Formal university introduction letter to prospective host companies.', 'Forms'],
    ['IT Completion Acknowledgement Form', 'Acknowledgment form completed at end of 12-week period.', 'Forms'],
    ['Log Book Template', 'Word template for supplementary logbook printouts.', 'Templates'],
    ['Report Template', 'Standard LaTeX and Word format for final industrial training report.', 'Templates'],
    ['Report Template User Manual', 'User guide on report formatting and citation standards.', 'Templates'],
    ['Report Guideline', 'Comprehensive guidelines on technical report requirements.', 'Guidelines and Rubrics'],
    ['Template Visit Presentation Slide', 'Standard slide format for visiting lecturer assessment.', 'Templates'],
    ['CCI Internship Evaluation Rubric', 'Official marking rubric for host and university supervisors.', 'Guidelines and Rubrics']
];

foreach ($kitDocs as $doc) {
    $catId = $catIds[$doc[2]];
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $doc[0]));
    $pdfPath = $storagePath . "/resources/{$slug}.pdf";
    createSamplePdf($pdfPath, $doc[0], $doc[1]);

    $stmt = $pdo->prepare("INSERT INTO resources (category_id, title, description, stored_name, original_name, mime, size, version, status, published_at, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, 'application/pdf', ?, 1, 'published', NOW(), ?, NOW())");
    $stmt->execute([
        $catId,
        $doc[0],
        $doc[1],
        "resources/{$slug}.pdf",
        "{$slug}.pdf",
        filesize($pdfPath),
        $coordId
    ]);
}

// 4 Journey Stages (Timeline Stepper)
$journeyStages = [
    [
        1,
        'Semester 1 – First Briefing',
        'Attend the initial briefing organised by the College of Computing and Informatics (CCI). Review eligibility prerequisites, credit requirements, and prepare your initial CV and career interests.'
    ],
    [
        2,
        'Semester 2 – Second Briefing, Placement Search & Pre-Registration',
        'Attend the second detailed briefing. Search and apply for accredited vacancies or register self-sourced placements. Pre-register and register the industrial training course subject once offer is accepted.'
    ],
    [
        3,
        'Special Semester – Complete 12-Week Industrial Training',
        'Undertake the 12-week industrial training at the approved host organisation. Submit daily attendance, record weekly digital logbooks, submit monthly progress reports, and host visiting lecturer visit.'
    ],
    [
        4,
        'Following Semester 1 – Results & Completion',
        'Submit final industrial training report, review combined evaluation marks (Host 40%, Visiting 30%, Report 20%, Logbook 10%), and receive the final academic grade endorsement.'
    ]
];
foreach ($journeyStages as $js) {
    $pdo->prepare("INSERT INTO journey_stages (stage_no, title, description, status, created_at) VALUES (?, ?, ?, 'published', NOW())")
        ->execute([$js[0], $js[1], $js[2]]);
}

// 4 Guidelines
$guidelinesData = [
    [
        'Eligibility Requirements',
        "<p>Students must fulfill the following minimum academic requirements prior to commencing industrial training:</p>
        <ul>
            <li><strong>Bachelor Degree Programmes:</strong> Must have successfully completed at least <strong>60 credit hours</strong>.</li>
            <li><strong>Diploma Programmes:</strong> Must have successfully completed at least <strong>45 credit hours</strong>.</li>
            <li><strong>Prerequisite Subjects:</strong> Prerequisite coursework varies by programme track (Software Engineering, Cyber Security, Information Systems). Confirm eligibility with your academic advisor.</li>
            <li>Good academic standing without active disciplinary sanctions.</li>
        </ul>",
        'eligibility',
        1
    ],
    [
        'Application & Placement Procedures',
        "<p>Standard procedure for securing host company placements:</p>
        <ol>
            <li>Prepare your professional CV and cover letter in InternFlow.</li>
            <li>Generate and download the official Industrial Training Application &amp; Verification Letter.</li>
            <li>Apply to partner company listings on InternFlow or approach external organisations.</li>
            <li>When securing an external placement, register the host supervisor contact in InternFlow to issue the System Invitation Token.</li>
            <li>Submit Form 1 (Offer Letter Form) within 7 days of receiving an acceptance offer.</li>
        </ol>",
        'procedure',
        2
    ],
    [
        'Roles and Responsibilities',
        "<p>Expectations for all parties during the 12-week training period:</p>
        <ul>
            <li><strong>Student Intern:</strong> Observe company working hours, adhere to corporate policies, maintain regular daily attendance, and submit weekly logbooks on time.</li>
            <li><strong>Host Supervisor:</strong> Provide technical guidance, oversee assigned duties, verify weekly attendance in batches, and evaluate workplace performance (40%).</li>
            <li><strong>University Supervisor:</strong> Provide academic mentorship, review and grade weekly logbooks (10%), conduct mid-term evaluation (30%), and grade the final report (20%).</li>
            <li><strong>Coordinator:</strong> Oversee cohort placement, resolve compliance inquiries, and lock final endorsed grades.</li>
        </ul>",
        'responsibility',
        3
    ],
    [
        'Important Instructions & Submission Deadlines',
        "<p>Critical operational instructions during the industrial training period:</p>
        <ul>
            <li><strong>Daily Attendance:</strong> Must be logged daily. Host supervisors verify records weekly or monthly. Overall attendance must remain <strong>at or above 80%</strong>.</li>
            <li><strong>Weekly Logbooks:</strong> Must be submitted weekly by Sunday 23:59. Dual review by company and university supervisor.</li>
            <li><strong>Monthly Reports:</strong> Submitted once per 4-week cycle (Weeks 4, 8, 12) for academic monitoring.</li>
            <li><strong>Final Report Submission:</strong> Submit digital copy with signed originality declaration within <strong>two weeks</strong> after the conclusion of the 12-week internship.</li>
            <li><strong>Visiting Lecturer Visit:</strong> Scheduled between Weeks 8 and 12. Coordinate date with your assigned university supervisor.</li>
        </ul>",
        'instruction',
        4
    ]
];
foreach ($guidelinesData as $g) {
    $pdo->prepare("INSERT INTO guidelines (title, body, kind, sort_order, status, created_at) VALUES (?, ?, ?, ?, 'published', NOW())")
        ->execute([$g[0], $g[1], $g[2], $g[3]]);
}

// 2 Announcements (1 published, 1 draft)
$pdo->prepare("INSERT INTO announcements (title, body, deadline_date, status, published_at, created_by, created_at)
    VALUES ('Special Semester 2026/2027 Industrial Training Registration Open',
    'All eligible CCI students who have completed 60 credits (Degree) or 45 credits (Diploma) must finalize company placement offers by May 15, 2026. Please ensure your CV is uploaded in your profile before applying.',
    '2026-05-15', 'published', NOW(), ?, NOW())")
    ->execute([$coordId]);

$pdo->prepare("INSERT INTO announcements (title, body, deadline_date, status, published_at, created_by, created_at)
    VALUES ('Upcoming Resume Writing & Interview Prep Workshop',
    'A virtual workshop with industry HR directors will be hosted next month. Details will be published soon.',
    NULL, 'draft', NULL, ?, NOW())")
    ->execute([$coordId]);

// Notifications for Aly
$pdo->prepare("INSERT INTO notifications (user_id, type, title, body, link, read_at, created_at)
    VALUES (?, 'placement_approved', 'Placement Approved', 'Your placement at Tenaga Nasional Berhad has been approved by the coordinator.', '/placements/my', NOW(), NOW())")
    ->execute([$alyUserId]);

$pdo->prepare("INSERT INTO notifications (user_id, type, title, body, link, read_at, created_at)
    VALUES (?, 'logbook_approved', 'Logbook Week 5 Approved', 'Your university supervisor has reviewed and approved your Week 5 logbook (Grade: 97.5%).', '/logbooks', NULL, NOW())")
    ->execute([$alyUserId]);

echo "Seeding completed successfully!\n";
