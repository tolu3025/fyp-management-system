<?php
// includes/lang.php
// Centralized translation dictionary for FYP Management System (English / Malay)

$lang_dict = [
    'en' => [
        // Branding & Headers
        'system_title' => 'Computer Science FYP Portal — Oduduwa University',
        'login_title' => 'CS Department FYP Portal',
        'login_subtitle' => 'Oduduwa University Ipetumodu',
        'dept_title' => 'Department of Computer Science',
        'college_title' => 'Ramon Adedoyin College of Natural and Applied Sciences',
        
        // Navigation Links
        'dashboard' => 'Dashboard',
        'register_assign' => 'Register & Assign',
        'manage_activities' => 'Manage Activities',
        'progress_reports' => 'Progress Reports',
        'my_students' => 'My Students',
        'register_title' => 'Register Title',
        'tasks_submissions' => 'Tasks & Submissions',
        'logout' => 'Logout',
        'back_to_dashboard' => 'Back to Dashboard',
        
        // Actions
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'back' => 'Back',
        'actions' => 'Actions',
        'save' => 'Save Changes',
        
        // General Forms & Fields
        'matric_no' => 'Matric Number',
        'full_name' => 'Full Name',
        'password' => 'Password',
        'semester' => 'Semester',
        'email' => 'Email Address',
        'staff_no' => 'Lecturer Username',
        'designation' => 'Designation / Title',
        'select_student' => 'Select Student',
        'select_supervisor' => 'Select Supervisor',
        'choose_student' => '-- Choose Student --',
        'choose_supervisor' => '-- Choose Lecturer --',
        'project_title' => 'Project Topic / Title',
        
        // Status Terms
        'status' => 'Status',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'resubmit' => 'Resubmit',
        'not_registered' => 'Not Registered',
        'not_assigned' => 'Not Assigned',
        'status_read' => 'Read',
        'status_unread' => 'Unread',
        
        // Login & Registration UI
        'login_button' => 'Log In',
        'register_button' => 'Register',
        'register_page_title' => 'Create an Account',
        'register_role' => 'Select Your Role',
        'student' => 'Student',
        'supervisor_role' => 'Supervisor (Lecturer)',
        'already_have_account' => 'Already have an account? Log In',
        'dont_have_account' => "Don't have an account? Register Here",
        'username_or_id' => 'Username / Matric Number',
        
        // HOD Dashboard
        'hod_workspace' => 'HOD Administration',
        'total_students' => 'Total Students',
        'total_supervisors' => 'Total Supervisors',
        'assigned_projects' => 'Assigned Projects',
        'recent_system_alerts' => 'Recent System Alerts',
        'no_alerts' => 'No new alerts.',
        'supervision_registry' => 'Active Supervision Registry',
        
        // HOD User Administration
        'user_mgmt' => 'User Registration & Supervisor Allocation',
        'user_mgmt_desc' => 'Manage accounts and distribute supervision loads',
        'reg_new_student' => 'Register New Student',
        'reg_new_supervisor' => 'Register New Supervisor',
        'assign_stud_sup' => 'Assign Student to Supervisor',
        'link_assignment' => 'Link Supervision Assignment',
        
        // HOD Activities Page
        'activity_mgmt' => 'Departmental FYP Activities',
        'activity_mgmt_desc' => 'Schedule milestones and events for all final year students',
        'add_new_activity' => 'Schedule New Activity',
        'activity_code' => 'Activity Code',
        'time' => 'Time',
        'date' => 'Date',
        'location' => 'Location',
        'activity_type' => 'Activity Type (e.g. Synopsis, Presentation)',
        'active_milestones' => 'Active Milestone Schedules',
        
        // HOD Reports Page
        'monitoring_reports' => 'Supervision Monitoring Reports',
        'monitoring_reports_desc' => 'Generate and review progress across all supervisors and students',
        'generate_report' => 'Export / Print Report',
        'overall_completion' => 'Overall Task Completion Progress',
        
        // Student Workspace
        'student_workspace' => 'Student Workspace',
        'my_fyp' => 'My Final Year Project',
        'task_completion_audit' => 'Task Completion Audit',
        'checklist_approved' => 'Checklist Tasks Approved',
        'supervisor_checklist' => 'Supervisor Tasks Checklist',
        'upload_submissions' => 'Upload Submissions',
        'task_goal' => 'Task Goal',
        'deadline' => 'Deadline',
        'upcoming_activities' => 'Upcoming Departmental FYP Activities',
        'recent_submissions' => 'Recent Submissions',
        'no_submissions' => 'No submissions made yet.',
        'reg_proj_title' => 'Register Project Title',
        'discussions_precede' => 'Discussions with supervisor must precede registration.',
        
        // Student Project Title Page
        'project_title_form' => 'Project Title Form',
        'assigned_supervisor' => 'Assigned Supervisor',
        'project_title_desc' => 'Register your final year project title after aligning with your supervisor',
        
        // Student Submissions Page
        'submit_deliverables' => 'Submit Deliverables & Progress Reports',
        'submit_deliverables_desc' => 'Log weekly activities, submit milestone tasks, or upload final project thesis',
        'weekly_progress_report' => 'Submit Weekly Progress Report',
        'upload_task_file' => 'Upload Completed Task File',
        'submit_final_report' => 'Submit Final Project Report',
        'report_week' => 'Report Week / Title',
        'report_content' => 'Report Details / Content',
        'select_task' => 'Select Associated Task',
        'choose_task' => '-- Select Task Milestone --',
        'upload_file' => 'Upload File (PDF, DOCX, ZIP)',
        'final_thesis' => 'Final Thesis Document',
        'my_submission_history' => 'My Submission History',
        'download_file' => 'Download File',
        
        // Supervisor Workspace
        'supervisor_workspace' => 'Supervisor Workspace',
        'my_students_load' => 'Supervision Load',
        'active_supervision_load' => 'Active Supervision Load',
        'no_students_assigned' => 'No students assigned to you yet.',
        'student_progress' => 'Student Progress',
        
        // Supervisor Tasks Page
        'assign_tasks_milestones' => 'Assign Tasks & Milestones',
        'assign_tasks_desc' => 'Create specific goals and deadlines for this student',
        'add_task' => 'Assign Task',
        'current_assigned_tasks' => 'Current Assigned Tasks for',
        
        // Supervisor Review/Feedback Page
        'review_submission' => 'Review Student Submission',
        'review_desc' => 'Evaluate progress, provide feedback, validate completion, or request corrections',
        'submission_details' => 'Submission Details',
        'student_name' => 'Student Name',
        'submission_type' => 'Submission Type',
        'date_submitted' => 'Date Submitted',
        'content_text' => 'Content / Text Body',
        'file_attachment' => 'File Attachment',
        'feedback_comments' => 'Feedback & Comments',
        'no_comments' => 'No comments posted yet.',
        'post_feedback' => 'Post Feedback & Update Status',
        'change_status' => 'Validate / Endorse Status',
        'waiting_review' => 'Waiting Review',
        'endorse_status' => 'Endorse / Approve Task',
        'request_resubmission' => 'Request Resubmission',
        'enter_comments' => 'Enter feedback comments here...',
        
        // Landing Page
        'landing_hero_title' => 'Streamline Your Final Year Project Journey',
        'landing_hero_desc' => 'A centralized portal for Ramon Adedoyin College of Natural and Applied Sciences, Department of Computer Science at Oduduwa University Ipetumodu. Designed for Students, Supervisors, and the HOD.',
        'explore_features' => 'Explore Features',
        'landing_roles_title' => 'Tailored Interfaces for All Roles',
        'landing_hod_title' => 'Head of Department',
        'landing_hod_desc' => 'Allocate supervisors, manage overall departmental activity milestones, and track system-wide progress metrics.',
        'landing_supervisor_title' => 'Supervisor / Lecturer',
        'landing_supervisor_desc' => 'Assign task deadlines, review weekly progress reports, upload comments, and endorse student milestones.',
        'landing_student_title' => 'Student',
        'landing_student_desc' => 'Register agreed project topics, review deadlines, upload weekly logs, and submit final thesis documents.',
        'ready_to_start' => 'Ready to get started?',
        
        // Submission Details / Remarks
        'view_remarks' => 'View Remarks',
        'view_details' => 'View Details',
        'corrections_requested' => 'Corrections Requested',
        'corrections_requested_desc' => 'Your supervisor has requested corrections. Please review the remarks below and submit an updated version.',
        'assessment_decision' => 'Assessment & Endorsement Form',
        'comments_timeline' => 'Feedback Remarks Timeline',
    ],
    'ms' => [
        // Branding & Headers
        'system_title' => 'Portal FYP Sains Komputer — Universiti Oduduwa',
        'login_title' => 'Portal FYP Jabatan Sains Komputer',
        'login_subtitle' => 'Universiti Oduduwa Ipetumodu',
        'dept_title' => 'Jabatan Sains Komputer',
        'college_title' => 'Kolej Sains Semula Jadi dan Gunaan Ramon Adedoyin',
        
        // Navigation Links
        'dashboard' => 'Papan Pemuka',
        'register_assign' => 'Daftar & Agih',
        'manage_activities' => 'Urus Aktiviti',
        'progress_reports' => 'Laporan Kemajuan',
        'my_students' => 'Pelajar Saya',
        'register_title' => 'Daftar Tajuk',
        'tasks_submissions' => 'Tugasan & Penghantaran',
        'logout' => 'Log Keluar',
        'back_to_dashboard' => 'Kembali ke Papan Pemuka',
        
        // Actions
        'submit' => 'Hantar',
        'cancel' => 'Batal',
        'edit' => 'Edit',
        'delete' => 'Padam',
        'back' => 'Kembali',
        'actions' => 'Tindakan',
        'save' => 'Simpan Perubahan',
        
        // General Forms & Fields
        'matric_no' => 'No Matrik',
        'full_name' => 'Nama Penuh',
        'password' => 'Katalaluan',
        'semester' => 'Semester',
        'email' => 'Alamat E-mel',
        'staff_no' => 'Nama Pengguna Pensyarah',
        'designation' => 'Jawatan / Gelaran',
        'select_student' => 'Pilih Pelajar',
        'select_supervisor' => 'Pilih Penyelia',
        'choose_student' => '-- Pilih Pelajar --',
        'choose_supervisor' => '-- Pilih Pensyarah --',
        'project_title' => 'Topik / Tajuk Projek',
        
        // Status Terms
        'status' => 'Status',
        'pending' => 'Belum Selesai',
        'approved' => 'Diluluskan',
        'resubmit' => 'Hantar Semula',
        'not_registered' => 'Belum Didaftar',
        'not_assigned' => 'Belum Diagihkan',
        'status_read' => 'Dibaca',
        'status_unread' => 'Belum Dibaca',
        
        // Login & Registration UI
        'login_button' => 'Log Masuk',
        'register_button' => 'Daftar',
        'register_page_title' => 'Cipta Akaun Baharu',
        'register_role' => 'Pilih Peranan Anda',
        'student' => 'Pelajar',
        'supervisor_role' => 'Penyelia (Pensyarah)',
        'already_have_account' => 'Sudah mempunyai akaun? Log Masuk',
        'dont_have_account' => "Belum mempunyai akaun? Daftar di Sini",
        'username_or_id' => 'Nama Pengguna / No Matrik',
        
        // HOD Dashboard
        'hod_workspace' => 'Pentadbiran HOD',
        'total_students' => 'Jumlah Pelajar',
        'total_supervisors' => 'Jumlah Penyelia',
        'assigned_projects' => 'Projek Diagihkan',
        'recent_system_alerts' => 'Makluman Sistem Terkini',
        'no_alerts' => 'Tiada makluman baharu.',
        'supervision_registry' => 'Pendaftaran Penyeliaan Aktif',
        
        // HOD User Administration
        'user_mgmt' => 'Pendaftaran Pengguna & Pengagihan Penyelia',
        'user_mgmt_desc' => 'Urus akaun dan agihkan beban penyeliaan',
        'reg_new_student' => 'Daftar Pelajar Baharu',
        'reg_new_supervisor' => 'Daftar Penyelia Baharu',
        'assign_stud_sup' => 'Agihkan Pelajar kepada Penyelia',
        'link_assignment' => 'Hubungkan Tugasan Penyeliaan',
        
        // HOD Activities Page
        'activity_mgmt' => 'Aktiviti FYP Jabatan',
        'activity_mgmt_desc' => 'Jadualkan pencapaian dan acara untuk semua pelajar tahun akhir',
        'add_new_activity' => 'Jadualkan Aktiviti Baharu',
        'activity_code' => 'Kod Aktiviti',
        'time' => 'Masa',
        'date' => 'Tarikh',
        'location' => 'Lokasi',
        'activity_type' => 'Jenis Aktiviti (cth. Sinopsis, Pembentangan)',
        'active_milestones' => 'Jadual Acara Milestone Aktif',
        
        // HOD Reports Page
        'monitoring_reports' => 'Laporan Pemantauan Penyeliaan',
        'monitoring_reports_desc' => 'Jana dan semak kemajuan di semua penyelia dan pelajar',
        'generate_report' => 'Eksport / Cetak Laporan',
        'overall_completion' => 'Kemajuan Keseluruhan Penyelesaian Tugasan',
        
        // Student Workspace
        'student_workspace' => 'Ruang Kerja Pelajar',
        'my_fyp' => 'Projek Tahun Akhir Saya',
        'task_completion_audit' => 'Audit Siap Tugasan',
        'checklist_approved' => 'Tugasan Senarai Semak Diluluskan',
        'supervisor_checklist' => 'Senarai Semak Tugasan Penyelia',
        'upload_submissions' => 'Muat Naik Penghantaran',
        'task_goal' => 'Matlamat Tugasan',
        'deadline' => 'Tarikh Akhir',
        'upcoming_activities' => 'Aktiviti FYP Jabatan Akan Datang',
        'recent_submissions' => 'Penghantaran Terkini',
        'no_submissions' => 'Belum ada penghantaran dibuat.',
        'reg_proj_title' => 'Daftar Tajuk Projek',
        'discussions_precede' => 'Perbincangan dengan penyelia mesti mendahului pendaftaran.',
        
        // Student Project Title Page
        'project_title_form' => 'Borang Tajuk Projek',
        'assigned_supervisor' => 'Penyelia Ditugaskan',
        'project_title_desc' => 'Daftar tajuk projek tahun akhir anda selepas selari dengan penyelia anda',
        
        // Student Submissions Page
        'submit_deliverables' => 'Hantar Laporan Kemajuan & Hasil Kerja',
        'submit_deliverables_desc' => 'Log aktiviti mingguan, hantar tugasan milestone, atau muat naik tesis projek akhir',
        'weekly_progress_report' => 'Hantar Laporan Kemajuan Mingguan',
        'upload_task_file' => 'Muat Naik Fail Tugasan Selesai',
        'submit_final_report' => 'Hantar Laporan Projek Akhir',
        'report_week' => 'Minggu Laporan / Tajuk',
        'report_content' => 'Butiran Laporan / Kandungan',
        'select_task' => 'Pilih Tugasan Berkaitan',
        'choose_task' => '-- Pilih Milestone Tugasan --',
        'upload_file' => 'Muat Naik Fail (PDF, DOCX, ZIP)',
        'final_thesis' => 'Dokumen Tesis Akhir',
        'my_submission_history' => 'Sejarah Penghantaran Saya',
        'download_file' => 'Muat Turun Fail',
        
        // Supervisor Workspace
        'supervisor_workspace' => 'Ruang Kerja Penyelia',
        'my_students_load' => 'Beban Penyeliaan',
        'active_supervision_load' => 'Beban Penyeliaan Aktif',
        'no_students_assigned' => 'Tiada pelajar diagihkan kepada anda lagi.',
        'student_progress' => 'Kemajuan Pelajar',
        
        // Supervisor Tasks Page
        'assign_tasks_milestones' => 'Agih Tugasan & Milestone',
        'assign_tasks_desc' => 'Cipta matlamat khusus dan tarikh akhir untuk pelajar ini',
        'add_task' => 'Agih Tugasan',
        'current_assigned_tasks' => 'Tugasan Semasa untuk',
        
        // Supervisor Review/Feedback Page
        'review_submission' => 'Semak Penghantaran Pelajar',
        'review_desc' => 'Nilai kemajuan, beri maklum balas, sahkan kesiapan, atau minta pembetulan',
        'submission_details' => 'Butiran Penghantaran',
        'student_name' => 'Nama Pelajar',
        'submission_type' => 'Jenis Penghantaran',
        'date_submitted' => 'Tarikh Dihantar',
        'content_text' => 'Kandungan / Teks Utama',
        'file_attachment' => 'Lampiran Fail',
        'feedback_comments' => 'Maklum Balas & Komen',
        'no_comments' => 'Tiada komen dihantar lagi.',
        'post_feedback' => 'Hantar Maklum Balas & Kemas Kini Status',
        'change_status' => 'Sahkan / Luluskan Status',
        'waiting_review' => 'Menunggu Semakan',
        'endorse_status' => 'Sahkan / Luluskan Tugasan',
        'request_resubmission' => 'Minta Hantar Semula',
        'enter_comments' => 'Masukkan komen maklum balas di sini...',
        
        // Landing Page
        'landing_hero_title' => 'Permudahkan Perjalanan Projek Tahun Akhir Anda',
        'landing_hero_desc' => 'Portal terpusat untuk Kolej Sains Semula Jadi dan Gunaan Ramon Adedoyin, Jabatan Sains Komputer di Universiti Oduduwa Ipetumodu. Direka untuk Pelajar, Penyelia, dan HOD.',
        'explore_features' => 'Terokai Ciri-ciri',
        'landing_roles_title' => 'Antaramuka Khusus untuk Semua Peranan',
        'landing_hod_title' => 'Ketua Jabatan',
        'landing_hod_desc' => 'Agihkan penyelia, urus milestone aktiviti jabatan keseluruhan, dan jejak metrik kemajuan seluruh sistem.',
        'landing_supervisor_title' => 'Penyelia / Pensyarah',
        'landing_supervisor_desc' => 'Agih tarikh akhir tugasan, semak laporan kemajuan mingguan, muat naik komen, dan sahkan milestone pelajar.',
        'landing_student_title' => 'Pelajar',
        'landing_student_desc' => 'Daftar topik projek yang dipersetujui, semak tarikh akhir, muat naik log mingguan, dan hantar dokumen tesis akhir.',
        'ready_to_start' => 'Sedia untuk bermula?',
        
        // Submission Details / Remarks
        'view_remarks' => 'Semak Ulasan',
        'view_details' => 'Lihat Butiran',
        'corrections_requested' => 'Pembetulan Diperlukan',
        'corrections_requested_desc' => 'Penyelia anda telah meminta pembetulan. Sila semak ulasan di bawah dan hantar semula versi dikemas kini.',
        'assessment_decision' => 'Borang Penilaian & Pengesahan',
        'comments_timeline' => 'Garis Masa Maklum Balas',
    ]
];
