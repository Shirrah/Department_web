-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 17, 2024 at 01:25 PM
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
-- Database: `dcs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id_admin` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass_admin` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_admin` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname_admin` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstname_admin` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id_admin`, `pass_admin`, `role_admin`, `lastname_admin`, `firstname_admin`) VALUES
('042803', '042803', 'Admin', 'Orais', 'Harrish');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE IF NOT EXISTS `attendances` (
  `id_attendance` int NOT NULL AUTO_INCREMENT,
  `id_event` int NOT NULL,
  `type_attendance` varchar(999) COLLATE utf8mb4_general_ci NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  `fine_amount` int NOT NULL,
  PRIMARY KEY (`id_attendance`),
  KEY `fk_event` (`id_event`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id_attendance`, `id_event`, `type_attendance`, `time_in`, `time_out`, `fine_amount`) VALUES
(45, 80, 'IN', '06:56:00', '07:56:00', 50);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id_event` int NOT NULL AUTO_INCREMENT,
  `name_event` varchar(999) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_event` datetime NOT NULL,
  `event_desc` varchar(999) COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_event`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id_event`, `name_event`, `date_event`, `event_desc`, `date_created`) VALUES
(80, 'CCS DAY 1', '2024-11-30 00:00:00', 'test', '2024-11-10 19:07:17');

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
  `payment_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `date_payment` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_payment`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id_payment`, `payment_name`, `payment_amount`, `date_payment`) VALUES
(16, 'Departmental T-shirt', 450.00, '2024-11-08 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `id_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstname_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_student` varchar(99) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `year_student` int NOT NULL,
  PRIMARY KEY (`id_student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id_student`, `pass_student`, `lastname_student`, `firstname_student`, `role_student`, `year_student`) VALUES
('1', '1', '1', '1', 'Student', 1),
('12213308022', '12213308022', 'Orais', 'Arrish Ann', 'Student', 1),
('80010715', '80010715', 'Christian', 'Costillas', 'Student', 4),
('80010010', '80010010', 'Dre', 'Am', 'Student', 2),
('80010002', '80010002', 'Goerge', 'NotFound', 'Student', 3),
('80010003', '80010003', 'Man', 'Snake', 'Student', 2),
('80010004', '80010004', 'Domain', 'Expansion', 'Student', 3),
('80010005', '80010005', 'Word', 'Pass', 'Student', 4),
('80000009', '80000009', '80000009', '80000009', 'Student', 2),
('80000001', '80000001', '80000001', '80000001', 'Student', 2),
('80000002', '80000002', '80000002', '80000002', 'Student', 4),
('2', '2', '2', '2', 'Student', 2),
('3', '3', '3', '3', 'Student', 3),
('4', '4', '4', '4', 'Student', 4);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

DROP TABLE IF EXISTS `student_attendance`;
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `id_attendance` int NOT NULL,
  `id_student` int NOT NULL,
  `date_attendance` datetime NOT NULL,
  `status_attendance` varchar(99) COLLATE utf8mb4_general_ci NOT NULL,
  `fine_amount` int NOT NULL,
  UNIQUE KEY `id_attendance` (`id_attendance`,`id_student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id_attendance`, `id_student`, `date_attendance`, `status_attendance`, `fine_amount`) VALUES
(45, 80062165, '2024-11-10 20:33:20', 'Present', 0),
(45, 80010715, '2024-11-10 19:48:30', 'Absent', 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_fees_record`
--

DROP TABLE IF EXISTS `student_fees_record`;
CREATE TABLE IF NOT EXISTS `student_fees_record` (
  `id_payment` int NOT NULL,
  `id_student` int NOT NULL,
  `status_payment` int NOT NULL,
  `date_payment` int NOT NULL,
  `payment_amount` int NOT NULL,
  UNIQUE KEY `id_payment` (`id_payment`,`id_student`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
