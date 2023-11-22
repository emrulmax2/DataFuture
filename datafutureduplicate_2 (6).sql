-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 21, 2023 at 08:34 AM
-- Server version: 5.7.36
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `datafutureduplicate_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE IF NOT EXISTS `academic_years` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hesa_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `df_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `target_date_hesa_report` date DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `from_date`, `to_date`, `target_date_hesa_report`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2021 - 2022', '0', NULL, '0', NULL, '2021-09-01', '2022-04-30', '2023-05-31', 1, NULL, NULL, '2023-01-10 08:46:32', '2023-10-05 06:23:40'),
(2, '2022 - 2023', '1', '3', '1', '4', '2023-05-01', '2023-12-31', '2022-02-28', 1, 1, NULL, '2023-01-12 04:23:40', '2023-10-05 06:23:29'),
(3, '2023 - 2024', '1', '1', '1', '2', '2023-10-01', '2023-10-31', '2023-11-30', 1, NULL, NULL, '2023-10-05 06:23:07', '2023-10-05 06:23:07');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `address_line_1` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_student_user_id_foreign` (`student_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `address_line_1`, `address_line_2`, `state`, `post_code`, `city`, `country`, `active`, `student_user_id`, `created_at`, `created_by`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 'London Churchill College', '116 Cavell Street', NULL, 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-28 04:14:18', 1, NULL, '2023-09-28 04:14:18', NULL),
(2, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-28 04:14:18', 1, NULL, '2023-09-28 04:14:18', NULL),
(3, 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 1, NULL, '2023-09-28 04:14:18', 1, NULL, '2023-09-28 04:14:18', NULL),
(4, 'London Churchill College', '116 Cavell Street', NULL, 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-28 08:09:39', 1, 1, '2023-09-29 05:29:23', NULL),
(5, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-28 08:09:39', 1, NULL, '2023-09-28 08:09:39', NULL),
(6, 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 0, NULL, '2023-09-28 08:09:39', 1, 1, '2023-09-29 08:22:23', NULL),
(17, 'Dahabshiil Transfer Services Ltd', '118 Cavell Street', NULL, 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-29 07:00:55', 1, NULL, '2023-09-29 07:00:55', NULL),
(19, 'London Churchill College', '116 Cavell Street', 'England', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-29 08:22:23', 1, NULL, '2023-09-29 08:22:23', NULL),
(20, 'Flat 11', '37 Cavell Street', NULL, 'E1 2BP', 'London', 'United Kingdom', 0, NULL, '2023-09-29 08:38:49', 1, 1, '2023-09-29 08:59:21', NULL),
(21, 'London Churchill College', '116 Cavell Street', NULL, 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-09-29 08:59:21', 1, NULL, '2023-09-29 08:59:21', NULL),
(22, 'London Churchill College', '116 Cavell Street', 'NULL', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-16 05:45:17', 1, NULL, '2023-10-16 05:45:17', NULL),
(23, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-16 05:45:17', 1, NULL, '2023-10-16 05:45:17', NULL),
(24, 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 1, NULL, '2023-10-16 05:45:17', 1, NULL, '2023-10-16 05:45:17', NULL),
(25, 'London Churchill College', '116 Cavell Street', 'NULL', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-17 09:58:02', 1, NULL, '2023-10-17 09:58:02', NULL),
(26, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-17 09:58:02', 1, NULL, '2023-10-17 09:58:02', NULL),
(27, 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 1, NULL, '2023-10-17 09:58:02', 1, NULL, '2023-10-17 09:58:02', NULL),
(28, 'London Churchill College', '116 Cavell Street', 'NULL', 'E1 2JA', 'London', 'United Kingdom', 0, 3, '2023-10-18 05:41:59', 1, NULL, '2023-10-19 08:54:50', NULL),
(29, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-18 05:41:59', 1, NULL, '2023-10-18 05:41:59', NULL),
(30, 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 1, NULL, '2023-10-18 05:42:00', 1, NULL, '2023-10-18 05:42:00', NULL),
(32, 'Dahabshiil Transfer Services Ltd', '118 Cavell Street', NULL, 'E1 2JA', 'London', 'United Kingdom', 1, NULL, '2023-10-19 08:54:51', NULL, NULL, '2023-10-19 08:54:51', NULL),
(33, 'Uttara, Dhaka', NULL, 'Dhaka', '1230', 'Dhaka', 'Bangladesh', 1, NULL, '2023-10-25 09:37:44', NULL, NULL, '2023-10-25 09:37:44', NULL),
(34, 'Uttara, Dhaka', NULL, 'Dhaka', '1230', 'Dhaka', 'Bangladesh', 1, NULL, '2023-10-25 09:37:44', NULL, NULL, '2023-10-25 09:37:44', NULL),
(35, 'Uttara, Dhaka', NULL, 'Dhaka', '1230', 'Dhaka', 'Bangladesh', 1, NULL, '2023-11-06 03:47:51', NULL, NULL, '2023-11-06 03:47:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `apel_credits`
--

DROP TABLE IF EXISTS `apel_credits`;
CREATE TABLE IF NOT EXISTS `apel_credits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apel_credits`
--

INSERT INTO `apel_credits` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test', 1, '4', 1, '2', 1, 1, 1, '2023-09-28 04:08:54', '2023-09-28 04:09:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE IF NOT EXISTS `applicants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_user_id` bigint(20) UNSIGNED NOT NULL,
  `application_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `sex_identifier_id` bigint(20) UNSIGNED NOT NULL,
  `submission_date` date DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `rejected_reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality_id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_referral_varified` tinyint(4) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicants_applicant_user_id_foreign` (`applicant_user_id`),
  KEY `applicants_title_id_foreign` (`title_id`),
  KEY `applicants_status_id_foreign` (`status_id`),
  KEY `applicants_nationality_id_foreign` (`nationality_id`),
  KEY `applicants_country_id_foreign` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `applicant_user_id`, `application_no`, `title_id`, `first_name`, `last_name`, `photo`, `date_of_birth`, `sex_identifier_id`, `submission_date`, `status_id`, `rejected_reason`, `nationality_id`, `country_id`, `referral_code`, `is_referral_varified`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '10000001', 2, 'Abdul', 'Latif', '1695140261_1-5.jpg', '1986-11-25', 2, '2023-04-12', 7, NULL, 1, 1, 'NUKZXMENA', 1, 1, 1, '2023-04-10 07:44:45', '2023-10-18 05:41:58', NULL),
(4, 1, '10000004', 2, 'Abdul', 'Latif', NULL, '1986-11-26', 2, NULL, 1, NULL, 1, 1, '9I3MKYQZP', 1, 1, 1, '2023-04-12 07:07:21', '2023-10-17 08:56:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_archives`
--

DROP TABLE IF EXISTS `applicant_archives`;
CREATE TABLE IF NOT EXISTS `applicant_archives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `table` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_value` text COLLATE utf8mb4_unicode_ci,
  `field_new_value` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_archives_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_archives`
--

INSERT INTO `applicant_archives` (`id`, `applicant_id`, `table`, `field_name`, `field_value`, `field_new_value`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'applicants', 'date_of_birth', '26-11-1986', '1986-11-25', 1, NULL, '2023-04-18 10:11:03', '2023-04-18 10:11:03', NULL),
(2, 1, 'applicant_other_details', 'ethnicity_id', '2', '1', 1, NULL, '2023-04-18 10:11:03', '2023-04-18 10:11:03', NULL),
(3, 1, 'applicant_disabilities', 'disabilitiy_id', '1,2', '1,2,3', 1, NULL, '2023-04-18 10:11:04', '2023-04-18 10:11:04', NULL),
(5, 1, 'applicant_contacts', 'home', '01740149260', '01740149261', 1, NULL, '2023-04-20 03:52:55', '2023-04-20 03:52:55', NULL),
(6, 1, 'applicant_contacts', 'mobile', '01740149262', '01740149263', 1, NULL, '2023-04-20 03:52:55', '2023-04-20 03:52:55', NULL),
(7, 1, 'applicant_contacts', 'address_line_1', 'London Churchill Colleges', 'London Churchill College', 1, NULL, '2023-04-20 03:52:55', '2023-04-20 03:52:55', NULL),
(8, 1, 'applicant_contacts', 'home', '01740149261', '01740149260', 1, NULL, '2023-04-20 03:54:19', '2023-04-20 03:54:19', NULL),
(9, 1, 'applicant_kin', 'name', 'Lutfors', 'Lutfor', 1, NULL, '2023-04-20 04:25:20', '2023-04-20 04:25:20', NULL),
(10, 1, 'applicant_kin', 'mobile', '01770878718', '01770878719', 1, NULL, '2023-04-20 04:25:20', '2023-04-20 04:25:20', NULL),
(11, 1, 'applicant_kin', 'email', 'lutfors@churchill.ac', 'lutfor@churchill.ac', 1, NULL, '2023-04-20 04:25:20', '2023-04-20 04:25:20', NULL),
(12, 1, 'applicant_kin', 'address_line_1', 'Uttara Consulting Ltds', 'Uttara Consulting Ltd', 1, NULL, '2023-04-20 04:25:20', '2023-04-20 04:25:20', NULL),
(13, 1, 'applicant_other_details', 'disabilty_allowance', '1', '0', 1, NULL, '2023-04-20 04:55:24', '2023-04-20 04:55:24', NULL),
(14, 1, 'applicant_disabilities', 'disabilitiy_id', '1,2,3', '1', 1, NULL, '2023-04-20 04:55:24', '2023-04-20 04:55:24', NULL),
(15, 1, 'applicant_proposed_courses', 'fund_receipt', '1', NULL, 1, NULL, '2023-04-20 05:35:55', '2023-04-20 05:35:55', NULL),
(16, 1, 'applicant_proposed_courses', 'applied_received_fund', '1', NULL, 1, NULL, '2023-04-20 05:35:55', '2023-04-20 05:35:55', NULL),
(17, 1, 'applicant_proposed_courses', 'student_loan', 'Student Loan', 'Others', 1, NULL, '2023-04-20 05:36:17', '2023-04-20 05:36:17', NULL),
(18, 1, 'applicant_proposed_courses', 'student_finance_england', '1', NULL, 1, NULL, '2023-04-20 05:36:17', '2023-04-20 05:36:17', NULL),
(19, 1, 'applicant_proposed_courses', 'other_funding', NULL, 'Self Funding', 1, NULL, '2023-04-20 05:36:17', '2023-04-20 05:36:17', NULL),
(20, 1, 'applicant_other_details', 'is_edication_qualification', '1', '0', 1, NULL, '2023-04-20 08:48:26', '2023-04-20 08:48:26', NULL),
(21, 1, 'applicant_other_details', 'is_edication_qualification', '0', '1', 1, NULL, '2023-04-20 08:51:01', '2023-04-20 08:51:01', NULL),
(22, 1, 'applicant_other_details', 'employment_status', 'Part Time', 'Fixed Term', 1, NULL, '2023-04-28 04:12:01', '2023-04-28 04:12:01', NULL),
(23, 1, 'applicant_other_details', 'employment_status', 'Fixed Term', 'Contractor', 1, NULL, '2023-04-28 04:12:17', '2023-04-28 04:12:17', NULL),
(24, 1, 'applicant_other_details', 'employment_status', 'Contractor', 'Part Time', 1, NULL, '2023-04-28 04:12:44', '2023-04-28 04:12:44', NULL),
(25, 1, 'applicant_users', 'email', 'limon@churchill.ac', 'themewar@gmail.com', 1, NULL, '2023-05-02 07:47:56', '2023-05-02 07:47:56', NULL),
(26, 1, 'applicant_users', 'email', 'limon@churchill.ac', 'themewar@gmail.com', 1, NULL, '2023-05-02 08:17:20', '2023-05-02 08:17:20', NULL),
(27, 1, 'applicants', 'photo', '1686829627_1.jpg', '1686829687_2.png', 1, NULL, '2023-06-15 05:48:07', '2023-06-15 05:48:07', NULL),
(28, 1, 'applicants', 'photo', '1686829687_2.png', '1693389453_1-5.jpg', 1, NULL, '2023-08-30 03:57:33', '2023-08-30 03:57:33', NULL),
(29, 1, 'applicants', 'status_id', '2', '3', 1, NULL, '2023-08-30 05:33:09', '2023-08-30 05:33:09', NULL),
(30, 1, 'applicants', 'status_id', '3', '4', 1, NULL, '2023-08-30 06:02:55', '2023-08-30 06:02:55', NULL),
(31, 1, 'applicants', 'status_id', '4', '3', 1, NULL, '2023-08-30 06:04:36', '2023-08-30 06:04:36', NULL),
(32, 1, 'applicants', 'status_id', '4', '8', 1, NULL, '2023-08-31 09:10:16', '2023-08-31 09:10:16', NULL),
(33, 1, 'applicants', 'rejected_reason', NULL, 'Wrong Information', 1, NULL, '2023-08-31 09:10:16', '2023-08-31 09:10:16', NULL),
(34, 1, 'applicants', 'status_id', '4', '5', 1, NULL, '2023-08-31 09:10:41', '2023-08-31 09:10:41', NULL),
(35, 1, 'applicants', 'rejected_reason', 'Wrong Information', NULL, 1, NULL, '2023-08-31 09:10:41', '2023-08-31 09:10:41', NULL),
(36, 1, 'applicants', 'status_id', '4', '5', 1, NULL, '2023-09-01 04:59:22', '2023-09-01 04:59:22', NULL),
(37, 1, 'applicants', 'status_id', '4', '5', 1, NULL, '2023-09-04 10:59:38', '2023-09-04 10:59:38', NULL),
(38, 1, 'applicants', 'status_id', '5', '6', 1, NULL, '2023-09-11 05:22:06', '2023-09-11 05:22:06', NULL),
(39, 1, 'applicants', 'proof_type', NULL, 'passport', 1, NULL, '2023-09-11 07:53:19', '2023-09-11 07:53:19', NULL),
(40, 1, 'applicant_disabilities', 'disabilitiy_id', '1', '1', 1, NULL, '2023-09-11 07:53:19', '2023-09-11 07:53:19', NULL),
(41, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-12 04:39:05', '2023-09-12 04:39:05', NULL),
(42, 1, 'applicants', 'proof_type', NULL, 'passport', 1, NULL, '2023-09-12 04:39:05', '2023-09-12 04:39:05', NULL),
(43, 1, 'applicants', 'proof_id', NULL, '324532453245', 1, NULL, '2023-09-12 04:39:05', '2023-09-12 04:39:05', NULL),
(44, 1, 'applicants', 'proof_expiredate', '', '2025-12-31', 1, NULL, '2023-09-12 04:39:05', '2023-09-12 04:39:05', NULL),
(45, 1, 'applicant_disabilities', 'disabilitiy_id', '1', '1', 1, NULL, '2023-09-14 07:23:07', '2023-09-14 07:23:07', NULL),
(46, 1, 'applicant_disabilities', 'disabilitiy_id', '1', '1', 1, NULL, '2023-09-14 07:29:49', '2023-09-14 07:29:49', NULL),
(47, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-14 08:25:18', '2023-09-14 08:25:18', NULL),
(48, 1, 'applicants', 'photo', '1693389453_1-5.jpg', '1695111752_download.png', 1, NULL, '2023-09-19 02:22:32', '2023-09-19 02:22:32', NULL),
(49, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-19 04:59:37', '2023-09-19 04:59:37', NULL),
(50, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-19 05:38:08', '2023-09-19 05:38:08', NULL),
(51, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-19 09:41:59', '2023-09-19 09:41:59', NULL),
(52, 1, 'applicants', 'photo', '1695111752_download.png', '1695140261_1-5.jpg', 1, NULL, '2023-09-19 10:17:41', '2023-09-19 10:17:41', NULL),
(53, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-20 02:49:26', '2023-09-20 02:49:26', NULL),
(54, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-28 03:52:46', '2023-09-28 03:52:46', NULL),
(55, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-28 04:14:19', '2023-09-28 04:14:19', NULL),
(56, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-09-28 08:09:40', '2023-09-28 08:09:40', NULL),
(57, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-10-16 05:45:18', '2023-10-16 05:45:18', NULL),
(58, 1, 'applicants', 'sex_identifier_id', '2', '1', 1, NULL, '2023-10-17 09:12:36', '2023-10-17 09:12:36', NULL),
(59, 1, 'applicant_disabilities', 'disabilitiy_id', '1', '1', 1, NULL, '2023-10-17 09:12:36', '2023-10-17 09:12:36', NULL),
(60, 1, 'applicants', 'sex_identifier_id', '1', '2', 1, NULL, '2023-10-17 09:13:33', '2023-10-17 09:13:33', NULL),
(61, 1, 'applicant_disabilities', 'disabilitiy_id', '1', '1', 1, NULL, '2023-10-17 09:13:33', '2023-10-17 09:13:33', NULL),
(62, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-10-17 09:58:03', '2023-10-17 09:58:03', NULL),
(63, 1, 'applicants', 'status_id', '6', '7', 1, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_contacts`
--

DROP TABLE IF EXISTS `applicant_contacts`;
CREATE TABLE IF NOT EXISTS `applicant_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `permanent_country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `home` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_verification` tinyint(4) DEFAULT '0',
  `address_line_1` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permanent_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_contacts_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_contacts`
--

INSERT INTO `applicant_contacts` (`id`, `applicant_id`, `country_id`, `permanent_country_id`, `home`, `mobile`, `mobile_verification`, `address_line_1`, `address_line_2`, `state`, `post_code`, `permanent_post_code`, `city`, `country`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, NULL, '01740149260', '01740149263', 0, 'London Churchill College', '116 Cavell Street', NULL, 'E1 2JA', NULL, 'London', 'United Kingdom', 1, 1, '2023-04-10 07:44:45', '2023-04-20 03:54:19', NULL),
(2, 4, NULL, NULL, '01740149260', '01740149260', 0, '118 Cavalry Drive', NULL, 'Cambridgeshire', 'PE15 9DP', NULL, 'March', 'United Kingdom', 1, 1, '2023-04-12 07:07:21', '2023-04-12 07:07:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_disabilities`
--

DROP TABLE IF EXISTS `applicant_disabilities`;
CREATE TABLE IF NOT EXISTS `applicant_disabilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `disabilitiy_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_disabilities_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_disabilities_disabilitiy_id_foreign` (`disabilitiy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=443 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_disabilities`
--

INSERT INTO `applicant_disabilities` (`id`, `applicant_id`, `disabilitiy_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(439, 4, 1, 1, NULL, '2023-10-17 08:56:46', '2023-10-17 08:56:46', NULL),
(440, 4, 2, 1, NULL, '2023-10-17 08:56:46', '2023-10-17 08:56:46', NULL),
(442, 1, 1, 1, NULL, '2023-10-17 09:13:33', '2023-10-17 09:13:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_documents`
--

DROP TABLE IF EXISTS `applicant_documents`;
CREATE TABLE IF NOT EXISTS `applicant_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `document_setting_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hard_copy_check` tinyint(4) DEFAULT NULL,
  `doc_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_documents_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_documents`
--

INSERT INTO `applicant_documents` (`id`, `applicant_id`, `document_setting_id`, `hard_copy_check`, `doc_type`, `disk_type`, `path`, `display_file_name`, `current_file_name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359380_h1.jpg', '1685359380_h1.jpg', '1685359380_h1.jpg', 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(2, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359382_10.jpg', '1685359382_10.jpg', '1685359382_10.jpg', 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(3, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359383_9.jpg', '1685359383_9.jpg', '1685359383_9.jpg', 1, NULL, NULL, '2023-05-29 05:23:03', '2023-05-29 05:23:03'),
(4, 1, NULL, 0, 'xlsx', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', '1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', '1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', 1, NULL, NULL, '2023-05-29 06:28:34', '2023-05-29 06:28:34'),
(5, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', '1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', '1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', 1, NULL, NULL, '2023-05-31 07:11:56', '2023-05-31 07:11:56'),
(8, 1, 2, 0, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685715906_screencapture-themeforest-net-user-themewar-portfolio-2023-03-31-20_48_35.png', 'Blood Group Test', '1685715906_screencapture-themeforest-net-user-themewar-portfolio-2023-03-31-20_48_35.png', 1, NULL, NULL, '2023-06-02 08:25:06', '2023-06-02 08:25:06'),
(9, 1, 2, 1, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685715951_20770013-slider-revolution-responsive-wordpress-plugin-license.pdf', 'Blood Group Test', '1685715951_20770013-slider-revolution-responsive-wordpress-plugin-license.pdf', 1, NULL, NULL, '2023-06-02 08:25:51', '2023-06-02 08:25:51'),
(10, 1, 1, 1, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685715989_20770013-slider-revolution-responsive-wordpress-plugin-license.pdf', 'Passport', '1685715989_20770013-slider-revolution-responsive-wordpress-plugin-license.pdf', 1, NULL, NULL, '2023-06-02 08:26:29', '2023-06-02 08:26:29'),
(11, 1, 1, 1, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685715989_shape.png', 'Passport', '1685715989_shape.png', 1, NULL, NULL, '2023-06-02 08:26:29', '2023-06-02 08:26:29'),
(12, 1, 1, 1, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685715990_May 2023.png', 'Passport', '1685715990_May 2023.png', 1, NULL, NULL, '2023-06-02 08:26:30', '2023-06-02 08:26:30'),
(13, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685968623_SobujCv-9.pdf', 'Academic Qualifications', '1685968623_SobujCv-9.pdf', 1, NULL, NULL, '2023-06-05 06:37:04', '2023-06-05 07:13:19'),
(14, 1, 1, 1, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686057359_01.Preview.__large_preview.jpg', 'Passport', '1686057359_01.Preview.__large_preview.jpg', 1, NULL, NULL, '2023-06-06 07:15:59', '2023-06-06 07:15:59'),
(16, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686138978_FYmAddwXoAEssnm.jpg', '1686138978_FYmAddwXoAEssnm.jpg', '1686138978_FYmAddwXoAEssnm.jpg', 1, NULL, NULL, '2023-06-07 05:56:19', '2023-06-07 05:56:19'),
(18, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686147600_FcOhZKCXEAALQSK.jpg', '1686147600_FcOhZKCXEAALQSK.jpg', '1686147600_FcOhZKCXEAALQSK.jpg', 1, NULL, NULL, '2023-06-07 08:20:00', '2023-06-07 08:20:00'),
(19, 1, NULL, 0, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', 1, NULL, NULL, '2023-06-07 08:28:11', '2023-06-07 08:43:56'),
(22, 1, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1687257043_Javed_Hussain.jpg', '1687257043_Javed_Hussain.jpg', '1687257043_Javed_Hussain.jpg', 1, NULL, NULL, '2023-06-20 04:30:44', '2023-06-20 05:52:55'),
(23, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1687257045_230601 College Oversight Board BioPic V4.pdf', '1687257045_230601 College Oversight Board BioPic V4.pdf', '1687257045_230601 College Oversight Board BioPic V4.pdf', 1, NULL, NULL, '2023-06-20 04:30:45', '2023-06-20 05:52:55'),
(24, 1, NULL, NULL, 'jpg', NULL, 'http://127.0.0.1:8000/storage/interviewresult/1692890555_1_2.jpg', '1692890555_1_2.jpg', '1692890555_1_2.jpg', 1, NULL, NULL, '2023-08-24 09:22:35', '2023-08-24 09:22:35'),
(25, 1, NULL, NULL, 'jpg', NULL, 'http://127.0.0.1:8000/storage/interviewresult/1692890589_1_2.jpg', '1692890589_1_2.jpg', '1692890589_1_2.jpg', 1, NULL, NULL, '2023-08-24 09:23:09', '2023-08-24 09:23:09'),
(26, 1, NULL, NULL, 'png', NULL, 'http://127.0.0.1:8000/storage/interviewresult/1692890684_Screenshot 2020-04-15 at 18.45.13.png', '1692890684_Screenshot 2020-04-15 at 18.45.13.png', '1692890684_Screenshot 2020-04-15 at 18.45.13.png', 1, NULL, NULL, '2023-08-24 09:24:44', '2023-08-24 09:24:44'),
(28, 1, NULL, NULL, 'png', NULL, 'http://127.0.0.1:8000/storage/interviewresult/1692897512_Location.png', '1692897512_Location.png', '1692897512_Location.png', 1, NULL, NULL, '2023-08-24 11:18:32', '2023-08-24 11:18:32'),
(29, 1, NULL, NULL, 'png', NULL, 'http://127.0.0.1:8000/storage/interviewresult/1692897650_Location.png', '1692897650_Location.png', '1692897650_Location.png', 1, NULL, NULL, '2023-08-24 11:20:50', '2023-08-24 11:20:50'),
(31, 1, NULL, NULL, 'jpg', NULL, 'http://localhost:8000/storage/interviewresult/1692975076_IMG-20230404-WA0018.jpg', '1692975076_IMG-20230404-WA0018.jpg', '1692975076_IMG-20230404-WA0018.jpg', 1, NULL, NULL, '2023-08-25 08:51:16', '2023-08-25 08:51:16'),
(32, 1, NULL, NULL, 'png', NULL, 'http://localhost:8000/storage/interviewresult/1692977581_Seal.png', '1692977581_Seal.png', '1692977581_Seal.png', 1, NULL, NULL, '2023-08-25 09:33:01', '2023-08-25 09:33:01'),
(33, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396885_Abdul_Latif_profile_5.pdf', 'English Test', '1693396885_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:01:26', '2023-08-30 06:01:26'),
(34, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396910_Abdul_Latif_profile_5.pdf', 'Social Number', '1693396910_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:01:50', '2023-08-30 06:01:50'),
(35, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396945_Abdul_Latif_profile_5.pdf', 'Interview', '1693396945_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:02:25', '2023-08-30 06:02:25'),
(36, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693400892_1_Letter.pdf', 'Welcome Message For New Students.', '1693400892_1_Letter.pdf', 1, NULL, NULL, '2023-08-30 07:09:16', '2023-08-30 07:09:16'),
(37, 1, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693848958_1693400892_1_Letter.pdf', 'Passport', '1693848958_1693400892_1_Letter.pdf', 1, NULL, NULL, '2023-09-04 11:36:00', '2023-09-04 11:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_emails`
--

DROP TABLE IF EXISTS `applicant_emails`;
CREATE TABLE IF NOT EXISTS `applicant_emails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `email_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comon_smtp_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_emails_email_template_id_foreign` (`email_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_emails`
--

INSERT INTO `applicant_emails` (`id`, `applicant_id`, `email_template_id`, `comon_smtp_id`, `subject`, `body`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(2, 1, NULL, 3, 'Welcome to LCC', '<h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><h2>Why do we use it?</h2><p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>', 1, NULL, NULL, '2023-06-20 04:30:43', '2023-06-20 05:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_emails_attachments`
--

DROP TABLE IF EXISTS `applicant_emails_attachments`;
CREATE TABLE IF NOT EXISTS `applicant_emails_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_email_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_document_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_emails_attachments`
--

INSERT INTO `applicant_emails_attachments` (`id`, `applicant_email_id`, `applicant_document_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 2, 22, 1, NULL, NULL, '2023-06-20 04:30:45', '2023-06-20 04:30:45'),
(4, 2, 23, 1, NULL, NULL, '2023-06-20 04:30:45', '2023-06-20 04:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_employments`
--

DROP TABLE IF EXISTS `applicant_employments`;
CREATE TABLE IF NOT EXISTS `applicant_employments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_phone` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_date` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continuing` tinyint(4) NOT NULL DEFAULT '0',
  `address_line_1` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_employments_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_employments`
--

INSERT INTO `applicant_employments` (`id`, `applicant_id`, `company_name`, `company_phone`, `position`, `start_date`, `end_date`, `continuing`, `address_line_1`, `address_line_2`, `state`, `post_code`, `city`, `country`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'ThemeWar', '01740149260', 'Sr Developers', '03-2023', '04-2023', 0, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', 1, 1, '2023-04-11 03:26:56', '2023-04-28 04:42:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_fee_eligibilities`
--

DROP TABLE IF EXISTS `applicant_fee_eligibilities`;
CREATE TABLE IF NOT EXISTS `applicant_fee_eligibilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `fee_eligibility_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_fee_eligibilities_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_fee_eligibilities_fee_eligibility_id_foreign` (`fee_eligibility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_fee_eligibilities`
--

INSERT INTO `applicant_fee_eligibilities` (`id`, `applicant_id`, `fee_eligibility_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, 1, 1, '2023-09-14 08:25:18', '2023-09-14 09:00:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_interviews`
--

DROP TABLE IF EXISTS `applicant_interviews`;
CREATE TABLE IF NOT EXISTS `applicant_interviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applicant_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `interview_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `interview_result` enum('Pass','Fail','Unattainded','N/A') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interview_status` enum('In progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'In progress',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_interviews_user_id_index` (`user_id`),
  KEY `applicant_interviews_applicant_id_index` (`applicant_id`),
  KEY `applicant_interviews_applicant_task_id_index` (`applicant_task_id`),
  KEY `applicant_interviews_applicant_document_id_index` (`applicant_document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_interviews`
--

INSERT INTO `applicant_interviews` (`id`, `user_id`, `applicant_id`, `applicant_task_id`, `applicant_document_id`, `interview_date`, `start_time`, `end_time`, `interview_result`, `interview_status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 7, 31, '2023-08-25', '14:50:00', '14:50:00', 'Fail', 'Completed', 1, 1, NULL, '2023-08-25 08:50:26', '2023-08-25 08:51:53'),
(2, 1, 1, 7, 32, '2023-08-25', '15:32:00', '15:32:00', 'Pass', 'Completed', 1, 1, NULL, '2023-08-25 09:31:50', '2023-08-25 09:33:10');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_kin`
--

DROP TABLE IF EXISTS `applicant_kin`;
CREATE TABLE IF NOT EXISTS `applicant_kin` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kins_relation_id` bigint(20) UNSIGNED NOT NULL,
  `mobile` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_kin_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_kin_kins_relation_id_foreign` (`kins_relation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_kin`
--

INSERT INTO `applicant_kin` (`id`, `applicant_id`, `name`, `kins_relation_id`, `mobile`, `email`, `address_line_1`, `address_line_2`, `state`, `post_code`, `city`, `country`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Lutfor', 1, '01770878719', 'lutfor@churchill.ac', 'Uttara Consulting Ltd', '13 Hawkswell Close', 'Surrey', 'GU21 3RS', 'Woking', 'United Kingdom', 1, 1, '2023-04-10 07:44:45', '2023-04-20 04:25:20', NULL),
(2, 4, 'Abdul Latif', 8, '01740149260', 'limon@churchill.ac', '110 Cavell Road', 'Cheshunt', 'Hertfordshire', 'EN7 6JL', 'Waltham Cross', 'United Kingdom', 1, 1, '2023-04-12 07:07:21', '2023-10-17 08:56:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_letters`
--

DROP TABLE IF EXISTS `applicant_letters`;
CREATE TABLE IF NOT EXISTS `applicant_letters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `letter_set_id` bigint(20) UNSIGNED NOT NULL,
  `pin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comon_smtp_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_email_or_attachment` tinyint(4) NOT NULL DEFAULT '1',
  `applicant_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED NOT NULL,
  `issued_date` date NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_letters_comon_smtp_id_foreign` (`comon_smtp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_letters`
--

INSERT INTO `applicant_letters` (`id`, `applicant_id`, `letter_set_id`, `pin`, `signatory_id`, `comon_smtp_id`, `is_email_or_attachment`, `applicant_document_id`, `issued_by`, `issued_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, NULL, 1, NULL, 1, '2023-07-25', 1, NULL, NULL, '2023-07-31 06:23:31', '2023-07-31 06:23:31'),
(2, 1, 1, NULL, 2, NULL, 1, NULL, 1, '2023-07-28', 1, NULL, NULL, '2023-07-31 06:28:54', '2023-07-31 07:42:54'),
(3, 1, 1, NULL, 1, NULL, 1, NULL, 1, '2023-08-31', 1, NULL, NULL, '2023-08-01 05:48:56', '2023-08-01 05:48:56'),
(4, 1, 1, NULL, 2, NULL, 2, 36, 1, '2023-08-27', 1, NULL, NULL, '2023-08-30 07:08:12', '2023-08-30 07:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_notes`
--

DROP TABLE IF EXISTS `applicant_notes`;
CREATE TABLE IF NOT EXISTS `applicant_notes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase` enum('Applicant','Admission','Register','Live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admission',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_notes`
--

INSERT INTO `applicant_notes` (`id`, `applicant_id`, `applicant_document_id`, `note`, `phase`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 16, '<p>CKEditor 5 allows you to retrieve the data from and save it to your server (or to your system in general) in various ways. In this guide, you can learn about the available options along with their pros and cons.</p>', 'Admission', 1, NULL, NULL, '2023-06-07 05:56:18', '2023-06-07 05:56:19'),
(2, 1, 18, '<p>This is the Second Note This one is updated</p>', 'Admission', 1, 1, NULL, '2023-06-07 05:59:16', '2023-06-07 08:20:00'),
(3, 1, 19, '<h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.&nbsp;</p><p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><ul><li>Lorem Ipsum has been the industry\'s standard dummy</li><li>Ipsum has been the industry\'s standard dummy</li><li>Lorem Ipsum has been the industry\'s</li></ul><p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>', 'Admission', 1, 1, NULL, '2023-06-07 06:00:14', '2023-06-07 08:54:27');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_other_details`
--

DROP TABLE IF EXISTS `applicant_other_details`;
CREATE TABLE IF NOT EXISTS `applicant_other_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `ethnicity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disability_status` tinyint(4) NOT NULL DEFAULT '0',
  `disabilty_allowance` tinyint(4) NOT NULL DEFAULT '0',
  `is_edication_qualification` tinyint(4) NOT NULL DEFAULT '0',
  `employment_status` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `college_introduction` enum('Self','Referred','Agent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hesa_gender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sexual_orientation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `religion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_other_details_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_other_details_ethnicity_id_foreign` (`ethnicity_id`),
  KEY `applicant_other_details_hesa_gender_id_foreign` (`hesa_gender_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_other_details`
--

INSERT INTO `applicant_other_details` (`id`, `applicant_id`, `ethnicity_id`, `disability_status`, `disabilty_allowance`, `is_edication_qualification`, `employment_status`, `college_introduction`, `hesa_gender_id`, `sexual_orientation_id`, `religion_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 0, 1, 'Part Time', NULL, NULL, NULL, NULL, 1, 1, '2023-04-10 07:44:45', '2023-04-28 04:12:44', NULL),
(2, 4, 1, 1, 0, 0, 'Unemployed', NULL, NULL, NULL, NULL, 1, 1, '2023-04-12 07:07:21', '2023-08-30 04:48:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_proof_of_ids`
--

DROP TABLE IF EXISTS `applicant_proof_of_ids`;
CREATE TABLE IF NOT EXISTS `applicant_proof_of_ids` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `proof_type` enum('passport','birth','driving','nid','respermit') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_expiredate` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_proof_of_ids_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_proof_of_ids`
--

INSERT INTO `applicant_proof_of_ids` (`id`, `applicant_id`, `proof_type`, `proof_id`, `proof_expiredate`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'passport', '234552345', '2025-09-01', 1, 1, '2023-09-14 07:23:07', '2023-09-19 04:29:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_proposed_courses`
--

DROP TABLE IF EXISTS `applicant_proposed_courses`;
CREATE TABLE IF NOT EXISTS `applicant_proposed_courses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_loan` enum('Independently/Private','Funding Body','Sponsor','Student Loan','Others') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Independently/Private',
  `student_finance_england` tinyint(4) DEFAULT NULL,
  `fund_receipt` tinyint(4) DEFAULT NULL,
  `applied_received_fund` tinyint(4) DEFAULT NULL,
  `other_funding` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_time` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_proposed_courses_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_proposed_courses_course_creation_id_foreign` (`course_creation_id`),
  KEY `applicant_proposed_courses_academic_year_id_foreign` (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_proposed_courses`
--

INSERT INTO `applicant_proposed_courses` (`id`, `applicant_id`, `course_creation_id`, `semester_id`, `academic_year_id`, `student_loan`, `student_finance_england`, `fund_receipt`, `applied_received_fund`, `other_funding`, `full_time`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 5, 4, 3, 'Others', NULL, NULL, NULL, 'Self Funding', 1, 1, 1, '2023-04-11 03:30:21', '2023-10-18 05:42:00', NULL),
(2, 4, 5, 4, NULL, 'Others', NULL, NULL, NULL, 'Self Funding', 1, 1, 1, '2023-04-11 03:30:21', '2023-04-20 05:36:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_qualifications`
--

DROP TABLE IF EXISTS `applicant_qualifications`;
CREATE TABLE IF NOT EXISTS `applicant_qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `awarding_body` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `highest_academic` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subjects` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_award_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_qualifications_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_qualifications`
--

INSERT INTO `applicant_qualifications` (`id`, `applicant_id`, `awarding_body`, `highest_academic`, `subjects`, `result`, `degree_award_date`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'SLC', 'HND', 'Netwarking', '3.75', '2023-04-01', 1, 1, '2023-04-11 03:25:43', '2023-04-27 09:36:39', NULL),
(2, 1, 'HNC', 'HNDS', 'Netwarking', '3.75', '2023-04-26', 1, NULL, '2023-04-11 03:26:00', '2023-04-27 09:36:57', NULL),
(3, 1, 'SLCS', 'SND', 'Netwarking', '3.5', '2023-04-30', 1, NULL, '2023-04-27 09:28:37', '2023-04-27 09:37:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_sms`
--

DROP TABLE IF EXISTS `applicant_sms`;
CREATE TABLE IF NOT EXISTS `applicant_sms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_sms`
--

INSERT INTO `applicant_sms` (`id`, `applicant_id`, `subject`, `sms`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'THis is test', 'asdf asdf asdf asdf asdf asdf', 1, NULL, NULL, '2023-06-20 03:35:34', '2023-06-20 06:00:44'),
(2, 1, 'THis is test', 'asdf asdf asdf sadf sadf', 1, NULL, NULL, '2023-06-20 03:35:56', '2023-06-20 03:35:56'),
(3, 1, 'asdf sadf', 'asdf asdf sadf sadf sdaf sadf', 1, NULL, NULL, '2023-06-20 03:36:38', '2023-06-20 03:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_tasks`
--

DROP TABLE IF EXISTS `applicant_tasks`;
CREATE TABLE IF NOT EXISTS `applicant_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `task_list_id` bigint(20) UNSIGNED NOT NULL,
  `external_link_ref` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','In Progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `task_status_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_tasks_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_tasks`
--

INSERT INTO `applicant_tasks` (`id`, `applicant_id`, `task_list_id`, `external_link_ref`, `status`, `task_status_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Pending', NULL, 1, 1, NULL, '2023-05-17 04:18:50', '2023-08-30 06:04:48'),
(3, 1, 4, NULL, 'Pending', NULL, 1, 1, NULL, '2023-05-17 04:19:00', '2023-08-30 06:05:20'),
(4, 1, 5, NULL, 'Completed', 1, 1, 1, NULL, '2023-05-17 04:21:51', '2023-08-30 06:01:38'),
(5, 1, 2, NULL, 'Pending', NULL, 1, 1, NULL, '2023-05-17 09:19:23', '2023-08-30 06:02:00'),
(6, 1, 6, NULL, 'Pending', NULL, 1, 1, NULL, '2023-05-17 09:25:15', '2023-08-30 06:05:07'),
(7, 1, 13, NULL, 'In Progress', 1, 1, 1, NULL, '2023-08-07 09:24:28', '2023-08-30 06:02:54'),
(8, 1, 14, NULL, 'Pending', NULL, 1, 1, NULL, '2023-08-30 05:33:09', '2023-08-30 06:04:36');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_task_documents`
--

DROP TABLE IF EXISTS `applicant_task_documents`;
CREATE TABLE IF NOT EXISTS `applicant_task_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_task_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_document_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_task_documents_applicant_task_id_foreign` (`applicant_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_task_documents`
--

INSERT INTO `applicant_task_documents` (`id`, `applicant_task_id`, `applicant_document_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(2, 1, 2, 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(3, 1, 3, 1, NULL, NULL, '2023-05-29 05:23:03', '2023-05-29 05:23:03'),
(4, 1, 4, 1, NULL, NULL, '2023-05-29 06:28:34', '2023-05-29 06:28:34'),
(5, 1, 5, 1, NULL, NULL, '2023-05-31 07:11:56', '2023-05-31 07:11:56'),
(6, 3, 13, 1, NULL, NULL, '2023-06-05 06:37:04', '2023-06-05 06:37:04'),
(7, 4, 33, 1, NULL, NULL, '2023-08-30 06:01:26', '2023-08-30 06:01:26'),
(8, 5, 34, 1, NULL, NULL, '2023-08-30 06:01:50', '2023-08-30 06:01:50'),
(9, 7, 35, 1, NULL, NULL, '2023-08-30 06:02:25', '2023-08-30 06:02:25'),
(10, 1, 37, 1, NULL, NULL, '2023-09-04 11:36:00', '2023-09-04 11:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_task_logs`
--

DROP TABLE IF EXISTS `applicant_task_logs`;
CREATE TABLE IF NOT EXISTS `applicant_task_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_tasks_id` bigint(20) UNSIGNED NOT NULL,
  `actions` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prev_field_value` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_field_value` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_task_logs`
--

INSERT INTO `applicant_task_logs` (`id`, `applicant_tasks_id`, `actions`, `field_name`, `prev_field_value`, `current_field_value`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', 1, NULL, NULL, '2023-05-31 07:11:56', '2023-05-31 07:11:56'),
(2, 1, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-05-31 07:12:48', '2023-05-31 07:12:48'),
(3, 1, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-05-31 07:13:05', '2023-05-31 07:13:05'),
(4, 4, 'Task Status', 'task_status_id', '2', '1', 1, NULL, NULL, '2023-05-31 07:16:18', '2023-05-31 07:16:18'),
(5, 5, 'Restore', '', '', 'Item Restored', 1, NULL, NULL, '2023-05-31 07:17:28', '2023-05-31 07:17:28'),
(6, 5, 'Delete', '', '', 'Item Deleted', 1, NULL, NULL, '2023-05-31 07:17:50', '2023-05-31 07:17:50'),
(7, 5, 'Restore', '', '', 'Item Restored', 1, NULL, NULL, '2023-05-31 07:18:03', '2023-05-31 07:18:03'),
(8, 1, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-05-31 08:26:07', '2023-05-31 08:26:07'),
(9, 3, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1685968623_SobujCv-9.pdf', 1, NULL, NULL, '2023-06-05 06:37:04', '2023-06-05 06:37:04'),
(10, 7, 'Delete', '', '', 'Item Deleted', 8, NULL, NULL, '2023-08-07 11:16:07', '2023-08-07 11:16:07'),
(11, 1, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 04:16:01', '2023-08-30 04:16:01'),
(12, 4, 'Task Status', 'task_status_id', NULL, '1', 1, NULL, NULL, '2023-08-30 04:33:20', '2023-08-30 04:33:20'),
(13, 8, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:00:56', '2023-08-30 06:00:56'),
(14, 3, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:01:09', '2023-08-30 06:01:09'),
(15, 4, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1693396885_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:01:26', '2023-08-30 06:01:26'),
(16, 4, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:01:38', '2023-08-30 06:01:38'),
(17, 5, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1693396910_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:01:50', '2023-08-30 06:01:50'),
(18, 5, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:02:00', '2023-08-30 06:02:00'),
(19, 6, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:02:09', '2023-08-30 06:02:09'),
(20, 7, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1693396945_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-08-30 06:02:25', '2023-08-30 06:02:25'),
(21, 7, 'Task Status', 'task_status_id', NULL, '1', 1, NULL, NULL, '2023-08-30 06:02:39', '2023-08-30 06:02:39'),
(22, 7, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-08-30 06:02:54', '2023-08-30 06:02:54'),
(23, 8, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-08-30 06:04:36', '2023-08-30 06:04:36'),
(24, 1, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-08-30 06:04:48', '2023-08-30 06:04:48'),
(25, 6, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-08-30 06:05:07', '2023-08-30 06:05:07'),
(26, 3, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-08-30 06:05:20', '2023-08-30 06:05:20'),
(27, 1, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1693848958_1693400892_1_Letter.pdf', 1, NULL, NULL, '2023-09-04 11:36:00', '2023-09-04 11:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_temporary_emails`
--

DROP TABLE IF EXISTS `applicant_temporary_emails`;
CREATE TABLE IF NOT EXISTS `applicant_temporary_emails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Active') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `activated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_temporary_emails_applicant_id_foreign` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_temporary_emails`
--

INSERT INTO `applicant_temporary_emails` (`id`, `applicant_id`, `email`, `status`, `activated_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 1, 'themewar@gmail.com', 'Pending', NULL, 1, NULL, '2023-05-02 08:23:04', '2023-05-02 08:23:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_users`
--

DROP TABLE IF EXISTS `applicant_users`;
CREATE TABLE IF NOT EXISTS `applicant_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` int(11) NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applicant_users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_users`
--

INSERT INTO `applicant_users` (`id`, `email`, `phone`, `email_verified_at`, `phone_verified_at`, `password`, `active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'limon@churchill.ac', NULL, '2023-04-04 11:58:59', NULL, '$2y$10$extam1NnT9P1aPoSWuyx/OtOct8v.D.2pJxfDpTysrBJ6sUbUnkyW', 0, NULL, '2023-04-04 05:54:37', '2023-05-02 08:17:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_view_unlocks`
--

DROP TABLE IF EXISTS `applicant_view_unlocks`;
CREATE TABLE IF NOT EXISTS `applicant_view_unlocks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expired_at` timestamp NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_view_unlocks_user_id_index` (`user_id`),
  KEY `applicant_view_unlocks_applicant_id_index` (`applicant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_view_unlocks`
--

INSERT INTO `applicant_view_unlocks` (`id`, `user_id`, `applicant_id`, `token`, `expired_at`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(21, 8, 1, 'GsgdH9uNzAlGTDWz', '2023-08-22 02:37:34', 1, NULL, NULL, '2023-08-22 01:37:34', '2023-08-22 01:37:34'),
(36, 1, 1, 'gOVZzckY125JD6mq', '2023-09-01 10:04:39', 1, NULL, NULL, '2023-09-01 09:04:39', '2023-09-01 09:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

DROP TABLE IF EXISTS `assessments`;
CREATE TABLE IF NOT EXISTS `assessments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_creation_id` bigint(20) UNSIGNED NOT NULL,
  `course_module_base_assesment_id` bigint(20) UNSIGNED NOT NULL,
  `assessment_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assessment_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessments_module_creation_id_foreign` (`module_creation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `module_creation_id`, `course_module_base_assesment_id`, `assessment_name`, `assessment_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(29, 25, 3, 'Assesment 01', 'AS-001', 1, NULL, '2023-02-23 03:31:25', '2023-02-23 03:31:25', NULL),
(31, 25, 5, 'Assesment 03', 'AS-003', 1, NULL, '2023-02-23 05:04:33', '2023-02-23 05:04:33', NULL),
(33, 26, 1, 'Assesment Heros', 'AS-091', 1, NULL, '2023-02-23 05:24:27', '2023-02-23 05:24:27', NULL),
(35, 27, 5, 'Assesment 03', 'AS-003', 1, NULL, '2023-02-23 07:34:17', '2023-02-23 07:34:17', NULL),
(36, 27, 4, 'Assesment 03', 'AS-002', 1, NULL, '2023-02-23 07:34:48', '2023-02-23 07:34:48', NULL),
(38, 28, 2, 'Assesment Zero', 'AS-076', 1, NULL, '2023-02-23 07:35:12', '2023-02-23 07:35:12', NULL),
(39, 29, 3, 'Assesment 01', 'AS-001', 1, NULL, '2023-09-04 10:23:48', '2023-09-04 10:23:48', NULL),
(40, 29, 5, 'Assesment 03', 'AS-003', 1, NULL, '2023-09-04 10:23:48', '2023-09-04 10:23:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assigns`
--

DROP TABLE IF EXISTS `assigns`;
CREATE TABLE IF NOT EXISTS `assigns` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigns_plan_id_foreign` (`plan_id`),
  KEY `assigns_student_id_foreign` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plans_date_list_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_feed_status_id` bigint(20) UNSIGNED NOT NULL,
  `email_notification` tinyint(4) NOT NULL DEFAULT '0',
  `sms_notification` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fkplans_date_id` (`plans_date_list_id`),
  KEY `attendances_student_foreign` (`student_id`),
  KEY `fkfeed_status` (`attendance_feed_status_id`),
  KEY `attendances_user_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_feed_statuses`
--

DROP TABLE IF EXISTS `attendance_feed_statuses`;
CREATE TABLE IF NOT EXISTS `attendance_feed_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(4) DEFAULT '1',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_informations`
--

DROP TABLE IF EXISTS `attendance_informations`;
CREATE TABLE IF NOT EXISTS `attendance_informations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plans_date_list_id` bigint(20) UNSIGNED NOT NULL,
  `tutor_id` bigint(20) UNSIGNED NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_informations_plans_date_list_id_foreign` (`plans_date_list_id`),
  KEY `attendance_informations_created_by_foreign` (`created_by`),
  KEY `attendance_informations_tutor_id_foreign` (`tutor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awarding_bodies`
--

DROP TABLE IF EXISTS `awarding_bodies`;
CREATE TABLE IF NOT EXISTS `awarding_bodies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hesa_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `df_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `awarding_bodies`
--

INSERT INTO `awarding_bodies` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'TRINITY COLLEGE LONDON', '0', NULL, '0', NULL, 1, NULL, NULL, '2023-01-05 05:18:55', NULL),
(3, 'NORTHUMBRIA UNIVERSITY', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(4, 'PEARSON', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(5, 'NCFE', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(7, 'ASSOCIATION OF CHARTERED CERTIFIED ACCOUNTANTS', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(8, 'LONDON CENTRE of  MARKETING', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(9, 'MANCHESTER METROPOLITAN UNIVERSITY (IAW MDP)', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(10, 'AABPS', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(11, 'APBP', '0', NULL, '0', NULL, 1, 1, NULL, '2022-12-23 02:37:49', NULL),
(12, 'ATHE', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(13, 'EBMA', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(14, 'THE INSTITUTE FOR THE MANAGEMENT OF INFORMATION SYSTEMS', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(15, 'ASCENTIS', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(16, 'UNIVERSITY OF BEDFORDSHIRE', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(17, 'ILM', '0', NULL, '0', NULL, 1, NULL, NULL, NULL, NULL),
(18, 'LONDON CHURCHILL', '0', NULL, '0', NULL, 1, NULL, '2022-12-21 22:05:19', '2023-10-05 05:26:59', NULL),
(19, 'Abdul Latif', '1', '1', '1', '2', 1, 1, '2023-01-05 05:07:27', '2023-10-05 05:23:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bank_holidays`
--

DROP TABLE IF EXISTS `bank_holidays`;
CREATE TABLE IF NOT EXISTS `bank_holidays` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Bank Holiday') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bank Holiday',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_holidays_academic_year_id_foreign` (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_holidays`
--

INSERT INTO `bank_holidays` (`id`, `academic_year_id`, `start_date`, `end_date`, `duration`, `title`, `type`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 2, '2023-01-16', '2023-01-16', 1, 'New Year’s Day', 'Bank Holiday', 1, NULL, NULL, '2023-03-17 06:08:12', '2023-03-17 06:08:12'),
(2, 3, '2023-10-01', '2023-10-01', 1, 'Tester Holiday', 'Bank Holiday', 1, 1, NULL, '2023-10-05 06:42:41', '2023-10-05 06:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `comon_smtps`
--

DROP TABLE IF EXISTS `comon_smtps`;
CREATE TABLE IF NOT EXISTS `comon_smtps` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `smtp_user` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_pass` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_host` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_port` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_encryption` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_authentication` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` tinyint(4) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comon_smtps`
--

INSERT INTO `comon_smtps` (`id`, `user_id`, `smtp_user`, `smtp_pass`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_authentication`, `account_type`, `is_default`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'wbl@lcc.ac.uk', 'wblpassword@', 'smtp.gmail.com', '587', 'tls', 'true', 0, 0, 1, 1, NULL, '2023-06-16 06:05:14', '2023-10-06 05:04:11'),
(2, NULL, 'registry@lcc.ac.uk', '1LCCRegistry1@', 'smtp.gmail.com', '587', 'tls', 'true', 0, 0, 1, 1, NULL, '2023-06-16 06:12:35', '2023-10-06 05:04:11'),
(3, NULL, 'no-reply@lcc.ac.uk', 'churchill1', 'smtp.gmail.com', '587', 'tls', 'true', 0, 1, 1, 1, NULL, '2023-06-19 04:43:01', '2023-10-06 05:04:11'),
(4, NULL, 'limon@churchill.ac', 'e1234', 'smtp.gmail.com', '587', 'tls', 'true', 0, 0, 1, NULL, NULL, '2023-10-06 04:55:49', '2023-10-06 05:04:11');

-- --------------------------------------------------------

--
-- Table structure for table `consent_policies`
--

DROP TABLE IF EXISTS `consent_policies`;
CREATE TABLE IF NOT EXISTS `consent_policies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_required` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consent_policies`
--

INSERT INTO `consent_policies` (`id`, `name`, `description`, `department_id`, `is_required`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Alumni', 'LCC Alumni service will contact you for following events and services,CV and cover letter writing drop in sessions and career workshops, Employability advice, including work experiences and interview techniques. You will be invited to career events, graduate fairs, employer forum and networking assistance.', 4, 'Yes', 1, 1, '2023-09-22 02:39:57', '2023-10-05 08:26:11', NULL),
(2, 'Marketing', 'We may contact you with up to date Information about courses offered by the college including changes to our course offer.', 10, 'Yes', 1, 1, '2023-09-22 02:50:28', '2023-09-22 03:45:00', NULL),
(3, 'Employment', 'Career advice, Career Events, Drop in Session, Seminar', NULL, 'No', 1, NULL, '2023-09-22 02:51:23', '2023-09-22 02:51:23', NULL),
(4, 'LCC Facilities', 'LCC facilities will contact you with Up to date news regarding college campus facilities and upcoming events', 6, 'No', 1, NULL, '2023-09-22 03:50:53', '2023-09-22 03:50:53', NULL),
(5, 'Student engagement', 'LCC student engagement team will contact you about opportunities to network with other LCC alumni/students including fun trips, Winter Extravaganza, food fair, graduate reunions and many more.', 5, 'No', 1, 1, '2023-09-22 03:51:28', '2023-10-05 08:25:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `iso_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `iso_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bangladesh', 1, '34', 1, '56', 1, 'BD', 1, 1, '2023-03-29 06:41:23', '2023-09-28 02:44:31', NULL),
(2, 'United Kingdom', 1, '1', 1, '2', 1, 'UK', 1, NULL, '2023-10-10 08:04:26', '2023-10-10 08:04:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `country_of_permanent_addresses`
--

DROP TABLE IF EXISTS `country_of_permanent_addresses`;
CREATE TABLE IF NOT EXISTS `country_of_permanent_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `country_of_permanent_addresses`
--

INSERT INTO `country_of_permanent_addresses` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Pakistan', 1, 'PA', 1, 'PAK', 1, 1, 1, '2023-09-28 05:45:07', '2023-09-28 05:45:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_offered` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pre_qualification` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `awarding_body_id` bigint(20) UNSIGNED NOT NULL,
  `source_tuition_fee_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courses_awarding_body_id_foreign` (`awarding_body_id`),
  KEY `courses_source_tuition_fee_id_foreign` (`source_tuition_fee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `degree_offered`, `pre_qualification`, `awarding_body_id`, `source_tuition_fee_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HND in Business', 'HND', 'Degree', 5, 2, 1, 1, '2023-01-05 06:58:22', '2023-01-05 07:34:06', NULL),
(2, 'HND in Hospitality Managment', 'HND', 'Degree', 4, 1, 1, NULL, '2023-01-05 07:34:34', '2023-01-05 07:34:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_base_datafutures`
--

DROP TABLE IF EXISTS `course_base_datafutures`;
CREATE TABLE IF NOT EXISTS `course_base_datafutures` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_type` enum('date','text','number') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `field_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_desc` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_base_datafutures_course_id_foreign` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_base_datafutures`
--

INSERT INTO `course_base_datafutures` (`id`, `course_id`, `field_name`, `field_type`, `field_value`, `field_desc`, `parent_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Field 01', 'text', 'TEST', 'This is test description. Updated by admin. Updated again.', NULL, 1, 1, '2023-01-09 04:48:34', '2023-01-09 06:04:36', NULL),
(2, 1, 'Field 02', 'number', '7865098', 'This is test description.', NULL, 1, NULL, '2023-01-09 04:48:57', '2023-01-09 04:48:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_creations`
--

DROP TABLE IF EXISTS `course_creations`;
CREATE TABLE IF NOT EXISTS `course_creations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `semester_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `course_creation_qualification_id` bigint(20) UNSIGNED DEFAULT NULL,
  `duration` tinyint(4) NOT NULL,
  `unit_length` enum('Years','Months','Days','Hours','Not applicable') COLLATE utf8mb4_unicode_ci NOT NULL,
  `slc_code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creations`
--

INSERT INTO `course_creations` (`id`, `semester_id`, `course_id`, `course_creation_qualification_id`, `duration`, `unit_length`, `slc_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 4, 1, NULL, 3, 'Months', '678', 1, NULL, '2023-01-18 07:22:32', '2023-01-18 07:22:32', NULL),
(6, 2, 1, NULL, 2, 'Years', 'SLC0983', 1, 1, '2023-02-24 05:57:44', '2023-02-24 05:58:00', NULL),
(7, 1, 1, 1, 22, 'Months', '45tr', 1, NULL, '2023-09-04 09:28:45', '2023-09-04 09:28:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_creation_availabilities`
--

DROP TABLE IF EXISTS `course_creation_availabilities`;
CREATE TABLE IF NOT EXISTS `course_creation_availabilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `admission_date` date NOT NULL,
  `admission_end_date` date NOT NULL,
  `course_start_date` date NOT NULL,
  `course_end_date` date NOT NULL,
  `last_joinning_date` date NOT NULL,
  `type` enum('UK','BOTH','OVERSEAS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_creation_availabilities_course_creation_id_foreign` (`course_creation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creation_availabilities`
--

INSERT INTO `course_creation_availabilities` (`id`, `course_creation_id`, `admission_date`, `admission_end_date`, `course_start_date`, `course_end_date`, `last_joinning_date`, `type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, '2022-08-20', '2023-09-20', '2023-09-01', '2024-09-30', '2023-09-20', 'UK', 1, NULL, '2023-09-07 07:38:17', '2023-09-07 07:38:17', NULL),
(2, 5, '2023-05-01', '2023-09-30', '2022-08-01', '2024-09-30', '2023-09-02', 'UK', 1, NULL, '2023-09-20 03:54:12', '2023-09-20 03:54:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_creation_datafutures`
--

DROP TABLE IF EXISTS `course_creation_datafutures`;
CREATE TABLE IF NOT EXISTS `course_creation_datafutures` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_type` enum('date','text','number') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_desc` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_creation_datafutures_course_creation_id_foreign` (`course_creation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_creation_instances`
--

DROP TABLE IF EXISTS `course_creation_instances`;
CREATE TABLE IF NOT EXISTS `course_creation_instances` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_teaching_week` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_creation_instances_course_creation_id_foreign` (`course_creation_id`),
  KEY `course_creation_instances_academic_year_id_foreign` (`academic_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creation_instances`
--

INSERT INTO `course_creation_instances` (`id`, `course_creation_id`, `academic_year_id`, `start_date`, `end_date`, `total_teaching_week`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 2, '2021-04-01', '2022-03-31', 52, 1, NULL, '2023-01-25 06:31:50', '2023-01-25 06:31:50', NULL),
(3, 5, 1, '2022-04-01', '2023-08-31', 52, 1, NULL, '2023-01-26 08:27:49', '2023-01-26 08:27:49', NULL),
(4, 6, 2, '2023-02-03', '2023-06-30', 5, 1, NULL, '2023-02-24 05:58:26', '2023-02-24 05:58:26', NULL),
(5, 6, 2, '2023-03-01', '2023-03-31', 5, 1, NULL, '2023-02-24 05:58:49', '2023-02-24 05:58:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_creation_qualifications`
--

DROP TABLE IF EXISTS `course_creation_qualifications`;
CREATE TABLE IF NOT EXISTS `course_creation_qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hesa` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hesa_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `df_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creation_qualifications`
--

INSERT INTO `course_creation_qualifications` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 'Secondary School Senior', '1', '5', '1', '6', 1, '2022-12-21 04:41:21', 1, '2023-10-05 08:16:52', NULL),
(2, 'Diploma Course', '1', '3', '1', '4', 1, '2022-12-21 06:44:36', 1, '2023-10-05 08:13:57', NULL),
(3, 'HND IN Diplomas', '1', '1', '1', '2', 1, '2023-10-05 08:10:45', 1, '2023-10-05 08:13:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

DROP TABLE IF EXISTS `course_modules`;
CREATE TABLE IF NOT EXISTS `course_modules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `module_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('core','specialist') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `credit_value` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_value` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_modules_course_id_foreign` (`course_id`),
  KEY `course_modules_module_level_id_foreign` (`module_level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_modules`
--

INSERT INTO `course_modules` (`id`, `course_id`, `module_level_id`, `name`, `code`, `status`, `credit_value`, `unit_value`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Module 01', '098713', 'core', '3', '34', 1, 1, NULL, '2023-01-06 04:02:59', '2023-01-06 05:02:27', NULL),
(2, 1, 1, 'Module 02', '098713', 'core', '37', '34456', 1, 1, 1, '2023-01-06 04:05:52', '2023-01-10 08:38:03', NULL),
(3, 1, 2, 'Module 03', '098713', 'core', '34', '34444', 1, 1, 1, '2023-01-09 06:06:44', '2023-01-09 06:06:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_module_base_assesments`
--

DROP TABLE IF EXISTS `course_module_base_assesments`;
CREATE TABLE IF NOT EXISTS `course_module_base_assesments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_module_id` bigint(20) UNSIGNED NOT NULL,
  `assesment_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assesment_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_module_base_assesments_course_module_id_foreign` (`course_module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_module_base_assesments`
--

INSERT INTO `course_module_base_assesments` (`id`, `course_module_id`, `assesment_code`, `assesment_name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'AS-091', 'Assesment Heros', 1, 1, '2023-01-09 10:03:27', '2023-01-10 05:56:12', NULL),
(2, 2, 'AS-076', 'Assesment Zero', 1, NULL, '2023-01-09 10:03:46', '2023-01-10 05:55:52', NULL),
(3, 1, 'AS-001', 'Assesment 01', 1, NULL, '2023-02-20 06:53:35', '2023-02-20 06:53:35', NULL),
(4, 1, 'AS-002', 'Assesment 03', 1, NULL, '2023-02-20 06:53:49', '2023-02-20 06:53:49', NULL),
(5, 1, 'AS-003', 'Assesment 03', 1, NULL, '2023-02-20 06:54:01', '2023-02-20 06:54:01', NULL),
(6, 3, 'AS-004', 'Assesment 04', 1, NULL, '2023-02-20 06:54:15', '2023-02-20 06:54:15', NULL),
(7, 3, 'AS-005', 'Assesment 05', 1, NULL, '2023-02-20 06:54:26', '2023-02-20 06:54:26', NULL),
(8, 3, 'AS-006', 'Assesment 06', 1, NULL, '2023-02-20 06:54:43', '2023-02-20 06:54:43', NULL),
(9, 3, 'AS-007', 'Assesment 07', 1, NULL, '2023-02-20 06:54:57', '2023-02-20 06:54:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Academic', 1, NULL, NULL, '2023-05-15 04:41:42', '2023-05-15 04:41:42'),
(2, 'Academic Admin', 1, NULL, NULL, '2023-05-15 04:41:55', '2023-05-15 04:41:55'),
(3, 'Admission', 1, NULL, NULL, '2023-09-22 03:35:16', '2023-09-22 03:35:16'),
(4, 'Alumni', 1, NULL, NULL, '2023-09-22 03:35:29', '2023-09-22 03:35:29'),
(5, 'Employability and Student engagement', 1, NULL, NULL, '2023-09-22 03:35:39', '2023-09-22 03:35:39'),
(6, 'Facilities', 1, NULL, NULL, '2023-09-22 03:35:49', '2023-09-22 03:35:49'),
(7, 'Finance', 1, NULL, NULL, '2023-09-22 03:35:59', '2023-09-22 03:35:59'),
(8, 'HR', 1, NULL, NULL, '2023-09-22 03:36:07', '2023-09-22 03:36:07'),
(9, 'IT and Monitoring', 1, NULL, NULL, '2023-09-22 03:36:15', '2023-09-22 03:36:15'),
(10, 'Marketing', 1, NULL, NULL, '2023-09-22 03:36:25', '2023-09-22 03:36:25'),
(11, 'Registry', 1, NULL, NULL, '2023-09-22 03:36:41', '2023-09-22 03:36:41'),
(12, 'Quality Assurance', 1, NULL, NULL, '2023-09-22 03:36:46', '2023-09-22 03:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `disabilities`
--

DROP TABLE IF EXISTS `disabilities`;
CREATE TABLE IF NOT EXISTS `disabilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disabilities`
--

INSERT INTO `disabilities` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Two or more impairments and/or disabling medical conditions', 1, '34', 1, '56', 1, 1, 1, '2023-03-30 08:31:38', '2023-03-30 08:31:50', NULL),
(2, 'A specific learning difficulty such as dyslexia, dyspraxia or AD(H)D', 0, NULL, 0, NULL, 0, 1, NULL, '2023-03-30 08:32:15', '2023-03-30 08:32:15', NULL),
(3, 'A social/communication impairment such as Asperger\'s syndrome/other autistic spectrum disorder', 0, NULL, 0, NULL, 0, 1, NULL, '2023-03-30 08:32:32', '2023-03-30 08:32:32', NULL),
(4, 'A long standing illness or health condition such as cancer, HIV, diabetes, chronic heart disease, or epilepsy', 0, NULL, 0, NULL, 0, 1, NULL, '2023-03-30 08:32:46', '2023-03-30 08:32:46', NULL),
(5, 'A mental health condition, such as depression, schizophrenia or anxiety disorder', 0, NULL, 0, NULL, 1, 1, NULL, '2023-03-30 08:33:03', '2023-03-30 08:33:03', NULL),
(6, 'A physical impairment or mobility issues, such as difficulty using arms or using a wheelchair or crutches', 0, NULL, 0, NULL, 1, 1, NULL, '2023-03-30 08:33:25', '2023-03-30 08:33:25', NULL),
(7, 'Deaf or a serious hearing impairment', 0, NULL, 0, NULL, 1, 1, NULL, '2023-03-30 08:33:44', '2023-03-30 08:33:44', NULL),
(8, 'Blind or a serious visual impairment uncorrected by glasses', 0, NULL, 0, NULL, 1, 1, 1, '2023-03-30 08:33:57', '2023-09-28 03:16:25', NULL),
(9, 'A disability, impairment or medical condition that is not listed above', 0, NULL, 0, NULL, 1, 1, 1, '2023-03-30 08:34:09', '2023-09-28 03:15:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_settings`
--

DROP TABLE IF EXISTS `document_settings`;
CREATE TABLE IF NOT EXISTS `document_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('optional','mandatory') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'optional',
  `application` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `admission` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `registration` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `live` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `staff` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `student_profile` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_settings`
--

INSERT INTO `document_settings` (`id`, `name`, `type`, `application`, `admission`, `registration`, `live`, `staff`, `student_profile`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Passport', 'optional', '1', '1', '1', '0', '0', '0', 1, 1, NULL, '2023-05-02 09:18:54', '2023-05-02 09:37:28'),
(2, 'Blood Group Test', 'mandatory', '1', '1', '0', '0', '0', '0', 1, NULL, NULL, '2023-05-03 03:18:04', '2023-05-03 03:18:04'),
(3, 'Common Uploads', 'optional', '0', '0', '0', '1', '0', '0', 1, NULL, NULL, '2023-09-25 09:45:46', '2023-10-05 09:39:43');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `email_title`, `description`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Virtual Class Email Description', '<p>You are required to attend [Module_Name] on [Class_Date] at [Class_Time]. Please click on the link bellow or enter the link on your browser to join the [business environment] virtually. Please note that based on your activity and interaction with your tutor [Tutor_Name], your attendance will be confirmed on the day. Link: [Virtual_Link]</p>', 1, NULL, NULL, '2023-08-30 07:46:34', '2023-08-30 07:46:34'),
(2, 'Campus Class Email Description', '<p>You are required to attend [Module_Name] on [Class_Date] at [Class_Time]. Please go to the campus Class Room: [Class_Room] to join the lecture. Please note that based on your activity and interaction with your tutor [Tutor_Name], your attendance will be confirmed on the day.&nbsp;</p>', 1, 2, NULL, '2023-08-30 07:47:01', '2023-10-06 03:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex_identifier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `ni_number` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ethnicity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `car_reg_number` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drive_license_number` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disability_status` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=Active,0=In Active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_address_id_foreign` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `title_id`, `first_name`, `last_name`, `photo`, `telephone`, `mobile`, `email`, `sex_identifier_id`, `date_of_birth`, `ni_number`, `nationality_id`, `ethnicity_id`, `car_reg_number`, `drive_license_number`, `address_id`, `disability_status`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Supe', 'Admin', '1700229842_1.jpg', NULL, '01740149260', 'midone@left4code.com', 2, '2023-10-01', 'sc-100000-L', 1, 1, NULL, NULL, 33, 'No', 1, NULL, '2023-10-25 09:37:44', '2023-11-17 08:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendances`
--

DROP TABLE IF EXISTS `employee_attendances`;
CREATE TABLE IF NOT EXISTS `employee_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_working_pattern_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_working_pattern_pay_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `clockin_contract` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clockin_punch` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clockin_system` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clockout_contract` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clockout_punch` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clockout_system` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_break` int(11) DEFAULT NULL,
  `break_details_html` text COLLATE utf8mb4_unicode_ci,
  `paid_break` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unpadi_break` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjustment` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_work_hour` int(11) DEFAULT NULL,
  `employee_leave_day_id` bigint(20) UNSIGNED DEFAULT NULL,
  `leave_status` int(11) DEFAULT '0' COMMENT '1=Holiday/Vacation,2=Meeting / Training,3=Sick Leave,4=Authorised Unpaid,5=Authorised Paid',
  `leave_adjustment` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_hour` int(11) DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `user_issues` int(11) DEFAULT '0',
  `isses_field` text COLLATE utf8mb4_unicode_ci,
  `overtime_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1=Yes,0=No',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=Approved,2=Pending,3=Canceled',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_attendances_employee_id_foreign` (`employee_id`),
  KEY `eald_id_fk` (`employee_leave_day_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendances`
--

INSERT INTO `employee_attendances` (`id`, `employee_id`, `employee_working_pattern_id`, `employee_working_pattern_pay_id`, `date`, `clockin_contract`, `clockin_punch`, `clockin_system`, `clockout_contract`, `clockout_punch`, `clockout_system`, `total_break`, `break_details_html`, `paid_break`, `unpadi_break`, `adjustment`, `total_work_hour`, `employee_leave_day_id`, `leave_status`, `leave_adjustment`, `leave_hour`, `note`, `user_issues`, `isses_field`, `overtime_status`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2023-11-13', '09:00', '08:03', '08:00', '18:00', '11:35', '17:00', 36, '<ol class=\"return_list\"><li><span class=\"re_br\"><span class=\"bre\">08:34</span> - <span class=\"ret\">08:45</span></span></li><li><span class=\"re_br\"><span class=\"bre\">10:56</span> - <span class=\"ret\">11:21</span></span></li></ol>', '00:30', '01:00', '+00:00', 480, 5, 1, '+00:00', 480, NULL, 0, NULL, 0, 1, 1, 1, NULL, '2023-11-17 05:35:57', '2023-11-17 05:36:25'),
(2, 1, 1, 1, '2023-11-20', '', '', '', '', '', '', 0, NULL, '00:30', '01:00', '+00:00', 0, NULL, 4, '+00:00', 480, '', 0, '', 0, 1, 1, NULL, NULL, '2023-11-19 22:47:28', '2023-11-19 22:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance_lives`
--

DROP TABLE IF EXISTS `employee_attendance_lives`;
CREATE TABLE IF NOT EXISTS `employee_attendance_lives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_type` int(11) NOT NULL COMMENT '1=ClockIn,2=Break,3=BreakReturn,4=ClockOut',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `employee_attendance_machine_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_attendance_lives_employee_id_foreign` (`employee_id`),
  KEY `eal_machine_id_fk` (`employee_attendance_machine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendance_lives`
--

INSERT INTO `employee_attendance_lives` (`id`, `employee_id`, `attendance_type`, `date`, `time`, `employee_attendance_machine_id`, `ip`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-11-10', '10:58:11', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 04:58:11', '2023-11-10 04:58:11'),
(2, 1, 2, '2023-11-10', '11:39:54', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:39:54', '2023-11-10 05:39:54'),
(3, 1, 3, '2023-11-10', '11:42:08', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:42:08', '2023-11-10 05:42:08'),
(4, 1, 2, '2023-11-10', '11:43:40', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:43:40', '2023-11-10 05:43:40'),
(5, 1, 3, '2023-11-10', '11:54:18', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:54:18', '2023-11-10 05:54:18'),
(6, 1, 4, '2023-11-10', '11:56:12', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:56:12', '2023-11-10 05:56:12'),
(7, 1, 1, '2023-11-13', '08:03:21', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:03:21', '2023-11-13 02:03:21'),
(8, 1, 2, '2023-11-13', '08:34:50', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:34:50', '2023-11-13 02:34:50'),
(9, 1, 3, '2023-11-13', '08:45:57', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:45:57', '2023-11-13 02:45:57'),
(10, 1, 2, '2023-11-13', '10:56:17', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 04:56:17', '2023-11-13 04:56:17'),
(11, 1, 3, '2023-11-13', '11:21:59', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 05:21:59', '2023-11-13 05:21:59'),
(12, 1, 4, '2023-11-13', '11:35:39', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 05:35:39', '2023-11-13 05:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance_machines`
--

DROP TABLE IF EXISTS `employee_attendance_machines`;
CREATE TABLE IF NOT EXISTS `employee_attendance_machines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendance_machines`
--

INSERT INTO `employee_attendance_machines` (`id`, `username`, `password`, `name`, `location`, `remember_token`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Upton Park', 'Upton Park', NULL, 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance_punch_histories`
--

DROP TABLE IF EXISTS `employee_attendance_punch_histories`;
CREATE TABLE IF NOT EXISTS `employee_attendance_punch_histories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `employee_attendance_machine_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_attendance_punch_histories_employee_id_foreign` (`employee_id`),
  KEY `eaph_machine_id_fk` (`employee_attendance_machine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendance_punch_histories`
--

INSERT INTO `employee_attendance_punch_histories` (`id`, `employee_id`, `date`, `time`, `employee_attendance_machine_id`, `ip`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-11-10', '08:48:43', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 02:48:43', '2023-11-10 02:48:43'),
(2, 1, '2023-11-10', '08:51:53', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 02:51:53', '2023-11-10 02:51:53'),
(3, 1, '2023-11-10', '10:58:07', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 04:58:07', '2023-11-10 04:58:07'),
(4, 1, '2023-11-10', '11:00:48', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:00:48', '2023-11-10 05:00:48'),
(5, 1, '2023-11-10', '11:00:55', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:00:55', '2023-11-10 05:00:55'),
(6, 1, '2023-11-10', '11:29:38', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:29:38', '2023-11-10 05:29:38'),
(7, 1, '2023-11-10', '11:39:41', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:39:41', '2023-11-10 05:39:41'),
(8, 1, '2023-11-10', '11:41:49', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:41:49', '2023-11-10 05:41:49'),
(9, 1, '2023-11-10', '11:41:59', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:41:59', '2023-11-10 05:41:59'),
(10, 1, '2023-11-10', '11:43:33', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:43:33', '2023-11-10 05:43:33'),
(11, 1, '2023-11-10', '11:54:14', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:54:14', '2023-11-10 05:54:14'),
(12, 1, '2023-11-10', '11:55:51', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:55:51', '2023-11-10 05:55:51'),
(13, 1, '2023-11-10', '11:56:07', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 05:56:07', '2023-11-10 05:56:07'),
(14, 1, '2023-11-10', '12:02:25', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 06:02:25', '2023-11-10 06:02:25'),
(15, 1, '2023-11-10', '12:03:23', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 06:03:23', '2023-11-10 06:03:23'),
(16, 1, '2023-11-10', '12:11:41', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-10 06:11:41', '2023-11-10 06:11:41'),
(17, 1, '2023-11-13', '08:03:19', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:03:19', '2023-11-13 02:03:19'),
(18, 1, '2023-11-13', '08:34:48', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:34:48', '2023-11-13 02:34:48'),
(19, 1, '2023-11-13', '08:45:53', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 02:45:53', '2023-11-13 02:45:53'),
(20, 1, '2023-11-13', '10:56:02', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 04:56:02', '2023-11-13 04:56:02'),
(21, 1, '2023-11-13', '11:21:55', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 05:21:55', '2023-11-13 05:21:55'),
(22, 1, '2023-11-13', '11:21:55', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 05:21:55', '2023-11-13 05:21:55'),
(23, 1, '2023-11-13', '11:35:34', 1, '127.0.0.1', 1, NULL, NULL, '2023-11-13 05:35:34', '2023-11-13 05:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `employee_bank_details`
--

DROP TABLE IF EXISTS `employee_bank_details`;
CREATE TABLE IF NOT EXISTS `employee_bank_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `beneficiary` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ac_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_bank_details_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_bank_details`
--

INSERT INTO `employee_bank_details` (`id`, `employee_id`, `beneficiary`, `sort_code`, `ac_no`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 1, 'Mr. Testers', '43-56-91', '98423577', 0, 1, 1, NULL, '2023-10-26 08:46:27', '2023-10-30 09:09:50'),
(5, 1, 'Mrs. Testres', '43-56-91', '45612378', 1, 1, NULL, NULL, '2023-10-27 02:40:41', '2023-10-30 09:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_disability`
--

DROP TABLE IF EXISTS `employee_disability`;
CREATE TABLE IF NOT EXISTS `employee_disability` (
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `disability_id` bigint(20) UNSIGNED NOT NULL,
  KEY `employee_disability_employee_id_foreign` (`employee_id`),
  KEY `employee_disability_disability_id_foreign` (`disability_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `document_setting_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hard_copy_check` tinyint(4) DEFAULT NULL,
  `doc_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_documents_employee_id_index` (`employee_id`),
  KEY `employee_documents_document_setting_id_index` (`document_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_eligibilites`
--

DROP TABLE IF EXISTS `employee_eligibilites`;
CREATE TABLE IF NOT EXISTS `employee_eligibilites` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_to_work` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_work_permit_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `workpermit_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workpermit_expire` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_type` bigint(20) UNSIGNED DEFAULT NULL,
  `doc_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_expire` date DEFAULT NULL,
  `doc_issue_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_eligibilites_employee_id_foreign` (`employee_id`),
  KEY `employee_eligibilites_document_type_foreign` (`document_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_eligibilites`
--

INSERT INTO `employee_eligibilites` (`id`, `employee_id`, `eligible_to_work`, `employee_work_permit_type_id`, `workpermit_number`, `workpermit_expire`, `document_type`, `doc_number`, `doc_expire`, `doc_issue_country`, `created_at`, `updated_at`) VALUES
(1, 1, 'Yes', 1, NULL, NULL, 1, '2342134', '2023-11-22', '1', '2023-10-25 09:37:44', '2023-11-06 03:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `employee_emergency_contacts`
--

DROP TABLE IF EXISTS `employee_emergency_contacts`;
CREATE TABLE IF NOT EXISTS `employee_emergency_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `emergency_contact_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kins_relation_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_telephone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_mobile` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_emergency_contacts_employee_id_foreign` (`employee_id`),
  KEY `employee_emergency_contacts_address_id_foreign` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_emergency_contacts`
--

INSERT INTO `employee_emergency_contacts` (`id`, `employee_id`, `emergency_contact_name`, `kins_relation_id`, `emergency_contact_telephone`, `emergency_contact_mobile`, `emergency_contact_email`, `address_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'The Kin', '8', '01740149260', '01740149260', 'limon@churchill.ac', 35, '2023-10-25 09:37:44', '2023-11-06 03:47:53');

-- --------------------------------------------------------

--
-- Table structure for table `employee_holiday_adjustments`
--

DROP TABLE IF EXISTS `employee_holiday_adjustments`;
CREATE TABLE IF NOT EXISTS `employee_holiday_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `hr_holiday_year_id` bigint(20) UNSIGNED NOT NULL,
  `employee_working_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `operator` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 = Plus, 2 = Minus',
  `hours` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_holiday_adjustments_employee_id_foreign` (`employee_id`),
  KEY `adjustment_holiday_year_id_frn_key` (`hr_holiday_year_id`),
  KEY `emp_pattern_id_frn_key` (`employee_working_pattern_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_holiday_adjustments`
--

INSERT INTO `employee_holiday_adjustments` (`id`, `employee_id`, `hr_holiday_year_id`, `employee_working_pattern_id`, `operator`, `hours`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 2, 420, 1, NULL, NULL, '2023-11-09 03:16:16', '2023-11-09 03:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `employee_holiday_authorised_bies`
--

DROP TABLE IF EXISTS `employee_holiday_authorised_bies`;
CREATE TABLE IF NOT EXISTS `employee_holiday_authorised_bies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_holiday_authorised_bies`
--

INSERT INTO `employee_holiday_authorised_bies` (`id`, `employee_id`, `user_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 1, 5, 1, NULL, NULL, '2023-10-30 09:09:50', '2023-10-30 09:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_hour_authorised_bies`
--

DROP TABLE IF EXISTS `employee_hour_authorised_bies`;
CREATE TABLE IF NOT EXISTS `employee_hour_authorised_bies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_hour_authorised_bies`
--

INSERT INTO `employee_hour_authorised_bies` (`id`, `employee_id`, `user_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(6, 1, 2, 1, NULL, NULL, '2023-10-30 09:09:50', '2023-10-30 09:09:50'),
(7, 1, 5, 1, NULL, NULL, '2023-10-30 09:09:50', '2023-10-30 09:09:50'),
(8, 1, 6, 1, NULL, NULL, '2023-10-30 09:09:50', '2023-10-30 09:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_info_penssion_schemes`
--

DROP TABLE IF EXISTS `employee_info_penssion_schemes`;
CREATE TABLE IF NOT EXISTS `employee_info_penssion_schemes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_info_penssion_schemes`
--

INSERT INTO `employee_info_penssion_schemes` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'NatWest Group Pension Fund', 1, NULL, NULL, NULL, NULL),
(2, 'LGPS – Strathclyde Pension Scheme', 1, NULL, NULL, NULL, NULL),
(3, 'BT Pension Scheme', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_job_titles`
--

DROP TABLE IF EXISTS `employee_job_titles`;
CREATE TABLE IF NOT EXISTS `employee_job_titles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_job_titles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_job_titles`
--

INSERT INTO `employee_job_titles` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 1, NULL, NULL, NULL, NULL),
(2, 'Teacher', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_leaves`
--

DROP TABLE IF EXISTS `employee_leaves`;
CREATE TABLE IF NOT EXISTS `employee_leaves` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `hr_holiday_year_id` bigint(20) UNSIGNED NOT NULL,
  `employee_working_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type` tinyint(4) NOT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `is_fraction` tinyint(4) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Approved','Canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_note` text COLLATE utf8mb4_unicode_ci,
  `approved_at` datetime DEFAULT NULL,
  `canceled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `canceled_note` text COLLATE utf8mb4_unicode_ci,
  `canceled_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_leaves_employee_id_foreign` (`employee_id`),
  KEY `emp_leave_year_id_fk` (`hr_holiday_year_id`),
  KEY `emp_leave_pattern_id_fk` (`employee_working_pattern_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_leaves`
--

INSERT INTO `employee_leaves` (`id`, `employee_id`, `hr_holiday_year_id`, `employee_working_pattern_id`, `leave_type`, `from_date`, `to_date`, `days`, `is_fraction`, `note`, `status`, `approved_by`, `approver_note`, `approved_at`, `canceled_by`, `canceled_note`, `canceled_at`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 1, 3, 1, 1, '2023-11-13', '2023-11-14', 2, 0, 'This is a test from the developer.', 'Approved', 1, NULL, '2023-11-08 15:07:59', NULL, NULL, NULL, 1, 1, NULL, '2023-11-08 04:40:54', '2023-11-09 03:10:24'),
(4, 1, 3, 1, 1, '2023-12-06', '2023-12-06', 1, 0, 'Nedd this leave', 'Pending', 1, NULL, '2023-11-10 13:42:59', NULL, NULL, NULL, 1, 1, NULL, '2023-11-09 05:12:36', '2023-11-10 07:42:59'),
(5, 1, 3, 1, 1, '2023-12-13', '2023-12-15', 3, 0, 'Test From Admin', 'Approved', 1, NULL, '2023-11-20 13:27:14', NULL, NULL, NULL, 1, 1, NULL, '2023-11-20 07:18:28', '2023-11-21 02:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_days`
--

DROP TABLE IF EXISTS `employee_leave_days`;
CREATE TABLE IF NOT EXISTS `employee_leave_days` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_leave_id` bigint(20) UNSIGNED NOT NULL,
  `leave_date` date NOT NULL,
  `hour` int(11) NOT NULL,
  `is_fraction` tinyint(4) NOT NULL DEFAULT '0',
  `status` enum('Active','In Active') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_leave_days_employee_leave_id_foreign` (`employee_leave_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_leave_days`
--

INSERT INTO `employee_leave_days` (`id`, `employee_leave_id`, `leave_date`, `hour`, `is_fraction`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(5, 3, '2023-11-13', 480, 0, 'Active', 1, 1, NULL, '2023-11-08 04:40:54', '2023-11-08 09:07:59'),
(6, 3, '2023-11-14', 480, 0, 'In Active', 1, 1, NULL, '2023-11-08 04:40:54', '2023-11-09 03:10:24'),
(7, 4, '2023-12-06', 480, 0, 'Active', 1, 1, NULL, '2023-11-09 05:12:36', '2023-11-10 07:42:59'),
(8, 5, '2023-12-13', 480, 0, 'In Active', 1, 1, NULL, '2023-11-20 07:18:28', '2023-11-20 07:28:25'),
(9, 5, '2023-12-14', 480, 0, 'In Active', 1, 1, NULL, '2023-11-20 07:18:28', '2023-11-21 02:12:24'),
(10, 5, '2023-12-15', 480, 0, 'Active', 1, 1, NULL, '2023-11-20 07:18:28', '2023-11-20 07:27:14');

-- --------------------------------------------------------

--
-- Table structure for table `employee_notes`
--

DROP TABLE IF EXISTS `employee_notes`;
CREATE TABLE IF NOT EXISTS `employee_notes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `opening_date` date DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase` enum('Applicant','Admission','Register','Live','Staff','Student Profile') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admission',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_notes_employee_id_index` (`employee_id`),
  KEY `employee_notes_employee_document_id_index` (`employee_document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_notice_periods`
--

DROP TABLE IF EXISTS `employee_notice_periods`;
CREATE TABLE IF NOT EXISTS `employee_notice_periods` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_notice_periods_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_notice_periods`
--

INSERT INTO `employee_notice_periods` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '4 Weeks', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_payment_settings`
--

DROP TABLE IF EXISTS `employee_payment_settings`;
CREATE TABLE IF NOT EXISTS `employee_payment_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `pay_frequency` enum('Monthly','Weekly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('Bank Transfer','Cash','Cheque') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_to_clockin` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holiday_entitled` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holiday_base` decimal(10,2) DEFAULT NULL,
  `bank_holiday_auto_book` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pension_enrolled` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_payment_settings_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_payment_settings`
--

INSERT INTO `employee_payment_settings` (`id`, `employee_id`, `pay_frequency`, `tax_code`, `payment_method`, `subject_to_clockin`, `holiday_entitled`, `holiday_base`, `bank_holiday_auto_book`, `pension_enrolled`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly', 'TaxCode', 'Bank Transfer', 'Yes', 'Yes', '5.60', 'Yes', 'Yes', 1, 1, NULL, '2023-10-26 08:46:27', '2023-10-30 09:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_penssion_schemes`
--

DROP TABLE IF EXISTS `employee_penssion_schemes`;
CREATE TABLE IF NOT EXISTS `employee_penssion_schemes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_info_penssion_scheme_id` bigint(20) UNSIGNED NOT NULL,
  `joining_date` date NOT NULL,
  `date_left` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_penssion_schemes_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_penssion_schemes`
--

INSERT INTO `employee_penssion_schemes` (`id`, `employee_id`, `employee_info_penssion_scheme_id`, `joining_date`, `date_left`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-01-01', '2024-12-31', 1, NULL, NULL, '2023-10-26 08:46:27', '2023-10-30 09:09:50'),
(2, 1, 2, '2023-10-02', '2023-10-31', 1, 1, NULL, '2023-10-27 03:45:15', '2023-10-30 09:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_terms`
--

DROP TABLE IF EXISTS `employee_terms`;
CREATE TABLE IF NOT EXISTS `employee_terms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employment_ssp_term_id` bigint(20) UNSIGNED NOT NULL,
  `employment_period_id` bigint(20) UNSIGNED NOT NULL,
  `employee_notice_period_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_terms_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_terms`
--

INSERT INTO `employee_terms` (`id`, `employee_id`, `employment_ssp_term_id`, `employment_period_id`, `employee_notice_period_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2023-10-25 09:37:44', '2023-10-25 09:37:44');

-- --------------------------------------------------------

--
-- Table structure for table `employee_venue`
--

DROP TABLE IF EXISTS `employee_venue`;
CREATE TABLE IF NOT EXISTS `employee_venue` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_venue_employee_id_foreign` (`employee_id`),
  KEY `employee_venue_venue_id_foreign` (`venue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_venue`
--

INSERT INTO `employee_venue` (`id`, `employee_id`, `venue_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_working_patterns`
--

DROP TABLE IF EXISTS `employee_working_patterns`;
CREATE TABLE IF NOT EXISTS `employee_working_patterns` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `effective_from` date DEFAULT NULL,
  `end_to` date DEFAULT NULL,
  `contracted_hour` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_working_patterns_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_working_patterns`
--

INSERT INTO `employee_working_patterns` (`id`, `employee_id`, `effective_from`, `end_to`, `contracted_hour`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-06-01', NULL, '40:00', 1, 1, NULL, NULL, '2023-11-02 05:40:51', '2023-11-02 05:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `employee_working_pattern_details`
--

DROP TABLE IF EXISTS `employee_working_pattern_details`;
CREATE TABLE IF NOT EXISTS `employee_working_pattern_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_working_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `day` int(11) NOT NULL,
  `day_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_br` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unpaid_br` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ewp_employee_wp_id` (`employee_working_pattern_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_working_pattern_details`
--

INSERT INTO `employee_working_pattern_details` (`id`, `employee_working_pattern_id`, `day`, `day_name`, `start`, `end`, `paid_br`, `unpaid_br`, `total`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Mon', '09:00', '18:00', '00:30', '01:00', '08:00', 1, NULL, NULL, '2023-11-02 06:03:47', '2023-11-02 06:03:47'),
(2, 1, 2, 'Tue', '09:00', '18:00', '00:30', '01:00', '08:00', 1, NULL, NULL, '2023-11-02 06:03:47', '2023-11-02 06:03:47'),
(3, 1, 3, 'Wed', '09:00', '18:00', '00:30', '01:00', '08:00', 1, NULL, NULL, '2023-11-02 06:03:47', '2023-11-02 06:03:47'),
(4, 1, 4, 'Thu', '09:00', '18:00', '00:30', '01:00', '08:00', 1, NULL, NULL, '2023-11-02 06:03:47', '2023-11-02 06:03:47'),
(5, 1, 5, 'Fir', '09:00', '18:00', '00:30', '01:00', '08:00', 1, NULL, NULL, '2023-11-02 06:03:48', '2023-11-02 06:03:48');

-- --------------------------------------------------------

--
-- Table structure for table `employee_working_pattern_pays`
--

DROP TABLE IF EXISTS `employee_working_pattern_pays`;
CREATE TABLE IF NOT EXISTS `employee_working_pattern_pays` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_working_pattern_id` bigint(20) UNSIGNED NOT NULL,
  `effective_from` date DEFAULT NULL,
  `end_to` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ewp_pay_employee_wp_id` (`employee_working_pattern_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_working_pattern_pays`
--

INSERT INTO `employee_working_pattern_pays` (`id`, `employee_working_pattern_id`, `effective_from`, `end_to`, `salary`, `hourly_rate`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-06-01', '2023-12-31', '20800.00', '10.00', 1, 1, 1, NULL, '2023-11-02 05:40:51', '2023-11-03 02:58:17');

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_document_types`
--

DROP TABLE IF EXISTS `employee_work_document_types`;
CREATE TABLE IF NOT EXISTS `employee_work_document_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_work_document_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_work_document_types`
--

INSERT INTO `employee_work_document_types` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Passport', 1, NULL, NULL, NULL, NULL),
(2, 'Visa', 1, NULL, NULL, '2023-11-20 08:05:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_permit_types`
--

DROP TABLE IF EXISTS `employee_work_permit_types`;
CREATE TABLE IF NOT EXISTS `employee_work_permit_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_work_permit_types`
--

INSERT INTO `employee_work_permit_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'British Citizen', NULL, NULL),
(2, 'Student Visa', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_types`
--

DROP TABLE IF EXISTS `employee_work_types`;
CREATE TABLE IF NOT EXISTS `employee_work_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_work_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_work_types`
--

INSERT INTO `employee_work_types` (`id`, `name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Employee', 1, NULL, NULL, NULL, NULL),
(2, 'Contractor', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employments`
--

DROP TABLE IF EXISTS `employments`;
CREATE TABLE IF NOT EXISTS `employments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `punch_number` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `works_number` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_on` date DEFAULT NULL,
  `employee_work_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_job_title_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `office_telephone` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_action` int(11) DEFAULT NULL,
  `last_action_date` date DEFAULT NULL,
  `last_action_time` time DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employments_employee_id_foreign` (`employee_id`),
  KEY `employments_employee_job_title_id_foreign` (`employee_job_title_id`),
  KEY `employments_department_id_foreign` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employments`
--

INSERT INTO `employments` (`id`, `employee_id`, `punch_number`, `works_number`, `started_on`, `employee_work_type_id`, `employee_job_title_id`, `department_id`, `office_telephone`, `mobile`, `email`, `last_action`, `last_action_date`, `last_action_time`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '111111', '231412341234', '2023-10-01', 1, 1, 1, '01740149260', NULL, 'limon0011@gmail.com', 4, '2023-11-13', '11:35:39', NULL, '2023-10-25 09:37:44', '2023-11-13 05:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `employment_periods`
--

DROP TABLE IF EXISTS `employment_periods`;
CREATE TABLE IF NOT EXISTS `employment_periods` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employment_periods_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_periods`
--

INSERT INTO `employment_periods` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Permanent', 1, NULL, NULL, NULL, NULL),
(2, 'Temporary', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employment_references`
--

DROP TABLE IF EXISTS `employment_references`;
CREATE TABLE IF NOT EXISTS `employment_references` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_employment_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employment_references_applicant_employment_id_foreign` (`applicant_employment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_references`
--

INSERT INTO `employment_references` (`id`, `applicant_employment_id`, `name`, `position`, `phone`, `email`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Abdul Latif', 'Supervisor', '01740149260', 'limon@churchill.ac', 1, 1, '2023-04-11 03:26:56', '2023-04-28 04:42:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employment_ssp_terms`
--

DROP TABLE IF EXISTS `employment_ssp_terms`;
CREATE TABLE IF NOT EXISTS `employment_ssp_terms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employment_ssp_terms_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_ssp_terms`
--

INSERT INTO `employment_ssp_terms` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Statory', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ethnicities`
--

DROP TABLE IF EXISTS `ethnicities`;
CREATE TABLE IF NOT EXISTS `ethnicities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ethnicities`
--

INSERT INTO `ethnicities` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Asian', 1, '34', 1, '56', 1, 1, 1, '2023-03-28 04:16:21', '2023-03-28 04:16:53', NULL),
(2, 'African', 1, '34', 1, NULL, 1, 1, NULL, '2023-04-18 09:04:36', '2023-04-18 09:04:36', NULL),
(3, 'Asian African', 1, '34', 1, '56', 1, 1, 1, '2023-09-27 07:43:31', '2023-09-27 07:47:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `e_learning_activity_settings`
--

DROP TABLE IF EXISTS `e_learning_activity_settings`;
CREATE TABLE IF NOT EXISTS `e_learning_activity_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` enum('General','Assignment Brief','Unit Handbook','Harvard Referencing','Lecture/Topic') COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_week` tinyint(4) DEFAULT '0',
  `active` tinyint(4) DEFAULT '1',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `e_learning_activity_settings`
--

INSERT INTO `e_learning_activity_settings` (`id`, `category`, `logo`, `has_week`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'General', 'activity_1_1698145083.png', 1, 1, 1, 1, NULL, '2023-10-24 04:58:03', '2023-10-24 05:44:51'),
(2, 'Assignment Brief', 'activity_2_1698146794.png', 0, 1, 1, 1, NULL, '2023-10-24 04:58:24', '2023-10-24 05:45:07');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_eligibilities`
--

DROP TABLE IF EXISTS `fee_eligibilities`;
CREATE TABLE IF NOT EXISTS `fee_eligibilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_eligibilities`
--

INSERT INTO `fee_eligibilities` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Eligible to pay home fees', 0, NULL, 1, '01', 0, 1, 1, '2023-09-14 05:00:06', '2023-09-14 05:07:45', NULL),
(2, 'Not eligible to pay home fees', 0, NULL, 1, '02', 0, 1, 1, '2023-09-14 05:00:23', '2023-09-14 05:07:27', NULL),
(3, 'Eligibility to pay home fees not assessed', 0, NULL, 1, '03', 0, 1, 1, '2023-09-14 05:00:44', '2023-09-14 05:07:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `form_data_types`
--

DROP TABLE IF EXISTS `form_data_types`;
CREATE TABLE IF NOT EXISTS `form_data_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text_input` text COLLATE utf8mb4_unicode_ci,
  `number_input` double DEFAULT NULL,
  `select_option` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkbox` enum('on','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `switch` enum('on','inactive') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `radio_button` enum('first','second') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_format` date DEFAULT NULL,
  `date_range` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_data_types`
--

INSERT INTO `form_data_types` (`id`, `text_input`, `number_input`, `select_option`, `checkbox`, `switch`, `radio_button`, `phone`, `email`, `date_format`, `date_range`, `deleted_at`, `updated_at`, `created_at`) VALUES
(1, 'First test', 1234, '1', 'no', 'inactive', 'first', '+8801722537936', 'suvra@churchill.ac', '2022-12-07', '7 Dec, 2022 - 7 Jan, 2023', NULL, '2022-12-12 06:33:57', '2022-12-07 03:30:05'),
(2, 'Second test', 1234, NULL, 'on', 'on', 'second', '+8801722537936', 'suvra@churchill.ac', '2022-12-07', '7 Dec, 2022 - 7 Jan, 2023', '2022-12-13 03:25:13', '2022-12-13 03:25:13', '2022-12-07 03:35:39'),
(3, NULL, NULL, NULL, 'no', 'inactive', 'first', '+8801722537936', 'suvra@churchill.ac', '2022-12-07', '7 Dec, 2022 - 7 Jan, 2023', '2022-12-13 07:08:20', '2022-12-13 07:08:20', '2022-12-07 04:11:30'),
(4, NULL, NULL, NULL, 'no', 'inactive', 'first', '+8801722537936', 'suvra@churchill.ac', '2022-12-07', '7 Dec, 2022 - 7 Jan, 2023', '2022-12-08 06:55:41', '2022-12-08 06:55:41', '2022-12-07 04:14:23'),
(5, 'New Test', NULL, NULL, 'no', 'inactive', 'first', '+8801722537936', 'suvra@churchill.ac', '2022-12-07', '7 Dec, 2022 - 7 Jan, 2023', NULL, '2022-12-07 04:22:02', '2022-12-07 04:22:02');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `evening_and_weekend` tinyint(4) DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groups_course_id_foreign` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `course_id`, `name`, `evening_and_weekend`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'A', 0, 1, 1, NULL, '2023-01-10 08:00:29', '2023-02-28 06:05:14'),
(2, 1, 'B', 0, 1, 1, NULL, '2023-02-28 06:05:21', '2023-09-08 04:03:46'),
(3, 1, 'C', 0, 1, 1, NULL, '2023-02-28 06:05:33', '2023-09-08 04:03:35'),
(4, NULL, 'D', 0, 1, NULL, NULL, '2023-02-28 06:05:39', '2023-02-28 06:05:39'),
(5, NULL, 'E', 0, 1, NULL, NULL, '2023-02-28 06:05:46', '2023-02-28 06:05:46'),
(6, 1, 'F', 1, 1, 1, NULL, '2023-09-07 08:48:54', '2023-09-08 04:54:37'),
(7, 1, 'G', 1, 1, 1, NULL, '2023-09-07 09:31:42', '2023-09-08 04:54:50'),
(8, 1, 'H', 1, 1, NULL, NULL, '2023-10-02 07:09:37', '2023-10-02 07:09:37');

-- --------------------------------------------------------

--
-- Table structure for table `hesa_genders`
--

DROP TABLE IF EXISTS `hesa_genders`;
CREATE TABLE IF NOT EXISTS `hesa_genders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hesa_genders`
--

INSERT INTO `hesa_genders` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Yes', 1, '01', 1, '01', 1, 1, NULL, '2023-09-20 09:57:44', '2023-09-20 09:57:44', NULL),
(2, 'No', 1, '02', 1, NULL, 1, 1, 1, '2023-09-20 09:57:56', '2023-09-27 10:14:28', NULL),
(3, 'Information refused', 1, '98', 1, NULL, 1, 1, 1, '2023-09-20 09:58:06', '2023-09-27 10:14:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `highest_qualification_on_entries`
--

DROP TABLE IF EXISTS `highest_qualification_on_entries`;
CREATE TABLE IF NOT EXISTS `highest_qualification_on_entries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `highest_qualification_on_entries`
--

INSERT INTO `highest_qualification_on_entries` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'UK doctorate degree', 1, '11', 1, '2', 1, 1, 1, '2023-09-28 05:29:12', '2023-09-28 05:29:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hr_bank_holidays`
--

DROP TABLE IF EXISTS `hr_bank_holidays`;
CREATE TABLE IF NOT EXISTS `hr_bank_holidays` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hr_holiday_year_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hr_holiday_year_id_frn_key` (`hr_holiday_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hr_bank_holidays`
--

INSERT INTO `hr_bank_holidays` (`id`, `hr_holiday_year_id`, `name`, `start_date`, `end_date`, `duration`, `description`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 3, 'Christmas Day', '2023-12-25', '2023-12-25', 1, NULL, 1, NULL, NULL, '2023-11-01 05:45:43', '2023-11-01 05:45:43'),
(4, 3, 'Boxing Day', '2023-12-26', '2023-12-26', 1, NULL, 1, NULL, NULL, '2023-11-01 05:45:43', '2023-11-01 05:45:43'),
(6, 3, 'New Year\'s Day', '2024-01-01', '2024-01-01', 1, NULL, 1, 1, NULL, '2023-11-01 05:45:43', '2023-11-01 05:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `hr_conditions`
--

DROP TABLE IF EXISTS `hr_conditions`;
CREATE TABLE IF NOT EXISTS `hr_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('Clock In','Clock Out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_frame` tinyint(4) NOT NULL,
  `minutes` int(11) NOT NULL,
  `notify` tinyint(4) NOT NULL COMMENT '1=Yes,0=No',
  `action` tinyint(4) NOT NULL COMMENT '1=Contract,2=Actual,3=Blank',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hr_conditions`
--

INSERT INTO `hr_conditions` (`id`, `type`, `time_frame`, `minutes`, `notify`, `action`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Clock In', 1, 15, 0, 1, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(2, 'Clock In', 2, 7, 1, 1, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(3, 'Clock In', 3, 7, 0, 2, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(4, 'Clock In', 4, 0, 1, 3, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(5, 'Clock Out', 1, 5, 0, 1, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(6, 'Clock Out', 2, 15, 0, 1, 1, NULL, NULL, '2023-11-13 09:01:46', '2023-11-13 09:01:46'),
(7, 'Clock Out', 3, 0, 1, 3, 1, NULL, NULL, '2023-11-13 09:01:47', '2023-11-13 09:01:47');

-- --------------------------------------------------------

--
-- Table structure for table `hr_holiday_years`
--

DROP TABLE IF EXISTS `hr_holiday_years`;
CREATE TABLE IF NOT EXISTS `hr_holiday_years` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `notice_period` int(11) NOT NULL,
  `bf_entitlement` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hr_holiday_years`
--

INSERT INTO `hr_holiday_years` (`id`, `start_date`, `end_date`, `notice_period`, `bf_entitlement`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(2, '2022-04-01', '2023-03-31', 21, 0, 0, 1, NULL, NULL, '2023-11-01 05:39:19', '2023-11-01 05:39:19'),
(3, '2023-04-01', '2024-03-31', 21, 0, 1, 1, NULL, NULL, '2023-11-01 05:40:10', '2023-11-01 05:40:10');

-- --------------------------------------------------------

--
-- Table structure for table `hr_holiday_year_leave_options`
--

DROP TABLE IF EXISTS `hr_holiday_year_leave_options`;
CREATE TABLE IF NOT EXISTS `hr_holiday_year_leave_options` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hr_holiday_year_id` bigint(20) UNSIGNED NOT NULL,
  `leave_option` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hr_holiday_year_id_lo_frn_key` (`hr_holiday_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hr_holiday_year_leave_options`
--

INSERT INTO `hr_holiday_year_leave_options` (`id`, `hr_holiday_year_id`, `leave_option`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(5, 2, 1, 1, NULL, NULL, '2023-11-01 05:40:48', '2023-11-01 05:40:48'),
(6, 3, 1, 1, NULL, NULL, '2023-11-01 05:40:54', '2023-11-01 05:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `instance_terms`
--

DROP TABLE IF EXISTS `instance_terms`;
CREATE TABLE IF NOT EXISTS `instance_terms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_creation_instance_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` enum('Autumn Term','Spring Term','Summer Term','Winter Term') COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_term` tinyint(4) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_teaching_weeks` int(11) NOT NULL,
  `teaching_start_date` date NOT NULL,
  `teaching_end_date` date NOT NULL,
  `revision_start_date` date NOT NULL,
  `revision_end_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instance_terms_course_creation_instance_id_foreign` (`course_creation_instance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instance_terms`
--

INSERT INTO `instance_terms` (`id`, `course_creation_instance_id`, `name`, `term`, `session_term`, `start_date`, `end_date`, `total_teaching_weeks`, `teaching_start_date`, `teaching_end_date`, `revision_start_date`, `revision_end_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2022 Sep HND', 'Autumn Term', 1, '2022-07-01', '2023-01-31', 3, '2023-01-05', '2023-01-31', '2023-01-26', '2023-01-30', 1, 1, NULL, '2023-01-26 04:23:31', '2023-10-09 02:49:30'),
(2, 1, '2022 Jan HND', 'Winter Term', 2, '2023-01-01', '2023-01-31', 12, '2023-01-01', '2023-01-31', '2023-01-01', '2023-01-31', 1, 1, NULL, '2023-01-27 04:33:01', '2023-01-27 04:58:29'),
(3, 1, 'April 2022', 'Spring Term', 1, '2022-07-01', '2022-11-30', 3, '2023-01-05', '2023-01-31', '2023-01-26', '2023-01-31', 1, 1, NULL, '2023-02-07 11:27:50', '2023-10-09 02:49:16'),
(4, 4, '2021 Sep HND', 'Spring Term', 1, '2023-03-01', '2023-03-31', 3, '2023-03-01', '2023-03-31', '2023-03-30', '2023-03-31', 1, NULL, NULL, '2023-02-24 05:59:41', '2023-02-24 05:59:41'),
(5, 5, '2021 Jan HND', 'Winter Term', 2, '2023-04-01', '2023-04-30', 5, '2023-04-01', '2023-04-30', '2023-04-29', '2023-04-30', 1, NULL, NULL, '2023-02-24 06:00:45', '2023-02-24 06:00:45'),
(6, 3, '2021 April', 'Spring Term', 1, '2021-04-01', '2021-08-31', 3, '2021-04-01', '2021-08-31', '2021-08-25', '2021-08-31', 1, NULL, NULL, '2023-10-09 02:51:29', '2023-10-09 02:51:29'),
(7, 3, '2021 Sep HND', 'Winter Term', 2, '2021-09-01', '2021-12-31', 1, '2021-04-01', '2021-08-31', '2021-04-01', '2021-08-31', 1, NULL, NULL, '2023-10-09 02:53:01', '2023-10-09 02:53:01');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_batches`
--

INSERT INTO `job_batches` (`id`, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at`) VALUES
('9a2b92ba-09d4-4675-90f7-8fdb1f6938bf', '', 9, 0, 0, '[]', 'a:0:{}', NULL, 1695119359, 1695119359),
('9a2b9d8f-6bdc-45f4-a0de-ce6551a4e73c', '', 9, 0, 0, '[]', 'a:0:{}', NULL, 1695121176, 1695121177),
('9a2bab56-4709-402c-b88a-577832a7b46f', '', 9, 0, 0, '[]', 'a:0:{}', NULL, 1695123488, 1695123488),
('9a2c028b-40ef-4041-a62f-ab6f417ee839', '', 10, 0, 0, '[]', 'a:0:{}', NULL, 1695138119, 1695138119),
('9a2d71fc-8c5c-4008-a9cd-004f99bf1510', '', 16, 0, 0, '[]', 'a:0:{}', NULL, 1695199765, 1695199766),
('9a3da081-d9b1-4e9e-9422-733ba7cf157a', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1695894765, 1695894766),
('9a3da835-faf4-499a-b1cf-6da6050d2b8b', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1695896057, 1695896058),
('9a3dfc60-cd41-44e5-b76c-8969cb339308', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1695910178, 1695910180),
('9a61fe73-f42b-4e22-8e94-ea0b3158cb85', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1697456715, 1697456718),
('9a645bd6-3954-412d-9338-11948a37b147', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1697558281, 1697558283),
('9a660340-8a39-43e1-9107-4c189e0a220c', '', 18, 0, 0, '[]', 'a:0:{}', NULL, 1697629319, 1697629320);

-- --------------------------------------------------------

--
-- Table structure for table `kins_relations`
--

DROP TABLE IF EXISTS `kins_relations`;
CREATE TABLE IF NOT EXISTS `kins_relations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kins_relations`
--

INSERT INTO `kins_relations` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Brother', 1, '34', 1, '56', 1, 1, 1, '2023-03-28 06:33:26', '2023-09-27 08:36:09', '2023-09-27 08:36:09'),
(8, 'Sister', 1, '1', 1, '2', 1, 1, 1, '2023-09-27 08:29:54', '2023-09-27 08:34:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `letter_header_footers`
--

DROP TABLE IF EXISTS `letter_header_footers`;
CREATE TABLE IF NOT EXISTS `letter_header_footers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Header','Footer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `for_letter` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
  `for_email` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `letter_sets`
--

DROP TABLE IF EXISTS `letter_sets`;
CREATE TABLE IF NOT EXISTS `letter_sets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `letter_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `letter_sets`
--

INSERT INTO `letter_sets` (`id`, `letter_type`, `letter_title`, `description`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Types 0111', 'Welcome Message For New Students.', '<p>Welcome Message For New Students. This is from updated.</p>', 1, 1, NULL, '2023-06-20 08:40:35', '2023-06-21 04:55:12'),
(2, 'Type 02', 'Welcome New Student', '<p>This is a test template</p>', 1, NULL, NULL, '2023-10-06 04:02:53', '2023-10-06 04:02:53');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(21, '2023_01_11_000001_create_course_creations_table', 10),
(6, '2023_01_05_105818_create_awarding_bodies_table', 3),
(7, '2023_01_05_112316_create_source_tuition_fees_table', 4),
(8, '2023_01_05_120823_create_courses_table', 5),
(9, '2023_01_05_151835_create_module_levels_table', 6),
(10, '2023_01_05_160500_create_course_modules_table', 7),
(11, '2023_01_06_121930_create_course_base_datafutures_table', 8),
(12, '2023_01_09_152851_create_course_module_base_assesments_table', 9),
(19, '2023_01_09_073823_create_academic_years_table', 10),
(18, '2023_01_04_105808_create_groups_table', 10),
(17, '2023_01_04_085225_create_venues_table', 10),
(20, '2023_01_10_120108_create_module_datafutures_table', 10),
(22, '2023_01_11_134834_create_course_creation_availabilities_table', 11),
(23, '2023_01_11_165437_create_course_creation_datafutures_table', 12),
(24, '2023_01_12_100422_create_course_creation_instances_table', 13),
(25, '2023_01_12_100423_create_instance_terms_table', 14),
(26, '2023_01_13_104712_create_module_creations_table', 15),
(27, '2023_01_16_140519_create_assessments_table', 16),
(28, '2023_01_10_114546_create_rooms_table', 17),
(30, '2023_02_23_153028_create_plans_table', 18),
(31, '2023_03_06_113447_add_code_to_academic_years_table', 19),
(32, '2023_03_06_115649_add_code_to_awarding_bodies_table', 19),
(33, '2023_03_06_121832_add_code_to_source_tuition_fees_table', 19),
(34, '2023_03_08_121259_create_bank_holidays_table', 20),
(35, '2023_03_14_094322_create_plans_date_lists_table', 20),
(36, '2023_03_06_141435_add_course_creation_qualification_id_to_course_creations_table', 20),
(37, '2023_03_27_103949_create_titles_table', 21),
(38, '2023_03_28_095258_create_ethnicities_table', 22),
(39, '2023_03_28_103821_create_kins_relations_table', 23),
(40, '2023_03_28_124031_create_sexual_orientations_table', 24),
(41, '2023_03_29_091233_create_religions_table', 25),
(42, '2023_03_29_100128_create_statuses_table', 26),
(43, '2023_03_29_115227_create_countries_table', 27),
(44, '2023_03_30_135056_create_disabilities_table', 28),
(45, '2023_03_27_143900_create_applicant_users_table', 29),
(46, '2023_03_29_215928_add_social_login_field', 29),
(63, '2023_04_04_125017_create_applicant_qualifications_table', 38),
(48, '2023_04_04_160824_create_addresses_table', 31),
(65, '2023_04_04_161642_create_employment_references_table', 38),
(64, '2023_04_04_160037_create_applicant_employments_table', 38),
(51, '2023_04_04_125007_create_applicants_table', 33),
(59, '2023_04_07_150607_create_applicant_other_details_table', 37),
(58, '2023_04_10_090035_create_applicant_kin_table', 36),
(54, '2023_04_10_090756_create_applicant_disabilities_table', 33),
(57, '2023_04_10_091453_create_applicant_contacts_table', 35),
(56, '2023_04_10_092101_create_applicant_proposed_courses_table', 34),
(66, '2023_03_31_141103_add_codes_to_awarding_bodies_table', 39),
(67, '2023_03_31_161159_add_codes_to_source_tuition_fees_table', 39),
(68, '2023_04_03_083245_add_codes_to_academic_years_table', 39),
(69, '2023_04_18_133335_create_applicant_archives_table', 39),
(70, '2023_05_02_085509_create_applicant_temporary_emails_table', 40),
(71, '2023_05_02_143436_add_mobile_verirification_field', 41),
(72, '2023_04_26_114540_create_document_settings_table', 42),
(73, '2023_05_03_123530_create_applicant_document_lists_table', 43),
(74, '2023_05_04_083510_create_departments_table', 44),
(81, '2023_05_04_081433_create_permission_categories_table', 81),
(76, '2023_05_05_114112_create_roles_table', 44),
(77, '2023_05_08_122942_create_permission_templates_table', 45),
(78, '2023_05_09_155919_create_user_roles_table', 46),
(79, '2023_05_11_133955_create_process_lists_table', 47),
(80, '2023_05_12_102651_create_task_lists_table', 47),
(82, '2023_05_16_112601_create_applicant_tasks_table', 82),
(83, '2023_05_18_105405_create_applicant_documents_table', 83),
(84, '2023_05_18_110348_create_applicant_task_documents_table', 83),
(85, '2023_05_30_104655_create_task_status_table', 84),
(86, '2023_05_30_111115_create_task_list_statuses_table', 85),
(87, '2023_05_30_130435_create_task_list_users_table', 86),
(88, '2023_05_31_124009_create_applicant_task_logs_table', 87),
(89, '2023_06_07_104156_create_applicant_notes_table', 88),
(90, '2023_06_15_111534_add_photo_to_applicants_table', 89),
(91, '2023_06_16_092445_create_comon_smtps_table', 90),
(92, '2023_06_19_130819_create_applicant_emails_table', 91),
(93, '2023_06_19_131957_create_applicant_emails_attachments_table', 91),
(94, '2023_06_20_090004_create_applicant_sms_table', 92),
(95, '2023_06_20_133425_create_letter_sets_table', 93),
(96, '2023_06_21_122309_create_signatories_table', 94),
(97, '2023_07_26_095626_create_options_table', 95),
(100, '2019_08_19_000000_create_failed_jobs_table', 98),
(99, '2023_07_31_110121_create_applicant_letters_table', 97),
(101, '2023_04_05_161730_create_jobs_table', 98),
(102, '2023_07_31_151644_create_applicant_interviews_table', 98),
(103, '2023_07_31_155305_create_applicant_view_unlocks_table', 98),
(104, '2023_01_04_113127_create_semesters_table', 99),
(105, '2023_01_04_115715_create_course_creation_qualifications_table', 99),
(106, '2023_08_11_101754_create_sms_templates_table', 100),
(107, '2023_08_11_102019_create_email_templates_table', 100),
(108, '2023_08_14_145032_create_letter_header_footers_table', 100),
(109, '2023_08_22_124408_add_pin_to_applicant_letters_table', 100),
(110, '2023_08_30_140500_add_comon_smtp_id_to_applicant_letters_table', 101),
(111, '2023_08_31_130746_add_rejected_reason_to_applicants_table', 102),
(112, '2023_09_07_144054_alter_table_groups', 103),
(167, '2023_09_17_145842_alter_created_by_to_student_qualifications', 108),
(166, '2023_09_12_170618_alter_applicant_document_id_to_student_interviews', 108),
(165, '2023_09_12_170209_alter_applicant_task_id_to_student_interviews', 108),
(164, '2023_09_12_140739_alter_applicant_document_id_to_student_letters', 108),
(163, '2023_09_11_163444_add_nationality_to_students_table', 108),
(162, '2023_09_04_183015_create_student_letters_table', 108),
(161, '2023_09_04_182552_create_student_task_documents_table', 108),
(160, '2023_09_04_181553_create_student_proposed_courses_table', 108),
(159, '2023_09_04_181124_create_student_qualifications_table', 108),
(158, '2023_09_04_173115_create_student_other_details_table', 108),
(157, '2023_09_04_172437_create_student_notes_table', 108),
(156, '2023_09_04_171630_create_student_contacts_table', 108),
(130, '2023_09_13_161626_create_applicant_proof_of_ids_table', 105),
(131, '2023_09_13_144954_create_hesa_genders_table', 106),
(132, '2023_09_14_023220_create_fee_eligibilities_table', 106),
(133, '2023_09_14_094058_create_applicant_fee_eligibilities_table', 106),
(155, '2023_09_04_170341_create_student_tasks_table', 108),
(135, '2023_09_12_101944_create_job_batches_table', 107),
(154, '2023_09_04_165659_create_student_sms_table', 108),
(153, '2023_09_04_164334_create_student_employments_table', 108),
(152, '2023_09_04_164002_create_student_emails__attachments_table', 108),
(151, '2023_09_04_163422_create_student_emails_table', 108),
(150, '2023_09_04_155545_create_student_interviews_table', 108),
(149, '2023_09_04_154704_create_student_documents_table', 108),
(175, '2023_09_04_154343_create_student_disabilities_table', 110),
(147, '2023_09_04_141648_create_student_kins_table', 108),
(146, '2023_09_04_140022_create_students_table', 108),
(168, '2023_09_17_152048_alter_countryid_to_student_contacts', 108),
(169, '2023_09_17_160206_alter_start_end_date_to_student_employments', 108),
(170, '2023_09_17_170618_alter_document_setting_id_to_student_documents', 108),
(171, '2023_09_17_170618_alter_task_list_id_to_student_tasks', 108),
(172, '2023_09_19_170618_add_applicant_task_id_to_student_tasks', 108),
(174, '2023_09_19_111757_add_academic_year_id_to_applicant_proposed_courses_table', 109),
(176, '2023_09_19_170619_alter_course_creations_id _to_student_proposed_courses', 111),
(177, '2023_09_19_170622_add_semester_id_to_student_proposed_courses', 112),
(178, '2023_09_19_161223_create_student_archives_table', 113),
(179, '2023_09_19_174456_alter_sex_orientation_id_to_student_other_details', 114),
(180, '2023_09_19_175112_alter_religion_id_to_student_other_details', 114),
(181, '2023_09_19_181547_create_student_fee_eligibilities_table', 114),
(182, '2023_09_19_181932_create_student_proof_of_ids_table', 114),
(183, '2023_09_20_054149_add_sms_template_id_to_student_sms_table', 114),
(184, '2023_09_20_062032_alter_common_smtp_id_to_applicant_letters_table', 114),
(185, '2023_09_20_063009_rename_common_smtp_id_to_student_letters_table', 114),
(186, '2023_09_20_063822_alter_common_smtp_id_to_student_letters_table', 114),
(187, '2023_09_20_070954_add_applicant_letter_id_to_student_letters_table', 114),
(188, '2023_09_20_074240_alt_student_document_id_to_student_letters_table', 114),
(189, '2023_09_20_160559_create_student_other_personal_information_table', 115),
(190, '2023_09_20_131628_add_users_to_student_interviews_table', 116),
(191, '2023_09_20_141744_add_email_template_id_to_student_emails_table', 116),
(192, '2023_09_20_143904_add_email_template_id_to_applicant_emails_table', 116),
(193, '2023_09_20_163846_rename_student_email_attachment_table', 116),
(194, '2023_09_21_085321_alter_table_student_other_personal_information', 117),
(196, '2023_09_21_095519_alter_table_students', 118),
(197, '2023_09_21_142142_create_student_employment_references_table', 119),
(198, '2023_09_22_075809_create_consent_policies_table', 120),
(199, '2023_09_22_095336_create_student_consents_table', 121),
(200, '2023_09_22_115307_add_status_to_student_consents_table', 122),
(202, '2023_09_22_132539_create_referral_codes_table', 123),
(203, '2023_09_22_154003_add_applicant_refferal_code_to_students_table', 124),
(204, '2023_09_22_154003_add_applicant_refferal_code_to_students_table', 124),
(205, '2023_09_24_152607_alter_fields_to_student_contacts_table', 125),
(206, '2023_09_24_164115_alter_fields_to_student_kins_table', 125),
(207, '2023_09_24_165019_alter_fields_to_student_employments_table', 125),
(208, '2023_09_25_164613_alter_table_student_notes', 125),
(209, '2023_09_26_122258_alter_table_student_notes', 125),
(210, '2023_09_26_135004_create_student_task_log_table', 126),
(211, '2023_09_27_124324_add_active_to_titles_table', 127),
(212, '2023_09_27_133709_add_active_to_ethnicities_table', 128),
(213, '2023_09_27_142122_add_active_to_kins_relations_table', 129),
(214, '2023_09_27_150529_add_active_to_sexual_orientations_table', 130),
(215, '2023_09_27_154846_add_active_to_religions_table', 131),
(216, '2023_09_27_160847_add_active_to_hesa_genders_table', 132),
(217, '2023_09_28_083616_add_active_to_countries_table', 133),
(218, '2023_09_28_090239_create_apel_credits_table', 134),
(219, '2023_09_27_144204_add_active_to_disabilities_table', 135),
(220, '2023_09_27_160548_add_active_to_fee_eligibilities_table', 135),
(221, '2023_09_28_094654_delete_country_from_student_contacts_table', 136),
(222, '2023_09_28_110937_create_highest_qualification_on_entries_table', 137),
(223, '2023_09_28_113223_create_country_of_permanent_addresses_table', 138),
(224, '2023_09_28_114741_create_previous_providers_table', 139),
(225, '2023_09_28_115955_create_qualification_type_identifiers_table', 140),
(226, '2023_09_28_121149_create_reason_for_engagement_endings_table', 141),
(227, '2023_09_28_114352_create_term_time_accommodation_types_table', 142),
(228, '2023_09_28_122952_create_student_identifiers_table', 142),
(229, '2023_09_29_110311_add_active_to_addresses_table', 143),
(230, '2023_09_29_131024_alter_term_time_accommodation_type_to_student_contacts', 144),
(231, '2023_10_02_083801_create_student_users_table', 145),
(232, '2023_10_02_102218_create_student_awarding_body_details_table', 145),
(233, '2023_10_02_143736_create_student_course_relations_table', 146),
(234, '2023_10_02_144443_alter_table_student_awarding_body_details', 147),
(235, '2023_10_02_151804_alter_table_student_proposed_courses', 147),
(236, '2023_10_03_092707_alter_table_student_fee_eligibilities', 147),
(238, '2023_10_04_155247_alter_table_options', 148),
(239, '2023_10_05_135623_alter_table_course_creation_qualifications', 149),
(240, '2023_10_06_103124_add_is_default_to_comon_smtps_table', 150),
(242, '2023_10_06_110724_create_venue_ip_addresses_table', 151),
(243, '2023_10_06_142310_rename_table', 152),
(244, '2023_10_10_115344_create_assigns_table', 153),
(245, '2023_10_09_135847_alter_user_id_from_students_table', 153),
(246, '2023_10_11_114701_add_sex_identifier_to_student_other_details_table', 154),
(247, '2023_10_17_144227_alter_table_applicants', 154),
(248, '2023_10_17_154950_alter_table_students', 155),
(249, '2023_10_18_111750_alter_table_student_other_details', 156),
(250, '2023_10_18_112241_drop_table_student_other_personal_information', 156),
(251, '2023_10_18_114528_alter_table_applicant_other_details', 157),
(252, '2023_10_17_134728_create_employee_work_type_table', 158),
(253, '2023_10_17_145058_add_staff_to_document_settings_table', 158),
(254, '2023_10_17_153638_create_employee_notice_periods_table', 158),
(255, '2023_10_17_155821_create_employment_periods_table', 158),
(256, '2023_10_17_163031_create_employment_ssp_terms_table', 158),
(258, '2023_10_19_125657_alter_table_student_archives', 159),
(259, '2023_10_19_134357_alter_table_addresses', 160),
(260, '2023_10_19_152738_alter_table_student_consents', 161),
(261, '2023_10_23_124638_create_plan_participants_table', 162),
(262, '2023_10_23_141833_alter_table_plans', 163),
(265, '2023_10_24_083436_create_e_learning_activity_settings_table', 164),
(266, '2023_10_19_151205_create_employee_job_titles_table', 165),
(267, '2023_10_19_160256_create_employee_work_document_types_table', 165),
(268, '2023_10_20_084507_create_employees_table', 165),
(269, '2023_10_20_095056_create_employments_table', 165),
(270, '2023_10_20_102539_create_employee_venue_table', 165),
(271, '2023_10_20_103054_create_employee_terms_table', 165),
(272, '2023_10_20_110640_create_employee_eligibilites_table', 165),
(273, '2023_10_20_111254_create_employee_emergency_contacts_table', 165),
(274, '2023_10_20_143834_add_work_permit_type_field_to_employee_eligibilites_table', 165),
(275, '2023_10_20_144203_create_employee_work_permit_types_table', 165),
(276, '2023_10_24_105605_alter_document_type_to_employee_eligibilites_table', 165),
(277, '2023_10_25_102358_alter_table_permission_templates', 166),
(278, '2023_10_25_103608_create_permission_template_groups_table', 167),
(279, '2023_10_25_182010_create_employee_disability_table', 168),
(280, '2023_10_25_191328_drop_disability_id_from_employees_table', 168),
(282, '2023_10_26_095040_create_employee_payment_settings_table', 169),
(286, '2023_10_26_130627_create_employee_info_penssion_schemes_table', 173),
(284, '2023_10_26_110549_create_employee_bank_details_table', 171),
(285, '2023_10_26_121704_alter_table_employee_payment_settings', 172),
(287, '2023_10_26_135135_create_employee_hour_authorised_bies_table', 174),
(288, '2023_10_26_141106_create_employee_holiday_authorised_bies_table', 175),
(289, '2023_10_26_144056_create_employee_penssion_schemes_table', 176),
(290, '2023_10_27_114619_create_employee_working_patterns_table', 177),
(292, '2023_10_30_122932_create_employee_working_pattern_details_table', 178),
(293, '2023_10_27_065759_add_photo_to_employees_table', 179),
(294, '2023_10_31_122015_create_hr_holiday_years_table', 180),
(296, '2023_10_31_141929_create_hr_bank_holidays_table', 181),
(297, '2023_11_01_092900_create_hr_holiday_year_leave_options_table', 182),
(299, '2023_11_02_084344_create_employee_holiday_adjustments_table', 183),
(300, '2023_11_02_105333_create_employee_working_pattern_pays_table', 184),
(301, '2023_11_02_105657_alter_table_employee_working_patterns', 185),
(302, '2023_10_31_115259_create_attendances_table', 186),
(303, '2023_11_01_133540_create_attendance_feed_statuses_table', 186),
(304, '2023_11_01_194529_add_columns_to_attendances_table', 186),
(305, '2023_11_02_092435_add_columns_to_plans_date_lists_table', 186),
(306, '2023_11_03_114415_create_attendance_informations_table', 186),
(307, '2023_11_08_081442_create_employee_leaves_table', 187),
(308, '2023_11_08_083013_create_employee_leave_days_table', 187),
(309, '2023_11_09_123935_create_employee_attendance_machines_table', 188),
(310, '2023_11_10_044505_alter_table_employments', 189),
(311, '2023_11_10_045023_create_employee_attendance_lives_table', 189),
(312, '2023_11_10_045837_create_employee_attendance_punch_histories_table', 189),
(313, '2023_11_13_084823_create_employee_attendances_table', 190),
(314, '2023_11_13_094052_create_employee_attendance_day_breaks_table', 190),
(315, '2023_11_13_102626_alter_table_e_learning_activity_settings', 191),
(316, '2023_11_13_135444_create_hr_conditions_table', 192),
(317, '2023_11_17_140546_alter_table_employees', 193),
(318, '2023_11_20_101843_alter_table_employee_eligibilites', 194),
(319, '2023_11_09_105740_create_employee_documents_table', 195),
(320, '2023_11_09_111530_create_employee_notes_table', 195);

-- --------------------------------------------------------

--
-- Table structure for table `module_creations`
--

DROP TABLE IF EXISTS `module_creations`;
CREATE TABLE IF NOT EXISTS `module_creations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `instance_term_id` bigint(20) UNSIGNED NOT NULL,
  `course_module_id` bigint(20) UNSIGNED NOT NULL,
  `module_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `module_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('core','specialist','optional') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_value` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_value` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moodle_enrollment_key` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_type` enum('Theory','Practical','Tutorial','Seminar') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_creations_instance_term_id_foreign` (`instance_term_id`),
  KEY `module_creations_course_module_id_foreign` (`course_module_id`),
  KEY `module_creations_module_level_id_foreign` (`module_level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_creations`
--

INSERT INTO `module_creations` (`id`, `instance_term_id`, `course_module_id`, `module_level_id`, `module_name`, `code`, `status`, `credit_value`, `unit_value`, `moodle_enrollment_key`, `class_type`, `submission_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(25, 1, 1, NULL, 'Module 01', '098713', 'core', '3', '34', 'ENKEY01', 'Practical', '2023-03-31', 1, 1, NULL, '2023-02-23 03:30:04', '2023-02-23 05:48:14'),
(26, 1, 2, 1, 'Module 02', '098713', 'core', '37', '34456', 'ENKEY02', 'Tutorial', NULL, 1, NULL, NULL, '2023-02-23 03:30:04', '2023-02-23 03:30:04'),
(27, 2, 1, NULL, 'Module 01', '098713', 'core', '3', '34', 'ENKEY03', 'Seminar', NULL, 1, NULL, NULL, '2023-02-23 07:33:45', '2023-02-23 07:33:45'),
(28, 2, 2, 1, 'Module 02', '098713', 'core', '37', '34456', 'ENKEY04', 'Theory', '2023-02-28', 1, 1, NULL, '2023-02-23 07:33:45', '2023-02-23 09:17:37'),
(29, 3, 1, NULL, 'Module 01', '098713', 'core', '3', '34', 'ENKEY05', 'Theory', NULL, 1, NULL, NULL, '2023-02-27 06:37:28', '2023-02-27 06:37:28'),
(30, 3, 3, 2, 'Module 03', '098713', 'core', '34', '34444', 'ENKEY06', 'Theory', NULL, 1, NULL, NULL, '2023-02-27 06:37:28', '2023-02-27 06:37:28');

-- --------------------------------------------------------

--
-- Table structure for table `module_datafutures`
--

DROP TABLE IF EXISTS `module_datafutures`;
CREATE TABLE IF NOT EXISTS `module_datafutures` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_module_id` bigint(20) UNSIGNED NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_type` enum('date','text','number') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_desc` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_datafutures_course_module_id_foreign` (`course_module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_levels`
--

DROP TABLE IF EXISTS `module_levels`;
CREATE TABLE IF NOT EXISTS `module_levels` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_levels`
--

INSERT INTO `module_levels` (`id`, `name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Level 01', 1, 1, '2023-01-05 09:31:33', '2023-01-05 09:32:23', NULL),
(2, 'Level 02', 1, NULL, '2023-01-05 09:31:42', '2023-01-05 09:31:42', NULL),
(3, 'Level 03', 1, NULL, '2023-01-05 09:31:53', '2023-01-05 09:31:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE IF NOT EXISTS `options` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `category`, `name`, `value`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'SMS', 'active_api', '2', 1, 1, NULL, NULL, '2023-10-06 02:52:49'),
(2, 'SMS', 'textlocal_api', 'TLxqLAv7LXs-UVlXEqZj7hrv1DEW1GuthHBPvDdesg', 1, 1, NULL, '2023-07-26 10:48:34', '2023-10-06 02:53:03'),
(3, 'SMS', 'smseagle_api', 'ZbzFiOcvDuappjuaaRuzL9M4FamkSZxN', 1, 1, NULL, NULL, '2023-10-06 02:53:03'),
(20, 'SITE_SETTINGS', 'company_name', 'London Churchill College', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:38'),
(21, 'SITE_SETTINGS', 'company_address', 'Barclay Hall, 156B Green Street E7 8JQ', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:38'),
(22, 'SITE_SETTINGS', 'company_phone', '+44 (0) 2073771077', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:38'),
(23, 'SITE_SETTINGS', 'company_email', 'limon@churchill.ac', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-04 10:20:39'),
(24, 'SITE_SETTINGS', 'company_term_condition_url', 'https://londonchurchillcollege.ac.uk/terms-and-conditions/', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:38'),
(25, 'SITE_SETTINGS', 'company_e_learning_url', 'https://moodle.londonchurchillcollege.ac.uk', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:38'),
(26, 'SITE_SETTINGS', 'company_doc_req_url', NULL, 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:52:37'),
(27, 'SITE_SETTINGS', 'company_hcuci', '10030391', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:47:39'),
(28, 'SITE_SETTINGS', 'site_logo', 'company_logo.svg', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:52:04'),
(29, 'SITE_SETTINGS', 'site_favicon', 'company_favicon.svg', 1, 1, NULL, '2023-10-04 10:20:39', '2023-10-05 03:52:04'),
(30, 'SITE_SETTINGS', 'company_right', '© 2023 by London Churchill College. All Rights Reserved.', 1, 1, NULL, '2023-10-05 03:47:39', '2023-10-05 03:47:39'),
(31, 'ADDR_ANYWHR_API', 'anywhere_api', 'gy26-rh34-cf82-wd85', 1, 1, NULL, '2023-10-05 04:41:22', '2023-10-05 04:41:22'),
(32, 'ADDR_ANYWHR_API', 'anywhere_tag', 'greedy0d-21', 1, 1, NULL, '2023-10-05 04:41:22', '2023-10-05 04:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_categories`
--

DROP TABLE IF EXISTS `permission_categories`;
CREATE TABLE IF NOT EXISTS `permission_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_categories`
--

INSERT INTO `permission_categories` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Admission', 1, 1, NULL, '2023-05-15 04:37:03', '2023-10-24 07:39:49'),
(2, 'Live Student', 1, 1, NULL, '2023-05-15 04:37:13', '2023-10-24 07:39:40'),
(3, 'HR', 1, NULL, NULL, '2023-10-24 07:39:58', '2023-10-24 07:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `permission_templates`
--

DROP TABLE IF EXISTS `permission_templates`;
CREATE TABLE IF NOT EXISTS `permission_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_category_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_templates_permission_category_id_index` (`permission_category_id`),
  KEY `permission_templates_role_id_index` (`role_id`),
  KEY `permission_templates_department_id_index` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_templates`
--

INSERT INTO `permission_templates` (`id`, `permission_category_id`, `role_id`, `department_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, NULL, NULL, '2023-10-25 06:29:29', '2023-10-25 08:17:52'),
(2, 3, 1, NULL, 1, NULL, NULL, '2023-10-25 08:02:07', '2023-10-25 08:17:52'),
(3, 2, 1, NULL, 1, NULL, NULL, '2023-10-25 08:02:07', '2023-10-25 08:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `permission_template_groups`
--

DROP TABLE IF EXISTS `permission_template_groups`;
CREATE TABLE IF NOT EXISTS `permission_template_groups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_template_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `R` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `W` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `D` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_template_param_groups_permission_template_id_foreign` (`permission_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_template_groups`
--

INSERT INTO `permission_template_groups` (`id`, `permission_template_id`, `name`, `R`, `W`, `D`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Personal Data', '1', '1', '0', 1, NULL, NULL, '2023-10-25 09:03:20', '2023-10-25 09:03:20'),
(2, 1, 'Contact Details', '1', '0', '0', 1, NULL, NULL, '2023-10-25 09:04:48', '2023-10-25 09:04:48'),
(3, 1, 'Next of Kin', '1', '1', '0', 1, NULL, NULL, '2023-10-25 09:05:08', '2023-10-25 09:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `module_creation_id` bigint(20) UNSIGNED NOT NULL,
  `venue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rooms_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sat` tinyint(4) DEFAULT '0',
  `sun` tinyint(4) DEFAULT '0',
  `mon` tinyint(4) DEFAULT '0',
  `tue` tinyint(4) DEFAULT '0',
  `wed` tinyint(4) DEFAULT '0',
  `thu` tinyint(4) DEFAULT '0',
  `fri` tinyint(4) DEFAULT '0',
  `module_enrollment_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `tutor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `personal_tutor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `virtual_room` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `visibility` tinyint(4) DEFAULT '1',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plans_course_id_foreign` (`course_id`),
  KEY `plans_module_creation_id_foreign` (`module_creation_id`),
  KEY `plans_venue_id_foreign` (`venue_id`),
  KEY `plans_rooms_id_foreign` (`rooms_id`),
  KEY `plans_group_id_foreign` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `course_id`, `module_creation_id`, `venue_id`, `rooms_id`, `group_id`, `name`, `start_time`, `end_time`, `label`, `sat`, `sun`, `mon`, `tue`, `wed`, `thu`, `fri`, `module_enrollment_key`, `submission_date`, `tutor_id`, `personal_tutor_id`, `virtual_room`, `note`, `visibility`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(15, 1, 25, 1, 1, 1, '', '10:15:00', '11:15:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 3, 4, NULL, 'Update Note', 1, 1, 1, '2023-03-03 09:00:32', '2023-09-04 10:54:29', NULL),
(16, 1, 25, 1, 1, 2, '', '11:00:00', '12:00:00', '', 0, 0, 0, 1, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, 5, 'http://video.com', 'Hi, This is a test note.', 1, 1, 1, '2023-03-03 09:00:32', '2023-10-23 09:02:33', NULL),
(17, 1, 25, 1, 3, 2, '', '13:00:00', '14:00:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 3, 8, NULL, NULL, 1, 1, 1, '2023-03-03 09:00:32', '2023-10-23 09:02:35', NULL),
(18, 1, 25, 1, 1, 4, '', '10:11:00', '10:11:00', '', 0, 0, 0, 0, 1, 0, 0, 'ENKEY01', '2023-03-31', 9, 5, '', '', 1, 1, 1, '2023-03-03 09:00:32', '2023-09-04 10:54:38', NULL),
(19, 1, 25, 1, 2, 3, '', '10:00:00', '10:00:00', '', 0, 0, 0, 0, 0, 1, 0, 'ENKEY01', '2023-03-31', 6, 4, '', '', 1, 1, 1, '2023-03-03 09:00:32', '2023-10-23 10:21:47', NULL),
(20, 1, 25, 1, 3, 5, '', '14:00:00', '15:00:00', '', 0, 0, 0, 0, 0, 0, 1, 'ENKEY01', '2023-03-31', 5, 9, '', '', 1, 1, 1, '2023-03-03 09:00:32', '2023-09-04 10:54:47', NULL),
(21, 1, 25, 1, 2, 4, '', '11:30:00', '12:30:00', '', 1, 0, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 5, 7, '', '', 1, 1, 1, '2023-03-03 09:00:32', '2023-09-04 10:54:51', NULL),
(22, 1, 25, 1, 4, 4, '', '14:50:00', '15:50:00', '', 0, 1, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, 4, '', '', 1, 1, 1, '2023-03-03 09:00:32', '2023-09-04 10:54:56', NULL),
(23, 1, 25, 2, 7, 2, '', '12:00:00', '13:00:00', '', 1, 0, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 5, 7, '', '', 1, 1, 1, '2023-03-03 09:04:15', '2023-10-23 09:02:35', NULL),
(24, 1, 25, 1, 3, 3, '', '12:00:00', '13:00:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 8, 7, 'http://video.com', 'This is note.', 1, 1, 1, '2023-03-06 08:44:15', '2023-10-23 10:21:47', NULL),
(25, 1, 25, 1, 1, 3, '', '11:00:00', '12:00:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, NULL, '', '', 1, 1, 1, '2023-09-04 10:56:43', '2023-10-23 10:21:47', NULL),
(26, 1, 25, 1, 2, 3, '', '01:00:00', '02:00:00', '', 0, 0, 0, 1, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, 2, NULL, 'This is test note', 1, 1, 1, '2023-09-04 10:56:43', '2023-10-23 10:21:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plans_date_lists`
--

DROP TABLE IF EXISTS `plans_date_lists`;
CREATE TABLE IF NOT EXISTS `plans_date_lists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `feed_given` tinyint(4) DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plans_date_lists_plan_id_foreign` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans_date_lists`
--

INSERT INTO `plans_date_lists` (`id`, `plan_id`, `name`, `date`, `feed_given`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 26, 'Teaching', '2023-01-10', 0, 1, NULL, '2023-10-10 03:58:02', '2023-10-10 03:58:02', NULL),
(2, 26, 'Teaching', '2023-01-17', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(3, 26, 'Teaching', '2023-01-24', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(4, 26, 'Teaching', '2023-01-31', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(5, 25, 'Teaching', '2023-01-09', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(6, 25, 'Teaching', '2023-01-23', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(7, 25, 'Revision', '2023-01-30', 0, 1, NULL, '2023-10-10 03:58:03', '2023-10-10 03:58:03', NULL),
(22, 24, 'Teaching', '2023-01-09', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(23, 24, 'Teaching', '2023-01-23', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(24, 24, 'Revision', '2023-01-30', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(25, 19, 'Teaching', '2023-01-05', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(26, 19, 'Teaching', '2023-01-12', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(27, 19, 'Teaching', '2023-01-19', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(28, 19, 'Revision', '2023-01-26', 0, 1, NULL, '2023-10-10 04:04:06', '2023-10-10 04:04:06', NULL),
(29, 23, 'Teaching', '2023-01-07', 0, 1, NULL, '2023-11-13 04:00:55', '2023-11-13 04:00:55', NULL),
(30, 23, 'Teaching', '2023-01-14', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(31, 23, 'Teaching', '2023-01-21', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(32, 23, 'Revision', '2023-01-28', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(33, 17, 'Teaching', '2023-01-09', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(34, 17, 'Teaching', '2023-01-23', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(35, 17, 'Revision', '2023-01-30', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(36, 16, 'Teaching', '2023-01-10', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(37, 16, 'Teaching', '2023-01-17', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(38, 16, 'Teaching', '2023-01-24', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL),
(39, 16, 'Teaching', '2023-01-31', 0, 1, NULL, '2023-11-13 04:00:57', '2023-11-13 04:00:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plan_participants`
--

DROP TABLE IF EXISTS `plan_participants`;
CREATE TABLE IF NOT EXISTS `plan_participants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('Manager','Auditor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manager',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plan_participants`
--

INSERT INTO `plan_participants` (`id`, `plan_id`, `user_id`, `type`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(22, 16, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(23, 17, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(24, 19, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(25, 23, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(26, 24, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(27, 25, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(28, 26, 5, 'Manager', 1, NULL, NULL, '2023-10-23 08:12:28', '2023-10-23 08:12:28'),
(29, 16, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(30, 17, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(31, 19, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(32, 23, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(33, 24, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(34, 25, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36'),
(35, 26, 2, 'Auditor', 1, NULL, NULL, '2023-10-23 08:13:36', '2023-10-23 08:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `previous_providers`
--

DROP TABLE IF EXISTS `previous_providers`;
CREATE TABLE IF NOT EXISTS `previous_providers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `previous_providers`
--

INSERT INTO `previous_providers` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'LCC', 1, '11', 1, '2', 1, 1, 1, '2023-09-28 05:57:55', '2023-09-28 05:58:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `process_lists`
--

DROP TABLE IF EXISTS `process_lists`;
CREATE TABLE IF NOT EXISTS `process_lists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase` enum('Applicant','Register','Live') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `process_lists`
--

INSERT INTO `process_lists` (`id`, `name`, `phase`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Applicant Process', 'Applicant', 1, NULL, NULL, '2023-05-15 07:30:08', '2023-05-15 07:37:13'),
(2, 'Register Process', 'Register', 1, NULL, NULL, '2023-05-15 07:37:00', '2023-05-15 07:37:00'),
(3, 'Student Discard Process', 'Applicant', 1, NULL, NULL, NULL, NULL),
(4, 'Live Student Process 01', 'Live', 1, 1, NULL, '2023-09-26 07:22:49', '2023-10-05 09:48:45');

-- --------------------------------------------------------

--
-- Table structure for table `qualification_type_identifiers`
--

DROP TABLE IF EXISTS `qualification_type_identifiers`;
CREATE TABLE IF NOT EXISTS `qualification_type_identifiers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qualification_type_identifiers`
--

INSERT INTO `qualification_type_identifiers` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HND', 1, '11', 1, '2', 1, 1, 1, '2023-09-28 06:09:58', '2023-09-28 06:10:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reason_for_engagement_endings`
--

DROP TABLE IF EXISTS `reason_for_engagement_endings`;
CREATE TABLE IF NOT EXISTS `reason_for_engagement_endings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reason_for_engagement_endings`
--

INSERT INTO `reason_for_engagement_endings` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ended', 1, '11', 1, '2', 1, 1, 1, '2023-09-28 06:22:00', '2023-09-28 06:22:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referral_codes`
--

DROP TABLE IF EXISTS `referral_codes`;
CREATE TABLE IF NOT EXISTS `referral_codes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Student','Agent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referral_codes_user_id_foreign` (`user_id`),
  KEY `referral_codes_student_id_foreign` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referral_codes`
--

INSERT INTO `referral_codes` (`id`, `code`, `type`, `user_id`, `student_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(2, 'NUKZXMENA', 'Agent', 28, NULL, 1, NULL, NULL, '2023-09-22 08:09:51', '2023-09-22 08:09:51'),
(3, '9I3MKYQZP', 'Agent', 15, NULL, 1, NULL, NULL, '2023-09-22 08:10:41', '2023-09-22 08:10:41');

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

DROP TABLE IF EXISTS `religions`;
CREATE TABLE IF NOT EXISTS `religions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Islam', 1, '34', 1, '56', 1, 1, 1, '2023-03-29 03:46:47', '2023-09-27 09:51:08', NULL),
(2, 'Hinduisum', 1, '34', 1, '56', 1, 1, 1, '2023-09-27 09:51:28', '2023-09-27 09:51:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Staff','Student','Agent','Admin','Tutor','External User') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `display_name`, `type`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Staff', 'Staff', 1, 1, NULL, '2023-05-10 04:30:12', '2023-10-24 10:22:53'),
(2, 'Tutor', 'Tutor', 1, 1, NULL, '2023-05-10 04:30:30', '2023-10-24 10:23:02'),
(3, 'Admin', 'Admin', 1, 1, NULL, '2023-05-10 04:30:45', '2023-10-24 10:23:18'),
(4, 'Student', 'Student', 1, NULL, '2023-10-24 09:08:18', NULL, '2023-10-24 09:08:18'),
(5, 'Agent', 'Agent', 1, NULL, NULL, '2023-09-22 07:04:50', '2023-09-22 07:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_capacity` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_venue_id_foreign` (`venue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `venue_id`, `name`, `room_capacity`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Room A1', 30, 1, 1, NULL, '2023-02-13 08:39:03', '2023-02-13 08:39:34'),
(2, 1, 'Room 02', 30, 1, NULL, NULL, '2023-02-28 06:06:53', '2023-02-28 06:06:53'),
(3, 1, 'Room 03', 30, 1, 1, NULL, '2023-02-28 06:07:06', '2023-02-28 06:07:16'),
(4, 1, 'Room 05', 45, 1, NULL, NULL, '2023-02-28 06:07:31', '2023-02-28 06:07:31'),
(5, 2, 'UP 01', 30, 1, NULL, NULL, '2023-02-28 06:07:45', '2023-02-28 06:07:45'),
(6, 2, 'UP 02', 30, 1, NULL, NULL, '2023-02-28 06:07:56', '2023-02-28 06:07:56'),
(7, 2, 'UP 03', 30, 1, NULL, NULL, '2023-02-28 06:08:07', '2023-02-28 06:08:07'),
(8, 2, 'UP 04', 30, 1, NULL, NULL, '2023-02-28 06:08:19', '2023-02-28 06:08:19'),
(9, 2, 'UP 05', 30, 1, NULL, NULL, '2023-02-28 06:08:31', '2023-02-28 06:08:31'),
(10, 2, 'UP 06', 26, 1, 1, NULL, '2023-10-05 09:05:37', '2023-10-05 09:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE IF NOT EXISTS `semesters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 'January 2023', 1, '2022-12-13 06:17:01', 1, '2022-12-13 09:22:01', NULL),
(2, 'September 2022', 1, '2022-12-13 07:02:48', NULL, '2022-12-13 07:04:41', NULL),
(3, 'April 2022', 1, '2022-12-13 07:08:04', NULL, '2022-12-13 07:08:21', NULL),
(4, 'January 2022', 1, '2022-12-13 07:10:37', NULL, '2023-10-19 04:48:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sexual_orientations`
--

DROP TABLE IF EXISTS `sexual_orientations`;
CREATE TABLE IF NOT EXISTS `sexual_orientations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sexual_orientations`
--

INSERT INTO `sexual_orientations` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bisexual', 1, '01', 1, '01', 1, 1, 1, '2023-03-28 06:58:29', '2023-09-27 09:14:31', NULL),
(2, 'Gay man', 1, '02', 1, NULL, 1, 1, 1, '2023-09-20 09:55:48', '2023-09-27 09:14:27', NULL),
(3, 'Gay woman/lesbian', 1, '03', 1, NULL, 1, 1, 1, '2023-09-20 09:56:03', '2023-09-27 09:14:23', NULL),
(4, 'Other', 1, '04', 1, NULL, 0, 1, 1, '2023-09-20 09:57:13', '2023-09-27 09:21:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sex_identifiers`
--

DROP TABLE IF EXISTS `sex_identifiers`;
CREATE TABLE IF NOT EXISTS `sex_identifiers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sex_identifiers`
--

INSERT INTO `sex_identifiers` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Female', 1, '11', 1, '2', 1, 1, 1, '2023-09-28 07:36:54', '2023-09-28 07:37:40', NULL),
(2, 'Male', 1, '1', 1, '2', 1, 1, 1, '2023-10-06 08:29:37', '2023-10-06 08:30:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `signatories`
--

DROP TABLE IF EXISTS `signatories`;
CREATE TABLE IF NOT EXISTS `signatories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `signatory_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signatory_post` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `signatory_name`, `signatory_post`, `signature`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Dr Rahaman Hasans', 'Head of Academic Quality Assurance and Compliance (HoQA)', '1696341496_SS_17.png', 1, 1, NULL, '2023-07-27 10:29:34', '2023-10-03 07:58:19'),
(2, 'Mehfuzul Haque (SFHEA )', 'Head of Programmes & Academic Monitoring', '1696342003_SS_11.jpg', 1, 1, NULL, '2023-07-27 10:31:33', '2023-10-03 08:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

DROP TABLE IF EXISTS `sms_templates`;
CREATE TABLE IF NOT EXISTS `sms_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sms_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `sms_title`, `description`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Virtual Class SMS', 'To attend [Module_Name] on [Class_Date] at [Class_Time], please visit [Virtual_Link]', 1, 1, NULL, '2023-10-06 03:03:06', '2023-10-06 03:03:33');

-- --------------------------------------------------------

--
-- Table structure for table `source_tuition_fees`
--

DROP TABLE IF EXISTS `source_tuition_fees`;
CREATE TABLE IF NOT EXISTS `source_tuition_fees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hesa_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `df_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `source_tuition_fees`
--

INSERT INTO `source_tuition_fees` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HSBC Bank', '0', NULL, '0', NULL, 1, NULL, '2023-01-05 05:40:00', '2023-10-05 07:07:31', NULL),
(2, 'HSBC Banks', '1', '3', '1', '4', 1, 1, '2023-01-05 05:40:12', '2023-10-05 07:07:18', NULL),
(3, 'Self Funding', '1', '1', '1', '2', 1, NULL, '2023-10-05 07:07:09', '2023-10-05 07:07:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
CREATE TABLE IF NOT EXISTS `statuses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Applicant','Register','Student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Submitting', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(2, 'New', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(3, 'In Progress', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(4, 'Awating Decision', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(5, 'Accepted', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(6, 'Offer Placed', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(7, 'Offer Accepted', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(8, 'Rejected', 'Applicant', 1, NULL, '2023-08-30 08:58:22', NULL, NULL),
(9, 'Offer Rejected', 'Applicant', 1, 1, NULL, '2023-10-05 09:29:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applicant_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `title_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nationality_id` bigint(20) UNSIGNED DEFAULT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `registration_no` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssn_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uhn_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_no` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `marital_status` enum('single','married','divorce') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex_identifier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_referral_varified` tinyint(4) DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `students_applicant_id_foreign` (`applicant_id`),
  KEY `students_applicant_user_id_foreign` (`applicant_user_id`),
  KEY `students_title_id_foreign` (`title_id`),
  KEY `students_status_id_foreign` (`status_id`),
  KEY `students_country_id_foreign` (`country_id`),
  KEY `students_nationality_id_foreign` (`nationality_id`),
  KEY `students_sex_identifier_id_foreign` (`sex_identifier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `applicant_id`, `applicant_user_id`, `student_user_id`, `title_id`, `status_id`, `nationality_id`, `country_id`, `registration_no`, `ssn_no`, `uhn_no`, `application_no`, `first_name`, `last_name`, `photo`, `date_of_birth`, `marital_status`, `sex_identifier_id`, `submission_date`, `referral_code`, `is_referral_varified`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(17, 1, 1, 3, 2, 7, 2, 2, NULL, NULL, NULL, '10000001', 'Abdul', 'Latif', '1695140261_1-5.jpg', '1986-11-25', NULL, 1, '2023-04-12', '9I3MKYQZP', 1, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-19 05:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `student_archives`
--

DROP TABLE IF EXISTS `student_archives`;
CREATE TABLE IF NOT EXISTS `student_archives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `table` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_value` text COLLATE utf8mb4_unicode_ci,
  `field_new_value` text COLLATE utf8mb4_unicode_ci,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_archives_student_id_foreign` (`student_id`),
  KEY `student_archives_student_user_id_foreign` (`student_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_archives`
--

INSERT INTO `student_archives` (`id`, `student_id`, `table`, `field_name`, `field_value`, `field_new_value`, `student_user_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(53, 17, 'student_other_details', 'sexual_orientation_id', NULL, '1', NULL, 1, NULL, '2023-10-18 05:47:41', '2023-10-18 05:47:41', NULL),
(54, 17, 'student_other_details', 'religion_id', NULL, '1', NULL, 1, NULL, '2023-10-18 05:47:41', '2023-10-18 05:47:41', NULL),
(55, 17, 'student_other_details', 'disabilty_allowance', '0', '1', NULL, 1, NULL, '2023-10-18 05:47:41', '2023-10-18 05:47:41', NULL),
(56, 17, 'student_other_details', 'hesa_gender_id', NULL, '1', NULL, 1, NULL, '2023-10-18 05:47:41', '2023-10-18 05:47:41', NULL),
(57, 17, 'student_disabilities', 'disabilitiy_id', '', '1', NULL, 1, NULL, '2023-10-18 05:47:41', '2023-10-18 05:47:41', NULL),
(58, 17, 'student_other_details', 'disabilty_allowance', '0', '1', NULL, 3, NULL, '2023-10-19 07:10:51', '2023-10-19 07:10:51', NULL),
(59, 17, 'student_disabilities', 'disabilitiy_id', ',', '1,5', 3, NULL, NULL, '2023-10-19 07:12:01', '2023-10-19 07:12:01', NULL),
(60, 17, 'student_contacts', 'permanent_address_id', '28', '32', 3, NULL, NULL, '2023-10-19 08:54:53', '2023-10-19 08:54:53', NULL),
(61, 17, 'student_contacts', 'updated_by', NULL, '3', 3, NULL, NULL, '2023-10-19 08:54:53', '2023-10-19 08:54:53', NULL),
(62, 17, 'student_contacts', 'term_time_accommodation_type_id', '4', '1', 3, NULL, NULL, '2023-10-19 08:54:53', '2023-10-19 08:54:53', NULL),
(63, 17, 'student_contacts', 'permanent_address_id', '32', '28', 3, NULL, NULL, '2023-10-19 08:56:36', '2023-10-19 08:56:36', NULL),
(64, 17, 'student_contacts', 'home', '01740149260', '01740149261', 3, NULL, NULL, '2023-10-19 08:58:20', '2023-10-19 08:58:20', NULL),
(65, 17, 'student_kins', 'kins_relation_id', '1', '8', 3, NULL, NULL, '2023-10-19 09:08:51', '2023-10-19 09:08:51', NULL),
(66, 17, 'student_kins', 'name', 'Lutfor', 'Selina', 3, NULL, NULL, '2023-10-19 09:08:51', '2023-10-19 09:08:51', NULL),
(67, 17, 'student_kins', 'mobile', '01770878719', '01770878710', 3, NULL, NULL, '2023-10-19 09:08:51', '2023-10-19 09:08:51', NULL),
(68, 17, 'student_kins', 'updated_by', NULL, '3', 3, NULL, NULL, '2023-10-19 09:08:51', '2023-10-19 09:08:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_awarding_body_details`
--

DROP TABLE IF EXISTS `student_awarding_body_details`;
CREATE TABLE IF NOT EXISTS `student_awarding_body_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_course_relation_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `registration_expire_date` date DEFAULT NULL,
  `registration_document_verified` enum('Yes','No') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_awarding_body_details_student_id_foreign` (`student_id`),
  KEY `student_awarding_body_details_student_course_relation_id_foreign` (`student_course_relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_consents`
--

DROP TABLE IF EXISTS `student_consents`;
CREATE TABLE IF NOT EXISTS `student_consents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `consent_policy_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Agree','Disagree','Unknown') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unknown',
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_consents_student_id_foreign` (`student_id`),
  KEY `student_consents_consent_policy_id_foreign` (`consent_policy_id`),
  KEY `student_consents_student_user_id_foreign` (`student_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_consents`
--

INSERT INTO `student_consents` (`id`, `student_id`, `consent_policy_id`, `status`, `student_user_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(16, 17, 1, 'Agree', 3, 1, 3, NULL, '2023-10-19 09:42:28', '2023-10-19 09:43:02'),
(17, 17, 2, 'Agree', 3, 1, 3, NULL, '2023-10-19 09:42:29', '2023-10-19 09:43:03'),
(18, 17, 3, 'Disagree', 3, 1, 3, NULL, '2023-10-19 09:42:29', '2023-10-19 09:43:03'),
(19, 17, 4, 'Agree', 3, 1, 3, NULL, '2023-10-19 09:42:29', '2023-10-19 09:43:03'),
(20, 17, 5, 'Disagree', 3, 1, 3, NULL, '2023-10-19 09:42:29', '2023-10-19 09:43:03');

-- --------------------------------------------------------

--
-- Table structure for table `student_contacts`
--

DROP TABLE IF EXISTS `student_contacts`;
CREATE TABLE IF NOT EXISTS `student_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `permanent_country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `permanent_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `term_time_address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `home` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_link_ref` text COLLATE utf8mb4_unicode_ci,
  `mobile_verification` tinyint(4) NOT NULL DEFAULT '0',
  `personal_email_verification` tinyint(4) DEFAULT '0',
  `personal_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `term_time_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `term_time_accommodation_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_contacts_student_id_index` (`student_id`),
  KEY `student_contacts_country_id_index` (`country_id`),
  KEY `student_contacts_permanent_country_id_index` (`permanent_country_id`),
  KEY `student_contacts_term_time_address_id_foreign` (`term_time_address_id`),
  KEY `student_contacts_permanent_address_id_foreign` (`permanent_address_id`),
  KEY `student_contacts_term_time_accommodation_type_id_foreign` (`term_time_accommodation_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_contacts`
--

INSERT INTO `student_contacts` (`id`, `student_id`, `country_id`, `permanent_country_id`, `permanent_address_id`, `term_time_address_id`, `home`, `mobile`, `external_link_ref`, `mobile_verification`, `personal_email_verification`, `personal_email`, `permanent_post_code`, `term_time_post_code`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`, `term_time_accommodation_type_id`) VALUES
(14, 17, NULL, NULL, 28, 28, '01740149261', '01740149263', 'NULL', 0, 0, NULL, NULL, 'E1 2JA', 1, 3, NULL, '2023-10-18 05:41:59', '2023-10-19 09:41:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_course_relations`
--

DROP TABLE IF EXISTS `student_course_relations`;
CREATE TABLE IF NOT EXISTS `student_course_relations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `active` tinyint(4) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_course_relations_student_id_foreign` (`student_id`),
  KEY `student_course_relations_course_creation_id_foreign` (`course_creation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_course_relations`
--

INSERT INTO `student_course_relations` (`id`, `course_creation_id`, `student_id`, `active`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 5, 17, 1, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_disabilities`
--

DROP TABLE IF EXISTS `student_disabilities`;
CREATE TABLE IF NOT EXISTS `student_disabilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `disability_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_disabilities_student_id_foreign` (`student_id`),
  KEY `student_disabilities_disability_id_foreign` (`disability_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_disabilities`
--

INSERT INTO `student_disabilities` (`id`, `student_id`, `disability_id`, `deleted_at`, `created_at`, `updated_at`) VALUES
(17, 17, 1, NULL, '2023-10-19 07:12:01', '2023-10-19 07:12:01'),
(18, 17, 5, NULL, '2023-10-19 07:12:01', '2023-10-19 07:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
CREATE TABLE IF NOT EXISTS `student_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `document_setting_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hard_copy_check` tinyint(4) DEFAULT NULL,
  `doc_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk_type` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_documents_student_id_index` (`student_id`),
  KEY `student_documents_document_setting_id_index` (`document_setting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_documents`
--

INSERT INTO `student_documents` (`id`, `student_id`, `document_setting_id`, `hard_copy_check`, `doc_type`, `disk_type`, `path`, `display_file_name`, `current_file_name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(255, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686138978_FYmAddwXoAEssnm.jpg', '1686138978_FYmAddwXoAEssnm.jpg', '1686138978_FYmAddwXoAEssnm.jpg', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(256, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686147600_FcOhZKCXEAALQSK.jpg', '1686147600_FcOhZKCXEAALQSK.jpg', '1686147600_FcOhZKCXEAALQSK.jpg', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(257, 17, NULL, 0, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(258, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359380_h1.jpg', '1685359380_h1.jpg', '1685359380_h1.jpg', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(259, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359382_10.jpg', '1685359382_10.jpg', '1685359382_10.jpg', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(260, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685359383_9.jpg', '1685359383_9.jpg', '1685359383_9.jpg', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(261, 17, NULL, 0, 'xlsx', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', '1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', '1685363314_Payroll_of_LCC Investment Ltd_for_07_05_2023.xlsx', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(262, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', '1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', '1685538715_Six_KPI_Submission_Performance_Report_2023_04_27_13_14_24.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(263, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693848958_1693400892_1_Letter.pdf', 'Passport', '1693848958_1693400892_1_Letter.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(264, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1685968623_SobujCv-9.pdf', 'Academic Qualifications', '1685968623_SobujCv-9.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(265, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396885_Abdul_Latif_profile_5.pdf', 'English Test', '1693396885_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(266, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396910_Abdul_Latif_profile_5.pdf', 'Social Number', '1693396910_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(267, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693396945_Abdul_Latif_profile_5.pdf', 'Interview', '1693396945_Abdul_Latif_profile_5.pdf', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(268, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1693400892_1_Letter.pdf', 'Welcome Message For New Students.', '1693400892_1_Letter.pdf', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(269, 17, NULL, NULL, 'jpg', NULL, 'http://localhost:8000/storage/interviewresult/1692975076_IMG-20230404-WA0018.jpg', '1692975076_IMG-20230404-WA0018.jpg', '1692975076_IMG-20230404-WA0018.jpg', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(270, 17, NULL, NULL, 'png', NULL, 'http://localhost:8000/storage/interviewresult/1692977581_Seal.png', '1692977581_Seal.png', '1692977581_Seal.png', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(271, 17, NULL, 0, 'jpg', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1687257043_Javed_Hussain.jpg', '1687257043_Javed_Hussain.jpg', '1687257043_Javed_Hussain.jpg', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(272, 17, NULL, 0, 'pdf', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1687257045_230601 College Oversight Board BioPic V4.pdf', '1687257045_230601 College Oversight Board BioPic V4.pdf', '1687257045_230601 College Oversight Board BioPic V4.pdf', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_emails`
--

DROP TABLE IF EXISTS `student_emails`;
CREATE TABLE IF NOT EXISTS `student_emails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `email_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `common_smtp_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_emails_student_id_index` (`student_id`),
  KEY `student_emails_common_smtp_id_index` (`common_smtp_id`),
  KEY `student_emails_email_template_id_foreign` (`email_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_emails`
--

INSERT INTO `student_emails` (`id`, `student_id`, `email_template_id`, `common_smtp_id`, `subject`, `body`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(11, 17, NULL, 3, 'Welcome to LCC', '<h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><h2>Why do we use it?</h2><p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_emails_attachments`
--

DROP TABLE IF EXISTS `student_emails_attachments`;
CREATE TABLE IF NOT EXISTS `student_emails_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_email_id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_emails__attachments_student_email_id_index` (`student_email_id`),
  KEY `student_emails__attachments_student_document_id_index` (`student_document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_emails_attachments`
--

INSERT INTO `student_emails_attachments` (`id`, `student_email_id`, `student_document_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(16, 11, 271, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(17, 11, 272, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_employments`
--

DROP TABLE IF EXISTS `student_employments`;
CREATE TABLE IF NOT EXISTS `student_employments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_phone` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_date` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continuing` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_employments_student_id_index` (`student_id`),
  KEY `student_employments_address_id_foreign` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_employments`
--

INSERT INTO `student_employments` (`id`, `student_id`, `address_id`, `company_name`, `company_phone`, `position`, `start_date`, `end_date`, `continuing`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(16, 17, 29, 'ThemeWar', '01740149260', 'Sr Developers', '03-2023', '04-2023', 0, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_employment_references`
--

DROP TABLE IF EXISTS `student_employment_references`;
CREATE TABLE IF NOT EXISTS `student_employment_references` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_employment_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_employment_references_student_employment_id_foreign` (`student_employment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_employment_references`
--

INSERT INTO `student_employment_references` (`id`, `student_employment_id`, `name`, `position`, `phone`, `email`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 16, 'Abdul Latif', 'Supervisor', '01740149260', 'limon@churchill.ac', 1, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_eligibilities`
--

DROP TABLE IF EXISTS `student_fee_eligibilities`;
CREATE TABLE IF NOT EXISTS `student_fee_eligibilities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_course_relation_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `fee_eligibility_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_fee_eligibilities_student_id_foreign` (`student_id`),
  KEY `student_fee_eligibilities_fee_eligibility_id_foreign` (`fee_eligibility_id`),
  KEY `student_fee_eligibilities_student_course_relation_id_foreign` (`student_course_relation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fee_eligibilities`
--

INSERT INTO `student_fee_eligibilities` (`id`, `student_course_relation_id`, `student_id`, `fee_eligibility_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(7, 4, 17, 3, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_interviews`
--

DROP TABLE IF EXISTS `student_interviews`;
CREATE TABLE IF NOT EXISTS `student_interviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_task_id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED NOT NULL,
  `interview_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `interview_result` enum('Pass','Fail','Unattainded','N/A') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interview_status` enum('In progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_interviews_student_id_index` (`student_id`),
  KEY `student_interviews_student_task_id_foreign` (`student_task_id`),
  KEY `student_interviews_student_document_id_foreign` (`student_document_id`),
  KEY `student_interviews_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_interviews`
--

INSERT INTO `student_interviews` (`id`, `student_id`, `user_id`, `student_task_id`, `student_document_id`, `interview_date`, `start_time`, `end_time`, `interview_result`, `interview_status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(11, 17, 1, 133, 269, '2023-08-25', '14:50:00', '14:50:00', 'Fail', NULL, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(12, 17, 1, 134, 270, '2023-08-25', '15:32:00', '15:32:00', 'Pass', NULL, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_kins`
--

DROP TABLE IF EXISTS `student_kins`;
CREATE TABLE IF NOT EXISTS `student_kins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `kins_relation_id` bigint(20) UNSIGNED NOT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_kins_student_id_foreign` (`student_id`),
  KEY `student_kins_kins_relation_id_foreign` (`kins_relation_id`),
  KEY `student_kins_address_id_foreign` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_kins`
--

INSERT INTO `student_kins` (`id`, `student_id`, `kins_relation_id`, `address_id`, `name`, `mobile`, `email`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(8, 17, 8, 30, 'Selina', '01770878710', 'lutfor@churchill.ac', 1, 3, NULL, '2023-10-18 05:42:00', '2023-10-19 09:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `student_letters`
--

DROP TABLE IF EXISTS `student_letters`;
CREATE TABLE IF NOT EXISTS `student_letters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_letter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `letter_set_id` bigint(20) UNSIGNED NOT NULL,
  `signatory_id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comon_smtp_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_email_or_attachment` tinyint(4) NOT NULL DEFAULT '1',
  `issued_by` bigint(20) NOT NULL,
  `issued_date` date NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_letters_student_id_foreign` (`student_id`),
  KEY `student_letters_letter_set_id_foreign` (`letter_set_id`),
  KEY `student_letters_signatory_id_foreign` (`signatory_id`),
  KEY `student_letters_comon_smtp_id_foreign` (`comon_smtp_id`),
  KEY `student_letters_applicant_letter_id_foreign` (`applicant_letter_id`),
  KEY `student_letters_student_document_id_foreign` (`student_document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_letters`
--

INSERT INTO `student_letters` (`id`, `student_id`, `applicant_letter_id`, `letter_set_id`, `signatory_id`, `student_document_id`, `comon_smtp_id`, `is_email_or_attachment`, `issued_by`, `issued_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(39, 17, 1, 1, 1, NULL, NULL, 1, 1, '2023-07-25', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(40, 17, 2, 1, 2, NULL, NULL, 1, 1, '2023-07-28', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(41, 17, 3, 1, 1, NULL, NULL, 1, 1, '2023-08-31', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(42, 17, 4, 1, 2, 268, NULL, 1, 1, '2023-08-27', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_notes`
--

DROP TABLE IF EXISTS `student_notes`;
CREATE TABLE IF NOT EXISTS `student_notes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `opening_date` date DEFAULT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase` enum('Applicant','Admission','Register','Live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admission',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_notes_student_id_index` (`student_id`),
  KEY `student_notes_student_document_id_index` (`student_document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_notes`
--

INSERT INTO `student_notes` (`id`, `student_id`, `student_document_id`, `opening_date`, `note`, `phase`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(49, 17, 255, NULL, '<p>CKEditor 5 allows you to retrieve the data from and save it to your server (or to your system in general) in various ways. In this guide, you can learn about the available options along with their pros and cons.</p>', 'Live', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(50, 17, 256, NULL, '<p>This is the Second Note This one is updated</p>', 'Live', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(51, 17, 257, NULL, '<h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.&nbsp;</p><p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><ul><li>Lorem Ipsum has been the industry\'s standard dummy</li><li>Ipsum has been the industry\'s standard dummy</li><li>Lorem Ipsum has been the industry\'s</li></ul><p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>', 'Live', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_other_details`
--

DROP TABLE IF EXISTS `student_other_details`;
CREATE TABLE IF NOT EXISTS `student_other_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `ethnicity_id` bigint(20) UNSIGNED NOT NULL,
  `sexual_orientation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `religion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disability_status` tinyint(4) NOT NULL DEFAULT '0',
  `disabilty_allowance` tinyint(4) NOT NULL DEFAULT '0',
  `is_education_qualification` tinyint(4) NOT NULL DEFAULT '0',
  `employment_status` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `college_introduction` enum('Self','Referred','Agent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hesa_gender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_other_details_student_id_index` (`student_id`),
  KEY `student_other_details_ethnicity_id_index` (`ethnicity_id`),
  KEY `student_other_details_sexual_orientation_id_index` (`sexual_orientation_id`),
  KEY `student_other_details_religion_id_index` (`religion_id`),
  KEY `student_other_details_hesa_gender_id_foreign` (`hesa_gender_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_other_details`
--

INSERT INTO `student_other_details` (`id`, `student_id`, `ethnicity_id`, `sexual_orientation_id`, `religion_id`, `disability_status`, `disabilty_allowance`, `is_education_qualification`, `employment_status`, `college_introduction`, `hesa_gender_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(7, 17, 2, 1, 2, 1, 1, 1, 'Part Time', NULL, 1, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-19 09:41:47');

-- --------------------------------------------------------

--
-- Table structure for table `student_proof_of_ids`
--

DROP TABLE IF EXISTS `student_proof_of_ids`;
CREATE TABLE IF NOT EXISTS `student_proof_of_ids` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `proof_type` enum('passport','birth','driving','nid','respermit') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_expiredate` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_proof_of_ids_student_id_foreign` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_proof_of_ids`
--

INSERT INTO `student_proof_of_ids` (`id`, `student_id`, `proof_type`, `proof_id`, `proof_expiredate`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(8, 17, 'passport', '234552345', '2025-09-01', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_proposed_courses`
--

DROP TABLE IF EXISTS `student_proposed_courses`;
CREATE TABLE IF NOT EXISTS `student_proposed_courses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_course_relation_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `course_creation_id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_loan` enum('Independently/Private','Funding Body','Sponsor','Student Loan','Others') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Independently/Private',
  `student_finance_england` tinyint(4) DEFAULT NULL,
  `fund_receipt` tinyint(4) DEFAULT NULL,
  `applied_received_fund` tinyint(4) DEFAULT NULL,
  `other_funding` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_time` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_proposed_courses_student_id_index` (`student_id`),
  KEY `student_proposed_courses_academic_year_id_index` (`academic_year_id`),
  KEY `student_proposed_courses_course_creations_id_index` (`course_creation_id`),
  KEY `student_proposed_courses_semester_id_foreign` (`semester_id`),
  KEY `student_course_relation_id_foreign` (`student_course_relation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_proposed_courses`
--

INSERT INTO `student_proposed_courses` (`id`, `student_course_relation_id`, `student_id`, `academic_year_id`, `course_creation_id`, `semester_id`, `student_loan`, `student_finance_england`, `fund_receipt`, `applied_received_fund`, `other_funding`, `full_time`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(9, 4, 17, 3, 5, 4, 'Others', NULL, NULL, NULL, 'Self Funding', 1, 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_qualifications`
--

DROP TABLE IF EXISTS `student_qualifications`;
CREATE TABLE IF NOT EXISTS `student_qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `awarding_body` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `highest_academic` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subjects` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_award_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_qualifications_student_id_index` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_qualifications`
--

INSERT INTO `student_qualifications` (`id`, `student_id`, `awarding_body`, `highest_academic`, `subjects`, `result`, `degree_award_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(50, 17, 'SLC', 'HND', 'Netwarking', '3.75', '2023-04-01', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(51, 17, 'HNC', 'HNDS', 'Netwarking', '3.75', '2023-04-26', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(52, 17, 'SLCS', 'SND', 'Netwarking', '3.5', '2023-04-30', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_sms`
--

DROP TABLE IF EXISTS `student_sms`;
CREATE TABLE IF NOT EXISTS `student_sms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `sms_template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_sms_student_id_index` (`student_id`),
  KEY `student_sms_sms_template_id_foreign` (`sms_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_sms`
--

INSERT INTO `student_sms` (`id`, `student_id`, `sms_template_id`, `subject`, `sms`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(21, 17, NULL, 'THis is test', 'asdf asdf asdf asdf asdf asdf', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(22, 17, NULL, 'THis is test', 'asdf asdf asdf sadf sadf', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(23, 17, NULL, 'asdf sadf', 'asdf asdf sadf sadf sdaf sadf', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_tasks`
--

DROP TABLE IF EXISTS `student_tasks`;
CREATE TABLE IF NOT EXISTS `student_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_list_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `external_link_ref` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','In Progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_tasks_student_id_index` (`student_id`),
  KEY `student_tasks_task_list_id_index` (`task_list_id`),
  KEY `student_tasks_task_status_id_index` (`task_status_id`),
  KEY `student_tasks_applicant_task_id_foreign` (`applicant_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_tasks`
--

INSERT INTO `student_tasks` (`id`, `student_id`, `applicant_task_id`, `task_list_id`, `task_status_id`, `external_link_ref`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(126, 17, 1, 1, NULL, 'NULL', 'Pending', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(127, 17, 3, 4, NULL, 'NULL', 'Pending', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(128, 17, 4, 5, 1, 'NULL', 'Completed', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(129, 17, 5, 2, NULL, 'NULL', 'Pending', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(130, 17, 6, 6, NULL, 'NULL', 'Pending', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(131, 17, 7, 13, 1, 'NULL', 'In Progress', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(132, 17, 8, 14, NULL, 'NULL', 'Pending', 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(133, 17, 7, 13, 1, 'NULL', 'In Progress', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00'),
(134, 17, 7, 13, 1, 'NULL', 'In Progress', 1, NULL, NULL, '2023-10-18 05:42:00', '2023-10-18 05:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_task_documents`
--

DROP TABLE IF EXISTS `student_task_documents`;
CREATE TABLE IF NOT EXISTS `student_task_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `student_task_id` bigint(20) UNSIGNED NOT NULL,
  `student_document_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_task_documents_student_id_foreign` (`student_id`),
  KEY `student_task_documents_student_task_id_foreign` (`student_task_id`),
  KEY `student_task_documents_student_document_id_foreign` (`student_document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_task_documents`
--

INSERT INTO `student_task_documents` (`id`, `student_id`, `student_task_id`, `student_document_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(163, 17, 126, 258, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(164, 17, 126, 259, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(165, 17, 126, 260, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(166, 17, 126, 261, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(167, 17, 126, 262, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(168, 17, 126, 263, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(169, 17, 127, 264, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(170, 17, 128, 265, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(171, 17, 129, 266, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59'),
(172, 17, 131, 267, 1, NULL, NULL, '2023-10-18 05:41:59', '2023-10-18 05:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_task_log`
--

DROP TABLE IF EXISTS `student_task_log`;
CREATE TABLE IF NOT EXISTS `student_task_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_tasks_id` bigint(20) UNSIGNED NOT NULL,
  `actions` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_name` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prev_field_value` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_field_value` varchar(145) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_task_log`
--

INSERT INTO `student_task_log` (`id`, `student_tasks_id`, `actions`, `field_name`, `prev_field_value`, `current_field_value`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 57, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1695738795_Elementor-Logo-Symbol-Red.png', 1, NULL, NULL, '2023-09-26 08:33:15', '2023-09-26 08:33:15'),
(2, 57, 'Status Changed', 'status', 'Pending', 'Completed', 1, NULL, NULL, '2023-09-26 08:38:24', '2023-09-26 08:38:24'),
(3, 57, 'Status Changed', 'status', 'Completed', 'Pending', 1, NULL, NULL, '2023-09-26 08:38:50', '2023-09-26 08:38:50'),
(4, 59, 'Delete', '', '', 'Item Deleted', 1, NULL, NULL, '2023-09-26 08:39:07', '2023-09-26 08:39:07'),
(5, 58, 'Restore', '', '', 'Item Restored', 1, NULL, NULL, '2023-09-26 08:39:20', '2023-09-26 08:39:20'),
(6, 59, 'Restore', '', '', 'Item Restored', 1, NULL, NULL, '2023-09-26 08:39:38', '2023-09-26 08:39:38'),
(7, 58, 'Delete', '', '', 'Item Deleted', 1, NULL, NULL, '2023-09-26 08:41:05', '2023-09-26 08:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `student_users`
--

DROP TABLE IF EXISTS `student_users`;
CREATE TABLE IF NOT EXISTS `student_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` int(11) NOT NULL,
  `first_login` int(11) NOT NULL DEFAULT '1',
  `social_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_users`
--

INSERT INTO `student_users` (`id`, `name`, `email`, `email_verified_at`, `password`, `photo`, `gender`, `active`, `first_login`, `social_id`, `social_type`, `deleted_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(3, 'Abdul Latif', 'limon@churchill.ac', NULL, '$2y$10$extam1NnT9P1aPoSWuyx/OtOct8v.D.2pJxfDpTysrBJ6sUbUnkyW', NULL, 'Male', 1, 0, NULL, NULL, NULL, NULL, '2023-10-18 05:41:59', '2023-10-19 09:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `task_lists`
--

DROP TABLE IF EXISTS `task_lists`;
CREATE TABLE IF NOT EXISTS `task_lists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `process_list_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interview` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `upload` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `external_link` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `external_link_ref` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_lists_process_list_id_index` (`process_list_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_lists`
--

INSERT INTO `task_lists` (`id`, `process_list_id`, `name`, `short_description`, `interview`, `upload`, `external_link`, `external_link_ref`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Passport', 'Passport document is required.', 'No', 'Yes', '0', '', 'No', 1, 1, NULL, '2023-05-15 07:59:01', '2023-05-30 08:56:03'),
(2, 1, 'Social Number', 'We need your social numbers', 'No', 'Yes', '0', '', 'No', 1, 1, NULL, '2023-05-15 08:02:08', '2023-05-30 08:56:15'),
(3, 2, 'Student ID Card', 'Student ID card is required.', 'No', 'Yes', '0', '', 'No', 1, 1, NULL, '2023-05-16 05:14:17', '2023-05-30 08:56:23'),
(4, 1, 'Academic Qualifications', 'Upload multiple qualification docs.', 'No', 'Yes', '0', '', 'No', 1, 1, NULL, '2023-05-16 05:14:41', '2023-05-30 08:56:34'),
(5, 1, 'English Test', 'English test result document is required.', 'No', 'Yes', '0', '', 'Yes', 1, 1, NULL, '2023-05-16 05:14:58', '2023-05-30 08:29:26'),
(6, 1, 'Consent Form', 'Please fill out the consent form', 'No', 'No', '1', 'https://lcc.ac.uk/', 'No', 1, 1, NULL, '2023-05-17 08:41:16', '2023-05-30 08:56:43'),
(12, 2, 'BP', 'Please fill out the consent form', 'No', 'Yes', '0', NULL, 'Yes', 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(13, 1, 'Interview', 'Applicant Interview Session Required', 'Yes', 'Yes', '0', '', 'Yes', 1, 1, NULL, NULL, '2023-08-07 11:42:53'),
(14, 3, 'Email Sent', 'Send an email to student', 'No', 'No', '0', NULL, 'No', 1, NULL, NULL, '2023-08-30 05:32:52', '2023-08-30 05:32:52'),
(15, 4, 'ID Card', 'Student ID Card Print', 'No', 'Yes', '0', NULL, 'No', 1, NULL, NULL, '2023-09-26 07:23:48', '2023-09-26 07:23:48'),
(16, 4, 'LCC ID Confirmation', 'LCC Student ID Confirmation', 'No', 'No', '0', NULL, 'No', 1, NULL, NULL, '2023-09-26 07:24:24', '2023-09-26 07:24:24'),
(17, 4, 'Required Data Capture', 'Student First Login Data Capture', 'No', 'Yes', '0', NULL, 'No', 1, NULL, NULL, '2023-09-26 07:25:04', '2023-10-05 09:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `task_list_statuses`
--

DROP TABLE IF EXISTS `task_list_statuses`;
CREATE TABLE IF NOT EXISTS `task_list_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_list_id` bigint(20) UNSIGNED NOT NULL,
  `task_status_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_list_statuses`
--

INSERT INTO `task_list_statuses` (`id`, `task_list_id`, `task_status_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 12, 3, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(2, 12, 4, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(5, 5, 1, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(6, 5, 2, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(7, 13, 1, NULL, 1, NULL, '2023-08-07 11:42:53', '2023-08-07 11:42:53'),
(8, 13, 2, NULL, 1, NULL, '2023-08-07 11:42:53', '2023-08-07 11:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `task_list_users`
--

DROP TABLE IF EXISTS `task_list_users`;
CREATE TABLE IF NOT EXISTS `task_list_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_list_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_list_users`
--

INSERT INTO `task_list_users` (`id`, `task_list_id`, `user_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(4, 12, 2, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(5, 12, 9, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(6, 12, 13, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(13, 5, 2, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(14, 5, 3, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(15, 5, 5, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(16, 1, 10, NULL, 1, NULL, '2023-05-30 08:56:04', '2023-05-30 08:56:04'),
(17, 1, 13, NULL, 1, NULL, '2023-05-30 08:56:04', '2023-05-30 08:56:04'),
(18, 1, 9, NULL, 1, NULL, '2023-05-30 08:56:04', '2023-05-30 08:56:04'),
(19, 2, 3, NULL, 1, NULL, '2023-05-30 08:56:15', '2023-05-30 08:56:15'),
(20, 2, 8, NULL, 1, NULL, '2023-05-30 08:56:15', '2023-05-30 08:56:15'),
(21, 2, 13, NULL, 1, NULL, '2023-05-30 08:56:15', '2023-05-30 08:56:15'),
(22, 3, 3, NULL, 1, NULL, '2023-05-30 08:56:23', '2023-05-30 08:56:23'),
(23, 3, 6, NULL, 1, NULL, '2023-05-30 08:56:23', '2023-05-30 08:56:23'),
(24, 4, 8, NULL, 1, NULL, '2023-05-30 08:56:34', '2023-05-30 08:56:34'),
(25, 4, 10, NULL, 1, NULL, '2023-05-30 08:56:34', '2023-05-30 08:56:34'),
(26, 6, 5, NULL, 1, NULL, '2023-05-30 08:56:44', '2023-05-30 08:56:44'),
(27, 6, 9, NULL, 1, NULL, '2023-05-30 08:56:44', '2023-05-30 08:56:44'),
(28, 6, 13, NULL, 1, NULL, '2023-05-30 08:56:44', '2023-05-30 08:56:44'),
(31, 13, 8, NULL, 1, NULL, '2023-08-07 11:42:53', '2023-08-07 11:42:53'),
(32, 13, 6, NULL, 1, NULL, '2023-08-07 11:42:53', '2023-08-07 11:42:53'),
(33, 14, 3, 1, NULL, NULL, '2023-08-30 05:32:52', '2023-08-30 05:32:52'),
(34, 14, 6, 1, NULL, NULL, '2023-08-30 05:32:52', '2023-08-30 05:32:52'),
(35, 15, 2, 1, NULL, NULL, '2023-09-26 07:23:50', '2023-09-26 07:23:50'),
(36, 15, 5, 1, NULL, NULL, '2023-09-26 07:23:50', '2023-09-26 07:23:50'),
(37, 16, 2, 1, NULL, NULL, '2023-09-26 07:24:25', '2023-09-26 07:24:25'),
(38, 16, 5, 1, NULL, NULL, '2023-09-26 07:24:25', '2023-09-26 07:24:25'),
(39, 17, 5, 1, NULL, NULL, '2023-09-26 07:25:05', '2023-09-26 07:25:05'),
(40, 17, 7, 1, NULL, NULL, '2023-09-26 07:25:05', '2023-09-26 07:25:05');

-- --------------------------------------------------------

--
-- Table structure for table `task_statuses`
--

DROP TABLE IF EXISTS `task_statuses`;
CREATE TABLE IF NOT EXISTS `task_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_statuses`
--

INSERT INTO `task_statuses` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Pass', 1, NULL, NULL, NULL, NULL),
(2, 'Fail', 1, NULL, NULL, NULL, NULL),
(3, 'Yes', 1, NULL, NULL, NULL, NULL),
(4, 'No', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `term_time_accommodation_types`
--

DROP TABLE IF EXISTS `term_time_accommodation_types`;
CREATE TABLE IF NOT EXISTS `term_time_accommodation_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `term_time_accommodation_types`
--

INSERT INTO `term_time_accommodation_types` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Provider maintained property', 1, '01', 1, '01', 1, 1, NULL, '2023-09-28 09:39:07', '2023-09-28 09:39:07', NULL),
(2, 'Parental/guardian home', 1, '01', 1, '01', 1, 1, NULL, '2023-09-28 09:39:19', '2023-09-28 09:39:19', NULL),
(3, 'Own residence', 1, '01', 1, '01', 1, 1, NULL, '2023-09-28 09:39:36', '2023-09-28 09:39:36', NULL),
(4, 'Other rented accommodation', 1, '01', 1, '01', 1, 1, NULL, '2023-09-28 09:39:50', '2023-09-28 09:39:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

DROP TABLE IF EXISTS `titles`;
CREATE TABLE IF NOT EXISTS `titles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_hesa` tinyint(4) NOT NULL DEFAULT '0',
  `hesa_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_df` tinyint(4) NOT NULL DEFAULT '0',
  `df_code` varchar(99) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `titles`
--

INSERT INTO `titles` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Mr', 1, '34', 1, '56', 1, 1, 1, '2023-03-27 07:32:30', '2023-09-27 07:16:16', NULL),
(3, 'Mrs', 1, '34', 1, '56', 1, 1, 1, '2023-09-27 06:53:11', '2023-09-27 07:16:57', NULL),
(4, 'Miss', 1, '34', 1, '56', 1, 1, 1, '2023-09-27 06:56:35', '2023-09-27 07:12:11', NULL),
(5, 'Dr.', 1, '1', 1, '2', 1, 1, 1, '2023-10-05 10:14:41', '2023-10-05 10:15:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `social_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `photo`, `gender`, `active`, `deleted_at`, `remember_token`, `created_at`, `updated_at`, `social_id`, `social_type`) VALUES
(1, 'Left4code', 'midone@left4code.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'CdmO77d5mKltOD5ET0nZTwp7P31NpOhveT9KwjOYRodviHiVyqa1KbewmIOK', NULL, NULL, NULL, NULL),
(2, 'Harrison Cremin', 'damion.fay@example.net', '2022-11-30 06:53:15', '$2y$10$F0ryXIQxB2eksXTTjX.uRe6dtUBWEMCMr9hMEAYyE37o.Qktn/p6G', 'Avatar_2_1684145272.jpg', 'Male', 1, NULL, 'ETYy4QlCjg', '2022-11-30 06:53:15', '2023-05-15 04:11:25', NULL, NULL),
(3, 'Zita Kerluke', 'padberg.jamar@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'LGdQXwI1f7', '2022-11-30 06:53:15', '2023-05-15 04:16:18', NULL, NULL),
(5, 'Celia Mueller', 'themewar@gmail.com', '2022-11-30 06:53:15', '$2y$10$ARzi56cRKRzb1mX/.xTooOcsJWxyfCS7S8GJ.aavTwN.29uWhNefO', 'Avatar_5_1684405386.jpg', 'Male', 1, NULL, 'L9mbprm5DK', '2022-11-30 06:53:15', '2023-05-18 04:23:06', NULL, NULL),
(6, 'Prof. Carli Bayer I', 'annamae43@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'ZSk0s3x4bc', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(7, 'Celine Rosenbaum DDS', 'koepp.nayeli@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'Fi72GjUCnk', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(8, 'Miss Simone Bergstrom', 'aturner@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'FGBnNpzD0I7q9o5RJK1fno0SGSychHqPH2V84OZnl1IFZkbymnwo3AsnvKS8', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(9, 'Glenda Sawayn DDS', 'ed73@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'PcGq2x4cf8WnyT5OkeZe5QJK5yuBRhelo6THo6c5I5H0fHFtCLsSMO7Vu9g6', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(10, 'Jewell Satterfield DDS', 'thowe@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'YQrg2ynerw', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(13, 'Abdul Latif', 'limon34@churchill.ac', NULL, '$2y$10$x8iTn58PwuPdusVpdI85jeaKvL.uRK6o.GOINjGvwCQHcXhc.L4wi', NULL, 'Male', 1, NULL, NULL, '2023-05-10 05:18:59', '2023-05-10 05:18:59', NULL, NULL),
(14, 'User Photos', 'userphoto@pht.com', NULL, '$2y$10$fHBnMmK7dT67Ek2lhArJF.ReZcwUN33U8LDLJhzfjKHrLsi.MXu3e', NULL, 'Male', 1, NULL, NULL, '2023-05-12 06:46:06', '2023-05-15 04:03:51', NULL, NULL),
(15, 'New Photo', 'newphoto@gmail.com', NULL, '$2y$10$QFZgU6HBrgVT2ZQ9dleKfOa8ePWQRAuBnJ.7fw00U/NNfQLLoNmiS', 'Avatar_15_1695391841.jpg', 'Male', 1, NULL, NULL, '2023-05-12 06:47:42', '2023-09-22 08:10:41', NULL, NULL),
(28, 'The Agentu', 'agent@lcc.ac.uk', NULL, '$2y$10$hbe.wunWpzEwy/HYKU0lwOViekYrsCKyfRSPiel9LfD8FXCtk7ine', 'Avatar_28_1695388975.png', 'Male', 1, NULL, NULL, '2023-09-22 07:22:55', '2023-09-22 08:09:15', NULL, NULL),
(34, 'Abdul Latif', 'limon@churchill.ac', NULL, '$2y$10$extam1NnT9P1aPoSWuyx/OtOct8v.D.2pJxfDpTysrBJ6sUbUnkyW', NULL, 'MALE', 1, NULL, NULL, '2023-09-28 08:09:39', '2023-09-28 08:09:39', NULL, NULL),
(35, 'Abdul ', 'limon0011@gmail.com', NULL, '$2y$10$i9LecIT6jH4uPkwDSQ7syu8D8gLkb48txKOgJ08mYUT6y80GeMsLO', NULL, 'Male', 1, NULL, NULL, '2023-10-25 09:37:44', '2023-10-25 09:37:44', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_roles_role_id_foreign` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_id`, `user_id`) VALUES
(1, 3, 13),
(2, 2, 13),
(3, 1, 13),
(4, 3, 8),
(15, 2, 14),
(17, 1, 2),
(18, 1, 5),
(19, 3, 1),
(20, 4, 20),
(21, 4, 21),
(22, 4, 22),
(25, 4, 25),
(27, 4, 27),
(34, 5, 28),
(35, 5, 15),
(39, 4, 32),
(40, 4, 33),
(41, 4, 34);

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

DROP TABLE IF EXISTS `venues`;
CREATE TABLE IF NOT EXISTS `venues` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idnumber` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukprn` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `idnumber`, `ukprn`, `postcode`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Barclay Hall', '324234', '234234', '12324', 1, 1, NULL, '2023-02-13 08:36:38', '2023-02-13 08:38:33'),
(2, 'Upton Park', '122', '2342334', 'werwerwer', 1, 1, NULL, '2023-02-13 08:37:09', '2023-10-06 05:44:18');

-- --------------------------------------------------------

--
-- Table structure for table `venue_ip_addresses`
--

DROP TABLE IF EXISTS `venue_ip_addresses`;
CREATE TABLE IF NOT EXISTS `venue_ip_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venue_ip_addresses_venue_id_foreign` (`venue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venue_ip_addresses`
--

INSERT INTO `venue_ip_addresses` (`id`, `venue_id`, `ip`, `created_by`, `updated_by`, `deleted_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(3, 2, '162.33.33.33', 1, NULL, NULL, NULL, '2023-10-06 05:44:18', '2023-10-06 05:44:18'),
(4, 2, '172.34.34.34', 1, NULL, NULL, NULL, '2023-10-06 05:44:18', '2023-10-06 05:44:18');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_student_user_id_foreign` FOREIGN KEY (`student_user_id`) REFERENCES `student_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `applicants_applicant_user_id_foreign` FOREIGN KEY (`applicant_user_id`) REFERENCES `applicant_users` (`id`),
  ADD CONSTRAINT `applicants_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `applicants_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `applicants_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`),
  ADD CONSTRAINT `applicants_title_id_foreign` FOREIGN KEY (`title_id`) REFERENCES `titles` (`id`);

--
-- Constraints for table `applicant_archives`
--
ALTER TABLE `applicant_archives`
  ADD CONSTRAINT `applicant_archives_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_contacts`
--
ALTER TABLE `applicant_contacts`
  ADD CONSTRAINT `applicant_contacts_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_disabilities`
--
ALTER TABLE `applicant_disabilities`
  ADD CONSTRAINT `applicant_disabilities_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_disabilities_disabilitiy_id_foreign` FOREIGN KEY (`disabilitiy_id`) REFERENCES `disabilities` (`id`);

--
-- Constraints for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD CONSTRAINT `applicant_documents_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_emails`
--
ALTER TABLE `applicant_emails`
  ADD CONSTRAINT `applicant_emails_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `applicant_employments`
--
ALTER TABLE `applicant_employments`
  ADD CONSTRAINT `applicant_employments_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_fee_eligibilities`
--
ALTER TABLE `applicant_fee_eligibilities`
  ADD CONSTRAINT `applicant_fee_eligibilities_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_fee_eligibilities_fee_eligibility_id_foreign` FOREIGN KEY (`fee_eligibility_id`) REFERENCES `fee_eligibilities` (`id`);

--
-- Constraints for table `applicant_kin`
--
ALTER TABLE `applicant_kin`
  ADD CONSTRAINT `applicant_kin_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_kin_kins_relation_id_foreign` FOREIGN KEY (`kins_relation_id`) REFERENCES `kins_relations` (`id`);

--
-- Constraints for table `applicant_letters`
--
ALTER TABLE `applicant_letters`
  ADD CONSTRAINT `applicant_letters_comon_smtp_id_foreign` FOREIGN KEY (`comon_smtp_id`) REFERENCES `comon_smtps` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `applicant_other_details`
--
ALTER TABLE `applicant_other_details`
  ADD CONSTRAINT `applicant_other_details_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_other_details_ethnicity_id_foreign` FOREIGN KEY (`ethnicity_id`) REFERENCES `ethnicities` (`id`),
  ADD CONSTRAINT `applicant_other_details_hesa_gender_id_foreign` FOREIGN KEY (`hesa_gender_id`) REFERENCES `hesa_genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `applicant_proof_of_ids`
--
ALTER TABLE `applicant_proof_of_ids`
  ADD CONSTRAINT `applicant_proof_of_ids_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_proposed_courses`
--
ALTER TABLE `applicant_proposed_courses`
  ADD CONSTRAINT `applicant_proposed_courses_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `applicant_proposed_courses_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_proposed_courses_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`);

--
-- Constraints for table `applicant_qualifications`
--
ALTER TABLE `applicant_qualifications`
  ADD CONSTRAINT `applicant_qualifications_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_tasks`
--
ALTER TABLE `applicant_tasks`
  ADD CONSTRAINT `applicant_tasks_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_task_documents`
--
ALTER TABLE `applicant_task_documents`
  ADD CONSTRAINT `applicant_task_documents_applicant_task_id_foreign` FOREIGN KEY (`applicant_task_id`) REFERENCES `applicant_tasks` (`id`);

--
-- Constraints for table `applicant_temporary_emails`
--
ALTER TABLE `applicant_temporary_emails`
  ADD CONSTRAINT `applicant_temporary_emails_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_module_creation_id_foreign` FOREIGN KEY (`module_creation_id`) REFERENCES `module_creations` (`id`);

--
-- Constraints for table `assigns`
--
ALTER TABLE `assigns`
  ADD CONSTRAINT `assigns_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assigns_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_student_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendances_user_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkfeed_status` FOREIGN KEY (`attendance_feed_status_id`) REFERENCES `attendance_feed_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fkplans_date_id` FOREIGN KEY (`plans_date_list_id`) REFERENCES `plans_date_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance_informations`
--
ALTER TABLE `attendance_informations`
  ADD CONSTRAINT `attendance_informations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_informations_plans_date_list_id_foreign` FOREIGN KEY (`plans_date_list_id`) REFERENCES `plans_date_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_informations_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bank_holidays`
--
ALTER TABLE `bank_holidays`
  ADD CONSTRAINT `bank_holidays_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_awarding_body_id_foreign` FOREIGN KEY (`awarding_body_id`) REFERENCES `awarding_bodies` (`id`),
  ADD CONSTRAINT `courses_source_tuition_fee_id_foreign` FOREIGN KEY (`source_tuition_fee_id`) REFERENCES `source_tuition_fees` (`id`);

--
-- Constraints for table `course_base_datafutures`
--
ALTER TABLE `course_base_datafutures`
  ADD CONSTRAINT `course_base_datafutures_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `course_creation_availabilities`
--
ALTER TABLE `course_creation_availabilities`
  ADD CONSTRAINT `course_creation_availabilities_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`);

--
-- Constraints for table `course_creation_datafutures`
--
ALTER TABLE `course_creation_datafutures`
  ADD CONSTRAINT `course_creation_datafutures_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`);

--
-- Constraints for table `course_creation_instances`
--
ALTER TABLE `course_creation_instances`
  ADD CONSTRAINT `course_creation_instances_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `course_creation_instances_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`);

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_modules_module_level_id_foreign` FOREIGN KEY (`module_level_id`) REFERENCES `module_levels` (`id`);

--
-- Constraints for table `course_module_base_assesments`
--
ALTER TABLE `course_module_base_assesments`
  ADD CONSTRAINT `course_module_base_assesments_course_module_id_foreign` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_attendances`
--
ALTER TABLE `employee_attendances`
  ADD CONSTRAINT `eald_id_fk` FOREIGN KEY (`employee_leave_day_id`) REFERENCES `employee_leave_days` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_attendance_lives`
--
ALTER TABLE `employee_attendance_lives`
  ADD CONSTRAINT `eal_machine_id_fk` FOREIGN KEY (`employee_attendance_machine_id`) REFERENCES `employee_attendance_machines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_attendance_lives_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_attendance_punch_histories`
--
ALTER TABLE `employee_attendance_punch_histories`
  ADD CONSTRAINT `eaph_machine_id_fk` FOREIGN KEY (`employee_attendance_machine_id`) REFERENCES `employee_attendance_machines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_attendance_punch_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_bank_details`
--
ALTER TABLE `employee_bank_details`
  ADD CONSTRAINT `employee_bank_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_disability`
--
ALTER TABLE `employee_disability`
  ADD CONSTRAINT `employee_disability_disability_id_foreign` FOREIGN KEY (`disability_id`) REFERENCES `disabilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_disability_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `employee_documents_document_setting_id_foreign` FOREIGN KEY (`document_setting_id`) REFERENCES `document_settings` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_eligibilites`
--
ALTER TABLE `employee_eligibilites`
  ADD CONSTRAINT `employee_eligibilites_document_type_foreign` FOREIGN KEY (`document_type`) REFERENCES `employee_work_document_types` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employee_eligibilites_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  ADD CONSTRAINT `employee_emergency_contacts_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employee_emergency_contacts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_holiday_adjustments`
--
ALTER TABLE `employee_holiday_adjustments`
  ADD CONSTRAINT `adjustment_holiday_year_id_frn_key` FOREIGN KEY (`hr_holiday_year_id`) REFERENCES `hr_holiday_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emp_pattern_id_frn_key` FOREIGN KEY (`employee_working_pattern_id`) REFERENCES `employee_working_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_holiday_adjustments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
  ADD CONSTRAINT `emp_leave_pattern_id_fk` FOREIGN KEY (`employee_working_pattern_id`) REFERENCES `employee_working_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emp_leave_year_id_fk` FOREIGN KEY (`hr_holiday_year_id`) REFERENCES `hr_holiday_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_leave_days`
--
ALTER TABLE `employee_leave_days`
  ADD CONSTRAINT `employee_leave_days_employee_leave_id_foreign` FOREIGN KEY (`employee_leave_id`) REFERENCES `employee_leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_notes`
--
ALTER TABLE `employee_notes`
  ADD CONSTRAINT `employee_notes_employee_document_id_foreign` FOREIGN KEY (`employee_document_id`) REFERENCES `employee_documents` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employee_notes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_payment_settings`
--
ALTER TABLE `employee_payment_settings`
  ADD CONSTRAINT `employee_payment_settings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_penssion_schemes`
--
ALTER TABLE `employee_penssion_schemes`
  ADD CONSTRAINT `employee_penssion_schemes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_terms`
--
ALTER TABLE `employee_terms`
  ADD CONSTRAINT `employee_terms_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_venue`
--
ALTER TABLE `employee_venue`
  ADD CONSTRAINT `employee_venue_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_venue_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_working_patterns`
--
ALTER TABLE `employee_working_patterns`
  ADD CONSTRAINT `employee_working_patterns_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_working_pattern_details`
--
ALTER TABLE `employee_working_pattern_details`
  ADD CONSTRAINT `ewp_employee_wp_id` FOREIGN KEY (`employee_working_pattern_id`) REFERENCES `employee_working_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_working_pattern_pays`
--
ALTER TABLE `employee_working_pattern_pays`
  ADD CONSTRAINT `ewp_pay_employee_wp_id` FOREIGN KEY (`employee_working_pattern_id`) REFERENCES `employee_working_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employments`
--
ALTER TABLE `employments`
  ADD CONSTRAINT `employments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employments_employee_job_title_id_foreign` FOREIGN KEY (`employee_job_title_id`) REFERENCES `employee_job_titles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `employment_references`
--
ALTER TABLE `employment_references`
  ADD CONSTRAINT `employment_references_applicant_employment_id_foreign` FOREIGN KEY (`applicant_employment_id`) REFERENCES `applicant_employments` (`id`);

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `hr_bank_holidays`
--
ALTER TABLE `hr_bank_holidays`
  ADD CONSTRAINT `hr_holiday_year_id_frn_key` FOREIGN KEY (`hr_holiday_year_id`) REFERENCES `hr_holiday_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_holiday_year_leave_options`
--
ALTER TABLE `hr_holiday_year_leave_options`
  ADD CONSTRAINT `hr_holiday_year_id_lo_frn_key` FOREIGN KEY (`hr_holiday_year_id`) REFERENCES `hr_holiday_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instance_terms`
--
ALTER TABLE `instance_terms`
  ADD CONSTRAINT `instance_terms_course_creation_instance_id_foreign` FOREIGN KEY (`course_creation_instance_id`) REFERENCES `course_creation_instances` (`id`);

--
-- Constraints for table `module_creations`
--
ALTER TABLE `module_creations`
  ADD CONSTRAINT `module_creations_course_module_id_foreign` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`),
  ADD CONSTRAINT `module_creations_instance_term_id_foreign` FOREIGN KEY (`instance_term_id`) REFERENCES `instance_terms` (`id`),
  ADD CONSTRAINT `module_creations_module_level_id_foreign` FOREIGN KEY (`module_level_id`) REFERENCES `module_levels` (`id`);

--
-- Constraints for table `module_datafutures`
--
ALTER TABLE `module_datafutures`
  ADD CONSTRAINT `module_datafutures_course_module_id_foreign` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`);

--
-- Constraints for table `permission_templates`
--
ALTER TABLE `permission_templates`
  ADD CONSTRAINT `permission_templates_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_templates_permission_category_id_foreign` FOREIGN KEY (`permission_category_id`) REFERENCES `permission_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_templates_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_template_groups`
--
ALTER TABLE `permission_template_groups`
  ADD CONSTRAINT `permission_template_param_groups_permission_template_id_foreign` FOREIGN KEY (`permission_template_id`) REFERENCES `permission_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plans`
--
ALTER TABLE `plans`
  ADD CONSTRAINT `plans_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `plans_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `plans_module_creation_id_foreign` FOREIGN KEY (`module_creation_id`) REFERENCES `module_creations` (`id`),
  ADD CONSTRAINT `plans_rooms_id_foreign` FOREIGN KEY (`rooms_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `plans_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `plans_date_lists`
--
ALTER TABLE `plans_date_lists`
  ADD CONSTRAINT `plans_date_lists_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `referral_codes`
--
ALTER TABLE `referral_codes`
  ADD CONSTRAINT `referral_codes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `students_applicant_user_id_foreign` FOREIGN KEY (`applicant_user_id`) REFERENCES `applicant_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `students_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `students_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `students_sex_identifier_id_foreign` FOREIGN KEY (`sex_identifier_id`) REFERENCES `sex_identifiers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `students_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `students_title_id_foreign` FOREIGN KEY (`title_id`) REFERENCES `titles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_archives`
--
ALTER TABLE `student_archives`
  ADD CONSTRAINT `student_archives_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_archives_student_user_id_foreign` FOREIGN KEY (`student_user_id`) REFERENCES `student_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_awarding_body_details`
--
ALTER TABLE `student_awarding_body_details`
  ADD CONSTRAINT `student_awarding_body_details_student_course_relation_id_foreign` FOREIGN KEY (`student_course_relation_id`) REFERENCES `student_course_relations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_awarding_body_details_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_consents`
--
ALTER TABLE `student_consents`
  ADD CONSTRAINT `student_consents_consent_policy_id_foreign` FOREIGN KEY (`consent_policy_id`) REFERENCES `consent_policies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_consents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_consents_student_user_id_foreign` FOREIGN KEY (`student_user_id`) REFERENCES `student_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_contacts`
--
ALTER TABLE `student_contacts`
  ADD CONSTRAINT `student_contacts_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_contacts_permanent_address_id_foreign` FOREIGN KEY (`permanent_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_contacts_permanent_country_id_foreign` FOREIGN KEY (`permanent_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_contacts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_contacts_term_time_accommodation_type_id_foreign` FOREIGN KEY (`term_time_accommodation_type_id`) REFERENCES `term_time_accommodation_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `student_contacts_term_time_address_id_foreign` FOREIGN KEY (`term_time_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_course_relations`
--
ALTER TABLE `student_course_relations`
  ADD CONSTRAINT `student_course_relations_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_course_relations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_disabilities`
--
ALTER TABLE `student_disabilities`
  ADD CONSTRAINT `student_disabilities_disability_id_foreign` FOREIGN KEY (`disability_id`) REFERENCES `disabilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_disabilities_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `student_documents_document_setting_id_foreign` FOREIGN KEY (`document_setting_id`) REFERENCES `document_settings` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_emails`
--
ALTER TABLE `student_emails`
  ADD CONSTRAINT `student_emails_common_smtp_id_foreign` FOREIGN KEY (`common_smtp_id`) REFERENCES `comon_smtps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_emails_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_emails_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_emails_attachments`
--
ALTER TABLE `student_emails_attachments`
  ADD CONSTRAINT `student_emails__attachments_student_document_id_foreign` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_emails__attachments_student_email_id_foreign` FOREIGN KEY (`student_email_id`) REFERENCES `student_emails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_employments`
--
ALTER TABLE `student_employments`
  ADD CONSTRAINT `student_employments_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_employments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_employment_references`
--
ALTER TABLE `student_employment_references`
  ADD CONSTRAINT `student_employment_references_student_employment_id_foreign` FOREIGN KEY (`student_employment_id`) REFERENCES `student_employments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_fee_eligibilities`
--
ALTER TABLE `student_fee_eligibilities`
  ADD CONSTRAINT `student_fee_eligibilities_fee_eligibility_id_foreign` FOREIGN KEY (`fee_eligibility_id`) REFERENCES `fee_eligibilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_fee_eligibilities_student_course_relation_id_foreign` FOREIGN KEY (`student_course_relation_id`) REFERENCES `student_course_relations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_fee_eligibilities_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_interviews`
--
ALTER TABLE `student_interviews`
  ADD CONSTRAINT `student_interviews_student_document_id_foreign` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_interviews_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_interviews_student_task_id_foreign` FOREIGN KEY (`student_task_id`) REFERENCES `student_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_interviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_kins`
--
ALTER TABLE `student_kins`
  ADD CONSTRAINT `student_kins_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_kins_kins_relation_id_foreign` FOREIGN KEY (`kins_relation_id`) REFERENCES `kins_relations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_kins_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_letters`
--
ALTER TABLE `student_letters`
  ADD CONSTRAINT `student_letters_applicant_letter_id_foreign` FOREIGN KEY (`applicant_letter_id`) REFERENCES `applicant_letters` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_letters_comon_smtp_id_foreign` FOREIGN KEY (`comon_smtp_id`) REFERENCES `comon_smtps` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_letters_letter_set_id_foreign` FOREIGN KEY (`letter_set_id`) REFERENCES `letter_sets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_letters_signatory_id_foreign` FOREIGN KEY (`signatory_id`) REFERENCES `signatories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_letters_student_document_id_foreign` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_letters_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_notes`
--
ALTER TABLE `student_notes`
  ADD CONSTRAINT `student_notes_student_document_id_foreign` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_notes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_other_details`
--
ALTER TABLE `student_other_details`
  ADD CONSTRAINT `student_other_details_ethnicity_id_foreign` FOREIGN KEY (`ethnicity_id`) REFERENCES `ethnicities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_other_details_hesa_gender_id_foreign` FOREIGN KEY (`hesa_gender_id`) REFERENCES `hesa_genders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_other_details_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_other_details_sexual_orientation_id_foreign` FOREIGN KEY (`sexual_orientation_id`) REFERENCES `sexual_orientations` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_other_details_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_proof_of_ids`
--
ALTER TABLE `student_proof_of_ids`
  ADD CONSTRAINT `student_proof_of_ids_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_proposed_courses`
--
ALTER TABLE `student_proposed_courses`
  ADD CONSTRAINT `student_course_relation_id_foreign` FOREIGN KEY (`student_course_relation_id`) REFERENCES `student_course_relations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_proposed_courses_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_proposed_courses_course_creation_id_foreign` FOREIGN KEY (`course_creation_id`) REFERENCES `course_creations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_proposed_courses_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_proposed_courses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_qualifications`
--
ALTER TABLE `student_qualifications`
  ADD CONSTRAINT `student_qualifications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_sms`
--
ALTER TABLE `student_sms`
  ADD CONSTRAINT `student_sms_sms_template_id_foreign` FOREIGN KEY (`sms_template_id`) REFERENCES `sms_templates` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_sms_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_tasks`
--
ALTER TABLE `student_tasks`
  ADD CONSTRAINT `student_tasks_applicant_task_id_foreign` FOREIGN KEY (`applicant_task_id`) REFERENCES `applicant_tasks` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_tasks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_tasks_task_list_id_foreign` FOREIGN KEY (`task_list_id`) REFERENCES `task_lists` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `student_tasks_task_status_id_foreign` FOREIGN KEY (`task_status_id`) REFERENCES `task_statuses` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `student_task_documents`
--
ALTER TABLE `student_task_documents`
  ADD CONSTRAINT `student_task_documents_student_document_id_foreign` FOREIGN KEY (`student_document_id`) REFERENCES `student_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_task_documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_task_documents_student_task_id_foreign` FOREIGN KEY (`student_task_id`) REFERENCES `student_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_lists`
--
ALTER TABLE `task_lists`
  ADD CONSTRAINT `task_lists_process_list_id_foreign` FOREIGN KEY (`process_list_id`) REFERENCES `process_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `venue_ip_addresses`
--
ALTER TABLE `venue_ip_addresses`
  ADD CONSTRAINT `venue_ip_addresses_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
