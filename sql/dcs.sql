-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 18, 2025 at 09:40 AM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u958767601_dcs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id_admin` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `pass_admin` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `role_admin` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname_admin` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `firstname_admin` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id_admin`, `pass_admin`, `role_admin`, `lastname_admin`, `firstname_admin`) VALUES
('80062165', '1234', 'Governor', 'Orais', 'Harrish'),
('110011', 'Haru_CM', 'Class Mayor', 'Orais', 'Harrish'),
('01092101', 'TheBestDean', 'Dean', 'Siega', 'Riza Lynn');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE IF NOT EXISTS `attendances` (
  `id_attendance` int NOT NULL AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `type_attendance` varchar(999) COLLATE utf8mb4_general_ci NOT NULL,
  `attendance_status` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `Penalty_type` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `Penalty_requirements` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id_attendance`),
  KEY `fk_event` (`id_event`)
) ENGINE=MyISAM AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id_attendance`, `id_event`, `type_attendance`, `attendance_status`, `Penalty_type`, `Penalty_requirements`, `start_time`, `end_time`) VALUES
(199, 176, 'IN', 'Ended', 'Community Service', '2 hours', '14:03:00', '15:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id_event` int NOT NULL AUTO_INCREMENT,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `name_event` varchar(999) COLLATE utf8mb4_general_ci NOT NULL,
  `date_event` date NOT NULL,
  `event_start_time` time NOT NULL,
  `event_end_time` time NOT NULL,
  `event_desc` varchar(999) COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(99) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_event`)
) ENGINE=MyISAM AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id_event`, `semester_ID`, `name_event`, `date_event`, `event_start_time`, `event_end_time`, `event_desc`, `date_created`, `created_by`) VALUES
(176, 'AY2025-2026-1stsemester', 'CCS ORIENTATION', '2025-05-11', '13:30:00', '14:30:00', 'test', '2025-05-18 13:30:43', '80062165');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int NOT NULL AUTO_INCREMENT,
  `id_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `feedback_type` enum('bug','feature','improvement','other') COLLATE utf8mb4_general_ci NOT NULL,
  `feedback_priority` enum('low','medium','high','critical') COLLATE utf8mb4_general_ci NOT NULL,
  `feedback_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `feedback_description` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `id_student` (`id_student`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `id_student`, `feedback_type`, `feedback_priority`, `feedback_title`, `feedback_description`, `status`, `created_at`, `updated_at`) VALUES
(15, '80062165', 'bug', 'low', 'test', 'test', 'pending', '2025-05-18 07:17:20', '2025-05-18 15:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_attachments`
--

DROP TABLE IF EXISTS `feedback_attachments`;
CREATE TABLE IF NOT EXISTS `feedback_attachments` (
  `attachment_id` int NOT NULL AUTO_INCREMENT,
  `feedback_id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_at` datetime NOT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `feedback_id` (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_counter`
--

DROP TABLE IF EXISTS `page_counter`;
CREATE TABLE IF NOT EXISTS `page_counter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_url` varchar(191) DEFAULT NULL,
  `visit_count` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_url` (`page_url`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `page_counter`
--

INSERT INTO `page_counter` (`id`, `page_url`, `visit_count`) VALUES
(1, '/Department_web/index.php', 123),
(2, '/Department_web/', 131),
(3, '/Department_web/?content=log-in', 8),
(4, '/Department_web/index.php?content=admin-index', 35),
(5, '/Department_web/index.php?content=admin-index&admin=&semester=AY2024-2025-summer', 2),
(6, '/Department_web//php/logout.php', 239),
(7, '/Department_web/index.php?content=admin-index&admin=&semester=AY2025-2026-1stsemester', 10),
(8, '/Department_web/index.php?content=admin-index&admin=student-management', 205),
(9, '/Department_web/index.php?content=admin-index&admin=dashboard', 51),
(10, '/Department_web/index.php?content=admin-index&admin=financial-statement', 114),
(11, '/Department_web/index.php?content=admin-index&admin=dashboard&semester=AY2025-2026-1stsemester', 8),
(12, '/Department_web/index.php?content=admin-index&admin=dashboard&semester=AY2024-2025-summer', 5),
(13, '/Department_web/index.php?content=admin-index&admin=admin-management', 32),
(14, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events', 270),
(15, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-fees', 191),
(16, '/Department_web/index.php?content=admin-index&admin=student-management&semester=AY2024-2025-summer', 4),
(17, '/Department_web/index.php?content=admin-index&admin=student-management&semester=AY2025-2026-1stsemester', 66),
(18, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+deleted+successfully.', 2),
(19, '/Department_web/index.php?content=admin-index&admin=ay-dashboard', 73),
(20, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=AY2024-2025-summer', 1),
(21, '/Department_web/index.php?content=log-out', 21),
(22, '/Department_web/index.php?content=log-in', 27),
(23, '/Department_web/index.php?content=admin-index&admin=&semester=', 4),
(24, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=AY2025-2026-1stsemester', 1),
(25, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&semester=', 8),
(26, '/Department_web/index.php?content=admin-index&admin=student-management&semester=AY2025-2026-2ndsemester', 13),
(27, '/Department_web/php/admin/update-attendance.php', 2),
(28, '/Department_web/index.php?content=student-index', 9),
(29, '/Department_web/stylesheet/admin/admin-index.css', 193),
(30, '/Department_web/index.php?content=student-index&student=student-dashboard', 13),
(31, '/Department_web/index.php?content=student-index&student=student-events', 13),
(32, '/Department_web/index.php?content=student-index&admin=dashboard&semester=', 1),
(33, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=AY2025-2026-2ndsemester', 2),
(34, '/Department_web/index.php?content=admin-index&admin=&semester=AY2025-2026-2ndsemester', 1),
(35, '/Department_web/index.php?content=admin-index&admin=dashboard&semester=AY2026-2027-2ndsemester', 2),
(36, '/Department_web/index.php?content=admin-index&admin=student-management&semester=AY2026-2027-2ndsemester', 16),
(37, '/Department_web/index.php?content=student-index&student=student-fees', 35),
(38, '/Department_web/index.php?content=student-index&student=student-qrcode', 17),
(39, '/Department_web/index.php?content=efms-scanner-index', 4),
(40, '/Department_web/index.php?content=efms-scanner-app&attendance_id=184', 1),
(41, '/Department_web/php/EFMS-scanner/style.css', 1),
(42, '/Department_web/index.php?content=admin-index&admin=admin-management&edit_id=80062165', 4),
(43, '/Department_web/index.php?content=admin-index&admin=student-management&semester=', 1),
(44, '/Department_web/index.php?content=admin-index&admin=&semester=AY2026-2027-2ndsemester', 1),
(45, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+deleted+successfully', 13),
(46, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+record+added+successfully', 72),
(47, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+record+updated+successfully', 22),
(48, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+deleted+successfully.', 8),
(49, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+created+successfully', 3),
(50, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+deleted+successfully', 2),
(51, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Attendance+and+all+related+records+deleted+successfully', 15),
(52, '/Department_web/index.php?content=admin-index&admin=event-management&admin_events=admin-events&status=success&message=Event+and+all+related+records+deleted+successfully', 1),
(53, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=2025-3704', 1),
(54, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=2025-1129', 1),
(55, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&delete_id=AY2027-2028-1stsemester', 1),
(56, '/Department_web/index.php?content=default', 32),
(57, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&semester=AY2026-2027-2ndsemester', 2),
(58, '/Department_web/index.php?content=admin-index&admin=ay-dashboard&semester=AY2025-2026-1stsemester', 3),
(59, '/Department_web/index.php?content=student-index&student=student-feedback', 56),
(60, '/Department_web/index.php?content=admin-index&admin=admin-feedback', 45),
(61, '/Department_web///stylesheet/admin/admin-feedback.css', 25),
(62, '/Department_web/school-logo.png', 6),
(63, '/Department_web/ccs-logo.png', 6),
(64, '/Department_web/efms-logo.png', 6),
(65, '/Department_web/images/school-logo.png', 2),
(66, '/Department_web/images/ccs-logo.png', 2),
(67, '/Department_web/images/efms-logo.png', 2),
(68, '/Department_web/assets/images/devs/harrish.jpg', 1),
(69, '/Department_web/assets/images/devs/angelica.jpg', 34),
(70, '/Department_web/assets/images/devs/janine.jpg', 3),
(71, '/Department_web/assets/images/devs/christian.jpg', 4),
(72, '/Department_web/assets/images/devs/ian.jpg', 42),
(73, '/Department_web/assets/images/devs/reyann.jpg', 10),
(74, '/Department_web/assets/images/devs/dave.jpg', 29),
(75, '/Department_web/assets/images/devs/Tangc', 3),
(76, '/Department_web/logo1.png', 4),
(77, '/Department_web/logo2.png', 4),
(78, '/Department_web/logo3.png', 4),
(79, '/Department_web/logo4.png', 3),
(80, '/Department_web/logo5.png', 3),
(81, '/Department_web/logo6.png', 2);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id_payment` int NOT NULL AUTO_INCREMENT,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `date_payment` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_payment`)
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id_payment`, `semester_ID`, `payment_name`, `payment_amount`, `date_payment`) VALUES
(148, 'AY2025-2026-1stsemester', 'Departmental T-shirt', 450.00, '2025-05-18 06:00:33');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

DROP TABLE IF EXISTS `semester`;
CREATE TABLE IF NOT EXISTS `semester` (
  `semester_ID` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'inactive',
  PRIMARY KEY (`semester_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_ID`, `academic_year`, `semester_type`, `date_created`, `status`) VALUES
('AY2025-2026-1stsemester', '2025-2026', '1st Semester', '2025-04-28 10:39:00', 'active'),
('AY2026-2027-2ndsemester', '2026-2027', '2nd Semester', '2025-04-28 10:51:37', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `id_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `pass_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `firstname_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `role_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `year_student` int NOT NULL,
  UNIQUE KEY `id_student` (`id_student`,`semester_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id_student`, `semester_ID`, `pass_student`, `lastname_student`, `firstname_student`, `role_student`, `year_student`) VALUES
('80080195', 'AY2025-2026-1stsemester', '80080195', 'Jaula', 'Janine', 'Student', 4),
('80060270', 'AY2025-2026-1stsemester', '80060270', 'Golpe', 'Dave Brian', 'Student', 4),
('80060100', 'AY2025-2026-1stsemester', '80060100', 'Ringcodo', 'Angelica', 'Student', 4),
('80010715', 'AY2025-2026-1stsemester', '80010715', 'Costillas', 'Christian', 'Student', 4),
('80062165', 'AY2025-2026-1stsemester', '80062165', 'Orais', 'Harrish', 'Student', 4),
('202310100', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231099', 'AY2025-2026-1stsemester', 'Pa$$w0rd99', 'Smith', 'Olivia', 'Student', 3),
('20231097', 'AY2025-2026-1stsemester', 'Pa$$w0rd97', 'Rodriguez', 'Daniel', 'Student', 1),
('20231098', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231096', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231094', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231095', 'AY2025-2026-1stsemester', 'Pa$$w0rd95', 'Ramos', 'Carla', 'Student', 3),
('20231092', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231093', 'AY2025-2026-1stsemester', 'Pa$$w0rd93', 'Reyes', 'Maria', 'Student', 4),
('20231090', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231091', 'AY2025-2026-1stsemester', 'Pa$$w0rd91', 'Dela Cruz', 'Juan', 'Student', 1),
('20231089', 'AY2025-2026-1stsemester', 'Pa$$w0rd89', 'Smith', 'Olivia', 'Student', 3),
('20231088', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231087', 'AY2025-2026-1stsemester', 'Pa$$w0rd87', 'Rodriguez', 'Daniel', 'Student', 1),
('20231085', 'AY2025-2026-1stsemester', 'Pa$$w0rd85', 'Ramos', 'Carla', 'Student', 3),
('20231086', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231084', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231083', 'AY2025-2026-1stsemester', 'Pa$$w0rd83', 'Reyes', 'Maria', 'Student', 4),
('20231082', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231080', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231081', 'AY2025-2026-1stsemester', 'Pa$$w0rd81', 'Dela Cruz', 'Juan', 'Student', 1),
('20231079', 'AY2025-2026-1stsemester', 'Pa$$w0rd79', 'Smith', 'Olivia', 'Student', 3),
('20231078', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231077', 'AY2025-2026-1stsemester', 'Pa$$w0rd77', 'Rodriguez', 'Daniel', 'Student', 1),
('20231076', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231075', 'AY2025-2026-1stsemester', 'Pa$$w0rd75', 'Ramos', 'Carla', 'Student', 3),
('20231074', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231073', 'AY2025-2026-1stsemester', 'Pa$$w0rd73', 'Reyes', 'Maria', 'Student', 4),
('20231072', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231071', 'AY2025-2026-1stsemester', 'Pa$$w0rd71', 'Dela Cruz', 'Juan', 'Student', 1),
('20231070', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231068', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231069', 'AY2025-2026-1stsemester', 'Pa$$w0rd69', 'Smith', 'Olivia', 'Student', 3),
('20231067', 'AY2025-2026-1stsemester', 'Pa$$w0rd67', 'Rodriguez', 'Daniel', 'Student', 1),
('20231066', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231065', 'AY2025-2026-1stsemester', 'Pa$$w0rd65', 'Ramos', 'Carla', 'Student', 3),
('20231064', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231063', 'AY2025-2026-1stsemester', 'Pa$$w0rd63', 'Reyes', 'Maria', 'Student', 4),
('20231062', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231061', 'AY2025-2026-1stsemester', 'Pa$$w0rd61', 'Dela Cruz', 'Juan', 'Student', 1),
('20231059', 'AY2025-2026-1stsemester', 'Pa$$w0rd59', 'Smith', 'Olivia', 'Student', 3),
('20231057', 'AY2025-2026-1stsemester', 'Pa$$w0rd57', 'Rodriguez', 'Daniel', 'Student', 1),
('20231058', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231056', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231054', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231055', 'AY2025-2026-1stsemester', 'Pa$$w0rd55', 'Ramos', 'Carla', 'Student', 3),
('20231053', 'AY2025-2026-1stsemester', 'Pa$$w0rd53', 'Reyes', 'Maria', 'Student', 4),
('20231052', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231051', 'AY2025-2026-1stsemester', 'Pa$$w0rd51', 'Dela Cruz', 'Juan', 'Student', 1),
('20231050', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231049', 'AY2025-2026-1stsemester', 'Pa$$w0rd49', 'Smith', 'Olivia', 'Student', 3),
('20231048', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231047', 'AY2025-2026-1stsemester', 'Pa$$w0rd47', 'Rodriguez', 'Daniel', 'Student', 1),
('20231045', 'AY2025-2026-1stsemester', 'Pa$$w0rd45', 'Ramos', 'Carla', 'Student', 3),
('20231046', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231044', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231043', 'AY2025-2026-1stsemester', 'Pa$$w0rd43', 'Reyes', 'Maria', 'Student', 4),
('20231042', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231041', 'AY2025-2026-1stsemester', 'Pa$$w0rd41', 'Dela Cruz', 'Juan', 'Student', 1),
('20231039', 'AY2025-2026-1stsemester', 'Pa$$w0rd39', 'Smith', 'Olivia', 'Student', 3),
('20231038', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231037', 'AY2025-2026-1stsemester', 'Pa$$w0rd37', 'Rodriguez', 'Daniel', 'Student', 1),
('20231036', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231035', 'AY2025-2026-1stsemester', 'Pa$$w0rd35', 'Ramos', 'Carla', 'Student', 3),
('20231034', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231033', 'AY2025-2026-1stsemester', 'Pa$$w0rd33', 'Reyes', 'Maria', 'Student', 4),
('20231032', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2),
('20231031', 'AY2025-2026-1stsemester', 'Pa$$w0rd31', 'Dela Cruz', 'Juan', 'Student', 1),
('20231030', 'AY2025-2026-1stsemester', 'password123', 'Brown', 'Matthew', 'Student', 2),
('20231029', 'AY2025-2026-1stsemester', 'Pa$$w0rd29', 'Smith', 'Olivia', 'Student', 3),
('20231028', 'AY2025-2026-1stsemester', 'password123', 'Jones', 'Emma', 'Student', 4),
('20231027', 'AY2025-2026-1stsemester', 'Pa$$w0rd27', 'Rodriguez', 'Daniel', 'Student', 1),
('20231026', 'AY2025-2026-1stsemester', 'password123', 'Johnson', 'Sophia', 'Student', 3),
('20231024', 'AY2025-2026-1stsemester', 'Pa$$w0rd19', 'Smith', 'Sophia', 'Student', 2),
('20231025', 'AY2025-2026-1stsemester', 'Pa$$w0rd20', 'Jones', 'Ethan', 'Student', 4),
('20231023', 'AY2025-2026-1stsemester', 'Pa$$w0rd18', 'Williams', 'Daniel', 'Student', 4),
('20231022', 'AY2025-2026-1stsemester', 'Pa$$w0rd17', 'Johnson', 'Emma', 'Student', 1),
('20231021', 'AY2025-2026-1stsemester', 'Pa$$w0rd16', 'Martinez', 'Michael', 'Student', 3),
('20231020', 'AY2025-2026-1stsemester', 'Pa$$w0rd15', 'Garcia', 'Olivia', 'Student', 2),
('20231019', 'AY2025-2026-1stsemester', 'Pa$$w0rd14', 'Rodriguez', 'Emily', 'Student', 1),
('20231018', 'AY2025-2026-1stsemester', 'Pa$$w0rd13', 'Brown', 'Matthew', 'Student', 4),
('20231017', 'AY2025-2026-1stsemester', 'Pa$$w0rd12', 'Davis', 'Sophia', 'Student', 2),
('20231016', 'AY2025-2026-1stsemester', 'Pa$$w0rd11', 'Smith', 'Ava', 'Student', 3),
('20231015', 'AY2025-2026-1stsemester', 'Pa$$w0rd10', 'Martinez', 'Ethan', 'Student', 1),
('20231014', 'AY2025-2026-1stsemester', 'Pa$$w0rd9', 'Johnson', 'Michael', 'Student', 2),
('20231013', 'AY2025-2026-1stsemester', 'Pa$$w0rd8', 'Garcia', 'Jacob', 'Student', 1),
('20231012', 'AY2025-2026-1stsemester', 'Pa$$w0rd7', 'Miller', 'Ava', 'Student', 2),
('20231011', 'AY2025-2026-1stsemester', 'Pa$$w0rd6', 'Williams', 'Emily', 'Student', 4),
('20231010', 'AY2025-2026-1stsemester', 'Pa$$w0rd5', 'Brown', 'Matthew', 'Student', 2),
('20231009', 'AY2025-2026-1stsemester', 'Pa$$w0rd4', 'Smith', 'Olivia', 'Student', 3),
('20231008', 'AY2025-2026-1stsemester', 'Pa$$w0rd3', 'Jones', 'Emma', 'Student', 4),
('20231007', 'AY2025-2026-1stsemester', 'Pa$$w0rd2', 'Rodriguez', 'Daniel', 'Student', 1),
('20231006', 'AY2025-2026-1stsemester', 'Pa$$w0rd1', 'Johnson', 'Sophia', 'Student', 3),
('20231005', 'AY2025-2026-1stsemester', 'password123', 'Ramos', 'Carla', 'Student', 3),
('20231004', 'AY2025-2026-1stsemester', 'password123', 'Gonzales', 'Jose', 'Student', 1),
('20231003', 'AY2025-2026-1stsemester', 'password123', 'Reyes', 'Maria', 'Student', 4),
('20231001', 'AY2025-2026-1stsemester', '20231001', 'Dela Cruz', 'Juan', 'Student', 1),
('20231002', 'AY2025-2026-1stsemester', 'password123', 'Santos', 'Ana', 'Student', 2);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

DROP TABLE IF EXISTS `student_attendance`;
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `student_attendance` int NOT NULL AUTO_INCREMENT,
  `id_attendance` int NOT NULL,
  `id_student` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `date_attendance` datetime NOT NULL,
  `status_attendance` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `Penalty_requirements` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`student_attendance`)
) ENGINE=MyISAM AUTO_INCREMENT=6275 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`student_attendance`, `id_attendance`, `id_student`, `semester_ID`, `date_attendance`, `status_attendance`, `Penalty_requirements`) VALUES
(6234, 199, '20231066', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6233, 199, '20231067', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6232, 199, '20231069', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6231, 199, '20231068', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6230, 199, '20231070', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6229, 199, '20231071', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6228, 199, '20231072', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6227, 199, '20231073', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6226, 199, '20231074', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6225, 199, '20231075', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6224, 199, '20231076', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6223, 199, '20231077', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6222, 199, '20231078', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6221, 199, '20231079', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6220, 199, '20231081', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6219, 199, '20231080', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6218, 199, '20231082', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6217, 199, '20231083', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6216, 199, '20231084', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6215, 199, '20231086', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6214, 199, '20231085', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6213, 199, '20231087', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6212, 199, '20231088', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6211, 199, '20231089', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6210, 199, '20231091', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6209, 199, '20231090', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6208, 199, '20231093', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6207, 199, '20231092', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6206, 199, '20231095', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6205, 199, '20231094', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6204, 199, '20231096', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6203, 199, '20231098', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6202, 199, '20231097', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6201, 199, '20231099', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6195, 199, '80080195', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6196, 199, '80060270', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6197, 199, '80060100', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6198, 199, '80010715', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6199, 199, '80062165', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6200, 199, '202310100', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6274, 199, '20231026', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6273, 199, '20231027', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6272, 199, '20231028', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6271, 199, '20231029', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6270, 199, '20231030', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6269, 199, '20231031', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6268, 199, '20231032', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6267, 199, '20231033', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6266, 199, '20231034', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6265, 199, '20231035', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6264, 199, '20231036', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6263, 199, '20231037', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6262, 199, '20231038', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6261, 199, '20231039', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6260, 199, '20231040', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6259, 199, '20231041', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6258, 199, '20231042', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6257, 199, '20231043', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6256, 199, '20231044', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6255, 199, '20231046', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6254, 199, '20231045', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6253, 199, '20231047', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6252, 199, '20231048', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6251, 199, '20231049', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6250, 199, '20231050', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6249, 199, '20231051', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6248, 199, '20231052', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6247, 199, '20231053', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6246, 199, '20231055', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6245, 199, '20231054', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6244, 199, '20231056', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6243, 199, '20231058', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6242, 199, '20231057', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6241, 199, '20231059', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6240, 199, '20231061', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6239, 199, '20231060', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6238, 199, '20231062', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6237, 199, '20231063', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6236, 199, '20231064', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6235, 199, '20231065', 'AY2025-2026-1stsemester', '2025-05-18 06:19:30', 'Absent', '25'),
(6194, 199, '20231002', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6193, 199, '20231001', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6192, 199, '20231003', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6191, 199, '20231004', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6190, 199, '20231005', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6189, 199, '20231006', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6188, 199, '20231007', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6187, 199, '20231008', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6186, 199, '20231009', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6185, 199, '20231010', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6184, 199, '20231011', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6183, 199, '20231012', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6182, 199, '20231013', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6181, 199, '20231014', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6180, 199, '20231015', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6179, 199, '20231016', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6178, 199, '20231017', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6177, 199, '20231018', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6176, 199, '20231019', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6175, 199, '20231020', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6170, 199, '20231024', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6171, 199, '20231025', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6172, 199, '20231023', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6173, 199, '20231022', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25'),
(6174, 199, '20231021', 'AY2025-2026-1stsemester', '2025-05-18 06:13:45', 'Absent', '25');

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_record`
--

DROP TABLE IF EXISTS `student_fees_record`;
CREATE TABLE IF NOT EXISTS `student_fees_record` (
  `id_payment` int NOT NULL,
  `id_student` int NOT NULL,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `status_payment` int NOT NULL,
  `date_payment` date NOT NULL,
  `payment_amount` int NOT NULL,
  UNIQUE KEY `id_payment` (`id_payment`,`id_student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_fees_record`
--

INSERT INTO `student_fees_record` (`id_payment`, `id_student`, `semester_ID`, `status_payment`, `date_payment`, `payment_amount`) VALUES
(148, 80080195, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 80062165, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 80060270, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 80060100, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 80010715, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231099, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231098, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231097, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231096, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231095, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231094, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231093, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231092, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231091, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231090, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231089, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231088, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231087, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231086, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231085, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231084, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231083, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231082, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231081, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231080, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231079, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231078, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231077, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231076, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231075, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231074, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231073, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231072, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231071, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231070, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231069, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231068, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231067, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231066, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231065, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231064, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231063, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231062, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231061, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231060, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231059, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231058, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231057, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231056, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231055, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231054, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231053, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231052, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231051, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231050, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231049, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231048, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231047, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231046, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231045, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231044, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231043, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231042, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231041, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231040, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231039, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231038, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231037, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231036, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231035, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231034, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231033, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231032, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231031, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231030, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231029, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231028, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231027, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231026, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 202310100, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231025, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231024, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231023, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231022, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231021, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231020, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231019, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231018, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231017, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231016, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231015, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231014, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231013, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231012, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231011, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231010, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231009, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231008, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231007, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231006, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231002, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231003, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231004, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231005, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450),
(148, 20231001, 'AY2025-2026-1stsemester', 0, '2025-05-18', 450);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
