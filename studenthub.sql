-- ============================================================================
-- StudentHub Portal - Complete Normalized Database Schema & Seed Data
-- Subject: Web Development Frameworks (ITUE203)
-- Core Production Schema: User Management, Roles & Authentication,
--         Student Records Directory, Dynamic Campus Events,
--         Seat Registrations, Security Audit Trail & Persistent Sessions
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub`;

-- ----------------------------------------------------------------------------
-- 1. Table: users
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: students
-- ----------------------------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `student_id` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `semester` INT NOT NULL DEFAULT 3,
  `cgpa` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `status` ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: events
-- ----------------------------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NOT NULL,
  `venue` VARCHAR(100) NOT NULL,
  `fee` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `max_seats` INT NOT NULL DEFAULT 100,
  `registered_seats` INT NOT NULL DEFAULT 0,
  `poster` VARCHAR(255) DEFAULT 'default_event.jpg',
  `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
  `description` TEXT NOT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: registrations (Event Registrations)
-- ----------------------------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `student_email` VARCHAR(100) NOT NULL,
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('confirmed', 'pending', 'cancelled') NOT NULL DEFAULT 'confirmed',
  `notes` TEXT NULL,
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table: audit_logs (System Activity & Security Audit Trail)
-- ----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `username` VARCHAR(50) DEFAULT 'System/Guest',
  `role` VARCHAR(20) DEFAULT 'guest',
  `action` VARCHAR(100) NOT NULL,
  `table_affected` VARCHAR(50) DEFAULT 'general',
  `record_id` INT NULL,
  `details` TEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table: remember_tokens (Persistent Remember-Me Authentication)
-- ----------------------------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- Passwords:
-- admin@studenthub.com   -> admin123  ($2y$10$0KKnEWGsSYY.F37lczCZAucX9AqGGUp/574G/yjbJAmIkJtBO.vvu)
-- student@studenthub.com -> student123 ($2y$10$WlUYfvKyID.ss4Zim64VB.ISI6uDldTGyJO8mVRBqalcMJoRtDhay)
-- ============================================================================

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@studenthub.com', '$2y$10$0KKnEWGsSYY.F37lczCZAucX9AqGGUp/574G/yjbJAmIkJtBO.vvu', 'admin', 'active', NOW(), NOW()),
(2, 'dhanushi', 'student@studenthub.com', '$2y$10$WlUYfvKyID.ss4Zim64VB.ISI6uDldTGyJO8mVRBqalcMJoRtDhay', 'student', 'active', NOW(), NOW()),
(3, 'aarav_sharma', 'aarav.sharma@charusat.edu.in', '$2y$10$WlUYfvKyID.ss4Zim64VB.ISI6uDldTGyJO8mVRBqalcMJoRtDhay', 'student', 'active', NULL, NOW()),
(4, 'priya_patel', 'priya.patel@charusat.edu.in', '$2y$10$WlUYfvKyID.ss4Zim64VB.ISI6uDldTGyJO8mVRBqalcMJoRtDhay', 'student', 'active', NULL, NOW());

-- Seed 20+ Students (Directory Roster)
INSERT INTO `students` (`id`, `user_id`, `student_id`, `full_name`, `email`, `phone`, `course`, `semester`, `cgpa`, `gender`, `status`) VALUES
(1, 2, '25CS024', 'Dhanushi Patel', 'student@studenthub.com', '9876543210', 'Computer Science & Engineering', 3, 9.45, 'Female', 'active'),
(2, 3, '25CS001', 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', '9823456781', 'Computer Science & Engineering', 3, 8.85, 'Male', 'active'),
(3, 4, '25CS002', 'Priya Patel', 'priya.patel@charusat.edu.in', '9712345672', 'Computer Science & Engineering', 3, 9.15, 'Female', 'active'),
(4, NULL, '25IT015', 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', '9632587410', 'Information Technology', 3, 8.40, 'Male', 'active'),
(5, NULL, '25CE033', 'Ananya Desai', 'ananya.desai@charusat.edu.in', '9874563211', 'Computer Engineering', 3, 9.02, 'Female', 'active'),
(6, NULL, '25AIML08', 'Kavya Shah', 'kavya.shah@charusat.edu.in', '9512345678', 'AI & Machine Learning', 3, 9.60, 'Female', 'active'),
(7, NULL, '25CS045', 'Devansh Joshi', 'devansh.joshi@charusat.edu.in', '9425631478', 'Computer Science & Engineering', 3, 7.95, 'Male', 'active'),
(8, NULL, '25IT042', 'Isha Varma', 'isha.varma@charusat.edu.in', '9871234560', 'Information Technology', 3, 8.70, 'Female', 'active'),
(9, NULL, '25CE019', 'Siddharth Dave', 'siddharth.dave@charusat.edu.in', '9631478520', 'Computer Engineering', 3, 8.35, 'Male', 'active'),
(10, NULL, '25AIML12', 'Tanvi Trivedi', 'tanvi.trivedi@charusat.edu.in', '9517534562', 'AI & Machine Learning', 3, 9.25, 'Female', 'active'),
(11, NULL, '24CS050', 'Harsh Solanki', 'harsh.solanki@charusat.edu.in', '9784561230', 'Computer Science & Engineering', 5, 8.10, 'Male', 'active'),
(12, NULL, '24IT028', 'Bhavya Choksi', 'bhavya.choksi@charusat.edu.in', '9845123670', 'Information Technology', 5, 8.90, 'Male', 'active'),
(13, NULL, '24CE064', 'Diya Panchal', 'diya.panchal@charusat.edu.in', '9612347890', 'Computer Engineering', 5, 9.30, 'Female', 'active'),
(14, NULL, '25CS088', 'Yash Parmar', 'yash.parmar@charusat.edu.in', '9547896321', 'Computer Science & Engineering', 3, 7.80, 'Male', 'active'),
(15, NULL, '25IT060', 'Rhea Soni', 'rhea.soni@charusat.edu.in', '9876501234', 'Information Technology', 3, 8.65, 'Female', 'active'),
(16, NULL, '25AIML20', 'Manav Shah', 'manav.shah@charusat.edu.in', '9421587632', 'AI & Machine Learning', 3, 8.50, 'Male', 'active'),
(17, NULL, '25CE075', 'Ritu Rajput', 'ritu.rajput@charusat.edu.in', '9753124680', 'Computer Engineering', 3, 8.95, 'Female', 'active'),
(18, NULL, '25CS102', 'Neel Bhatt', 'neel.bhatt@charusat.edu.in', '9632145789', 'Computer Science & Engineering', 3, 8.20, 'Male', 'active'),
(19, NULL, '24CS110', 'Krisha Patel', 'krisha.patel@charusat.edu.in', '9514785236', 'Computer Science & Engineering', 5, 9.05, 'Female', 'active'),
(20, NULL, '25IT082', 'Sahil Rana', 'sahil.rana@charusat.edu.in', '9826451379', 'Information Technology', 3, 7.65, 'Male', 'active'),
(21, NULL, '25AIML29', 'Vidhi Modi', 'vidhi.modi@charusat.edu.in', '9718456230', 'AI & Machine Learning', 3, 9.40, 'Female', 'active');

-- Seed 16+ Campus Events (University Calendar)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `event_time`, `venue`, `fee`, `max_seats`, `registered_seats`, `poster`, `status`, `description`, `created_by`) VALUES
(1, 'CodeSprint Hackathon 2026', 'Technical', '2026-11-15', '09:00:00', 'Computer Lab 3, CSPIT', 0.00, 100, 42, 'hackathon.jpg', 'upcoming', 'A 24-hour national level coding hackathon focusing on AI and modern web technologies.', 1),
(2, 'GenAI & Agentic Systems Workshop', 'Workshop', '2026-11-20', '10:30:00', 'Auditorium Hall B', 150.00, 60, 58, 'ai_workshop.jpg', 'upcoming', 'Hands-on masterclass on building LLM agents with Google Gemini API and tool calling.', 1),
(3, 'Spandan Annual Cultural Festival', 'Cultural', '2026-12-05', '17:00:00', 'Central University Grounds', 0.00, 500, 310, 'cultural_fest.jpg', 'upcoming', 'Flagship cultural extravaganza with dance competitions, drama, music band battles and fashion shows.', 1),
(4, 'Inter-College Football Championship', 'Sports', '2026-11-28', '08:00:00', 'CHARUSAT Sports Complex', 50.00, 120, 96, 'football.jpg', 'upcoming', 'Annual 7-a-side football tournament for engineering and pharmacy faculties.', 1),
(5, 'Cybersecurity & Ethical Hacking Symposium', 'Seminar', '2026-12-12', '11:00:00', 'Seminar Hall 1', 100.00, 80, 45, 'cyber_sec.jpg', 'upcoming', 'Industry veterans sharing actionable insights on cloud defense, penetration testing, and zero trust.', 1),
(6, 'Full-Stack Web Dev Bootcamp with PHP & MySQL', 'Technical', '2026-11-25', '14:00:00', 'IT Department Lab 4', 0.00, 75, 62, 'web_dev.jpg', 'upcoming', 'Intensive hands-on workshop covering secure PHP sessions, AJAX CRUD, and prepared statements.', 1),
(7, 'Robotics & IoT RoboWars', 'Technical', '2026-12-18', '10:00:00', 'Mechanical Workshop Bay', 200.00, 50, 32, 'robowars.jpg', 'upcoming', 'Combat robotics showcase where autonomous and RC combat bots clash in bulletproof arena.', 1),
(8, 'Campus Placement Preparation Summit', 'Seminar', '2026-11-10', '15:00:00', 'Main Auditorium', 0.00, 250, 210, 'placement.jpg', 'upcoming', 'HR leaders and alumni discussing interview strategies, DSA problem solving, and resume building.', 1),
(9, 'Battle of the Bands - Acoustic Night', 'Cultural', '2026-12-08', '18:30:00', 'Open Air Theatre (OAT)', 0.00, 300, 185, 'music.jpg', 'upcoming', 'Electrifying musical showdown featuring top college rock and acoustic bands across the state.', 1),
(10, 'Cloud Native Architecture with Google Cloud', 'Workshop', '2026-12-22', '09:30:00', 'Lab 5, DEPSTAR', 120.00, 50, 40, 'cloud_gcp.jpg', 'upcoming', 'Deep dive into microservices, containerization with Docker, Kubernetes, and Cloud Run.', 1),
(11, 'Chess Masters Trophy 2026', 'Sports', '2026-11-18', '10:00:00', 'Student Activity Center', 0.00, 64, 48, 'chess.jpg', 'upcoming', 'FIDE rules rapid chess championship for all undergraduate and postgraduate students.', 1),
(12, 'Mobile App Dev with Flutter & Dart', 'Technical', '2026-12-14', '13:00:00', 'Computer Lab 2', 100.00, 60, 50, 'flutter.jpg', 'upcoming', 'Build cross-platform iOS and Android applications with state management and Firebase backend.', 1),
(13, 'Photography & Short Film Exhibition', 'Cultural', '2026-12-02', '11:00:00', 'Art Gallery, Humanities Bldg', 0.00, 150, 70, 'photography.jpg', 'upcoming', 'Showcasing the best street, wildlife, and portrait photography captured by student lensmen.', 1),
(14, 'Table Tennis Inter-Department Tournament', 'Sports', '2026-11-22', '16:00:00', 'Indoor Sports Hall', 0.00, 32, 28, 'table_tennis.jpg', 'upcoming', 'Men and Women singles knockout table tennis championship with exciting prize pool.', 1),
(15, 'Women in Tech & Leadership Panel', 'Seminar', '2026-12-01', '14:30:00', 'Seminar Hall 2', 0.00, 100, 85, 'women_tech.jpg', 'upcoming', 'Inspiring talk with female engineering alumni working at leading tech enterprises.', 1),
(16, 'Game Development Jam with Unity', 'Technical', '2026-12-28', '10:00:00', 'Graphics Lab, CSPIT', 150.00, 40, 35, 'game_jam.jpg', 'upcoming', '48-hour challenge to design, script, and publish 2D and 3D indie games using Unity C#.', 1);

-- Seed Registrations
INSERT INTO `registrations` (`id`, `event_id`, `student_id`, `student_name`, `student_email`, `registration_date`, `status`, `notes`) VALUES
(1, 1, 1, 'Dhanushi Patel', 'student@studenthub.com', NOW() - INTERVAL 5 DAY, 'confirmed', 'Team Leader for Web Sprint track'),
(2, 2, 1, 'Dhanushi Patel', 'student@studenthub.com', NOW() - INTERVAL 4 DAY, 'confirmed', 'Interested in tool calling with Gemini'),
(3, 1, 2, 'Aarav Sharma', 'aarav.sharma@charusat.edu.in', NOW() - INTERVAL 3 DAY, 'confirmed', 'Algorithms track participant'),
(4, 3, 3, 'Priya Patel', 'priya.patel@charusat.edu.in', NOW() - INTERVAL 2 DAY, 'confirmed', 'Classical dance solo performance'),
(5, 6, 4, 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', NOW() - INTERVAL 1 DAY, 'confirmed', 'PHP MySQL enthusiast'),
(6, 2, 5, 'Ananya Desai', 'ananya.desai@charusat.edu.in', NOW() - INTERVAL 12 HOUR, 'confirmed', 'AI Workshop attendee');

-- Seed Audit Logs (Security Audit Trail)
INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `role`, `action`, `table_affected`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin', 'admin', 'SYSTEM_INITIALIZATION', 'all', 1, 'StudentHub database schema deployed and initialized with seed data', '127.0.0.1', NOW() - INTERVAL 7 DAY),
(2, 1, 'admin', 'admin', 'USER_LOGIN', 'users', 1, 'Admin logged into portal from local subnet', '127.0.0.1', NOW() - INTERVAL 2 DAY),
(3, 1, 'admin', 'admin', 'EVENT_CREATE', 'events', 1, 'Created new event: CodeSprint Hackathon 2026', '127.0.0.1', NOW() - INTERVAL 2 DAY),
(4, 2, 'dhanushi', 'student', 'EVENT_REGISTER', 'registrations', 1, 'Registered for event: CodeSprint Hackathon 2026', '127.0.0.1', NOW() - INTERVAL 1 DAY),
(5, 1, 'admin', 'admin', 'STUDENT_UPDATE', 'students', 1, 'Updated student profile and semester status', '127.0.0.1', NOW() - INTERVAL 3 HOUR);
