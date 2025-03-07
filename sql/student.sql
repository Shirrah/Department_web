-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 07, 2025 at 01:47 AM
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
('80062165', 'AY2025-2026-1stsemester', '1234', 'Orais', 'Harrish', 'Student', 4),
('48099001', 'AY2025-2026-1stsemester', '78901', 'Taylor', 'Sarah', 'Student', 4),
('47088990', 'AY2025-2026-1stsemester', '67890', 'Brown', 'Michael', 'Student', 3),
('46077899', 'AY2025-2026-1stsemester', '56789', 'Johnson', 'Emily', 'Student', 2),
('45066788', 'AY2025-2026-1stsemester', '45678', 'Smith', 'John', 'Student', 1),
('44055677', 'AY2025-2026-1stsemester', '34567', 'Orais', 'Harrish', 'Student', 4),
('43044566', 'AY2025-2026-1stsemester', '23456', 'Taylor', 'Sarah', 'Student', 3),
('42033455', 'AY2025-2026-1stsemester', '12345', 'Brown', 'Michael', 'Student', 2),
('41022344', 'AY2025-2026-1stsemester', '90123', 'Johnson', 'Emily', 'Student', 1),
('40011233', 'AY2025-2026-1stsemester', '89012', 'Smith', 'John', 'Student', 4),
('39001122', 'AY2025-2026-1stsemester', '78901', 'Orais', 'Harrish', 'Student', 3),
('38099001', 'AY2025-2026-1stsemester', '67890', 'Taylor', 'Sarah', 'Student', 2),
('37088990', 'AY2025-2026-1stsemester', '56789', 'Brown', 'Michael', 'Student', 1),
('36077899', 'AY2025-2026-1stsemester', '45678', 'Johnson', 'Emily', 'Student', 4),
('35066788', 'AY2025-2026-1stsemester', '34567', 'Smith', 'John', 'Student', 3),
('34055677', 'AY2025-2026-1stsemester', '23456', 'Orais', 'Harrish', 'Student', 2),
('33044566', 'AY2025-2026-1stsemester', '12345', 'Taylor', 'Sarah', 'Student', 1),
('32033455', 'AY2025-2026-1stsemester', '90123', 'Brown', 'Michael', 'Student', 4),
('31022344', 'AY2025-2026-1stsemester', '89012', 'Johnson', 'Emily', 'Student', 3),
('30011233', 'AY2025-2026-1stsemester', '78901', 'Smith', 'John', 'Student', 2),
('29001122', 'AY2025-2026-1stsemester', '67890', 'Orais', 'Harrish', 'Student', 1),
('28099001', 'AY2025-2026-1stsemester', '56789', 'Taylor', 'Sarah', 'Student', 4),
('27088990', 'AY2025-2026-1stsemester', '45678', 'Brown', 'Michael', 'Student', 3),
('26077899', 'AY2025-2026-1stsemester', '34567', 'Johnson', 'Emily', 'Student', 2),
('25066788', 'AY2025-2026-1stsemester', '23456', 'Smith', 'John', 'Student', 1),
('24055677', 'AY2025-2026-1stsemester', '12345', 'Orais', 'Harrish', 'Student', 4),
('23044566', 'AY2025-2026-1stsemester', '90123', 'Taylor', 'Sarah', 'Student', 3),
('22033455', 'AY2025-2026-1stsemester', '89012', 'Brown', 'Michael', 'Student', 2),
('21022344', 'AY2025-2026-1stsemester', '78901', 'Johnson', 'Emily', 'Student', 1),
('20011233', 'AY2025-2026-1stsemester', '67890', 'Smith', 'John', 'Student', 4),
('19001122', 'AY2025-2026-1stsemester', '56789', 'Orais', 'Harrish', 'Student', 3),
('18099001', 'AY2025-2026-1stsemester', '45678', 'Taylor', 'Sarah', 'Student', 2),
('17088990', 'AY2025-2026-1stsemester', '34567', 'Brown', 'Michael', 'Student', 1),
('16077889', 'AY2025-2026-1stsemester', '23456', 'Johnson', 'Emily', 'Student', 4),
('15066778', 'AY2025-2026-1stsemester', '12345', 'Smith', 'John', 'Student', 3),
('14055667', 'AY2025-2026-1stsemester', '90123', 'Orais', 'Harrish', 'Student', 2),
('12033445', 'AY2025-2026-1stsemester', '78901', 'Brown', 'Michael', 'Student', 4),
('13044556', 'AY2025-2026-1stsemester', '89012', 'Taylor', 'Sarah', 'Student', 1),
('11022334', 'AY2025-2026-1stsemester', '67890', 'Johnson', 'Emily', 'Student', 3),
('99001122', 'AY2025-2026-1stsemester', '45678', 'Orais', 'Harrish', 'Student', 1),
('10011223', 'AY2025-2026-1stsemester', '56789', 'Smith', 'John', 'Student', 2),
('88990011', 'AY2025-2026-1stsemester', '34567', 'Taylor', 'Sarah', 'Student', 4),
('55667788', 'AY2025-2026-1stsemester', '90123', 'Smith', 'John', 'Student', 1),
('77889900', 'AY2025-2026-1stsemester', '23456', 'Brown', 'Michael', 'Student', 3),
('66778899', 'AY2025-2026-1stsemester', '12345', 'Johnson', 'Emily', 'Student', 2),
('44556677', 'AY2025-2026-1stsemester', '89012', 'Orais', 'Harrish', 'Student', 4),
('33445566', 'AY2025-2026-1stsemester', '78901', 'Taylor', 'Sarah', 'Student', 3),
('22334455', 'AY2025-2026-1stsemester', '67890', 'Brown', 'Michael', 'Student', 2),
('11223344', 'AY2025-2026-1stsemester', '56789', 'Johnson', 'Emily', 'Student', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
