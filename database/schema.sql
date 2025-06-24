-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 10.35.233.124:3306
-- Generation Time: Jun 24, 2025 at 08:38 PM
-- Server version: 8.0.42
-- PHP Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k87747_dabs`
--
CREATE DATABASE IF NOT EXISTS `k87747_dabs` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `k87747_dabs`;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
  `id` int NOT NULL,
  `briefing_id` int NOT NULL COMMENT 'Briefing this activity belongs to',
  `date` date NOT NULL,
  `time` time NOT NULL COMMENT 'Scheduled time for the activity',
  `title` varchar(255) NOT NULL COMMENT 'Short title describing the activity',
  `description` text COMMENT 'Detailed description of the activity',
  `area` varchar(255) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT NULL COMMENT 'Priority level of the activity',
  `labor_count` int DEFAULT '0',
  `contractors` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_to` varchar(100) DEFAULT NULL COMMENT 'Person or team assigned to this activity',
  `completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores scheduled activities for daily briefings';

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `briefing_id`, `date`, `time`, `title`, `description`, `area`, `priority`, `labor_count`, `contractors`, `created_at`, `updated_at`, `assigned_to`, `completed`) VALUES
(43, 10, '0000-00-00', '00:00:00', 'll', 'll', 'block 1', 'low', 0, NULL, '2025-06-10 15:01:35', '2025-06-10 15:01:35', '', 0),
(44, 11, '0000-00-00', '00:00:00', 'nn', 'nn', 'block 1', 'medium', 0, NULL, '2025-06-10 15:01:35', '2025-06-10 15:01:35', '', 0),
(45, 14, '2025-06-16', '00:00:00', 'mm', 'mm', 'block 1', 'medium', 2, NULL, '2025-06-16 21:59:21', '2025-06-16 21:59:21', '', 0),
(48, 15, '2025-06-17', '00:00:00', 'm', '', 'block 1', 'medium', 0, NULL, '2025-06-17 10:07:13', '2025-06-17 10:07:13', '', 0),
(49, 15, '2025-06-17', '00:00:00', 'nnn', 'nnnnnnnnnnnnn', 'Block 2', 'low', 2, NULL, '2025-06-17 10:07:49', '2025-06-17 10:07:49', '', 0),
(51, 16, '2025-06-18', '00:00:00', 'kk', '', 'block 1', 'medium', 0, '[4]', '2025-06-18 10:33:26', '2025-06-18 14:18:11', '', 0),
(52, 20, '2025-06-24', '08:00:00', 'mm', 'mm', 'Block 1', 'medium', 1, 'Panacea', '2025-06-24 15:53:35', '2025-06-24 15:54:11', '', 0),
(53, 20, '2025-06-24', '15:02:00', 'Vents', 'Vents to apartment 1', 'Block 2', 'medium', 2, 'GPL', '2025-06-24 16:03:06', '2025-06-24 16:03:06', 'Nathan Bell', 0);

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'User who performed the action',
  `action` varchar(100) NOT NULL COMMENT 'Type of action performed',
  `details` text COMMENT 'Additional details about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address action was performed from',
  `timestamp` datetime NOT NULL COMMENT 'When the action occurred'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='System-wide audit trail of user actions';

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(32, 1, 'login_success', 'User logged in successfully', '172.69.224.185', '2025-05-29 11:29:01'),
(33, 1, 'login_success', 'User logged in successfully', '172.71.178.63', '2025-05-29 11:34:32'),
(34, 1, 'login_success', 'User logged in successfully', '172.71.178.143', '2025-05-29 13:51:35'),
(35, 1, 'login_success', 'User logged in successfully', '172.71.178.143', '2025-05-29 13:51:43'),
(36, 1, 'login_success', 'User logged in successfully', '162.158.216.29', '2025-05-29 13:52:41'),
(37, 1, 'login_success', 'User logged in successfully', '162.158.216.29', '2025-05-29 13:53:01'),
(38, 1, 'login_success', 'User logged in successfully', '172.70.85.151', '2025-05-29 13:56:33'),
(39, 1, 'create_briefing', 'Created new briefing for 29/05/2025', '172.69.224.83', '2025-05-29 14:07:11'),
(40, 1, 'login_success', 'User logged in successfully', '141.101.98.58', '2025-05-29 15:27:55'),
(41, 1, 'login_success', 'User logged in successfully', '162.158.216.35', '2025-05-29 20:15:21'),
(42, 1, 'login_success', 'User logged in successfully', '172.70.162.220', '2025-05-30 11:05:06'),
(43, 1, 'create_briefing', 'Created new briefing for 30/05/2025', '172.70.162.220', '2025-05-30 11:05:06'),
(44, 1, 'login_success', 'User logged in successfully', '172.71.241.83', '2025-05-30 11:42:23'),
(45, 1, 'login_success', 'User logged in successfully', '162.158.216.98', '2025-05-30 12:46:31'),
(46, 1, 'login_success', 'User logged in successfully', '172.70.85.252', '2025-05-30 13:46:54'),
(47, 1, 'login_success', 'User logged in successfully', '172.70.91.222', '2025-05-30 14:53:10'),
(48, 1, 'login_success', 'User logged in successfully', '172.69.195.19', '2025-05-30 16:08:40'),
(49, 1, 'login_success', 'User logged in successfully', '172.69.43.190', '2025-06-02 07:22:08'),
(50, 1, 'create_briefing', 'Created new briefing for 02/06/2025', '172.69.43.190', '2025-06-02 07:22:08'),
(51, 1, 'login_success', 'User logged in successfully', '172.70.162.97', '2025-06-02 10:46:45'),
(52, 1, 'login_success', 'User logged in successfully', '172.69.224.83', '2025-06-02 11:43:39'),
(53, 1, 'login_success', 'User logged in successfully', '172.71.241.82', '2025-06-02 13:45:59'),
(54, 1, 'login_success', 'User logged in successfully', '141.101.99.132', '2025-06-02 15:19:44'),
(55, 1, 'login_success', 'User logged in successfully', '141.101.98.208', '2025-06-03 07:40:02'),
(56, 1, 'create_briefing', 'Created new briefing for 03/06/2025', '141.101.98.208', '2025-06-03 07:40:02'),
(57, 1, 'login_success', 'User logged in successfully', '172.69.224.169', '2025-06-03 10:57:03'),
(58, 1, 'login_success', 'User logged in successfully', '172.70.86.194', '2025-06-03 11:12:35'),
(59, 1, 'login_success', 'User logged in successfully', '172.70.90.224', '2025-06-03 11:48:26'),
(60, 1, 'login_success', 'User logged in successfully', '172.71.26.70', '2025-06-03 13:33:27'),
(61, 1, 'login_success', 'User logged in successfully', '172.68.229.130', '2025-06-03 13:45:29'),
(62, 1, 'login_success', 'User logged in successfully', '172.71.241.144', '2025-06-03 15:47:37'),
(63, 1, 'login_success', 'User logged in successfully', '172.71.178.32', '2025-06-03 16:12:17'),
(64, 1, 'login_success', 'User logged in successfully', '172.70.162.98', '2025-06-03 16:26:32'),
(65, 1, 'login_success', 'User logged in successfully', '172.70.91.221', '2025-06-03 16:36:10'),
(66, 1, 'login_success', 'User logged in successfully', '141.101.98.72', '2025-06-03 16:44:32'),
(67, 1, 'login_success', 'User logged in successfully', '172.68.229.65', '2025-06-03 18:43:28'),
(68, 1, 'login_success', 'User logged in successfully', '172.69.224.185', '2025-06-03 18:51:34'),
(69, 1, 'login_success', 'User logged in successfully', '172.68.229.136', '2025-06-03 19:30:49'),
(70, 1, 'login_success', 'User logged in successfully', '172.69.194.73', '2025-06-03 19:34:17'),
(71, 1, 'login_success', 'User logged in successfully', '172.70.86.194', '2025-06-03 19:39:33'),
(72, 1, 'login_success', 'User logged in successfully', '172.71.241.126', '2025-06-03 19:55:42'),
(73, 1, 'login_success', 'User logged in successfully', '172.71.241.60', '2025-06-03 20:01:58'),
(74, 1, 'login_success', 'User logged in successfully', '172.71.26.82', '2025-06-04 07:24:53'),
(75, 1, 'create_briefing', 'Created new briefing for 04/06/2025', '172.71.26.82', '2025-06-04 07:24:53'),
(76, 1, 'login_success', 'User logged in successfully', '172.71.178.98', '2025-06-04 07:33:16'),
(77, 1, 'login_success', 'User logged in successfully', '172.71.26.82', '2025-06-04 07:35:32'),
(78, 1, 'login_success', 'User logged in successfully', '172.68.229.57', '2025-06-04 07:49:53'),
(79, 1, 'login_success', 'User logged in successfully', '172.71.241.38', '2025-06-04 10:03:14'),
(80, 1, 'login_success', 'User logged in successfully', '172.68.229.56', '2025-06-04 10:10:57'),
(81, 1, 'login_success', 'User logged in successfully', '172.70.162.5', '2025-06-04 10:11:33'),
(82, 1, 'login_success', 'User logged in successfully', '172.71.178.142', '2025-06-04 12:45:12'),
(83, 1, 'login_success', 'User logged in successfully', '172.70.85.83', '2025-06-04 13:42:04'),
(84, 1, 'login_success', 'User logged in successfully', '172.71.26.82', '2025-06-04 14:06:06'),
(85, 1, 'login_success', 'User logged in successfully', '172.70.162.40', '2025-06-04 14:09:01'),
(86, 1, 'login_success', 'User logged in successfully', '172.68.229.107', '2025-06-04 14:11:37'),
(87, 1, 'login_success', 'User logged in successfully', '172.68.229.107', '2025-06-04 14:11:38'),
(88, 1, 'login_success', 'User logged in successfully', '172.68.229.107', '2025-06-04 14:11:39'),
(89, 1, 'login_success', 'User logged in successfully', '172.68.229.107', '2025-06-04 14:11:40'),
(90, 1, 'login_success', 'User logged in successfully', '172.68.229.107', '2025-06-04 14:11:41'),
(91, 1, 'login_success', 'User logged in successfully', '172.71.178.63', '2025-06-04 14:11:56'),
(92, 1, 'login_success', 'User logged in successfully', '172.71.241.50', '2025-06-04 14:14:19'),
(93, 1, 'login_success', 'User logged in successfully', '172.68.229.126', '2025-06-04 14:22:31'),
(94, 1, 'login_success', 'User logged in successfully', '172.71.241.29', '2025-06-04 15:34:29'),
(95, 1, 'login_success', 'User logged in successfully', '172.69.195.135', '2025-06-04 15:41:17'),
(96, 1, 'login_success', 'User logged in successfully', '172.69.224.153', '2025-06-04 19:51:05'),
(97, 1, 'login_success', 'User logged in successfully', '172.71.241.57', '2025-06-04 20:45:32'),
(98, 1, 'copy_activities', 'Copied 4 activities from 03/06/2025 to 04/06/2025', '172.70.163.101', '0000-00-00 00:00:00'),
(99, 1, 'login_success', 'User logged in successfully', '172.68.186.2', '2025-06-04 21:55:41'),
(100, 1, 'login_success', 'User logged in successfully', '172.70.162.39', '2025-06-05 07:21:16'),
(101, 1, 'create_briefing', 'Created new briefing for 05/06/2025', '172.70.162.39', '2025-06-05 07:21:16'),
(102, 1, 'login_success', 'User logged in successfully', '172.69.194.199', '2025-06-05 12:34:40'),
(103, 1, 'copy_activities', 'Copied 8 activities from 04/06/2025 to 05/06/2025', '162.158.216.147', '0000-00-00 00:00:00'),
(104, 1, 'login_success', 'User logged in successfully', '172.69.224.28', '2025-06-05 12:58:38'),
(105, 1, 'login_success', 'User logged in successfully', '172.70.163.107', '2025-06-05 13:40:15'),
(106, 1, 'copy_activities', 'Copied 8 activities from 04/06/2025 to 05/06/2025', '172.70.90.57', '0000-00-00 00:00:00'),
(107, 1, 'login_success', 'User logged in successfully', '172.71.241.126', '2025-06-05 18:03:11'),
(108, 1, 'login_success', 'User logged in successfully', '172.70.90.189', '2025-06-05 19:40:09'),
(109, 1, 'login_success', 'User logged in successfully', '162.158.216.200', '2025-06-05 20:29:27'),
(110, 1, 'login_success', 'User logged in successfully', '172.71.178.96', '2025-06-06 07:20:12'),
(111, 1, 'create_briefing', 'Created new briefing for 06/06/2025', '172.71.178.96', '2025-06-06 07:20:13'),
(112, 1, 'login_success', 'User logged in successfully', '162.158.216.29', '2025-06-06 10:47:03'),
(113, 1, 'login_success', 'User logged in successfully', '172.68.229.134', '2025-06-06 10:47:29'),
(114, 1, 'login_success', 'User logged in successfully', '172.71.241.79', '2025-06-06 13:28:51'),
(115, 1, 'login_success', 'User logged in successfully', '172.69.195.226', '2025-06-06 15:43:05'),
(116, 1, 'login_success', 'User logged in successfully', '172.70.163.7', '2025-06-07 10:06:03'),
(117, 1, 'create_briefing', 'Created new briefing for 07/06/2025', '172.70.163.7', '2025-06-07 10:06:03'),
(118, 1, 'login_success', 'User logged in successfully', '172.71.241.122', '2025-06-07 10:30:03'),
(119, 1, 'login_success', 'User logged in successfully', '172.70.91.222', '2025-06-09 07:16:38'),
(120, 1, 'create_briefing', 'Created new briefing for 09/06/2025', '172.70.91.222', '2025-06-09 07:16:39'),
(121, 1, 'login_success', 'User logged in successfully', '172.69.224.83', '2025-06-09 12:09:10'),
(122, 1, 'login_success', 'User logged in successfully', '172.71.241.18', '2025-06-09 13:41:16'),
(123, 1, 'login_success', 'User logged in successfully', '162.158.216.168', '2025-06-09 14:43:42'),
(124, 1, 'login_success', 'User logged in successfully', '172.71.241.166', '2025-06-09 15:52:29'),
(125, 1, 'create_briefing', 'Created new briefing for 09/06/2025', '172.68.186.3', '2025-06-09 16:27:19'),
(126, 1, 'login_success', 'User logged in successfully', '172.69.224.147', '2025-06-09 16:39:08'),
(127, 1, 'login_success', 'User logged in successfully', '141.101.98.81', '2025-06-10 07:38:59'),
(128, 1, 'create_briefing', 'Created new briefing for 10/06/2025', '141.101.98.81', '2025-06-10 07:38:59'),
(129, 1, 'login_success', 'User logged in successfully', '141.101.98.178', '2025-06-10 10:37:39'),
(130, 1, 'login_success', 'User logged in successfully', '172.70.163.106', '2025-06-10 12:37:30'),
(131, 1, 'login_success', 'User logged in successfully', '172.68.229.135', '2025-06-10 13:30:02'),
(132, 1, 'login_success', 'User logged in successfully', '141.101.99.105', '2025-06-10 15:41:10'),
(133, 1, 'login_success', 'User logged in successfully', '172.69.224.100', '2025-06-11 07:12:35'),
(134, 1, 'create_briefing', 'Created new briefing for 11/06/2025', '172.69.224.100', '2025-06-11 07:12:35'),
(135, 1, 'login_success', 'User logged in successfully', '162.158.216.28', '2025-06-11 10:07:29'),
(136, 1, 'login_success', 'User logged in successfully', '141.101.98.117', '2025-06-11 15:30:44'),
(137, 1, 'login_success', 'User logged in successfully', '172.70.162.219', '2025-06-11 16:31:34'),
(138, 1, 'login_success', 'User logged in successfully', '172.69.195.136', '2025-06-15 09:44:14'),
(139, 1, 'create_briefing', 'Created new briefing for 15/06/2025', '172.69.195.136', '2025-06-15 09:44:14'),
(140, 1, 'login_success', 'User logged in successfully', '172.70.90.188', '2025-06-16 12:08:07'),
(141, 1, 'create_briefing', 'Created new briefing for 16/06/2025', '172.70.90.188', '2025-06-16 12:08:07'),
(142, 1, 'login_success', 'User logged in successfully', '172.70.91.84', '2025-06-16 12:18:31'),
(143, 1, 'login_success', 'User logged in successfully', '172.68.229.63', '2025-06-16 13:25:17'),
(144, 1, 'login_success', 'User logged in successfully', '172.71.26.18', '2025-06-16 14:44:38'),
(145, 1, 'login_success', 'User logged in successfully', '172.71.178.115', '2025-06-16 20:59:31'),
(146, 1, 'login_success', 'User logged in successfully', '172.71.178.115', '2025-06-16 20:59:32'),
(147, 1, 'login_success', 'User logged in successfully', '162.158.216.184', '2025-06-17 07:26:57'),
(148, 1, 'create_briefing', 'Created new briefing for 17/06/2025', '162.158.216.184', '2025-06-17 07:26:57'),
(149, 1, 'login_success', 'User logged in successfully', '172.70.91.15', '2025-06-17 08:16:30'),
(150, 1, 'login_success', 'User logged in successfully', '141.101.98.178', '2025-06-17 09:45:08'),
(151, 1, 'login_success', 'User logged in successfully', '172.70.162.40', '2025-06-17 10:45:23'),
(152, 1, 'login_success', 'User logged in successfully', '172.71.178.172', '2025-06-17 11:50:06'),
(153, 1, 'login_success', 'User logged in successfully', '162.158.216.35', '2025-06-17 12:54:12'),
(154, 1, 'login_success', 'User logged in successfully', '172.71.241.35', '2025-06-17 15:22:49'),
(155, 1, 'login_success', 'User logged in successfully', '141.101.98.24', '2025-06-17 15:24:49'),
(156, 1, 'login_success', 'User logged in successfully', '172.70.90.147', '2025-06-17 15:54:24'),
(157, 1, 'login_success', 'User logged in successfully', '172.70.160.213', '2025-06-18 07:33:42'),
(158, 1, 'create_briefing', 'Created new briefing for 18/06/2025', '172.70.160.213', '2025-06-18 07:33:42'),
(159, 1, 'login_success', 'User logged in successfully', '172.70.91.203', '2025-06-18 09:50:52'),
(160, 1, 'login_success', 'User logged in successfully', '141.101.99.18', '2025-06-18 10:30:11'),
(161, 1, 'login_success', 'User logged in successfully', '172.70.162.9', '2025-06-18 13:30:23'),
(162, 1, 'login_success', 'User logged in successfully', '172.71.178.63', '2025-06-18 16:31:23'),
(163, 1, 'login_success', 'User logged in successfully', '172.70.162.97', '2025-06-19 07:15:12'),
(164, 1, 'create_briefing', 'Created new briefing for 19/06/2025', '172.70.162.97', '2025-06-19 07:15:12'),
(165, 1, 'login_success', 'User logged in successfully', '172.71.178.160', '2025-06-19 15:17:50'),
(166, 1, 'login_success', 'User logged in successfully', '172.71.241.104', '2025-06-20 13:12:08'),
(167, 1, 'create_briefing', 'Created new briefing for 20/06/2025', '172.71.241.104', '2025-06-20 13:12:08'),
(168, 1, 'login_success', 'User logged in successfully', '172.70.160.212', '2025-06-23 10:36:23'),
(169, 1, 'create_briefing', 'Created new briefing for 23/06/2025', '172.70.160.212', '2025-06-23 10:36:23'),
(170, 1, 'login_success', 'User logged in successfully', '172.68.229.138', '2025-06-23 10:41:54'),
(171, 1, 'login_success', 'User logged in successfully', '141.101.98.109', '2025-06-23 12:05:28'),
(172, 1, 'login_success', 'User logged in successfully', '172.70.162.9', '2025-06-23 13:54:33'),
(173, 1, 'login_success', 'User logged in successfully', '172.70.86.221', '2025-06-23 16:01:06'),
(174, 1, 'login_success', 'User logged in successfully', '172.71.178.129', '2025-06-23 20:50:07'),
(175, 1, 'login_success', 'User logged in successfully', '172.69.224.147', '2025-06-23 21:45:00'),
(176, 1, 'login_success', 'User logged in successfully', '172.69.224.147', '2025-06-23 21:45:39'),
(177, 1, 'login_success', 'User logged in successfully', '172.69.194.33', '2025-06-24 07:17:14'),
(178, 1, 'create_briefing', 'Created new briefing for 24/06/2025', '172.69.194.32', '2025-06-24 07:17:14'),
(179, 1, 'login_success', 'User logged in successfully', '172.68.229.125', '2025-06-24 07:53:07'),
(180, 1, 'login_success', 'User logged in successfully', '172.69.195.178', '2025-06-24 10:38:10'),
(181, 1, 'login_success', 'User logged in successfully', '172.69.224.98', '2025-06-24 14:50:54'),
(182, 1, 'login_success', 'User logged in successfully', '172.69.195.60', '2025-06-24 18:41:26');

-- --------------------------------------------------------

--
-- Table structure for table `briefings`
--

DROP TABLE IF EXISTS `briefings`;
CREATE TABLE `briefings` (
  `id` int NOT NULL,
  `project_id` int NOT NULL COMMENT 'Project this briefing belongs to',
  `date` date NOT NULL COMMENT 'Date for this briefing',
  `overview` text COMMENT 'General overview of the day''s activities',
  `safety_info` text COMMENT 'Safety information and hazard warnings',
  `notes` text COMMENT 'Additional notes and information',
  `created_by` int DEFAULT NULL COMMENT 'User who created this briefing',
  `updated_by` int DEFAULT NULL COMMENT 'User who last updated this briefing',
  `last_updated` datetime DEFAULT NULL COMMENT 'When briefing was last updated',
  `status` enum('draft','published','archived') DEFAULT 'draft' COMMENT 'Current status of the briefing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores daily briefing information for construction sites';

--
-- Dumping data for table `briefings`
--

INSERT INTO `briefings` (`id`, `project_id`, `date`, `overview`, `safety_info`, `notes`, `created_by`, `updated_by`, `last_updated`, `status`) VALUES
(10, 1, '2025-06-09', '', '<ul>\n<li><span style=\"background-color: #f1c40f;\"><strong><span style=\"text-decoration: underline;\">No specific safety information for today.</span></strong></span></li>\n<li><span style=\"background-color: #f1c40f;\"><strong><span style=\"text-decoration: underline;\">Remember to follow standard safety protocols.</span></strong></span></li>\n<li><span style=\"background-color: #f1c40f;\"><strong><span style=\"text-decoration: underline;\">Always wear appropriate PPE.</span></strong></span></li>\n<li><span style=\"background-color: #f1c40f;\"><strong><span style=\"text-decoration: underline;\">Wearing gloves is mandotry on site</span></strong></span></li>\n</ul>', '', 1, 1, '2025-06-09 17:29:46', 'draft'),
(11, 1, '2025-06-10', '', '<ul>\n<li>No specific safety information for today.</li>\n<li>Remember to follow standard safety protocols.</li>\n<li>Always wear appropriate PPE.</li>\n<li>TEST</li>\n</ul>', '', 1, 1, '2025-06-10 11:39:13', 'draft'),
(12, 1, '2025-06-11', '', '', '', 1, NULL, '2025-06-11 07:12:35', 'draft'),
(13, 1, '2025-06-15', '', '', '', 1, NULL, '2025-06-15 09:44:14', 'draft'),
(14, 1, '2025-06-16', '', '', '', 1, NULL, '2025-06-16 12:08:07', 'draft'),
(15, 1, '2025-06-17', '', '', '', 1, NULL, '2025-06-17 07:26:57', 'draft'),
(16, 1, '2025-06-18', '', '<ul>\n<li>No specific safety information for today.</li>\n<li>Remember to follow standard safety protocols.</li>\n<li>Always wear appropriate PPE.</li>\n<li>GLOVES!!!!</li>\n</ul>', '', 1, 1, '2025-06-18 15:02:55', 'draft'),
(17, 1, '2025-06-19', '', '', '', 1, NULL, '2025-06-19 07:15:12', 'draft'),
(18, 1, '2025-06-20', '', '', '', 1, NULL, '2025-06-20 13:12:08', 'draft'),
(19, 1, '2025-06-23', '', '', '', 1, NULL, '2025-06-23 10:36:23', 'draft'),
(20, 1, '2025-06-24', '', '<p>bb</p>', '', 1, 1, '2025-06-24 11:41:53', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `dabs_attendees`
--

DROP TABLE IF EXISTS `dabs_attendees`;
CREATE TABLE `dabs_attendees` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `briefing_date` date NOT NULL,
  `attendee_name` varchar(255) NOT NULL,
  `subcontractor_name` varchar(100) DEFAULT NULL,
  `added_by` varchar(255) DEFAULT NULL,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `dabs_attendees`
--

INSERT INTO `dabs_attendees` (`id`, `project_id`, `briefing_date`, `attendee_name`, `subcontractor_name`, `added_by`, `added_at`) VALUES
(51, 1, '2025-06-04', 'Mark riddex', 'Cara Brickwork', 'System Admin', '2025-06-04 22:38:53'),
(56, 1, '2025-06-05', 'Anthony Titiosho', 'McGoff', 'System Admin', '2025-06-05 08:21:23'),
(57, 1, '2025-06-05', 'Chris Irlam', 'McGoff', 'System Admin', '2025-06-05 08:21:23'),
(58, 1, '2025-06-05', 'Damian Purnell', 'McGoff', 'System Admin', '2025-06-05 08:21:23'),
(59, 1, '2025-06-05', 'Dean Smith', 'McGoff', 'System Admin', '2025-06-05 08:21:23'),
(60, 1, '2025-06-05', 'Jamie Tandy', 'Craven', 'System Admin', '2025-06-05 08:21:23'),
(61, 1, '2025-06-05', 'Marcel Aspden', 'Panacea', 'System Admin', '2025-06-05 08:21:23'),
(63, 1, '2025-06-05', 'Mick Green', 'McGoff', 'System Admin', '2025-06-05 08:21:23'),
(64, 1, '2025-06-05', 'Mike Pearson', 'RWS', 'System Admin', '2025-06-05 08:21:23'),
(65, 1, '2025-06-05', 'Nathan Bell', 'GPL', 'System Admin', '2025-06-05 08:21:23'),
(66, 1, '2025-06-05', 'Piotr Sowa', 'Panacea', 'System Admin', '2025-06-05 08:21:23'),
(68, 1, '2025-06-24', 'Alex Stratulat', 'Panacea', 'System Admin', '2025-06-24 15:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `dabs_notes`
--

DROP TABLE IF EXISTS `dabs_notes`;
CREATE TABLE `dabs_notes` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `note_date` date NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dabs_notes`
--

INSERT INTO `dabs_notes` (`id`, `project_id`, `note_date`, `notes`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 1, '2025-06-03', '<ol>\r\n<li>test notes</li>\r\n</ol>\r\n<ul style=\"list-style-type: square;\">\r\n<li><strong>test notes 2</strong></li>\r\n<li><strong>tesdt notes 3</strong></li>\r\n<li><strong>Mick Green Loves Ba^^y Boys</strong></li>\r\n<li><strong>Mark Riddex is Sha**ing the Cleaner :-)</strong></li>\r\n<li><strong>Frank Edge is buying the breakfasts for Fridays DABS!!!!</strong></li>\r\n<li><strong>None of the above comments are true, you F**kin Smowflakes !!!</strong></li>\r\n<li><strong>Commets 4&amp;5 are true though :-0</strong></li>\r\n</ul>', '2025-06-03 17:01:42', '2025-06-03 23:43:19', 'System Admin'),
(2, 1, '2025-06-04', '<ul>\r\n<li><strong>Test Note, just to make sure it works ok</strong></li>\r\n</ul>', '2025-06-04 17:34:07', '2025-06-04 22:51:25', 'System Admin'),
(3, 1, '2025-06-09', '<ol>\r\n<li>test notes</li>\r\n</ol>\r\n<ul>\r\n<li><strong>test notes 2</strong></li>\r\n<li><strong>tesdt notes 3</strong></li>\r\n<li><strong>Mick Green Loves Ba^^y Boys</strong></li>\r\n<li><strong>Mark Riddex is Sha**ing the Cleaner :-)</strong></li>\r\n<li><strong>Frank Edge is buying the breakfasts for Fridays DABS!!!!</strong></li>\r\n<li><strong>None of the above comments are true.</strong></li>\r\n<li><strong>Commets 4&amp;5 are true though :-0</strong></li>\r\n</ul>', '2025-06-09 08:19:05', '2025-06-09 08:19:05', 'System Admin'),
(4, 1, '2025-06-10', '<p>vv</p>', '2025-06-10 14:34:21', '2025-06-10 14:34:21', 'System Admin'),
(5, 1, '2025-06-15', '<p>jjjj</p>', '2025-06-15 10:44:26', '2025-06-15 10:44:26', 'System Admin'),
(6, 1, '2025-06-18', '<p>jjjj</p>', '2025-06-18 15:02:39', '2025-06-18 15:02:39', 'System Admin'),
(7, 1, '2025-06-24', '<p>Test Note, just to make sure it works ok</p>', '2025-06-24 11:42:25', '2025-06-24 11:42:25', 'System Admin');

-- --------------------------------------------------------

--
-- Table structure for table `dabs_subcontractors`
--

DROP TABLE IF EXISTS `dabs_subcontractors`;
CREATE TABLE `dabs_subcontractors` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `trade` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `dabs_subcontractors`
--

INSERT INTO `dabs_subcontractors` (`id`, `project_id`, `name`, `trade`, `contact_name`, `phone`, `email`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 1, 'Cara Brickwork', 'Brickwork', '', '', '', 'Active', 'System Admin', '2025-06-03 16:59:52', '2025-06-03 16:59:52'),
(5, 1, 'Panacea', 'Drylining', '', '', '', 'Active', 'System Admin', '2025-06-03 22:06:06', '2025-06-03 22:06:06'),
(6, 1, 'GPL', 'Plumbing/vents', '', '', '', 'Active', 'System Admin', '2025-06-03 22:29:17', '2025-06-03 22:29:17'),
(7, 1, 'Cara Brickwork', 'Brickwork', '', '', '', 'Active', 'System Admin', '2025-06-03 23:42:49', '2025-06-18 11:22:40'),
(8, 1, 'Red Window Systems', 'Window Fitters', '', '', '', 'Active', 'System Admin', '2025-06-04 17:28:35', '2025-06-04 17:28:35');

-- --------------------------------------------------------

--
-- Table structure for table `dabs_subcontractor_tasks`
--

DROP TABLE IF EXISTS `dabs_subcontractor_tasks`;
CREATE TABLE `dabs_subcontractor_tasks` (
  `id` int NOT NULL,
  `subcontractor_id` int NOT NULL,
  `task_date` date NOT NULL,
  `task_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempt_time`) VALUES
(1, 'admin', '172.71.241.19', '2025-05-29 07:46:51'),
(2, 'admin', '172.71.241.19', '2025-05-29 07:46:58'),
(3, 'admin', '172.71.241.19', '2025-05-29 07:47:26'),
(4, 'admin', '172.71.178.129', '2025-05-29 07:48:42'),
(5, 'admin', '172.71.178.129', '2025-05-29 07:48:53'),
(6, 'admin', '172.68.229.124', '2025-05-29 07:53:48'),
(7, 'admin', '172.68.229.124', '2025-05-29 07:53:54'),
(8, 'irlam', '172.70.162.98', '2025-05-29 08:06:32'),
(9, 'irlam', '172.71.241.34', '2025-05-29 08:08:14'),
(10, 'irlam', '172.71.178.35', '2025-05-29 08:19:00'),
(11, 'irlam', '172.71.178.35', '2025-05-29 08:19:07'),
(12, 'irlam', '172.70.85.83', '2025-05-29 09:31:38'),
(13, 'irlam', '172.68.186.62', '2025-05-29 09:35:26'),
(14, 'irlam', '172.68.186.62', '2025-05-29 09:35:32'),
(15, 'irlam', '172.68.186.62', '2025-05-29 09:35:55'),
(16, 'irlam', '172.68.186.62', '2025-05-29 09:36:01'),
(17, 'irlam', '172.68.229.138', '2025-05-29 09:42:57'),
(18, 'irlam', '172.68.229.138', '2025-05-29 09:43:02'),
(19, 'irlam', '162.158.216.168', '2025-05-29 09:47:22'),
(20, 'irlam', '162.158.216.216', '2025-05-29 11:28:13'),
(21, 'irlam', '162.158.216.201', '2025-06-04 14:13:26'),
(22, 'admin', '172.71.241.50', '2025-06-04 14:14:09'),
(23, 'irlam', '172.71.241.104', '2025-06-20 13:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'User requesting password reset',
  `token` varchar(64) NOT NULL COMMENT 'Secure token sent via email for verification',
  `expires_at` datetime NOT NULL COMMENT 'Token expiration date and time (usually 1 hour)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When reset request was made'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores password reset requests and tokens';

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Project name',
  `description` text COMMENT 'Detailed project description',
  `location` varchar(255) DEFAULT NULL COMMENT 'Physical location of the project',
  `manager` varchar(100) DEFAULT NULL COMMENT 'Name of project manager',
  `start_date` date NOT NULL COMMENT 'Project start date',
  `end_date` date DEFAULT NULL COMMENT 'Estimated completion date',
  `status` enum('planning','active','paused','completed') DEFAULT 'planning' COMMENT 'Current project status',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores construction projects information';

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `location`, `manager`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DVN - Rochdale Road', 'This is a sample project to demonstrate the system.', 'Manchester', 'Chris Irlam', '2025-05-28', '2025-11-28', 'active', '2025-05-28 15:42:10', '2025-05-29 20:00:54');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE `remember_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'User associated with this token',
  `token` varchar(64) NOT NULL COMMENT 'Secure random token for cookie authentication',
  `expires_at` datetime NOT NULL COMMENT 'Token expiration date and time',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When token was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores "remember me" authentication tokens';

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, 'b38331c87e539b8501789f993d952f58cadd96a4971776ff471eb5f259e8fecd', '2025-06-28 13:53:01', '2025-05-29 12:53:01');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int NOT NULL,
  `briefing_id` int NOT NULL COMMENT 'Briefing this resource allocation belongs to',
  `name` varchar(100) NOT NULL COMMENT 'Name of the resource',
  `type` enum('personnel','equipment','material','other') NOT NULL COMMENT 'Type of resource',
  `location` varchar(100) DEFAULT NULL COMMENT 'Location where the resource will be used',
  `assigned_to` varchar(100) DEFAULT NULL COMMENT 'Person or team the resource is assigned to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores resource allocations for daily briefings';

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `briefing_id`, `name`, `type`, `location`, `assigned_to`) VALUES
(705, 10, '7', '', 'block 1', ''),
(708, 11, 'Worker 1', 'personnel', 'block 1', ''),
(709, 11, '', '', 'block 1', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL COMMENT 'Unique login username',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `name` varchar(100) NOT NULL COMMENT 'User full name',
  `email` varchar(100) NOT NULL COMMENT 'User email address',
  `role` enum('admin','manager','user') DEFAULT 'user' COMMENT 'User permission level',
  `last_login` datetime DEFAULT NULL COMMENT 'Last successful login timestamp',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last profile update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores user accounts and authentication details';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$W82fChXlaYEhJtcz1t/hHOA7tRSC4m9U3NLM.7/ehCEv8YKnCyErO', 'System Admin', 'admin@defecttracker.uk', 'admin', '2025-06-24 18:41:26', '2025-05-28 15:42:10', '2025-06-24 17:41:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activities_briefing_area` (`briefing_id`,`area`),
  ADD KEY `idx_date_project` (`date`,`briefing_id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `briefings`
--
ALTER TABLE `briefings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`,`date`) COMMENT 'Only one briefing per project per day',
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `dabs_attendees`
--
ALTER TABLE `dabs_attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendee` (`project_id`,`briefing_date`,`attendee_name`),
  ADD KEY `idx_attendees_date_project` (`briefing_date`,`project_id`),
  ADD KEY `idx_attendees_subcontractor` (`subcontractor_name`);

--
-- Indexes for table `dabs_notes`
--
ALTER TABLE `dabs_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`,`note_date`);

--
-- Indexes for table `dabs_subcontractors`
--
ALTER TABLE `dabs_subcontractors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subcontractors_project` (`project_id`,`name`);

--
-- Indexes for table `dabs_subcontractor_tasks`
--
ALTER TABLE `dabs_subcontractor_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcontractor_id` (`subcontractor_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`attempt_time`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`) COMMENT 'One active reset request per user',
  ADD KEY `token` (`token`) COMMENT 'Index for faster token lookups';

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`) COMMENT 'One active remember token per user',
  ADD KEY `token` (`token`) COMMENT 'Index for faster token lookups';

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resources_briefing_location` (`briefing_id`,`location`,`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `briefings`
--
ALTER TABLE `briefings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `dabs_attendees`
--
ALTER TABLE `dabs_attendees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `dabs_notes`
--
ALTER TABLE `dabs_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dabs_subcontractors`
--
ALTER TABLE `dabs_subcontractors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `dabs_subcontractor_tasks`
--
ALTER TABLE `dabs_subcontractor_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=710;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`briefing_id`) REFERENCES `briefings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `briefings`
--
ALTER TABLE `briefings`
  ADD CONSTRAINT `briefings_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `briefings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `briefings_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dabs_attendees`
--
ALTER TABLE `dabs_attendees`
  ADD CONSTRAINT `dabs_attendees_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dabs_subcontractor_tasks`
--
ALTER TABLE `dabs_subcontractor_tasks`
  ADD CONSTRAINT `dabs_subcontractor_tasks_ibfk_1` FOREIGN KEY (`subcontractor_id`) REFERENCES `dabs_subcontractors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`briefing_id`) REFERENCES `briefings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
