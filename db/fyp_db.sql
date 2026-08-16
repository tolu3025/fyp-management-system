-- MySQL Database Export for FYP Management System — Oduduwa University Ipetumodu
-- Database: fyp_management_system

CREATE DATABASE IF NOT EXISTS fyp_management_system;
USE fyp_management_system;

-- --------------------------------------------------------
-- 1. Table: HOD
-- --------------------------------------------------------
DROP TABLE IF EXISTS HOD;
CREATE TABLE HOD (
    No_staf VARCHAR(30) PRIMARY KEY,
    Nama VARCHAR(100) NOT NULL,
    Katalaluan VARCHAR(255) NOT NULL, -- BCrypt hashed 'password123'
    Jawatan VARCHAR(50) NOT NULL,
    Kod_aktiviti VARCHAR(30) NULL,
    Email VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed HOD Account
-- password123 -> $2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy
INSERT INTO HOD (No_staf, Nama, Katalaluan, Jawatan, Kod_aktiviti, Email) VALUES
('HOD001', 'Dr. J. A. Adedoyin', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 'Head of Department', NULL, 'hod@oduduwa.edu.ng');

-- --------------------------------------------------------
-- 2. Table: Supervisor
-- --------------------------------------------------------
DROP TABLE IF EXISTS Supervisor;
CREATE TABLE Supervisor (
    No_staf VARCHAR(30) PRIMARY KEY,
    Nama VARCHAR(100) NOT NULL,
    Katalaluan VARCHAR(255) NOT NULL, -- BCrypt hashed 'password123'
    Jawatan VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Supervisor Accounts
INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email) VALUES
('Lec001', 'Dr. Samuel Alabi', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 'Senior Lecturer', 'alabi@oduduwa.edu.ng'),
('Lec002', 'Mrs. Victoria Babalola', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 'Lecturer I', 'babalola@oduduwa.edu.ng');

-- --------------------------------------------------------
-- 3. Table: Student
-- --------------------------------------------------------
DROP TABLE IF EXISTS Student;
CREATE TABLE Student (
    No_matrik VARCHAR(30) PRIMARY KEY,
    Nama VARCHAR(100) NOT NULL,
    Katalaluan VARCHAR(255) NOT NULL, -- BCrypt hashed 'password123'
    Semester INT NOT NULL,
    Email VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Student Accounts
INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email) VALUES
('CSC/2022/001', 'Adekunle Tobi', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 8, 'adekunle@student.oduduwa.edu.ng'),
('CSC/2022/002', 'Okonkwo Blessing', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 8, 'okonkwo@student.oduduwa.edu.ng'),
('CSC/2022/003', 'Ibrahim Fatima', '$2y$10$ezTi7aX3M8VBSeyWRNsJlem/i/2XZxJ7ASA0bAu5EkHiko5pbVhJy', 8, 'ibrahim@student.oduduwa.edu.ng');

-- --------------------------------------------------------
-- 4. Table: Project
-- --------------------------------------------------------
DROP TABLE IF EXISTS Project;
CREATE TABLE Project (
    ID_projek INT AUTO_INCREMENT PRIMARY KEY,
    Tajuk_Projek VARCHAR(255) NULL,
    No_matrik VARCHAR(30) UNIQUE,
    No_staf VARCHAR(30),
    FOREIGN KEY (No_matrik) REFERENCES Student(No_matrik) ON DELETE CASCADE,
    FOREIGN KEY (No_staf) REFERENCES Supervisor(No_staf) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Assignments (HOD sets supervisor, student registers project later)
INSERT INTO Project (Tajuk_Projek, No_matrik, No_staf) VALUES
(NULL, 'CSC/2022/001', 'Lec001'),
(NULL, 'CSC/2022/002', 'Lec001'),
(NULL, 'CSC/2022/003', 'Lec002');

-- --------------------------------------------------------
-- 5. Table: Activity
-- --------------------------------------------------------
DROP TABLE IF EXISTS Activity;
CREATE TABLE Activity (
    Kod_aktiviti VARCHAR(30) PRIMARY KEY,
    Masa TIME NOT NULL,
    Tarikh DATE NOT NULL,
    Lokasi VARCHAR(100) NOT NULL,
    Jenis VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Activities (Assigned by HOD for all students)
INSERT INTO Activity (Kod_aktiviti, Masa, Tarikh, Lokasi, Jenis) VALUES
('ACT001', '10:00:00', '2026-09-01', 'Main Auditorium', 'FYP Orientation & Synopsis Guidelines'),
('ACT002', '12:30:00', '2026-10-15', 'Lab A (Computer Science)', 'FYP Interim Presentation & Progress Check'),
('ACT003', '09:00:00', '2026-12-05', 'Conference Room', 'Final FYP Viva & Report Submission');

-- --------------------------------------------------------
-- 6. Table: Task
-- --------------------------------------------------------
DROP TABLE IF EXISTS Task;
CREATE TABLE Task (
    ID_tugasan INT AUTO_INCREMENT PRIMARY KEY,
    Jenis VARCHAR(255) NOT NULL,
    Ulasan TEXT NULL,
    Pengesahan VARCHAR(50) DEFAULT 'Belum Disahkan', -- 'Belum Disahkan', 'Disahkan', 'Hantar Semula'
    Tarikh DATE NOT NULL,
    Deadline DATE NOT NULL,
    No_matrik VARCHAR(30),
    No_staf VARCHAR(30),
    ID_projek INT,
    FOREIGN KEY (No_matrik) REFERENCES Student(No_matrik) ON DELETE CASCADE,
    FOREIGN KEY (No_staf) REFERENCES Supervisor(No_staf) ON DELETE CASCADE,
    FOREIGN KEY (ID_projek) REFERENCES Project(ID_projek) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Table: Submissions
-- --------------------------------------------------------
DROP TABLE IF EXISTS Submissions;
CREATE TABLE Submissions (
    ID_hantaran INT AUTO_INCREMENT PRIMARY KEY,
    ID_projek INT NOT NULL,
    No_matrik VARCHAR(30) NOT NULL,
    ID_tugasan INT NULL,
    Jenis_Hantaran VARCHAR(50) NOT NULL, -- 'weekly', 'task', 'final'
    Tajuk VARCHAR(255) NOT NULL,
    Kandungan TEXT NULL,
    File_Path VARCHAR(255) NULL,
    Tarikh_Hantar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status VARCHAR(50) DEFAULT 'Menunggu Semakan', -- 'Menunggu Semakan', 'Disemak', 'Hantar Semula'
    FOREIGN KEY (ID_projek) REFERENCES Project(ID_projek) ON DELETE CASCADE,
    FOREIGN KEY (No_matrik) REFERENCES Student(No_matrik) ON DELETE CASCADE,
    FOREIGN KEY (ID_tugasan) REFERENCES Task(ID_tugasan) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Table: Comments
-- --------------------------------------------------------
DROP TABLE IF EXISTS Comments;
CREATE TABLE Comments (
    ID_ulasan INT AUTO_INCREMENT PRIMARY KEY,
    ID_hantaran INT NOT NULL,
    Pengulas_ID VARCHAR(30) NOT NULL,
    Peranan_Pengulas VARCHAR(20) NOT NULL, -- 'HOD', 'Supervisor'
    Ulasan TEXT NOT NULL,
    Tarikh_Ulasan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_hantaran) REFERENCES Submissions(ID_hantaran) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. Table: Notifications
-- --------------------------------------------------------
DROP TABLE IF EXISTS Notifications;
CREATE TABLE Notifications (
    ID_notifikasi INT AUTO_INCREMENT PRIMARY KEY,
    Penerima_ID VARCHAR(30) NOT NULL, -- No_matrik or No_staf
    Mesej TEXT NOT NULL,
    Status_Baca TINYINT(1) DEFAULT 0, -- 0 = Unread, 1 = Read
    Tarikh_Cipta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
