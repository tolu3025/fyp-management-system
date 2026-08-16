# FYP Management System — Oduduwa University Ipetumodu

Welcome to the Final Year Project (FYP) Management System built for the Department of Computer Science, Ramon Adedoyin College of Natural and Applied Sciences, Oduduwa University Ipetumodu.

This system provides role-based access for the Head of Department (HOD), Supervisors (Lecturers), and Students to manage, monitor, and submit FYP deliverables.

---

## 🛠️ Technology Stack
- **Backend:** PHP (PDO, OOP principles)
- **Frontend:** HTML5, Vanilla CSS, Vanilla JavaScript (Premium visual styles, Inter typography, slide animations)
- **Database:** MySQL
- **Environment:** XAMPP / WAMP (Apache + PHP + MySQL)

---

## 🚀 Setup & Installation Instructions

Follow these steps to run the project locally on your machine using **XAMPP**:

### Step 1: Clone or Copy Project Files
Place the complete `fyp_management_system` directory inside your XAMPP's document root directory, usually:
`C:\xampp\htdocs\fyp_management_system\`

### Step 2: Configure and Start MySQL/Apache
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and **MySQL** modules.

### Step 3: Create and Import MySQL Database
1. Open your browser and navigate to **phpMyAdmin**: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click on the **Databases** tab.
3. Create a new database named exactly: `fyp_management_system`
4. Select the newly created database, click on the **Import** tab.
5. Click **Choose File** and locate the database export file: `db/fyp_db.sql`
6. Click **Import** (or **Go** depending on your phpMyAdmin version) at the bottom.

### Step 4: Verify Database Settings
The database settings are located in `config/db.php`. By default, it is configured for XAMPP:
- Host: `127.0.0.1`
- Database: `fyp_management_system`
- User: `root`
- Password: `""` (empty password)

If your database server has a different root password, modify the password field in [config/db.php](file:///C:/Users/Lenovo%20ThinkBook/.gemini/antigravity-ide/scratch/fyp_management_system/config/db.php) accordingly.

### Step 5: Launch the Application
Navigate to the following URL in Google Chrome:
[http://localhost/fyp_management_system/index.php](http://localhost/fyp_management_system/index.php)

---

## 🔑 Default Login Credentials (Pre-seeded Sample Data)

Use these credentials to log in and test different user roles:

| Role | Username / ID | Password | Email |
| :--- | :--- | :--- | :--- |
| **Head of Department (HOD)** | `HOD001` | `password123` | `hod@oduduwa.edu.ng` |
| **Supervisor (Lecturer 1)** | `Lec001` | `password123` | `alabi@oduduwa.edu.ng` |
| **Supervisor (Lecturer 2)** | `Lec002` | `password123` | `babalola@oduduwa.edu.ng` |
| **Student (Student 1)** | `CSC/2022/001` | `password123` | `adekunle@student.oduduwa.edu.ng` |
| **Student (Student 2)** | `CSC/2022/002` | `password123` | `okonkwo@student.oduduwa.edu.ng` |
| **Student (Student 3)** | `CSC/2022/003` | `password123` | `ibrahim@student.oduduwa.edu.ng` |

---

## 🔔 Testing Automated Email Notifications locally
Since local development environments (XAMPP/WAMP) do not usually have active SMTP servers configured, we have built an **Email Logging System**. 

- Outgoing emails triggered by student submissions or supervisor comments are logged to a file: [logs/emails.log](file:///C:/Users/Lenovo%20ThinkBook/.gemini/antigravity-ide/scratch/fyp_management_system/logs/emails.log).
- You can inspect these emails in real-time by expanding the **🛠️ Developer Email Logs Console** located at the bottom (footer drawer) of any page after logging in.
- This allows full system and unit verification of notification scenarios without SMTP setup!
