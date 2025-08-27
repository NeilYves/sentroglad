-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 02:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barangay_mingming`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `activity_description` text NOT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `activity_description`, `activity_type`, `user_id`, `timestamp`) VALUES
(1, 'New resident John Doe registered.', 'New Resident', NULL, '2025-06-01 08:12:57'),
(2, 'Certificate of Indigency issued to Jane Smith.', 'Certificate Issued', NULL, '2025-06-01 08:12:57'),
(3, 'System settings updated by Admin.', 'System Update', NULL, '2025-06-01 08:12:57'),
(4, 'New resident John Doe registered.', 'New Resident', NULL, '2025-06-01 08:17:58'),
(5, 'Certificate of Indigency issued to Jane Smith.', 'Certificate Issued', NULL, '2025-06-01 08:17:58'),
(6, 'System settings updated by Admin.', 'System Update', NULL, '2025-06-01 08:17:58'),
(7, 'Deleted resident: David Brown', 'Delete Resident', NULL, '2025-06-01 08:30:16'),
(8, 'Deleted official: Juan Dela Cruz (Barangay Captain)', 'Delete Official', NULL, '2025-06-01 08:30:31'),
(9, 'Deleted official: Juan Dela Cruz (Barangay Captain)', 'Delete Official', NULL, '2025-06-01 08:30:33'),
(10, 'Deleted official: Maria Santos (Barangay Secretary)', 'Delete Official', NULL, '2025-06-01 08:30:35'),
(11, 'Deleted official: Maria Santos (Barangay Secretary)', 'Delete Official', NULL, '2025-06-01 08:31:03'),
(12, 'Deleted official: Pedro Reyes (Barangay Treasurer)', 'Delete Official', NULL, '2025-06-01 08:31:05'),
(13, 'Deleted official: Pedro Reyes (Barangay Treasurer)', 'Delete Official', NULL, '2025-06-01 08:31:07'),
(14, 'Deleted official: Ana Garcia (Kagawad)', 'Delete Official', NULL, '2025-06-01 08:31:09'),
(15, 'Deleted official: Ana Garcia (Kagawad)', 'Delete Official', NULL, '2025-06-01 08:31:11'),
(16, 'Deleted resident: David Brown', 'Delete Resident', NULL, '2025-06-01 08:31:13'),
(17, 'Deleted resident: Emily White', 'Delete Resident', NULL, '2025-06-01 08:31:15'),
(18, 'Deleted resident: Emily White', 'Delete Resident', NULL, '2025-06-01 08:31:17'),
(19, 'Deleted resident: Jane Smith', 'Delete Resident', NULL, '2025-06-01 08:31:27'),
(20, 'Deleted resident: Jane Smith', 'Delete Resident', NULL, '2025-06-01 08:31:29'),
(21, 'Deleted resident: John Doe', 'Delete Resident', NULL, '2025-06-01 08:31:30'),
(22, 'Deleted resident: John Doe', 'Delete Resident', NULL, '2025-06-01 08:31:32'),
(23, 'Deleted resident: Michael Lee', 'Delete Resident', NULL, '2025-06-01 08:31:33'),
(24, 'Deleted resident: Michael Lee', 'Delete Resident', NULL, '2025-06-01 08:31:35'),
(25, 'Added new official: keven (head)', 'New Official', NULL, '2025-06-01 08:41:40'),
(26, 'Updated official details for ID: 9 (keven)', 'Update Official', NULL, '2025-06-01 08:49:20'),
(27, 'Added new official: keven (head)', 'New Official', NULL, '2025-06-01 08:50:00'),
(28, 'Added new resident: Kevenzyrel Sonsona Pascioles', 'New Resident', NULL, '2025-06-01 08:50:15'),
(29, 'Added new resident: Neil Yves Meguiso', 'New Resident', NULL, '2025-06-01 09:25:35'),
(30, 'Simulated SMS blast sent to 2 resident(s). Message: \'hi\'.', 'SMS Blast Sent', NULL, '2025-06-02 01:55:50'),
(31, 'Simulated SMS blast sent to 1 resident(s). Message: \'hi\'.', 'SMS Blast Sent', NULL, '2025-06-02 01:56:03'),
(32, 'Simulated SMS blast sent to 2 resident(s). Message: \'hi\'.', 'SMS Blast Sent', NULL, '2025-06-02 01:56:13'),
(33, 'Added new resident: `123123', 'New Resident', NULL, '2025-06-02 01:56:40'),
(34, 'Simulated SMS blast sent to 3 resident(s). Message: \'qwewqe\'.', 'SMS Blast Sent', NULL, '2025-06-02 01:56:55'),
(35, 'Simulated SMS blast sent to 1 resident(s). Message: \'23123\'.', 'SMS Blast Sent', NULL, '2025-06-02 01:57:03'),
(36, 'Added new official: keven (Barangay Captain)', 'New Official', NULL, '2025-06-02 06:03:56'),
(37, 'Updated official details for ID: 10 (keven)', 'Update Official', NULL, '2025-06-02 06:10:20'),
(38, 'Updated official details for ID: 9 (keven)', 'Update Official', NULL, '2025-06-02 06:10:28'),
(39, 'Updated official details for ID: 11 (keven)', 'Update Official', NULL, '2025-06-02 06:10:52'),
(40, 'Deleted purok: Purok 6 - Matatag', 'Delete Purok', NULL, '2025-06-02 06:40:46'),
(41, 'Deleted purok: Purok 5 - Maligaya', 'Delete Purok', NULL, '2025-06-02 06:40:52'),
(42, 'Deleted purok: Purok 4 - Masagana', 'Delete Purok', NULL, '2025-06-02 06:40:55'),
(43, 'Deleted purok: Purok 3 - Bagong Lipunan', 'Delete Purok', NULL, '2025-06-02 06:41:06'),
(44, 'Deleted purok: Purok 2 - Katipunan', 'Delete Purok', NULL, '2025-06-02 06:42:17'),
(45, 'Added new official: Neil Yves Meguiso (Barangay Kagawad)', 'New Official', NULL, '2025-06-02 07:09:05'),
(46, 'Added new resident: Kevenzyrel Sonsona Pascioles', 'New Resident', NULL, '2025-06-02 07:19:18'),
(47, 'Added new resident: Kevenzyrel Sonsona Pascioles', 'New Resident', NULL, '2025-06-02 08:02:52'),
(48, 'New announcement added: mingming bayot', 'Announcement', NULL, '2025-06-03 05:43:55'),
(49, 'Added new resident: marso ariem S.', 'New Resident', NULL, '2025-06-05 09:02:36'),
(50, 'SMS blast sent to 1 resident(s). Message: \'hi\'.', 'SMS Blast Sent', NULL, '2025-06-08 10:33:41'),
(51, 'Added new resident: mingming S Meguiso', 'New Resident', NULL, '2025-06-08 10:35:25'),
(52, 'Updated resident details for ID: 17 (mingming S Meguiso)', 'Update Resident', NULL, '2025-06-08 10:39:38'),
(53, 'SMS blast sent to 1 resident(s). Message: \'mingming buarrraat\'.', 'SMS Blast Sent', NULL, '2025-06-08 10:40:01'),
(54, 'SMS blast sent to 0 resident(s). Message: \'bobo\'.', 'SMS Blast Sent', NULL, '2025-06-08 10:46:27'),
(55, 'SMS blast sent to 0 resident(s). Message: \'bbooboboboob\'.', 'SMS Blast Sent', NULL, '2025-06-08 10:46:41'),
(56, 'SMS blast sent to 0 resident(s). Message: \'hi\'.', 'SMS Blast Sent', NULL, '2025-06-08 11:38:59'),
(57, 'Added new resident: Francis F. Gravina', 'New Resident', NULL, '2025-06-13 03:41:00'),
(58, 'Updated resident details for ID: 18 (Francis F. Gravina)', 'Update Resident', NULL, '2025-06-13 03:41:43'),
(59, 'Updated resident details for ID: 18 (Francis Gravina)', 'Update Resident', NULL, '2025-06-13 04:25:36'),
(60, 'Issued certificate (COR-2025-06-0001: COR) to Francis F. Gravina. Purpose: To certify residency in the barangay....', 'Certificate Issued', NULL, '2025-06-14 00:49:50'),
(61, 'Added new resident: Ariem Marso', 'New Resident', NULL, '2025-06-14 00:52:28'),
(62, 'Updated resident details for ID: 20 (Ariem Marso)', 'Update Resident', NULL, '2025-06-14 00:52:44'),
(63, 'Deleted resident:  ', 'Delete Resident', NULL, '2025-06-14 01:16:06'),
(64, 'Deleted resident:  ', 'Delete Resident', NULL, '2025-06-14 01:16:08'),
(65, 'Deleted resident:  ', 'Delete Resident', NULL, '2025-06-14 01:16:10'),
(66, 'Updated resident details for ID: 14 (Marsing Gueres)', 'Update Resident', NULL, '2025-06-14 01:17:01'),
(67, 'Issued certificate (BC-2025-06-0001) to Francis Gravina. Purpose: To certify residency in the barangay....', 'Certificate Issued', NULL, '2025-06-14 01:24:18'),
(68, 'Issued certificate (BC-2025-06-0002) to Francis Gravina. Purpose: To certify residency in the barangay....', 'Certificate Issued', NULL, '2025-06-14 01:29:22'),
(69, 'Issued certificate (COR-2025-06-0002) to Francis Gravina. Purpose: change place...', 'Certificate Issued', NULL, '2025-06-14 01:40:57'),
(70, 'Issued certificate (COR-2025-06-0003) to Francis Gravina. Purpose: change place...', 'Certificate Issued', NULL, '2025-06-14 01:44:26'),
(71, 'Issued certificate (COR-2025-06-0004) to Francis Gravina. Purpose: change place...', 'Certificate Issued', NULL, '2025-06-14 01:56:04'),
(72, 'Issued certificate (COR-2025-06-0005) to Marsing Gueres. Purpose: new house...', 'Certificate Issued', NULL, '2025-06-14 02:22:21'),
(73, 'Issued certificate (COR-2025-06-0006) to Kevenzyrel Pascioles. Purpose: aa...', 'Certificate Issued', NULL, '2025-06-14 02:37:20'),
(74, 'Issued certificate (COLI-2025-06-0001) to marso S.. Purpose: For scholarship application....', 'Certificate Issued', NULL, '2025-06-14 03:11:17'),
(75, 'Issued certificate (COLI-2025-06-0002) to marso S.. Purpose: For scholarship application....', 'Certificate Issued', NULL, '2025-06-14 03:11:52'),
(76, 'Issued certificate (BC-2025-06-0003) to Francis Gravina. Purpose: aaa...', 'Certificate Issued', NULL, '2025-06-14 12:10:58'),
(77, 'User deleted: resident_viewer', 'User Management', 1, '2025-06-19 11:57:29'),
(78, 'User deleted: data_clerk', 'User Management', 1, '2025-06-19 11:57:35'),
(79, 'User deleted: resident_coord', 'User Management', 1, '2025-06-19 11:57:39'),
(80, 'User deleted: comm_officer', 'User Management', 1, '2025-06-19 11:57:45'),
(81, 'User deleted: reports_user', 'User Management', 1, '2025-06-19 11:57:50'),
(82, 'New user created: kev with role: Superadmin', 'User Management', 1, '2025-06-19 12:04:17'),
(83, 'User deleted: kev', 'User Management', 1, '2025-06-19 12:05:09'),
(84, 'New user created: kev with role: Superadmin', 'User Management', 1, '2025-06-19 12:05:21'),
(85, 'Issued certificate (COLI-2025-07-0001) to Francis Gravina. Purpose: For scholarship application....', 'Certificate Issued', NULL, '2025-07-03 07:28:55'),
(86, 'Added new official: mingming S Meguiso (SK Chairperson)', 'New Official', NULL, '2025-07-06 12:55:49'),
(87, 'Added new official: Kevenzyrel Sonsona Pascioles (Kagawad - Committee on Agriculture)', 'New Official', NULL, '2025-07-06 12:56:46'),
(88, 'Updated resident details for ID: 18 (Francis Gravina)', 'Update Resident', NULL, '2025-07-06 13:52:54'),
(89, 'New user created: staff with role: 123', 'User Management', 1, '2025-07-07 04:52:50'),
(90, 'Moved 1 official(s) from \'Barangay Captain\' to \'Ex-Barangay Captain\' due to new appointment', 'Position Transition', NULL, '2025-07-10 11:24:17'),
(91, 'Added new official: Neil Yves Meguiso (Barangay Captain)', 'New Official', NULL, '2025-07-10 11:24:17'),
(92, 'Moved 1 official(s) from \'Barangay Treasurer\' to \'Ex-Barangay Treasurer\' due to new appointment', 'Position Transition', NULL, '2025-07-10 11:25:28'),
(93, 'Added new official: Kevenzyrel Sonsona Pascioles (Barangay Treasurer)', 'New Official', NULL, '2025-07-10 11:25:28'),
(94, 'Database updated to support resident archive functionality', 'System Update', NULL, '2025-07-10 12:01:56'),
(95, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:02:08'),
(96, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:02:20'),
(97, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:06:05'),
(98, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:06:26'),
(99, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:06:35'),
(100, 'Archived resident: Marsing Gueres', 'Archive Resident', NULL, '2025-07-10 12:23:00'),
(101, 'Archived resident: Ariem Marso', 'Archive Resident', NULL, '2025-07-10 12:26:01'),
(102, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:26:16'),
(103, 'Restored resident from archive: Marsing Gueres', 'Restore Resident', NULL, '2025-07-10 12:26:25'),
(104, 'Restored resident from archive: Ariem Marso', 'Restore Resident', NULL, '2025-07-10 12:26:49'),
(105, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:35:37'),
(106, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:35:46'),
(107, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:36:00'),
(108, 'Archived resident: Marsing Gueres', 'Archive Resident', NULL, '2025-07-10 12:38:45'),
(109, 'Archived resident: Ariem Marso', 'Archive Resident', NULL, '2025-07-10 12:38:51'),
(110, 'Archived resident: mingming Meguiso', 'Archive Resident', NULL, '2025-07-10 12:40:47'),
(111, 'Restored resident from archive: mingming Meguiso', 'Restore Resident', NULL, '2025-07-10 12:41:05'),
(112, 'Restored resident from archive: Ariem Marso', 'Restore Resident', NULL, '2025-07-10 12:41:13'),
(113, 'Restored resident from archive: Marsing Gueres', 'Restore Resident', NULL, '2025-07-10 12:41:20'),
(114, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:41:26'),
(115, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-10 12:42:25'),
(116, 'Archived resident: Marsing Gueres', 'Archive Resident', NULL, '2025-07-10 12:42:38'),
(117, 'Restored resident from archive: Marsing Gueres', 'Restore Resident', NULL, '2025-07-10 12:42:45'),
(118, 'Archived resident: Marsing Gueres', 'Archive Resident', NULL, '2025-07-10 12:43:00'),
(119, 'Archived resident: Ariem Marso', 'Archive Resident', NULL, '2025-07-10 12:43:11'),
(120, 'Restored resident from archive: Ariem Marso', 'Restore Resident', NULL, '2025-07-10 12:43:17'),
(121, 'Restored resident from archive: Marsing Gueres', 'Restore Resident', NULL, '2025-07-10 12:43:24'),
(122, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-10 12:43:32'),
(123, 'Added new official: marso ariem S. (Kagawad - Committee on Agriculture)', 'New Official', NULL, '2025-07-10 13:39:58'),
(124, 'Moved 2 official(s) from \'Kagawad - Committee on Agriculture\' to \'Ex-Kagawad - Committee on Agriculture\' due to new appointment', 'Position Transition', NULL, '2025-07-10 13:44:20'),
(125, 'Added new official: mingming S Meguiso (Kagawad - Committee on Agriculture)', 'New Official', NULL, '2025-07-10 13:44:20'),
(126, 'Issued certificate (BC-2025-07-0001) to marso S.. Purpose: aa...', 'Certificate Issued', NULL, '2025-07-11 13:49:11'),
(127, 'Issued certificate (BC-2025-07-0002) to marso S.. Purpose: To start a business....', 'Certificate Issued', NULL, '2025-07-11 13:49:36'),
(128, 'Issued certificate (BC-2025-07-0003) to marso S.. Purpose: To start a business....', 'Certificate Issued', NULL, '2025-07-11 13:49:43'),
(129, 'Issued certificate (BC-2025-07-0004) to marso S.. Purpose: To start a business....', 'Certificate Issued', NULL, '2025-07-11 13:56:03'),
(130, 'Issued certificate (BC-2025-07-0005) to marso S.. Purpose: s...', 'Certificate Issued', NULL, '2025-07-11 22:57:21'),
(131, 'Issued certificate (BC-2025-07-0006) to marso S.. Purpose: To start a business....', 'Certificate Issued', NULL, '2025-07-11 22:57:49'),
(132, 'Issued certificate (COI-2025-07-0001) to marso S.. Purpose: z...', 'Certificate Issued', NULL, '2025-07-11 22:58:06'),
(133, 'Issued certificate (COLI-2025-07-0002) to marso S.. Purpose: For scholarship application....', 'Certificate Issued', NULL, '2025-07-11 22:58:13'),
(134, 'Issued certificate (COR-2025-07-0001) to marso S.. Purpose: z...', 'Certificate Issued', NULL, '2025-07-11 22:58:21'),
(135, 'Moved 1 official(s) from \'Barangay Secretary\' to \'Ex-Barangay Secretary\' due to new appointment', 'Position Transition', NULL, '2025-07-11 22:59:09'),
(136, 'Added new official: Kevenzyrel Sonsona Pascioles (Barangay Secretary)', 'New Official', NULL, '2025-07-11 22:59:09'),
(137, 'Issued certificate (BC-2025-07-0007) to marso S.. Purpose: a...', 'Certificate Issued', NULL, '2025-07-11 22:59:47'),
(138, 'Issued certificate (BC-2025-07-0008) to marso S.. Purpose: To start a business....', 'Certificate Issued', NULL, '2025-07-11 23:00:01'),
(139, 'Issued certificate (COLI-2025-07-0003) to marso S.. Purpose: For scholarship application....', 'Certificate Issued', NULL, '2025-07-11 23:00:16'),
(140, 'Issued certificate (BC-2025-07-0009) to marso S.. Purpose: aa...', 'Certificate Issued', NULL, '2025-07-12 00:10:07'),
(141, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-12 13:36:05'),
(142, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-12 13:36:14'),
(143, 'Archived resident: Francis Gravina', 'Archive Resident', NULL, '2025-07-12 13:36:20'),
(144, 'Restored resident from archive: Francis Gravina', 'Restore Resident', NULL, '2025-07-12 13:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publish_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_date` datetime DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `publish_date`, `event_date`, `author_id`, `is_active`) VALUES
(1, 'Barangay Assembly', 'All residents are invited to the quarterly Barangay Assembly. Venue: Barangay Hall.', '2025-06-01 08:12:57', '2025-06-15 14:00:00', NULL, 1),
(2, 'Free Anti-Rabies Vaccination', 'Free anti-rabies vaccination for pets will be conducted at the Barangay Plaza.', '2025-06-01 08:12:57', '2025-06-20 09:00:00', NULL, 1),
(3, 'Barangay Assembly', 'All residents are invited to the quarterly Barangay Assembly. Venue: Barangay Hall.', '2025-06-01 08:17:58', '2025-06-15 14:00:00', NULL, 1),
(4, 'Free Anti-Rabies Vaccination', 'Free anti-rabies vaccination for pets will be conducted at the Barangay Plaza.', '2025-06-01 08:17:58', '2025-06-20 09:00:00', NULL, 1),
(5, 'mingming bayot', 'asdasd\n\nLocation: barangay hall 5', '2025-06-03 05:43:55', '2025-06-03 13:43:00', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `certificate_types`
--

CREATE TABLE `certificate_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_file` varchar(255) DEFAULT NULL,
  `default_purpose` text DEFAULT NULL,
  `default_fee` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_types`
--

INSERT INTO `certificate_types` (`id`, `name`, `description`, `template_file`, `default_purpose`, `default_fee`, `is_active`) VALUES
(1, 'Barangay Clearance', NULL, 'template_barangay_clearance.php', NULL, 0.00, 1),
(3, 'Certificate of Residency', NULL, 'template_cert_residency.php', NULL, 0.00, 1),
(4, 'Business Clearance', NULL, 'template_business_clearance.php', 'To start a business.', 100.00, 1),
(5, 'Certificate of Low Income', NULL, 'template_low_income.php', 'For scholarship application.', 0.00, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `comprehensive_residents`
-- (See below for the actual view)
--
CREATE TABLE `comprehensive_residents` (
`id` int(11)
,`constructed_fullname` varchar(323)
,`fullname` varchar(255)
,`first_name` varchar(100)
,`middle_name` varchar(100)
,`last_name` varchar(100)
,`suffix` varchar(20)
,`gender` enum('Male','Female','Other')
,`birthdate` date
,`age` int(11)
,`educational_attainment` enum('No Formal Education','Elementary','Elementary Graduate','High School','High School Graduate','Vocational','College','College Graduate','Post Graduate')
,`family_planning` enum('Yes','No','Not Applicable')
,`no_maintenance` enum('Yes','No')
,`water_source` enum('Level 0 - Deepwell','Level 1 - Point Source','Level 2 - Communal Faucet','Level 3 - Individual Connection','Others')
,`toilet_facility` enum('Water Sealed','Closed Pit','Open Pit','None/No Toilet','Other')
,`pantawid_4ps` enum('Yes','No')
,`backyard_gardening` enum('Yes','No')
,`address` text
,`contact_number` varchar(20)
,`email` varchar(100)
,`status` enum('Active','Deceased','Moved Out','Archived')
,`purok_id` int(11)
,`purok_name` varchar(100)
,`date_status_changed` date
,`status_remarks` text
,`registration_date` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` int(11) NOT NULL,
  `household_name` varchar(255) NOT NULL,
  `head_of_household_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `purok_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`id`, `household_name`, `head_of_household_id`, `address`, `purok_id`, `date_created`, `last_updated`, `contact_number`) VALUES
(1, 'Miranda-Gravina', NULL, '123', 1, '2025-06-05 16:44:49', '2025-06-05 16:44:49', NULL),
(6, 'Piang-Abang', NULL, '123123', 1, '2025-06-05 16:49:34', '2025-06-05 16:49:34', NULL),
(7, 'Selagpo-Meguiso ', NULL, '12312312333', NULL, '2025-06-05 17:01:13', '2025-06-05 17:01:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `issued_certificates`
--

CREATE TABLE `issued_certificates` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `certificate_type_id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `purpose` text DEFAULT NULL,
  `issuing_official_id` int(11) DEFAULT NULL,
  `fee_paid` decimal(10,2) DEFAULT 0.00,
  `or_number` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issued_certificates`
--

INSERT INTO `issued_certificates` (`id`, `resident_id`, `certificate_type_id`, `control_number`, `issue_date`, `purpose`, `issuing_official_id`, `fee_paid`, `or_number`, `remarks`, `created_at`) VALUES
(1, 18, 3, 'COR-2025-06-0001', '2025-06-14', 'To certify residency in the barangay.', 11, 25.00, '122112', '1221212', '2025-06-14 00:49:50'),
(2, 18, 1, 'BC-2025-06-0001', '2025-06-14', 'To certify residency in the barangay.', NULL, 0.00, NULL, NULL, '2025-06-14 01:24:18'),
(3, 18, 1, 'BC-2025-06-0002', '2025-06-14', 'To certify residency in the barangay.', NULL, 0.00, NULL, NULL, '2025-06-14 01:29:22'),
(4, 18, 3, 'COR-2025-06-0002', '2025-06-14', 'change place', NULL, 0.00, NULL, NULL, '2025-06-14 01:40:57'),
(5, 18, 3, 'COR-2025-06-0003', '2025-06-14', 'change place', NULL, 0.00, NULL, NULL, '2025-06-14 01:44:26'),
(6, 18, 3, 'COR-2025-06-0004', '2025-06-14', 'change place', NULL, 0.00, NULL, NULL, '2025-06-14 01:56:04'),
(7, 14, 3, 'COR-2025-06-0005', '2025-06-14', 'new house', NULL, 0.00, NULL, NULL, '2025-06-14 02:22:21'),
(8, 15, 3, 'COR-2025-06-0006', '2025-06-14', 'aa', NULL, 0.00, NULL, NULL, '2025-06-14 02:37:20'),
(9, 16, 5, 'COLI-2025-06-0001', '2025-06-14', 'For scholarship application.', NULL, 0.00, NULL, NULL, '2025-06-14 03:11:17'),
(10, 16, 5, 'COLI-2025-06-0002', '2025-06-14', 'For scholarship application.', NULL, 0.00, NULL, NULL, '2025-06-14 03:11:52'),
(11, 18, 1, 'BC-2025-06-0003', '2025-06-14', 'aaa', NULL, 0.00, NULL, NULL, '2025-06-14 12:10:58'),
(12, 18, 5, 'COLI-2025-07-0001', '2025-07-03', 'For scholarship application.', NULL, 0.00, NULL, NULL, '2025-07-03 07:28:55'),
(13, 16, 1, 'BC-2025-07-0001', '2025-07-11', 'aa', NULL, 0.00, NULL, NULL, '2025-07-11 13:49:11'),
(14, 16, 4, 'BC-2025-07-0002', '2025-07-11', 'To start a business.', NULL, 0.00, NULL, NULL, '2025-07-11 13:49:36'),
(15, 16, 4, 'BC-2025-07-0003', '2025-07-11', 'To start a business.', NULL, 0.00, NULL, NULL, '2025-07-11 13:49:43'),
(16, 16, 4, 'BC-2025-07-0004', '2025-07-11', 'To start a business.', NULL, 0.00, NULL, NULL, '2025-07-11 13:56:03'),
(17, 16, 1, 'BC-2025-07-0005', '2025-07-12', 's', NULL, 0.00, NULL, NULL, '2025-07-11 22:57:21'),
(18, 16, 4, 'BC-2025-07-0006', '2025-07-12', 'To start a business.', NULL, 0.00, NULL, NULL, '2025-07-11 22:57:49'),
(20, 16, 5, 'COLI-2025-07-0002', '2025-07-12', 'For scholarship application.', NULL, 0.00, NULL, NULL, '2025-07-11 22:58:13'),
(21, 16, 3, 'COR-2025-07-0001', '2025-07-12', 'z', NULL, 0.00, NULL, NULL, '2025-07-11 22:58:21'),
(22, 16, 1, 'BC-2025-07-0007', '2025-07-12', 'a', NULL, 0.00, NULL, NULL, '2025-07-11 22:59:47'),
(23, 16, 4, 'BC-2025-07-0008', '2025-07-12', 'To start a business.', NULL, 0.00, NULL, NULL, '2025-07-11 23:00:01'),
(24, 16, 5, 'COLI-2025-07-0003', '2025-07-12', 'For scholarship application.', NULL, 0.00, NULL, NULL, '2025-07-11 23:00:16'),
(25, 16, 1, 'BC-2025-07-0009', '2025-07-12', 'aa', NULL, 0.00, NULL, NULL, '2025-07-12 00:10:07');

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL DEFAULT 'Other',
  `position` varchar(100) NOT NULL,
  `term_start_date` date DEFAULT NULL,
  `term_end_date` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `officials`
--

INSERT INTO `officials` (`id`, `fullname`, `gender`, `position`, `term_start_date`, `term_end_date`, `contact_number`, `image_path`, `display_order`) VALUES
(9, 'Jasper Corpuz', 'Other', 'Ex-Barangay Treasurer', '2025-06-01', '2025-08-08', '09123123123', NULL, 0),
(10, 'Justine Sarte', 'Male', 'Ex-Barangay Secretary', '3222-02-23', '3232-02-23', '12334324234', NULL, 1),
(11, 'Angelo Corpuz', 'Male', 'Ex-Barangay Captain', '2025-06-02', '2025-06-18', '09123123123', 'assets/images/uploads/officials/official_683d406c82555.jpg', 3),
(12, 'Neil Yves Meguiso', 'Male', 'Barangay Kagawad', '0213-03-12', '0123-03-12', '09123123123', '0', 6),
(13, ' Omar P Abang', 'Male', 'SK Chairperson', '2025-07-06', '2025-07-22', '09123123125', NULL, 0),
(14, 'Francis Rex Gravina', 'Male', 'Ex-Kagawad - Committee on Agriculture', '2025-07-06', '2025-07-30', '09123123123', NULL, 0),
(15, 'Neil Yves Meguiso', 'Male', 'Barangay Captain', '2025-07-10', '2025-07-11', '09123123123', NULL, 0),
(16, 'Kevenzyrel Sonsona Pascioles', 'Female', 'Barangay Treasurer', '2025-07-10', '2025-07-11', '09123123125', NULL, 0),
(17, 'marso ariem S.', 'Male', 'Ex-Kagawad - Committee on Agriculture', '2025-07-10', '2025-07-11', '0912345678', NULL, 0),
(18, 'John Paul T Nayre', 'Male', 'Kagawad - Committee on Agriculture', '2025-07-10', '2025-07-11', '09123123123', NULL, 0),
(19, 'Keven Zyrel Pascioles', 'Male', 'Barangay Secretary', '2025-07-12', '2025-07-12', '09123123123', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `puroks`
--

CREATE TABLE `puroks` (
  `id` int(11) NOT NULL,
  `purok_name` varchar(100) NOT NULL,
  `purok_leader` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `puroks`
--

INSERT INTO `puroks` (`id`, `purok_name`, `purok_leader`, `description`, `created_at`) VALUES
(1, 'Purok Mangga', 'Jose Rizal', 'Northern part of the barangay', '2025-06-02 06:31:10'),
(7, 'Purok Bayabas', 'Juan Luna', 'Area near the school and health center', '2025-06-02 06:31:10'),
(8, 'Purok Langka', 'Andres Bonifacio', 'Central area near the main road', '2025-06-02 07:20:02'),
(9, 'Purok Sariguellas', 'Maria Clara', 'Eastern section with residential areas', '2025-06-02 07:20:02'),
(10, 'Purok 4 - Masagana', 'Lapu-Lapu', 'Western area with commercial establishments', '2025-06-02 07:20:02'),
(11, 'Purok 5 - Maligaya', 'Gabriela Silang', 'Southern part near the river', '2025-06-02 07:20:02'),
(12, 'Purok 6 - Matatag', 'Antonio Luna', 'Mountainous area in the northeast', '2025-06-02 07:20:02');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `suffix` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` varchar(50) NOT NULL DEFAULT 'Single',
  `spouse_name` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `educational_attainment` enum('No Formal Education','Elementary','Elementary Graduate','High School','High School Graduate','Vocational','College','College Graduate','Post Graduate') DEFAULT NULL,
  `family_planning` enum('Yes','No','Not Applicable') DEFAULT 'Not Applicable',
  `no_maintenance` enum('Yes','No') DEFAULT 'No',
  `water_source` enum('Level 0 - Deepwell','Level 1 - Point Source','Level 2 - Communal Faucet','Level 3 - Individual Connection','Others') DEFAULT NULL,
  `toilet_facility` enum('Water Sealed','Closed Pit','Open Pit','None/No Toilet','Other') DEFAULT NULL,
  `pantawid_4ps` enum('Yes','No') DEFAULT 'No',
  `backyard_gardening` enum('Yes','No') DEFAULT 'No',
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('Active','Deceased','Moved Out','Archived') NOT NULL DEFAULT 'Active',
  `purok_id` int(11) DEFAULT NULL,
  `date_status_changed` date DEFAULT NULL,
  `status_remarks` text DEFAULT NULL,
  `archived_date` datetime DEFAULT NULL,
  `archived_by` varchar(100) DEFAULT NULL,
  `archive_reason` text DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `household_id` int(11) DEFAULT NULL,
  `maintenance_medicine` varchar(50) DEFAULT NULL,
  `other_medicine` varchar(100) DEFAULT NULL,
  `gender_other` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `fullname`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `civil_status`, `spouse_name`, `birthdate`, `age`, `educational_attainment`, `family_planning`, `no_maintenance`, `water_source`, `toilet_facility`, `pantawid_4ps`, `backyard_gardening`, `address`, `contact_number`, `email`, `status`, `purok_id`, `date_status_changed`, `status_remarks`, `archived_date`, `archived_by`, `archive_reason`, `registration_date`, `household_id`, `maintenance_medicine`, `other_medicine`, `gender_other`) VALUES
(14, 'Neil Yves S Meguiso', 'Neil Yves ', 'S.', 'Meguiso', '', 'Female', 'Widow/er', NULL, '1313-03-12', 712, 'College Graduate', 'No', '', 'Level 0 - Deepwell', 'Closed Pit', 'Yes', 'Yes', '123213', '12312323123', 'kevenzyrel@gmail.com', 'Active', 1, '0000-00-00', '', NULL, NULL, NULL, '2025-06-02 07:19:18', 6, 'Diabetes', '', ''),
(15, 'Kevenzyrel Sonsona Pascioles', 'Kevenzyrel', 'Test', 'Pascioles', '', 'Male', 'Single', NULL, '1985-05-15', 40, 'High School Graduate', 'Yes', '', 'Level 2 - Communal Faucet', 'Water Sealed', 'Yes', 'Yes', 'asdasd', '09987654321', 'kevenzyrel@gmail.com', 'Active', 1, '0000-00-00', '', NULL, NULL, NULL, '2025-06-02 08:02:52', NULL, NULL, '', ''),
(16, 'marso ariem S.', 'marso', 'ariem', 'S.', NULL, 'Male', 'Single', NULL, '2005-05-05', 20, 'High School Graduate', 'Yes', '', '', 'Water Sealed', 'Yes', 'Yes', '123213123', '09123123123', 'kevenzyrel@gmail.com', 'Active', 1, NULL, NULL, NULL, NULL, NULL, '2025-06-05 09:02:36', 7, 'Not Applicable', '', ''),
(17, 'Omar P Abang', 'Omar', 'P', 'Abang', '', 'Male', 'Single', NULL, '2003-02-05', 22, 'College', 'No', '', '', 'Water Sealed', 'Yes', 'Yes', 'asdzxcwae', '09272232561', 'ming@gmail.com', 'Active', 1, '0000-00-00', '', NULL, NULL, NULL, '2025-06-08 10:35:25', NULL, 'Not Applicable', '', ''),
(18, 'Francis F. Gravina', 'Francis', 'F.', 'Gravina', '', 'Other', 'Married', 'Alexander', '2000-05-05', 25, 'College', 'No', 'No', 'Level 2 - Communal Faucet', 'Open Pit', 'No', 'No', '', '09123123123', '', 'Active', 1, '0000-00-00', '', NULL, NULL, NULL, '2025-06-13 03:41:00', 7, '', '', 'LGBTQ'),
(20, NULL, 'Ariem', 'A.', 'Marso', '', 'Male', 'Separated', NULL, '1999-02-22', 26, 'College Graduate', 'No', '', 'Level 0 - Deepwell', 'Closed Pit', 'Yes', 'Yes', NULL, '12312323123', NULL, 'Active', 1, '0000-00-00', '', NULL, NULL, NULL, '2025-06-14 00:52:28', 6, 'Hypertension', '', '');

--
-- Triggers `residents`
--
DELIMITER $$
CREATE TRIGGER `calculate_age_on_insert` BEFORE INSERT ON `residents` FOR EACH ROW BEGIN
    IF NEW.birthdate IS NOT NULL THEN
        SET NEW.age = FLOOR(DATEDIFF(CURDATE(), NEW.birthdate) / 365.25);
    ELSE
        SET NEW.age = NULL;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_age_on_birthdate_change` BEFORE UPDATE ON `residents` FOR EACH ROW BEGIN
    IF NEW.birthdate IS NOT NULL THEN
        SET NEW.age = FLOOR(DATEDIFF(CURDATE(), NEW.birthdate) / 365.25);
    ELSE
        SET NEW.age = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('barangay_address_line1', '123 Main Street'),
('barangay_address_line2', 'Example City, Province'),
('barangay_logo_path', 'images/barangay_logo_path_684cdba5162442.67680959.png'),
('barangay_name', 'Barangay mingmong'),
('barangay_seal_text', 'OFFICIAL KEVEN'),
('current_punong_barangay_id', '11'),
('default_certificate_fee', '50.00'),
('municipality_logo_path', 'images/municipality_logo_path_684cdac128d731.01757220.png'),
('municipality_seal_text', 'MUNICIPALITY SEAL');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'staff',
  `access_permissions` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `access_permissions`, `created_at`) VALUES
(1, 'admin', 'mingming', 'Barangay Secretary', '[\"dashboard\", \"residents\", \"officials\", \"certificates\", \"puroks\", \"households\", \"announcements\", \"sms_blast\", \"reports\"]', '2025-06-05 19:35:07'),
(3, 'user2', 'mingming', 'staff', '[\"residents\", \"certificates\"]', '2025-06-05 19:35:49'),
(10, 'kev', 'Pascioles123', 'Superadmin', '[]', '2025-06-19 20:05:21'),
(11, 'staff', 'mingming', '123', '[\"households\",\"sms_blast\"]', '2025-07-07 12:52:50');

-- --------------------------------------------------------

--
-- Structure for view `comprehensive_residents`
--
DROP TABLE IF EXISTS `comprehensive_residents`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `comprehensive_residents`  AS SELECT `r`.`id` AS `id`, concat(coalesce(`r`.`first_name`,''),case when `r`.`middle_name` is not null and `r`.`middle_name` <> '' then concat(' ',`r`.`middle_name`) else '' end,case when `r`.`last_name` is not null and `r`.`last_name` <> '' then concat(' ',`r`.`last_name`) else '' end,case when `r`.`suffix` is not null and `r`.`suffix` <> '' then concat(' ',`r`.`suffix`) else '' end) AS `constructed_fullname`, `r`.`fullname` AS `fullname`, `r`.`first_name` AS `first_name`, `r`.`middle_name` AS `middle_name`, `r`.`last_name` AS `last_name`, `r`.`suffix` AS `suffix`, `r`.`gender` AS `gender`, `r`.`birthdate` AS `birthdate`, `r`.`age` AS `age`, `r`.`educational_attainment` AS `educational_attainment`, `r`.`family_planning` AS `family_planning`, `r`.`no_maintenance` AS `no_maintenance`, `r`.`water_source` AS `water_source`, `r`.`toilet_facility` AS `toilet_facility`, `r`.`pantawid_4ps` AS `pantawid_4ps`, `r`.`backyard_gardening` AS `backyard_gardening`, `r`.`address` AS `address`, `r`.`contact_number` AS `contact_number`, `r`.`email` AS `email`, `r`.`status` AS `status`, `r`.`purok_id` AS `purok_id`, `p`.`purok_name` AS `purok_name`, `r`.`date_status_changed` AS `date_status_changed`, `r`.`status_remarks` AS `status_remarks`, `r`.`registration_date` AS `registration_date` FROM (`residents` `r` left join `puroks` `p` on(`r`.`purok_id` = `p`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `certificate_types`
--
ALTER TABLE `certificate_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `household_name` (`household_name`),
  ADD KEY `head_of_household_id` (`head_of_household_id`),
  ADD KEY `purok_id` (`purok_id`);

--
-- Indexes for table `issued_certificates`
--
ALTER TABLE `issued_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `control_number` (`control_number`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `certificate_type_id` (`certificate_type_id`),
  ADD KEY `issuing_official_id` (`issuing_official_id`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `puroks`
--
ALTER TABLE `puroks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purok_name` (`purok_name`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_purok_id` (`purok_id`),
  ADD KEY `idx_last_name` (`last_name`),
  ADD KEY `idx_educational_attainment` (`educational_attainment`),
  ADD KEY `idx_4ps` (`pantawid_4ps`),
  ADD KEY `fk_household` (`household_id`),
  ADD KEY `idx_first_name` (`first_name`),
  ADD KEY `idx_archived_status` (`status`,`archived_date`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `certificate_types`
--
ALTER TABLE `certificate_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `issued_certificates`
--
ALTER TABLE `issued_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `puroks`
--
ALTER TABLE `puroks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `households`
--
ALTER TABLE `households`
  ADD CONSTRAINT `households_ibfk_1` FOREIGN KEY (`head_of_household_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `households_ibfk_2` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `issued_certificates`
--
ALTER TABLE `issued_certificates`
  ADD CONSTRAINT `fk_issued_certificates_official` FOREIGN KEY (`issuing_official_id`) REFERENCES `officials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_issued_certificates_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_issued_certificates_type` FOREIGN KEY (`certificate_type_id`) REFERENCES `certificate_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `fk_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_residents_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
