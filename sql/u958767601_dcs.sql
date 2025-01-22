-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 22, 2025 at 12:49 PM
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
('Haru_admin', '1234', 'Governor', 'Orais', 'Harrish'),
('JANINE_ADMIN', '1234$', 'Admin', 'Jaula', 'Janine Mhyles'),
('ANGELICA_ADMIN', '1234$', 'Admin', 'Ringcodo', 'Angelica'),
('REY_ADMIN', '1234$', 'Admin', 'Tangcaungco', 'Rey Ann');

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
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id_attendance`, `id_event`, `type_attendance`, `attendance_status`, `Penalty_type`, `Penalty_requirements`, `start_time`, `end_time`) VALUES
(134, 146, 'IN', 'Pending', 'Fee', '40', '20:33:00', '22:33:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id_event`, `semester_ID`, `name_event`, `date_event`, `event_start_time`, `event_end_time`, `event_desc`, `date_created`, `created_by`) VALUES
(146, 'AY2024-2025-1stsemester', 'ddfgdgdgd', '2025-01-22', '00:15:00', '02:15:00', 'dgdgdg', '2025-01-22 00:15:15', 'Haru_admin'),
(147, 'AY2024-2025-1stsemester', 'Test event', '2025-01-04', '00:31:00', '05:31:00', 'ererererer', '2025-01-22 00:31:36', 'JANINE_ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id_payment`, `semester_ID`, `payment_name`, `payment_amount`, `date_payment`) VALUES
(118, 'AY2023-2024-1stsemester', 'Departmental T-shirt', 450.00, '2025-01-18 05:35:49');

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
  PRIMARY KEY (`semester_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_ID`, `academic_year`, `semester_type`, `date_created`) VALUES
('AY2024-2025-1stsemester', '2024-2025', '1st Semester', '0000-00-00 00:00:00');

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
('48099001', 'AY2024-2025-1stsemester', '78901', 'Taylor', 'Sarah', 'Student', 4),
('47088990', 'AY2024-2025-1stsemester', '67890', 'Brown', 'Michael', 'Student', 3),
('46077899', 'AY2024-2025-1stsemester', '56789', 'Johnson', 'Emily', 'Student', 2),
('45066788', 'AY2024-2025-1stsemester', '45678', 'Smith', 'John', 'Student', 1),
('44055677', 'AY2024-2025-1stsemester', '34567', 'Orais', 'Harrish', 'Student', 4),
('43044566', 'AY2024-2025-1stsemester', '23456', 'Taylor', 'Sarah', 'Student', 3),
('42033455', 'AY2024-2025-1stsemester', '12345', 'Brown', 'Michael', 'Student', 2),
('41022344', 'AY2024-2025-1stsemester', '90123', 'Johnson', 'Emily', 'Student', 1),
('40011233', 'AY2024-2025-1stsemester', '89012', 'Smith', 'John', 'Student', 4),
('39001122', 'AY2024-2025-1stsemester', '78901', 'Orais', 'Harrish', 'Student', 3),
('38099001', 'AY2024-2025-1stsemester', '67890', 'Taylor', 'Sarah', 'Student', 2),
('37088990', 'AY2024-2025-1stsemester', '56789', 'Brown', 'Michael', 'Student', 1),
('36077899', 'AY2024-2025-1stsemester', '45678', 'Johnson', 'Emily', 'Student', 4),
('35066788', 'AY2024-2025-1stsemester', '34567', 'Smith', 'John', 'Student', 3),
('34055677', 'AY2024-2025-1stsemester', '23456', 'Orais', 'Harrish', 'Student', 2),
('33044566', 'AY2024-2025-1stsemester', '12345', 'Taylor', 'Sarah', 'Student', 1),
('32033455', 'AY2024-2025-1stsemester', '90123', 'Brown', 'Michael', 'Student', 4),
('31022344', 'AY2024-2025-1stsemester', '89012', 'Johnson', 'Emily', 'Student', 3),
('30011233', 'AY2024-2025-1stsemester', '78901', 'Smith', 'John', 'Student', 2),
('29001122', 'AY2024-2025-1stsemester', '67890', 'Orais', 'Harrish', 'Student', 1),
('28099001', 'AY2024-2025-1stsemester', '56789', 'Taylor', 'Sarah', 'Student', 4),
('18099001', 'AY2024-2025-1stsemester', '45678', 'Taylor', 'Sarah', 'Student', 2),
('19001122', 'AY2024-2025-1stsemester', '56789', 'Orais', 'Harrish', 'Student', 3),
('20011233', 'AY2024-2025-1stsemester', '67890', 'Smith', 'John', 'Student', 4),
('21022344', 'AY2024-2025-1stsemester', '78901', 'Johnson', 'Emily', 'Student', 1),
('22033455', 'AY2024-2025-1stsemester', '89012', 'Brown', 'Michael', 'Student', 2),
('23044566', 'AY2024-2025-1stsemester', '90123', 'Taylor', 'Sarah', 'Student', 3),
('24055677', 'AY2024-2025-1stsemester', '12345', 'Orais', 'Harrish', 'Student', 4),
('25066788', 'AY2024-2025-1stsemester', '23456', 'Smith', 'John', 'Student', 1),
('26077899', 'AY2024-2025-1stsemester', '34567', 'Johnson', 'Emily', 'Student', 2),
('27088990', 'AY2024-2025-1stsemester', '45678', 'Brown', 'Michael', 'Student', 3),
('17088990', 'AY2024-2025-1stsemester', '34567', 'Brown', 'Michael', 'Student', 1),
('16077889', 'AY2024-2025-1stsemester', '23456', 'Johnson', 'Emily', 'Student', 4),
('15066778', 'AY2024-2025-1stsemester', '12345', 'Smith', 'John', 'Student', 3),
('14055667', 'AY2024-2025-1stsemester', '90123', 'Orais', 'Harrish', 'Student', 2),
('13044556', 'AY2024-2025-1stsemester', '89012', 'Taylor', 'Sarah', 'Student', 1),
('12033445', 'AY2024-2025-1stsemester', '78901', 'Brown', 'Michael', 'Student', 4),
('11022334', 'AY2024-2025-1stsemester', '67890', 'Johnson', 'Emily', 'Student', 3),
('10011223', 'AY2024-2025-1stsemester', '56789', 'Smith', 'John', 'Student', 2),
('99001122', 'AY2024-2025-1stsemester', '45678', 'Orais', 'Harrish', 'Student', 1),
('88990011', 'AY2024-2025-1stsemester', '34567', 'Taylor', 'Sarah', 'Student', 4),
('77889900', 'AY2024-2025-1stsemester', '23456', 'Brown', 'Michael', 'Student', 3),
('66778899', 'AY2024-2025-1stsemester', '12345', 'Johnson', 'Emily', 'Student', 2),
('55667788', 'AY2024-2025-1stsemester', '90123', 'Smith', 'John', 'Student', 1),
('44556677', 'AY2024-2025-1stsemester', '89012', 'Orais', 'Harrish', 'Student', 4),
('33445566', 'AY2024-2025-1stsemester', '78901', 'Taylor', 'Sarah', 'Student', 3),
('11223344', 'AY2024-2025-1stsemester', '56789', 'Johnson', 'Emily', 'Student', 1),
('22334455', 'AY2024-2025-1stsemester', '67890', 'Brown', 'Michael', 'Student', 2);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

DROP TABLE IF EXISTS `student_attendance`;
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `SFC_ID` int NOT NULL,
  `id_attendance` int NOT NULL,
  `id_student` int NOT NULL,
  `semester_ID` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `date_attendance` datetime NOT NULL,
  `status_attendance` varchar(99) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`SFC_ID`, `id_attendance`, `id_student`, `semester_ID`, `date_attendance`, `status_attendance`) VALUES
(0, 121, 13044556, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 12033445, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 11223344, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 10011223, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 11022334, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 15066778, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 14055667, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 119, 4534533, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 4534533, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 224234354, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 224234354, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 1211434, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 1211434, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 121, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 119, 121, 'AY2023-2024-2ndsemester', '2024-12-14 01:47:00', 'Absent'),
(0, 121, 16077889, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 17088990, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 18099001, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 19001122, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 20011233, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 21022344, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 22033455, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 22334455, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 23044566, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 24055677, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 25066788, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 26077899, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 27088990, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 28099001, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 29001122, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 30011233, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 31022344, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 32033455, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 33044566, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 33445566, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 34055677, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 35066788, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 36077899, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 37088990, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 38099001, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 39001122, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 40011233, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 41022344, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 42033455, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 43044566, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 44055677, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 44556677, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 45066788, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 46077899, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 47088990, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 48099001, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 55667788, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 66778899, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 77889900, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 88990011, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 121, 99001122, 'AY2024-2025-1stsemester', '2025-01-18 19:49:01', 'Absent'),
(0, 122, 10011223, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 11022334, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 11223344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 12033445, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 13044556, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 14055667, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 15066778, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 16077889, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 17088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 18099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 19001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 20011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 21022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 22033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 22334455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 23044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 24055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 25066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 26077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 27088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 28099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 29001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 30011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 31022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 32033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 33044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 33445566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 34055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 35066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 36077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 37088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 38099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 39001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 40011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 41022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 42033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 43044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 44055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 44556677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 45066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 46077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 47088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 48099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 55667788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 66778899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 77889900, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 88990011, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 122, 99001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 10011223, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 11022334, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 11223344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 12033445, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 13044556, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 14055667, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 15066778, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 16077889, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 17088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 18099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 19001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 20011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 21022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 22033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 22334455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 23044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 24055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 25066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 26077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 27088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 28099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 29001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 30011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 31022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 32033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 33044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 33445566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 34055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 35066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 36077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 37088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 38099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 39001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 40011233, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 41022344, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 42033455, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 43044566, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 44055677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 44556677, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 45066788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 46077899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 47088990, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 48099001, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 55667788, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 66778899, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 77889900, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 88990011, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent'),
(0, 123, 99001122, 'AY2024-2025-1stsemester', '2025-01-18 20:45:41', 'Absent');

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
(115, 1211434, 'AY2023-2024-2ndsemester', 0, '2024-12-14', 450),
(115, 121, 'AY2023-2024-2ndsemester', 0, '2024-12-14', 450),
(115, 224234354, 'AY2023-2024-2ndsemester', 0, '2024-12-14', 450),
(115, 4534533, 'AY2023-2024-2ndsemester', 0, '2024-12-14', 450),
(116, 121, 'AY2023-2024-1stsemester', 0, '2024-12-14', 20),
(116, 1211434, 'AY2023-2024-1stsemester', 0, '2024-12-14', 20),
(116, 224234354, 'AY2023-2024-1stsemester', 0, '2024-12-14', 20),
(116, 4534533, 'AY2023-2024-1stsemester', 0, '2024-12-14', 20),
(117, 10011223, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 11022334, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 11223344, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 12033445, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 13044556, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 14055667, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 15066778, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 16077889, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 17088990, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 18099001, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 19001122, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 20011233, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 21022344, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 22033455, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 22334455, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 23044566, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 24055677, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 25066788, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 26077899, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 27088990, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 28099001, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 29001122, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 30011233, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 31022344, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 32033455, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 33044566, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 33445566, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 34055677, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 35066788, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 36077899, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 37088990, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 38099001, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 39001122, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 40011233, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 41022344, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 42033455, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 43044566, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 44055677, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 44556677, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 45066788, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 46077899, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 47088990, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 48099001, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 55667788, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 66778899, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 77889900, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 88990011, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(117, 99001122, 'AY2023-2024-1stsemester', 0, '2024-12-15', 450),
(120, 10011223, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 11022334, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 11223344, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 12033445, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 13044556, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 14055667, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 15066778, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 16077889, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 17088990, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 18099001, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 19001122, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 20011233, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 21022344, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 22033455, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 22334455, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 23044566, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 24055677, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 25066788, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 26077899, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 27088990, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 28099001, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 29001122, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 30011233, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 31022344, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 32033455, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 33044566, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 33445566, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 34055677, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 35066788, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 36077899, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 37088990, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 38099001, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 39001122, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 40011233, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 41022344, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 42033455, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 43044566, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 44055677, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 44556677, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 45066788, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 46077899, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 47088990, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 48099001, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 55667788, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 66778899, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 77889900, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 88990011, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234),
(120, 99001122, 'AY2024-2025-1stsemester', 0, '2025-01-18', 1234);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
