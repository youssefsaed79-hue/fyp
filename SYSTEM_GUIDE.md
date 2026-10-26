# InternFlow: System Architecture, Directory Structure & Operational Guide

**Platform:** InternFlow — University Internship Placement, Monitoring and Evaluation Platform  
**Institutional Setting:** Universiti Tenaga Nasional (UNITEN), College of Computing and Informatics (CCI)  
**Programme:** Bachelor of Computer Science (Software Engineering) (Hons.)  
**Document Version:** 1.0 (Comprehensive System & Architectural Specification)  

---

## 1. Executive Overview & Institutional Context

**InternFlow** is a custom, standalone, role-based internship management web application built specifically for the **12-week industrial training programme** at the College of Computing and Informatics (CCI), Universiti Tenaga Nasional (UNITEN).

Before InternFlow, internship coordination was divided across disjointed channels: emails, Google Forms, printed logbooks, static portal downloads, and separate evaluation spreadsheets. InternFlow unifies this entire operational lifecycle into one secure, auditable, and automated web platform connecting all four university stakeholders:

```
[Information & Resources] ➔ [Application & Screening] ➔ [Placement Approval] ➔ [Supervisor Assignment]
         ➔ [Daily Attendance] ➔ [Weekly Logbooks] ➔ [Monthly Reports] ➔ [4-Way Chat]
         ➔ [Final Report & Dual Rubrics] ➔ [Weighted Grade Lock] ➔ [Completion]
```

### Institutional Scope Boundaries (Version 1.0)
To adhere strictly to the approved academic specification (Chapters 1–3):
1. **Standalone System (Assumption A1):** No direct API connection to the university IDMS database. Student cohort data enters exclusively through the Coordinator's validated CSV/Excel import.
2. **Zero Inappropriate Automations:** No real-time GPS or biometric attendance tracking (daily logs with batch verification are used instead). No payment gateway. No live video calling (in-app 4-way group chat with meeting link support is used instead). No native mobile app.
3. **Academic Integrity:** No automated plagiarism engine; students confirm originality via a legally binding digital declaration checkbox prior to submitting their final report.
4. **Resources Independence:** The Internship Resources module is hosted natively inside InternFlow (not an external link) and does not contain an FAQ section.

---

## 2. Technology Stack & Development Tools (Table 3.10)

| Layer / Role | Technology Selected | Implementation Details |
|---|---|---|
| **Backend Core** | **Plain PHP 8.x** (Strict Typing) | Modular MVC structure. **Zero external frameworks** (no Laravel, Symfony, or CodeIgniter). Native sessions, strict typing (`declare(strict_types=1);`), custom PSR-4 autoloader. |
| **Database Engine** | **MySQL 8.0 / 8.4** (InnoDB) | Standalone relational database. 29 normalized tables, strict foreign keys, cascading constraints, compound indexes, and **PDO prepared statements only** (`ATTR_EMULATE_PREPARES => false`). |
| **Frontend UI** | **HTML5, CSS3, Vanilla JavaScript** | Hand-crafted responsive CSS (CSS Grid & Flexbox, responsive from 360px+). **Zero external CSS frameworks** (no Bootstrap, no Tailwind, no Blade). |
| **Visual Identity** | **UNITEN CCI Brand Palette** | Strictly enforced 4-token color system derived from the official CCI Internship website (`#1D3E2F`, `#2F5F48`, `#F3F2EC`, `#E7EEF0`). |
| **Analytics & Charts**| **Chart.js (Local Vendor)** | Stored locally in `/public/assets/vendor/chart.umd.min.js`. Zero runtime CDN dependencies. |
| **Import & File Engine**| **Native PHP CSV (`fgetcsv`)** | Native streamed batch parsing, multi-row duplicate detection, and transactional provisioning. |
| **File Storage** | **Private Storage (`storage/uploads/`)**| Stored strictly outside the webroot. Served only via authenticated streaming controllers (`FileStore::serve()`). Zero public file URLs. |
| **Email Service** | **SMTP / Offline Fallback** | All outbound mail is delivered via SMTP (if configured) and simultaneously recorded in the `email_log` database table and `storage/mail.log` for offline demonstration. |
| **Web Server** | **Apache / XAMPP / WampServer**| Front controller pattern via `public/index.php` and `public/.htaccess`. |

---

## 3. Visual Identity & Brand Color Palette (Section 17)

InternFlow enforces a unified institutional aesthetic across every dashboard, card, table, button, and chart:

```
+---------------------------------------------------------------------------------------+
|  Brand Color Tokens (CSS Custom Properties in /public/assets/css/app.css)            |
+---------------------------------------------------------------------------------------+
|  --c-forest-dark: #1D3E2F  | Dark Forest Green (Sidebar, topbar, headings, headers)   |
|  --c-forest:      #2F5F48  | Muted Forest Green (Primary action buttons, active tabs) |
|  --c-offwhite:    #F3F2EC  | Warm Off-White (Application canvas, text on dark panels) |
|  --c-bluegray:    #E7EEF0  | Pale Blue Gray (Secondary cards, zebra rows, inputs)     |
+---------------------------------------------------------------------------------------+
```

### Subdued Semantic Status Colors:
Status badges combine high-contrast labels with subdued semantic hues:
- **Verified / Approved / Completed:** Green (`#1E6B47`, background `#E2F2E9`)
- **Pending / Under Review / Warning:** Amber (`#8D5C0B`, background `#FDF4E3`)
- **Flagged / Returned / Rejected / Danger:** Red (`#A61C1C`, background `#FDE8E8`)
- **Submitted / Shortlisted / Info:** Blue-Gray (`#2C5D63`, background `#E7EEF0`)
- **Draft / Inactive / Neutral:** Slate Gray (`#5A6872`, background `#EAECEE`)

---

## 4. Complete Directory & File Structure

```
internflow/
├── .htaccess                       <- Denies access to app/, storage/, database/
├── index.php                       <- Root redirector forwarding requests to public/
├── README.md                       <- Setup guide, ERD diagram, and requirements traceability
├── TESTING.md                      <- Full test suite for all 43 FRs, 13 NFRs, and 21 UCs
├── SYSTEM_GUIDE.md                 <- This comprehensive architectural & operational manual
│
├── public/                         <- ONLY WEB-ACCESSIBLE DIRECTORY (DocumentRoot)
│   ├── index.php                   <- Front Controller, autoloader, and route dispatcher
│   ├── .htaccess                   <- Apache mod_rewrite rules routing requests to index.php
│   └── assets/
│       ├── css/
│       │   └── app.css             <- Hand-written responsive design system & color tokens
│       ├── js/
│       │   └── app.js              <- Client-side validation, mobile navigation, modals, polling
│       └── vendor/
│           └── chart.umd.min.js    <- Local standalone Chart.js library (zero CDN reliance)
│
├── app/
│   ├── config/
│   │   ├── config.php              <- Active environment configuration (DB, SMTP, paths)
│   │   └── config.sample.php       <- Reference configuration template
│   │
│   ├── core/                       <- Core Infrastructure & Foundation Services
│   │   ├── Auth.php                <- Session auth, RBAC authorization, placement scoping
│   │   ├── Database.php            <- PDO singleton connection, prepared statements, transactions
│   │   ├── Router.php              <- HTTP routing dispatcher (clean URLs & ?r= query routing)
│   │   ├── Session.php             <- Secure session management, 30-minute idle expiration
│   │   ├── Csrf.php                <- Synchronizer Token Pattern CSRF protection
│   │   ├── Validator.php           <- Centralized server-side request validation engine
│   │   ├── Mailer.php              <- Dual-mode mailer (SMTP + DB logging + mail.log)
│   │   ├── FileStore.php           <- Private storage, MIME validation, 10MB limits, streaming
│   │   ├── Notifier.php            <- In-app notifications dispatcher
│   │   ├── Flash.php               <- Session-based flash notifications (success/danger/info)
│   │   ├── View.php                <- Template renderer with layout and partial support
│   │   └── helpers.php             <- Global utility functions (e(), url(), asset())
│   │
│   ├── controllers/                <- Application Controllers (MVC)
│   │   ├── AuthController.php          <- Login, logout, password recovery, forced first-time change
│   │   ├── DashboardController.php     <- Role-specific dashboards (Student, Company, Sup, Coord)
│   │   ├── ImportController.php        <- Bulk cohort CSV/Excel import wizard & preview
│   │   ├── UserController.php          <- User directory, manual single creation, status toggle
│   │   ├── CompanyController.php       <- Partner registration, approvals, invitation token register
│   │   ├── ProfileController.php       <- Student profile CRUD, CV upload, eligibility gate
│   │   ├── VacancyController.php       <- Vacancy publishing, search/filter, coordinator vetting
│   │   ├── ApplicationController.php   <- Applications, company screening pipeline, offer lock
│   │   ├── PlacementController.php     <- Placement approvals, activation, self-source, interns
│   │   ├── SupervisorController.php    <- Supervisor assignment, workload suggested allocation
│   │   ├── AttendanceController.php    <- Daily student check-in/out, batch company verification
│   │   ├── LogbookController.php       <- 12-week logbooks, dual review, grading, revision history
│   │   ├── MonthlyReportController.php <- 4-week monthly progress summaries and monitoring
│   │   ├── FinalReportController.php   <- Final report upload, originality declaration, marking
│   │   ├── EvaluationController.php    <- Host 9-criteria rubric, visiting rubric, grade locking
│   │   ├── ChatController.php          <- 4-way group chat with meeting links and AJAX polling
│   │   ├── NotificationController.php  <- In-app notification center and read status
│   │   ├── ResourcesController.php     <- Student resources hub (Kit, Journey, Guidelines, Notices)
│   │   ├── ResourceAdminController.php <- Coordinator resource CRUD, categories, announcements
│   │   └── SettingsController.php      <- Assessment weights (40/30/20/10), thresholds, CSV exports
│   │
│   ├── services/                   <- Domain Business Logic Services
│   │   ├── BulkImportService.php       <- Streamed cohort parsing, duplicate detection, insert chunks
│   │   ├── GradeEngine.php             <- Weighted evaluation engine (40/30/20/10) & Rule 8 check
│   │   ├── AttendanceRule.php          <- Monthly attendance % calculator & <80% alert engine
│   │   └── BatchVerificationService.php<- 1-click batch verification of attendance periods
│   │
│   └── views/                      <- Presentation Templates (HTML5 + PHP)
│       ├── layouts/
│       │   ├── main.php            <- Primary layout (topbar, role-based sidebar, flash, footer)
│       │   └── auth.php            <- Centered split-panel authentication layout
│       ├── partials/
│       │   ├── topbar.php          <- Top navigation bar with notification bell & user pill
│       │   ├── sidebar.php         <- Dynamic, role-scoped left sidebar navigation
│       │   ├── flash.php           <- Flash message alerts banner
│       │   └── badges.php          <- Reusable semantic color-coded status pills
│       ├── auth/                   <- Login, password reset, first-time change, token acceptance
│       ├── dashboard/              <- 4 role dashboards (student, company, supervisor, coordinator)
│       ├── import/                 <- Cohort CSV upload, preview error table, confirmation
│       ├── users/                  <- User directory, manual user creation form
│       ├── companies/              <- Partner registration form, company approval table, profile
│       ├── profile/                <- Student profile, CV upload, document management
│       ├── vacancies/              <- Search/filter listings, vacancy view, company vacancy CRUD
│       ├── applications/           <- Student applications list, company candidate screening pipeline
│       ├── placements/             <- Placement approvals, self-sourced form, my placement, interns
│       ├── supervisors/            <- Supervisor allocation screen, workload balancing chart
│       ├── attendance/             <- Daily student log form, company batch verification screen
│       ├── logbooks/               <- 12-week schedule, logbook editor, company review, supervisor grading
│       ├── monthly_reports/        <- Student 4-week upload, supervisor monitoring viewer
│       ├── final_reports/          <- Student upload with originality declaration, supervisor marking
│       ├── evaluations/            <- Host 9-criteria rubric, visiting rubric, coordinator grade lock
│       ├── chat/                   <- 4-way conversation view, message stream, meeting link input
│       ├── notifications/          <- Notification center list, mark as read
│       ├── resources/              <- Student portal (Tabs: Kit, Journey, Guidelines, Announcements)
│       ├── resource_admin/         <- Coordinator resources CRUD, categories, announcements admin
│       ├── settings/               <- Weight configuration (40/30/20/10), CSV data exports hub
│       └── errors/                 <- 403 Forbidden, 404 Not Found friendly error templates
│
├── storage/                        <- PRIVATE STORAGE (OUTSIDE WEBROOT)
│   ├── backups/                    <- Automated daily SQL database backups (.sql)
│   ├── exports/                    <- Generated administrative CSV export files
│   ├── mail.log                    <- Offline plain-text email simulation audit log
│   └── uploads/                    <- Private user-uploaded documents
│       ├── documents/              <- Student CVs, transcripts, verification letters
│       ├── evidence/               <- Attendance medical certificates (MC) and timesheets
│       ├── reports/                <- Monthly progress reports and final internship reports
│       └── resources/              <- Coordinator official Internship Kit templates and forms
│
└── database/                       <- DATABASE SCRIPTS & DEMO DATA
    ├── schema.sql                  <- Complete MySQL DDL schema (29 tables + default settings)
    ├── seed.sql                    <- Comprehensive SQL demo seed dataset (all 4 roles populated)
    ├── seed.php                    <- Automated PHP seeding script generating sample files & data
    ├── sample_cohort.csv           <- 30 valid students + 3 bad test rows for testing import
    ├── backup.bat                  <- Windows automated daily database backup batch script
    └── backup.sh                   <- Linux/macOS automated daily database backup shell script
```

---

## 5. The Four User Roles & RBAC Matrix

InternFlow strictly implements the **four user roles** specified in Section 1.1 of the report:

```
+--------------------------------------------------------------------------------------------------------+
| Role (DB Enum)             | User Description              | Access Scope                              |
+----------------------------+-------------------------------+-------------------------------------------+
| 'coordinator'              | University Coordinator        | Institutional Administrator. Full system  |
|                            | (UNITEN Academic Staff)       | access, cohort import, approvals, lock.   |
|----------------------------+-------------------------------+-------------------------------------------+
| 'student'                  | Industrial Training Student   | Read-only resources, profile/CV, job      |
|                            | (UNITEN CCI Degree/Diploma)   | search, attendance, logbook, reports.     |
|----------------------------+-------------------------------+-------------------------------------------+
| 'company'                  | Host Company Supervisor       | (a) Partner Company: vacancies & interns  |
|                            | (Industry Employer)           | (b) Token-Invited: scoped to 1 intern.    |
|----------------------------+-------------------------------+-------------------------------------------+
| 'university_supervisor'    | Visiting Lecturer             | Academic supervision. Assigned students   |
|                            | (UNITEN Faculty Member)       | roster, logbook grading, report marking.  |
+--------------------------------------------------------------------------------------------------------+
```

### Role-Based Access Control (RBAC) Matrix (Table 3.5)

| Functional Area | Student | Company | University Supervisor | Coordinator |
|---|---|---|---|---|
| **Bulk Cohort Import** | No access | No access | No access | **Full control** |
| **User Directory & Creation** | No access | No access | No access | **Full control** |
| **Company Registration** | Request invitation | **Register / profile** | No access | **Verify / revoke** |
| **Profile & Documents** | **Full (own)** | View applicants | View assigned | **Full control** |
| **Listings & Applications** | **Search / apply** | **Full (own)** | No access | **Review / vet** |
| **Offer, Approval & Assign**| **Accept / decline** | **Issue offer** | View assigned | **Approve / assign** |
| **Daily Attendance** | **Log daily** | **Batch verify** | Monitor | **Audit** |
| **Weekly Logbook** | **Submit / resubmit** | **Review / comment**| **Grade / return** | **Audit** |
| **Monthly Progress Reports**| **Upload once** | View / feedback | View / feedback | **Overview** |
| **Evaluation & Final Grade**| View own grade | **Evaluate (40%)** | **Evaluate (30%+20%+10%)** | **Overview / lock**|
| **Internship Resources** | **View / download** | No access | No access | **Upload / publish**|
| **Announcements** | **View (published)** | No access | No access | **Create / publish**|
| **4-Way Group Chat** | **Participate** | **Participate** | **Participate** | **Monitor / chat** |
| **Dashboards** | Stepper & cards | Interns & batches | Assigned students | **Analytics & charts**|

### Row-Level Security Rules:
1. **Student Row-Level Scope:** A student can only view/edit their own profile, applications, attendance, logbooks, and grades.
2. **Company Row-Level Scope:** A partner company only sees applications for its own vacancies. A token-invited external supervisor is hard-locked by `users.scope_placement_id = placements.id`—they cannot see any other student or company in the system.
3. **University Supervisor Row-Level Scope:** A visiting lecturer can only access students where `placements.university_supervisor_id = users.id`.
4. **File Store Isolation:** Files cannot be downloaded via raw filesystem URLs. Every download request passes through `FileStore::serve()`, which verifies active session authentication and row-level ownership before streaming binary bytes.

---

## 6. The 14 Functional Modules Explained

### Module 1: Authentication & User Management (AUTH)
- **FR-AUTH-01 (Secure Authentication):** Passwords hashed with bcrypt (`password_hash($pw, PASSWORD_BCRYPT)`). Login attempts are tracked in session with an automated 5-attempt/60-second cooldown throttle.
- **FR-AUTH-02 (Role Redirection):** Upon login, `AuthController::redirectByRole()` inspects the authenticated role and redirects:
  - `student` &rarr; `/dashboard`
  - `company` &rarr; `/dashboard`
  - `university_supervisor` &rarr; `/dashboard`
  - `coordinator` &rarr; `/dashboard`
- **FR-AUTH-03 (Bulk Student Cohort Import):** The Coordinator uploads a CSV or Excel file via `/import`. `BulkImportService` parses rows with `fgetcsv()`, validates required columns (Student ID, Name, Email, Programme, Intake), checks for duplicates within the file and against existing database records, and displays an interactive **Preview & Error Breakdown**. Upon confirmation, accounts are auto-provisioned with `verified = 1`, `must_change_password = 1`, and activation emails are dispatched.
- **FR-AUTH-04 (Coordinator-Managed Accounts):** Public registration is prohibited for students and supervisors. Coordinator manually creates exception accounts via `/users/create`.
- **FR-AUTH-05 (Password Recovery & Forced Change):** Password reset requests generate a 32-byte cryptographically secure token (`hash('sha256', $token)`) valid for 72 hours. Accounts created via bulk import are flagged `must_change_password = 1` and forced to choose a new password upon first login (`/force-change-password`).

### Module 2: University & Company Management (UCM)
- **FR-UCM-01 (Partner Company Registration & Approval):** Companies register via `/companies/register`. Registration creates a company profile with `status = 'pending'`. The Coordinator reviews registrations at `/companies` and approves, rejects, or suspends them. **Only approved companies can post vacancies.**
- **FR-UCM-02 (System Invitation Token):** For self-sourced placements (`/placements/self-source`), the system generates a single-use, 72-hour time-limited token (`random_bytes(32)`), stores the SHA-256 hash in `invitation_tokens`, and emails the link to the external company representative.
- **FR-UCM-03 (Scoped Invited Account):** Clicking `/invite/accept?token=...` opens the token registration form. Registering creates an account with `role = 'company'` and `scope_placement_id = :placement_id`. This account is restricted exclusively to that one student.

### Module 3: Student Profile & Document Management (SPD)
- **FR-SPD-01 (Student Profile Management):** Students manage personal details, CGPA, technical skills, interests, emergency contacts, and profile photo at `/profile`.
- **FR-SPD-02 (Document Upload):** Secure upload of CV, Verification Letter, and Academic Transcript (PDF/DOCX, max 10MB).
- **FR-SPD-03 (Eligibility Gate):** The application engine verifies `students.profile_complete = 1` (CV uploaded). If incomplete, the system **blocks internship applications** and prompts the student to upload their CV first.

### Module 4: Internship Listing Management (ILM)
- **FR-ILM-01 (Publish Vacancies):** Approved companies create vacancies specifying title, duties, requirements, available slots, monthly allowance, location, working arrangement (`onsite`, `remote`, `hybrid`), and application deadline at `/vacancies/create`.
- **FR-ILM-02 (Search & Filter):** Students filter active listings at `/vacancies` by keyword, industry, location, and arrangement.
- **FR-ILM-03 (Coordinator Vacancy Vetting):** Coordinator reviews listings at `/vacancies/vetting` and can flag or deactivate inappropriate posts.

### Module 5: Application & Placement Management (APM)
- **FR-APM-01 (Application Submission):** Eligible students apply to published listings with an optional cover letter and stored CV.
- **FR-APM-02 (Company Screening Pipeline):** Companies screen candidates through a 5-stage pipeline at `/applications/screen`:
  `Submitted` &rarr; `Under Review` &rarr; `Shortlisted` &rarr; `Interview` &rarr; `Accepted / Rejected`
  Companies record interview details and issue digital offers (`offer_issued = 1`).
- **FR-APM-03 (Self-Sourced Placement Registration):** Students who secure external internships register company details, supervisor contact, position, and dates at `/placements/self-source`, triggering an automated invitation token.
- **FR-APM-04 (Offer Acceptance & Automated Lock - Business Rule 1):** When a student accepts a digital offer at `/applications`:
  1. A placement record is generated with status `placement_pending`.
  2. **All other active applications submitted by that student are automatically updated to `withdrawn` in a database transaction.**
- **FR-APM-05 (University Coordinator Approval):** Coordinator inspects pending placements at `/placements/approvals` and approves them (`university_approved`).

### Module 6: Supervisor Assignment (SVA)
- **FR-SVA-01 (Supervisor Assignment & Suggested Allocation):** Coordinator assigns a University Supervisor (Visiting Lecturer) at `/supervisors/assignment`. The system calculates current student loads per lecturer and automatically highlights the **suggested supervisor with the lowest current workload**.
- **FR-SVA-02 (Supervisor Workload View):** Visual distribution of assigned students across faculty members.

### Module 7: Attendance Management (ATT)
- **FR-ATT-01 (Daily Attendance Submission):** Active interns record daily check-in, check-out, working hours, arrangement, and absence reason/medical proof at `/attendance`. Records are saved as `pending_verification`.
- **FR-ATT-02 (Batch Attendance Verification - Assumption A4):** Host supervisors verify attendance at `/attendance/batch` **weekly or monthly in one batch action**. Supervisors can flag individual days with comments; approving the batch marks all unflagged days as `verified` in a single click.
- **FR-ATT-03 (Attendance 80% Rule - Business Rule 5):** `AttendanceRule::calculateMonthlyPercentage()` computes attendance:
  $$\text{Attendance \%} = \frac{\text{Verified Present Days}}{\text{Expected Working Days in Period}} \times 100$$
  If verified attendance falls below **80%**, automated warning alerts appear on student, supervisor, and coordinator dashboards.

### Module 8: Digital Logbook Management (LOG)
- **FR-LOG-01 (Automatic 12-Week Logbook Generation):** When a placement transitions to `internship_active`, the system automatically inserts **12 weekly logbook entries** spanning the start and end dates.
- **FR-LOG-02 (Single-Submission Weekly Logbook):** Students document tasks, technical skills developed, challenges encountered, solutions applied, personal reflection, hours, and attachments at `/logbooks/edit`. Submitting once automatically routes the entry to both the host supervisor and university supervisor.
- **FR-LOG-03 (Dual-Party Review):**
  - Host Supervisor reviews entry and adds workplace comments at `/logbooks/review` (`company_reviewed`).
  - University Supervisor reviews, adds academic comments, and assigns a numerical grade (0–100) at `/logbooks/supervisor` (`approved`).
- **FR-LOG-04 (Correction, Resubmission & Revision History):** If a supervisor returns a logbook (`returned`), the student edits and resubmits. The system archives the previous submission into `logbook_revisions` before updating status to `resubmitted`.

### Module 9: Monthly Progress Report Management (MPR)
- **FR-MPR-01 (Monthly Report Single Upload):** Students submit a progress summary and optional report file once per 4-week period at `/monthly-reports` (Month 1: Weeks 1–4, Month 2: Weeks 5–8, Month 3: Weeks 9–12). Both evaluators are notified automatically. **Monthly reports are used strictly for academic monitoring and carry no separate numeric mark.**

### Module 10: Evaluation & Marking (EVAL)
- **FR-EVAL-01 (Host Supervisor Digital Rubric - 40%):** Host supervisor evaluates the intern at `/evaluations/host-rubric` across 9 criteria (rated 1 to 5):
  1. Attendance, 2. Punctuality, 3. Communication, 4. Technical Knowledge, 5. Work Quality, 6. Problem Solving, 7. Teamwork, 8. Professional Behaviour, 9. Training Objectives.
  Sum (out of 45) is converted to a percentage ($Score / 45 \times 100$). Counts for **40%** of the final grade.
- **FR-EVAL-02 (University Supervisor Assessment - 30% + 20%):**
  - **Visiting Lecturer Assessment (30%):** Evaluates visit date, technical scope, defense, employer consultation, and academic alignment (converted to 30%).
  - **Final Report Marking (20%):** Marks the industrial training report (0–100, converted to 20%).
  - **Logbook Marking (10%):** Average of weekly logbook grades constitutes 10%.
- **FR-EVAL-03 (Weighted Final Grade & Coordinator Lock - Business Rule 8):** `GradeEngine::calculateForPlacement()` computes:
  $$\text{Final Grade} = (\text{Host} \times 0.40) + (\text{Visiting} \times 0.30) + (\text{Report} \times 0.20) + (\text{Logbooks} \times 0.10)$$
  **The grade can only be calculated and locked when all four assessment components exist.** The Coordinator reviews the consolidated overview at `/evaluations/lock` and locks the grade, marking the placement as `completed`.

### Module 11: Final Report Submission (FRS)
- **FR-FRS-01 (Final Report Upload & Originality Declaration):** Students upload their comprehensive final report at `/final-reports`. Submission requires ticking a mandatory **Student Originality Declaration** checkbox affirming academic honesty. Files are routed to both academic and host supervisors.

### Module 12: Internal Communication & Notifications (COM)
- **FR-COM-01 (Unified 4-Way Group Chat):** On placement activation, a single group conversation is created connecting Student, Host Supervisor, Visiting Lecturer, and Coordinator (`/chat`). Features message streaming and support for shared meeting links (Teams/Zoom/Meet).
- **FR-COM-02 (Alerts & Notifications):** Real-time in-app notification center (`/notifications`) with an unread badge counter in the topbar. Offline mail simulation logs all outbound notices to `email_log` and `storage/mail.log`.

### Module 13: Dashboards & Reporting (DASH)
- **FR-DASH-01 (Role-Tailored Dashboards & CSV Exports):**
  - **Student:** Stepper lifecycle tracker, attendance percentage, logbook completion count, and **4 quick-access resource cards**.
  - **Company:** Active interns, pending attendance batches, logbooks awaiting review, and pending rubrics.
  - **Supervisor:** Assigned students roster, logbooks to grade, reports to mark, and visiting assessments.
  - **Coordinator:** System-wide KPI cards, Chart.js placement distribution doughnut chart, supervisor workload horizontal bar chart, at-risk student monitoring, and CSV data exports (`/settings/exports`).

### Module 14: Internship Resources Management (IRM)
- **FR-IRM-01 to 04 (Student Resources Portal):** Reachable from sidebar at `/resources` with 4 sections:
  1. **Internship Kit:** Downloadable forms (F1, F2), templates, rubrics, and briefing slides grouped by category.
  2. **Internship Journey:** 4-stage process timeline stepper (Semester 1 &rarr; Semester 2 &rarr; Special Sem &rarr; Results).
  3. **Internship Guidelines:** Academic eligibility (Degree 60 credits, Diploma 45 credits), procedures, responsibilities, and instructions.
  4. **Announcements:** Published notices ordered newest first.
  *Accessible to all logged-in students with or without an approved placement (Business Rule 9).*
- **FR-IRM-05 to 07 (Coordinator Resources Administration):** Full CRUD at `/resource-admin` for Categories, Kit Documents (replacing a file automatically increments the document `version`), Journey Stages, Guidelines, and Announcements (publishing an announcement dispatches in-app alerts to all students).

---

## 7. Core Architectural Services & Workflow Engines

### 1. Front Controller Router (`app/core/Router.php`)
Handles both clean URLs (`/internflow/public/login`) and query routing (`?r=auth/login`). Automatically detects subfolder installations and strips script paths:
```php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}
```

### 2. Database Singleton & Transactions (`app/core/Database.php`)
Guarantees a single PDO connection across the request lifecycle and provides automated transaction wrapping:
```php
Database::transaction(function($pdo) {
    // Operations inside transaction
    // Auto-commits on return; auto-rolls back on Throwable
});
```

### 3. Central Access Control (`app/core/Auth.php`)
Enforces role checks and row-level placement ownership:
- `Auth::requireRole('coordinator')`: Aborts with HTTP 403 if user lacks role.
- `Auth::requirePlacementAccess($placementId)`: Verifies that the logged-in student owns the placement, or the company hosts it, or the supervisor is assigned to it, or the user is Coordinator.

### 4. Grade Calculation Engine (`app/services/GradeEngine.php`)
Enforces **Business Rule 8** and dynamically loads assessment weights from the `settings` table:
```php
$allReady = ($hostScore !== null && $visitingScore !== null && $reportScore !== null && $logbookScore !== null);
if ($allReady) {
    $weightedTotal = ($hostScore * 0.40) + ($visitingScore * 0.30) + ($reportScore * 0.20) + ($logbookScore * 0.10);
    $letterGrade = self::determineLetterGrade($weightedTotal);
}
```

### 5. Private File Store (`app/core/FileStore.php`)
Validates uploaded files against allowed MIME types and extensions (`pdf`, `docx`, `pptx`, `jpg`, `png`), enforces a 10 MB ceiling, stores files with UUID hashes in `storage/uploads/`, and streams content securely:
```php
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
---

## 9. End-to-End Operational Lifecycle: "How It Works Step-by-Step"

InternFlow orchestrates a continuous, auditable workflow structured into six sequential phases. Here is exactly how the system behaves from an empty database to student completion:

```
+--------------------------------------------------------------------------------------------------+
| PHASE 1: Bootstrapping & Cohort Ingestion                                                        |
| Coordinator Bootstrap ➔ CSV Bulk Ingestion ➔ Error Validation ➔ Student Provisioning & Activation|
+--------------------------------------------------------------------------------------------------+
                                                ↓
+--------------------------------------------------------------------------------------------------+
| PHASE 2: Industry Onboarding & Vacancy Publication                                              |
| Company Registration ➔ Coordinator Vetting ➔ Vacancy Creation ➔ Listing Vetting & Publishing     |
+--------------------------------------------------------------------------------------------------+
                                                ↓
+--------------------------------------------------------------------------------------------------+
| PHASE 3: Student Readiness, Discovery & Offer Locking                                            |
| Profile & CV Upload ➔ Eligibility Gate Passed ➔ Apply to Jobs ➔ Screening Pipeline ➔ Offer Lock  |
+--------------------------------------------------------------------------------------------------+
                                                ↓
+--------------------------------------------------------------------------------------------------+
| PHASE 4: Placement Vetting & Supervisor Allocation                                               |
| Institutional Approval ➔ Workload-Balanced Academic Supervisor Assignment ➔ System Activation    |
+--------------------------------------------------------------------------------------------------+
                                                ↓
+--------------------------------------------------------------------------------------------------+
| PHASE 5: Active 12-Week Industrial Training Execution                                           |
| Auto-Generated 12 Logbooks ➔ Daily Attendance & Batch Verify ➔ Monthly Reports ➔ 4-Way Chat     |
+--------------------------------------------------------------------------------------------------+
                                                ↓
+--------------------------------------------------------------------------------------------------+
| PHASE 6: Dual Rubrics, Weighted Grade Calculation & Completion                                   |
| Final Report (Originality Declaration) ➔ Host 9-Criteria Rubric (40%) ➔ Visiting Evaluation (30%)|
| ➔ Report Mark (20%) ➔ Logbook Average (10%) ➔ GradeEngine Lock ➔ Cohort CSV Export               |
+--------------------------------------------------------------------------------------------------+
```

---

### Phase 1: Bootstrapping & Cohort Ingestion
1. **Cold Start:** Because public registration for students and academic supervisors is strictly prohibited (`FR-AUTH-04`), the system starts with an initial Coordinator bootstrap account created via `database/seed.sql` or direct insert.
2. **Bulk Cohort Ingestion (`/import`):** The Coordinator logs in and uploads the official cohort spreadsheet (`.csv` or `.xlsx`).
3. **Validation & Duplicate Prevention (`BulkImportService`):**
   - The engine streams the file row by row (`fgetcsv()`).
   - Verifies required headers (`student_id`, `name`, `email`, `programme`, `intake`).
   - Detects intra-file duplicate student IDs or emails.
   - Cross-references existing database records to prevent duplicate enrollment.
4. **Interactive Preview Table:** If invalid rows are detected (e.g. malformed email or missing ID), the system flags the exact line numbers and error reasons while allowing valid rows to proceed.
5. **Account Provisioning:** Confirming the import creates `users` and `students` records in a single database transaction. Default credentials are set, flagged with `must_change_password = 1`. Activation notices are logged to `email_log` and `storage/mail.log`.
6. **First-Time Password Change (`/force-change-password`):** Upon initial login, the student is intercepted by `Auth::checkFirstLogin()` and forced to define a secure personal password before accessing the system.

---

### Phase 2: Industry Onboarding & Vacancy Publishing
1. **Employer Registration (`/companies/register`):** Prospective partner companies submit their company profile (registration number, industry, address, contact person, website).
2. **Pending State:** Newly registered companies are placed in `status = 'pending'` and cannot publish listings.
3. **Coordinator Approval (`/companies`):** The Coordinator reviews the company credentials and clicks **Approve**. The company status updates to `active`.
4. **Vacancy Creation (`/vacancies/create`):** The active employer posts internship vacancies defining title, department, job scope, required qualifications, available slots, monthly stipend, location, working arrangement (`onsite`, `remote`, `hybrid`), and application deadline.
5. **Coordinator Listing Vetting (`/vacancies/vetting`):** The Coordinator audits published listings to ensure institutional suitability and can flag or unpublish substandard postings.

---

### Phase 3: Student Profiling, Job Application & Offer Locking
1. **Student Profile Completion (`/profile`):** The student fills in contact details, CGPA, technical skills, interests, and emergency contacts.
2. **Document Upload & Eligibility Gate (`FR-SPD-03`):** The student uploads their PDF CV. Once saved, `students.profile_complete` becomes `1`. If incomplete, the application engine blocks vacancy submissions.
3. **Resource Access (`/resources`):** Regardless of application status, students can access the **Internship Resources Hub** (Internship Kit downloadable templates, 4-stage Journey stepper, Academic Guidelines, and official announcements).
4. **Vacancy Search & Application (`/vacancies`):** The student searches vetted vacancies by keywords, arrangement, or industry, and submits an application along with an optional personalized cover letter.
5. **Company Screening Pipeline (`/applications/screen`):**
   - The employer reviews applicants across a 5-stage kanban-style pipeline: `Submitted` ➔ `Under Review` ➔ `Shortlisted` ➔ `Interview` ➔ `Accepted / Rejected`.
   - The employer schedules interviews, notes feedback, and issues an official digital internship offer (`offer_issued = 1`).
6. **Offer Acceptance & Business Rule 1 Lock (`ApplicationController::respondOffer`):**
   - The student views offers at `/applications` and clicks **Accept Offer**.
   - The engine opens a database transaction:
     - The selected application status transitions to `accepted`.
     - A new `placements` record is created with status `placement_pending`.
     - **All other active applications submitted by this student are automatically updated to `withdrawn`.**
     - The student is now locked from submitting additional applications.

---

### Phase 4: Self-Sourcing Alternative & Placement Vetting
1. **Alternative: Self-Sourced Placements (`/placements/self-source`):**
   - If a student secures an internship independently, they fill the self-source form with company details, position, dates, and supervisor email.
   - **System Invitation Token (`FR-UCM-02`, Rule 2):** The system generates a single-use, 72-hour cryptographically secure token (`random_bytes(32)`) and emails an invitation link to the external supervisor.
   - **Scoped Registration (`/invite/accept`):** The external supervisor clicks the link and creates their credentials. This account is flagged `users.scope_placement_id = :id`—granting access *strictly* to this student without exposing other institutional data.
2. **Coordinator Placement Approval (`/placements/approvals`):**
   - The Coordinator audits the offer letter, job scope, and company details.
   - Clicking **Approve** moves status to `university_approved`.
3. **Supervisor Assignment with Load Balancing (`/supervisors/assignment`):**
   - The Coordinator assigns a University Supervisor (Visiting Lecturer) from the academic staff list.
   - The UI displays current supervisee counts per lecturer and dynamically recommends the lecturer with the lowest current workload (`FR-SVA-01`).
4. **Placement Activation:** Once approved and assigned, the placement transitions to `internship_active`.

---

### Phase 5: The 12-Week Active Training Execution
1. **Automated Lifecycle Initialization:**
   - Transitioning to `internship_active` triggers `LogbookController::generateWeeks()` which pre-populates **12 weekly logbook entries** across the 12-week calendar (`FR-LOG-01`).
   - A unified **4-Way Group Chat** (`/chat`) is automatically provisioned connecting Student, Host Supervisor, University Supervisor, and Coordinator (`FR-COM-01`).
2. **Daily Attendance Management (`/attendance`):**
   - Active interns record their daily check-in time, check-out time, total working hours, modality, and absence justification (with medical certificate attachment if applicable).
   - Records are stored as `pending_verification`.
3. **1-Click Batch Verification (`AttendanceController::batchVerify`, Rule 4):**
   - The Host Supervisor navigates to `/attendance/batch`.
   - The supervisor inspects the attendance log for the week or month.
   - If discrepancies exist, individual days can be flagged with comments.
   - Clicking **Approve Unflagged Records** instantly verifies the entire period in a single click.
4. **Attendance 80% Rule Monitoring (`AttendanceRule`, Rule 5):**
   - The system continuously calculates verified attendance against expected working days:
     $$\text{Verified Rate} = \frac{\text{Verified Present Days}}{\text{Expected Working Days}} \times 100$$
   - If verified attendance falls below **80%**, automated warning badges and alerts are triggered on student, supervisor, and coordinator dashboards.
5. **Weekly Digital Logbooks (`/logbooks`, Rule 6):**
   - The student completes their weekly submission at `/logbooks/edit`: tasks performed, technical skills utilized, problems encountered, applied solutions, weekly reflection, and hours logged.
   - **Dual-Party Review Flow:**
     1. Host Supervisor reviews the entry at `/logbooks/review` and submits workplace comments (`company_reviewed`).
     2. University Supervisor reviews the entry at `/logbooks/supervisor`, inputs academic feedback, and awards a numerical grade from 0 to 100 (`approved`).
   - If rejected (`returned`), the student resubmits. The system captures an immutable snapshot in `logbook_revisions` for full auditability (`FR-LOG-04`).
6. **Monthly Progress Reports (`/monthly-reports`, Rule 10):**
   - Students upload a structured summary and progress document at 4-week milestones (Month 1, Month 2, Month 3).
   - Both supervisors receive notifications and review the progress to track milestones. Monthly reports carry no separate numeric marks but ensure early intervention for lagging projects.
7. **Continuous 4-Way Communication (`/chat`):**
   - Stakeholders exchange messages in real-time.
   - University supervisors or students can paste Microsoft Teams, Google Meet, or Zoom links into the chat for virtual meetings.

---

### Phase 6: Dual Rubrics, Weighted Grade Calculation & Completion
1. **Final Report Submission (`/final-reports`, `FR-FRS-01`):**
   - At the conclusion of Week 12, the student uploads their comprehensive Industrial Training Final Report.
   - **Student Originality Declaration:** The submission form enforces a mandatory legal affirmation checkbox declaring that the report is original work adhering to academic integrity policies.
2. **Host Supervisor Digital Rubric (`/evaluations/host-rubric`, 40% Weight):**
   - The Host Supervisor completes the 9-criteria evaluation (scored 1 to 5 per criterion, max 45 points):
     1. Attendance & Dependability
     2. Punctuality
     3. Communication & Interpersonal Skills
     4. Technical Knowledge & Application
     5. Quality & Accuracy of Work
     6. Initiative & Problem Solving
     7. Teamwork & Cooperation
     8. Professional Conduct & Ethics
     9. Achievement of Training Objectives
   - The system computes:
     $$\text{Host Score \%} = \frac{\text{Total Points}}{45} \times 100$$
3. **University Supervisor Academic Evaluation (50% Weight Combined):**
   - **Visiting Lecturer Assessment (30%):** Evaluates presentation, defense, company supervisor consultation, and academic achievement (0–100 converted to 30%).
   - **Final Report Marking (20%):** University Supervisor grades the written report against academic standards (0–100 converted to 20%).
4. **Weekly Logbook Average (10% Weight):**
   - The system aggregates the numerical scores across all 12 approved logbooks to compute the 10% logbook component.
5. **Weighted Grade Calculation (`GradeEngine`, Rule 8):**
   - The `GradeEngine` combines all four components according to the institutional weight configuration:
     $$\text{Final Grade} = (\text{Host} \times 0.40) + (\text{Visiting} \times 0.30) + (\text{Report} \times 0.20) + (\text{Logbooks} \times 0.10)$$
   - Standard letter grade mapped:
     - `80 - 100%`: **A / A+** (Distinction)
     - `70 - 79%`: **B / B+** (Credit)
     - `60 - 69%`: **C / C+** (Pass)
     - `50 - 59%`: **D** (Marginal Pass)
     - `0 - 49%`: **F** (Fail)
   - **Strict Rule 8 Enforcement:** Grade calculation and locking are strictly blocked until **all four components are fully submitted and graded**.
6. **Coordinator Grade Lock & Completion (`/evaluations/lock`):**
   - The Coordinator audits the consolidated score sheet.
   - Clicking **Lock Final Grade** permanently locks the assessment, prevents further edits, and moves the placement to `completed`.
7. **Institutional Reporting & Data Export (`/settings/exports`):**
   - The Coordinator exports full cohort outcomes to administrative CSV files for faculty examination boards, accreditation records, and UNITEN CCI archives.

---

## 10. How to Run, Test, and Maintain the System

### 1. Running Under WampServer (Recommended for Local Windows)
1. Ensure the project is located in **`C:\wamp64\www\internflow`**.
2. Start WampServer (ensure Apache and MySQL services are green).
3. Access in any browser:
   - **`http://localhost/internflow/public/login`**
   - (Or simply `http://localhost/internflow` — root `index.php` will auto-redirect to `public/`).

### 2. Running Under XAMPP
1. Place project in `C:\xampp\htdocs\internflow`.
2. Ensure Apache `rewrite_module` is enabled in `httpd.conf`.
3. Navigate to `http://localhost/internflow/public/login`.

### 3. Running Under PHP Built-in Server
From PowerShell / Command Prompt inside the project directory:
```cmd
cd C:\wamp64\www\internflow
php -S 127.0.0.1:8000 -t public
```
Navigate to: **`http://127.0.0.1:8000/login`**

### 4. Database Schema & Re-Seeding
- **Database Name:** `internflow` (MySQL 8.x)
- **Import Schema:** `mysql -u root -p internflow < database/schema.sql`
- **Import Demo Seed Data:** `mysql -u root -p internflow < database/seed.sql`
- **Automated CLI Seeder:** `php database/seed.php` (creates realistic sample files in `storage/` and populates mock data across all 4 roles).

### 5. Automated Database Backups (NFR-REL-02)
- **Windows:** Double-click or run `database/backup.bat` (generates `storage/backups/internflow_backup_TIMESTAMP.sql` with automatic 30-day retention cleanup).
- **Linux:** Run `database/backup.sh` (or schedule via crontab).
- **Database Restoration:**
  ```cmd
  mysql -u root -p internflow < storage/backups/internflow_backup_20261023_010000.sql
  ```

### 6. Standard Demo Accounts (Password: `Password@123`)
| Role | Email | Scenario / Permissions |
|---|---|---|
| **University Coordinator** | `coordinator@internflow.test` | Full institutional control, CSV cohort import, approvals, grade lock, exports. |
| **Active Student (TNB)** | `aly.youssef@student.uniten.edu.my` | Active placement at Tenaga Nasional Berhad. Daily attendance, logbooks, reports. |
| **Host Supervisor (TNB)** | `intern@tnb.com.my` | Host mentor at TNB. 1-click batch attendance verification, logbook review, 40% rubric. |
| **University Supervisor** | `dr.ahmed@uniten.edu.my` | Academic visiting lecturer. Assigned student roster, logbook grading, 30% visit, 20% report. |
| **Invited Scoped Supervisor** | `david.lee@infinitedata.test` | Token-registered external supervisor scoped exclusively to student placement #2. |
| **Partner Company (Petronas)** | `hr@petronas.test` | Approved partner company managing active vacancies and applicant screening pipeline. |

