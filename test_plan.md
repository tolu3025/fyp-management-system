# Testing Documentation - FYP Management System

This document outlines the unit and system tests performed to verify the requirements, data integrity, and notification mechanisms of the FYP Management System.

---

## 1. Unit Testing

### Test Case UT-001: Authentication Logic and Role Redirection
- **Objective:** Verify that users are authenticated using hashed passwords and redirected to their role-specific dashboards.
- **Actions:**
  1. Log in with incorrect credentials. (Expected: Display "Invalid Login ID or Password" alert).
  2. Log in with `HOD001` / `password123`. (Expected: Redirect to `hod_dashboard.php`).
  3. Log in with `Lec001` / `password123`. (Expected: Redirect to `supervisor_dashboard.php`).
  4. Log in with `CSC/2022/001` / `password123`. (Expected: Redirect to `student_dashboard.php`).
- **Results:** PASS. Session variables correctly populated with user roles.

### Test Case UT-002: HOD Activity CRUD Management
- **Objective:** Verify HOD's ability to schedule, update, and delete departmental activities.
- **Actions:**
  1. Navigate to HOD Activities page. Add an activity: Code `ACT004`, Title `Synopsis Defence`, Date `2026-09-15`, Time `14:00`, Location `Seminar Room`.
  2. Edit `ACT004` to change Location to `Hall A`.
  3. Delete `ACT004`.
- **Results:** PASS. Database is successfully updated without duplicating codes; validation prevents empty entries.

### Test Case UT-003: Supervisor Assignment Constraints
- **Objective:** Verify that assigning a supervisor to a student inserts/updates the `Project` table.
- **Actions:**
  1. Navigate to HOD Register & Assign. Assign student `CSC/2022/001` to `Lec001`.
  2. Verify that a database row is inserted into the `Project` table.
  3. Assign student `CSC/2022/001` to `Lec002`.
  4. Verify that the existing row in `Project` is updated rather than creating a duplicate project entry.
- **Results:** PASS. `Project` table maintains a `No_matrik` UNIQUE constraint.

### Test Case UT-004: Project Title Registration
- **Objective:** Verify that students can register project titles, which updates their project assignment row.
- **Actions:**
  1. Log in as student `CSC/2022/001`. Navigate to Register Title.
  2. Enter project title "Distributed FYP Database System". Submit.
  3. Verify `Project` table has `Tajuk_Projek` populated for this student.
- **Results:** PASS. Title correctly updated in the database.

### Test Case UT-005: Supervisor Task Assignments
- **Objective:** Verify that supervisors can assign tasks with deadlines to their students.
- **Actions:**
  1. Log in as `Lec001`. Navigate to assigned student list and choose `Adekunle Tobi`.
  2. Assign task: "Write Chapter 1 Introduction". Set a deadline. Submit.
  3. Check `Task` table to verify row creation with status `Belum Disahkan`.
- **Results:** PASS. Task assigned and shown on student's task board.

---

## 2. Notification Verification (Scenario A & B)

### Test Case NT-001: Student Submits Deliverable (Scenario A)
- **Objective:** Validate that student submissions trigger in-system alerts and email notifications for both HOD and assigned Supervisor.
- **Actions:**
  1. Log in as student `CSC/2022/001`. Navigate to Submissions.
  2. Select weekly report, type title "Week 1 Accomplishments", enter log contents, and submit.
  3. Log in as Supervisor `Lec001`. Check the notification tray.
  4. Log in as HOD `HOD001`. Check the notification tray.
  5. Check `logs/emails.log` or open the Developer Email Console in the footer.
- **Results:** PASS.
  - In-system notification created for both HOD and Supervisor: *"New submission by Adekunle Tobi (CSC/2022/001): Weekly Progress Report - 'Week 1 Accomplishments' on..."*
  - Emails logged to `logs/emails.log` for both `alabi@oduduwa.edu.ng` (Supervisor) and `hod@oduduwa.edu.ng` (HOD).

### Test Case NT-002: Supervisor Comments & Endorses (Scenario B)
- **Objective:** Validate that supervisor reviews trigger dashboard notifications and email notifications to the student.
- **Actions:**
  1. Log in as Supervisor `Lec001`. Navigate to student's submission.
  2. Click **Review** next to the submission.
  3. Enter comments: "Good introduction. Chapter approved."
  4. Choose "Validate & Endorse progress (Pengesahan)". Submit review.
  5. Log in as student `CSC/2022/001`. Check notification tray and comments section.
  6. Check `logs/emails.log` or footer console.
- **Results:** PASS.
  - In-system notification created for student: *"Dr. Samuel Alabi posted feedback on your Weekly Progress Report submission: 'Week 1 Accomplishments' and endorsed the progress."*
  - Email logged to `logs/emails.log` for `adekunle@student.oduduwa.edu.ng`.
  - Linked task status in `Task` table updated to `Disahkan`.

---

## 3. System Integration Testing

- **Data Integrity Audits:** Verified that cascade constraints prevent orphaned submissions, comments, or tasks when a student is deleted.
- **XSS/SQL Injection Checks:** Input validation sanitizes input variables (e.g. `sanitize()`), and PDO prepared statements protect against malicious SQL injections.
- **Chrome Compatibility:** Verified responsive layout wrapping on mobile size breakpoints and desktop viewing.
