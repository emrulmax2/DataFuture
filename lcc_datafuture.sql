-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 07, 2023 at 03:48 PM
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
-- Database: `lcc_datafuture`
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `from_date`, `to_date`, `target_date_hesa_report`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2021 - 2022', '0', NULL, '0', NULL, '2021-09-01', '2022-04-30', '2023-05-31', 1, NULL, NULL, '2023-01-10 08:46:32', '2023-01-10 10:13:01'),
(2, '2022 - 2023', '0', NULL, '0', NULL, '2023-05-01', '2023-07-31', '2022-02-28', 1, NULL, NULL, '2023-01-12 04:23:40', '2023-01-12 04:23:40');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `address_line_1`, `address_line_2`, `state`, `post_code`, `city`, `country`, `created_at`, `created_by`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(2, 'Flat 11', '37 Cavell Street', '', 'E1 2BP', 'London', 'United Kingdom', '2023-04-07 04:23:38', 1, 1, '2023-04-07 08:01:47', NULL),
(3, 'London Churchill College', '116 Cavell Street', '', 'E1 2JA', 'London', 'United Kingdom', '2023-04-07 04:25:26', 1, NULL, '2023-04-07 04:25:26', NULL),
(4, '116 Cavalry Drive', '', 'Cambridgeshire', 'PE15 9DP', 'March', 'United Kingdom', '2023-04-07 05:19:41', 1, NULL, '2023-04-07 05:19:41', NULL);

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
  `date_of_birth` date NOT NULL,
  `gender` enum('MALE','FEMALE','OTHERS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `submission_date` date DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
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

INSERT INTO `applicants` (`id`, `applicant_user_id`, `application_no`, `title_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `submission_date`, `status_id`, `nationality_id`, `country_id`, `referral_code`, `is_referral_varified`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '', 2, 'Abdul', 'Latif', '1986-11-25', 'MALE', '2023-04-12', 2, 1, 1, '4587', 0, 1, 1, '2023-04-10 07:44:45', '2023-04-18 10:11:03', NULL),
(4, 1, '', 2, 'Abdul', 'Latif', '1986-11-26', 'MALE', NULL, 2, 1, 1, NULL, NULL, 1, 1, '2023-04-12 07:07:21', '2023-04-12 07:07:21', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(26, 1, 'applicant_users', 'email', 'limon@churchill.ac', 'themewar@gmail.com', 1, NULL, '2023-05-02 08:17:20', '2023-05-02 08:17:20', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=374 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_disabilities`
--

INSERT INTO `applicant_disabilities` (`id`, `applicant_id`, `disabilitiy_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(371, 1, 1, 1, NULL, '2023-04-20 04:55:24', '2023-04-20 04:55:24', NULL),
(372, 4, 1, 1, NULL, '2023-04-20 10:05:53', '2023-04-20 10:05:53', NULL),
(373, 4, 2, 1, NULL, '2023-04-20 10:05:53', '2023-04-20 10:05:53', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(19, 1, NULL, 0, 'png', NULL, 'http://127.0.0.1:8000/storage/applicants/1/1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', '1686148091_maxresdefault 1.png', 1, NULL, NULL, '2023-06-07 08:28:11', '2023-06-07 08:43:56');

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
(2, 4, 'Abdul Latif', 1, '01740149260', 'limon@churchill.ac', '110 Cavell Road', 'Cheshunt', 'Hertfordshire', 'EN7 6JL', 'Waltham Cross', 'United Kingdom', 1, 1, '2023-04-12 07:07:21', '2023-04-12 07:07:21', NULL);

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
  `gender_identity` enum('Yes','No','Refused') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexual_orientation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `religion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applicant_other_details_applicant_id_foreign` (`applicant_id`),
  KEY `applicant_other_details_ethnicity_id_foreign` (`ethnicity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_other_details`
--

INSERT INTO `applicant_other_details` (`id`, `applicant_id`, `ethnicity_id`, `disability_status`, `disabilty_allowance`, `is_edication_qualification`, `employment_status`, `college_introduction`, `gender_identity`, `sexual_orientation_id`, `religion_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 0, 1, 'Part Time', NULL, NULL, NULL, NULL, 1, 1, '2023-04-10 07:44:45', '2023-04-28 04:12:44', NULL),
(2, 4, 1, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, 1, 1, '2023-04-12 07:07:21', '2023-04-12 07:07:21', NULL);

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
  KEY `applicant_proposed_courses_course_creation_id_foreign` (`course_creation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_proposed_courses`
--

INSERT INTO `applicant_proposed_courses` (`id`, `applicant_id`, `course_creation_id`, `semester_id`, `student_loan`, `student_finance_england`, `fund_receipt`, `applied_received_fund`, `other_funding`, `full_time`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 5, 4, 'Others', NULL, NULL, NULL, 'Self Funding', 1, 1, 1, '2023-04-11 03:30:21', '2023-04-20 05:36:17', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_tasks`
--

INSERT INTO `applicant_tasks` (`id`, `applicant_id`, `task_list_id`, `external_link_ref`, `status`, `task_status_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Completed', NULL, 1, 1, NULL, '2023-05-17 04:18:50', '2023-05-31 08:26:07'),
(3, 1, 4, NULL, 'Pending', NULL, 1, 1, NULL, '2023-05-17 04:19:00', '2023-05-31 04:42:09'),
(4, 1, 5, NULL, 'Pending', 1, 1, 1, NULL, '2023-05-17 04:21:51', '2023-05-31 07:16:18'),
(5, 1, 2, NULL, 'Pending', NULL, 1, NULL, NULL, '2023-05-17 09:19:23', '2023-05-31 07:18:03'),
(6, 1, 6, NULL, 'Pending', NULL, 1, NULL, NULL, '2023-05-17 09:25:15', '2023-05-30 05:38:07');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_task_documents`
--

INSERT INTO `applicant_task_documents` (`id`, `applicant_task_id`, `applicant_document_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(2, 1, 2, 1, NULL, NULL, '2023-05-29 05:23:02', '2023-05-29 05:23:02'),
(3, 1, 3, 1, NULL, NULL, '2023-05-29 05:23:03', '2023-05-29 05:23:03'),
(4, 1, 4, 1, NULL, NULL, '2023-05-29 06:28:34', '2023-05-29 06:28:34'),
(5, 1, 5, 1, NULL, NULL, '2023-05-31 07:11:56', '2023-05-31 07:11:56'),
(6, 3, 13, 1, NULL, NULL, '2023-06-05 06:37:04', '2023-06-05 06:37:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, 3, 'Document', '', '', 'http://127.0.0.1:8000/storage/applicants/1/1685968623_SobujCv-9.pdf', 1, NULL, NULL, '2023-06-05 06:37:04', '2023-06-05 06:37:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `module_creation_id`, `course_module_base_assesment_id`, `assessment_name`, `assessment_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(29, 25, 3, 'Assesment 01', 'AS-001', 1, NULL, '2023-02-23 03:31:25', '2023-02-23 03:31:25', NULL),
(31, 25, 5, 'Assesment 03', 'AS-003', 1, NULL, '2023-02-23 05:04:33', '2023-02-23 05:04:33', NULL),
(33, 26, 1, 'Assesment Heros', 'AS-091', 1, NULL, '2023-02-23 05:24:27', '2023-02-23 05:24:27', NULL),
(35, 27, 5, 'Assesment 03', 'AS-003', 1, NULL, '2023-02-23 07:34:17', '2023-02-23 07:34:17', NULL),
(36, 27, 4, 'Assesment 03', 'AS-002', 1, NULL, '2023-02-23 07:34:48', '2023-02-23 07:34:48', NULL),
(38, 28, 2, 'Assesment Zero', 'AS-076', 1, NULL, '2023-02-23 07:35:12', '2023-02-23 07:35:12', NULL);

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
(18, 'LONDON CHURCHILL', '0', NULL, '0', NULL, 1, NULL, '2022-12-21 22:05:19', '2022-12-21 22:05:19', NULL),
(19, 'Abdul Latif', '0', NULL, '0', NULL, 1, NULL, '2023-01-05 05:07:27', '2023-01-05 05:07:27', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_holidays`
--

INSERT INTO `bank_holidays` (`id`, `academic_year_id`, `start_date`, `end_date`, `duration`, `title`, `type`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 2, '2023-01-02', '2023-01-02', 1, 'New Year’s Day', 'Bank Holiday', 1, NULL, NULL, '2023-03-17 06:08:12', '2023-03-17 06:08:12');

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
  `iso_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `iso_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bangladesh', 1, '34', 1, '56', 'BD', 1, 1, '2023-03-29 06:41:23', '2023-03-29 06:41:48', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creations`
--

INSERT INTO `course_creations` (`id`, `semester_id`, `course_id`, `course_creation_qualification_id`, `duration`, `unit_length`, `slc_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 4, 1, NULL, 3, 'Months', '678', 1, NULL, '2023-01-18 07:22:32', '2023-01-18 07:22:32', NULL),
(6, 2, 1, NULL, 2, 'Years', 'SLC0983', 1, 1, '2023-02-24 05:57:44', '2023-02-24 05:58:00', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_creation_qualifications`
--

INSERT INTO `course_creation_qualifications` (`id`, `name`, `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 'Secondary School Senior', 1, '2022-12-21 04:41:21', 1, '2022-12-21 07:34:41', NULL),
(2, 'Diploma Course', 1, '2022-12-21 06:44:36', NULL, '2022-12-21 06:44:36', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'IT Department', 1, NULL, NULL, '2023-05-15 04:41:42', '2023-05-15 04:41:42'),
(2, 'Admin Department', 1, NULL, NULL, '2023-05-15 04:41:55', '2023-05-15 04:41:55');

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

INSERT INTO `disabilities` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Two or more impairments and/or disabling medical conditions', 1, '34', 1, '56', 1, 1, '2023-03-30 08:31:38', '2023-03-30 08:31:50', NULL),
(2, 'A specific learning difficulty such as dyslexia, dyspraxia or AD(H)D', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:32:15', '2023-03-30 08:32:15', NULL),
(3, 'A social/communication impairment such as Asperger\'s syndrome/other autistic spectrum disorder', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:32:32', '2023-03-30 08:32:32', NULL),
(4, 'A long standing illness or health condition such as cancer, HIV, diabetes, chronic heart disease, or epilepsy', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:32:46', '2023-03-30 08:32:46', NULL),
(5, 'A mental health condition, such as depression, schizophrenia or anxiety disorder', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:33:03', '2023-03-30 08:33:03', NULL),
(6, 'A physical impairment or mobility issues, such as difficulty using arms or using a wheelchair or crutches', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:33:25', '2023-03-30 08:33:25', NULL),
(7, 'Deaf or a serious hearing impairment', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:33:44', '2023-03-30 08:33:44', NULL),
(8, 'Blind or a serious visual impairment uncorrected by glasses', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:33:57', '2023-03-30 08:33:57', NULL),
(9, 'A disability, impairment or medical condition that is not listed above', 0, NULL, 0, NULL, 1, NULL, '2023-03-30 08:34:09', '2023-03-30 08:34:09', NULL);

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
  `student_profile` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_settings`
--

INSERT INTO `document_settings` (`id`, `name`, `type`, `application`, `admission`, `registration`, `live`, `student_profile`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Passport', 'optional', '1', '1', '1', '0', '0', 1, 1, NULL, '2023-05-02 09:18:54', '2023-05-02 09:37:28'),
(2, 'Blood Group Test', 'mandatory', '1', '1', '0', '0', '0', 1, NULL, NULL, '2023-05-03 03:18:04', '2023-05-03 03:18:04');

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ethnicities`
--

INSERT INTO `ethnicities` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Asian', 1, '34', 1, '56', 1, 1, '2023-03-28 04:16:21', '2023-03-28 04:16:53', NULL),
(2, 'African', 1, '34', 1, NULL, 1, NULL, '2023-04-18 09:04:36', '2023-04-18 09:04:36', NULL);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'A', 1, 1, NULL, '2023-01-10 08:00:29', '2023-02-28 06:05:14'),
(2, 'B', 1, NULL, NULL, '2023-02-28 06:05:21', '2023-02-28 06:05:21'),
(3, 'C', 1, NULL, NULL, '2023-02-28 06:05:33', '2023-02-28 06:05:33'),
(4, 'D', 1, NULL, NULL, '2023-02-28 06:05:39', '2023-02-28 06:05:39'),
(5, 'E', 1, NULL, NULL, '2023-02-28 06:05:46', '2023-02-28 06:05:46');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instance_terms`
--

INSERT INTO `instance_terms` (`id`, `course_creation_instance_id`, `name`, `term`, `session_term`, `start_date`, `end_date`, `total_teaching_weeks`, `teaching_start_date`, `teaching_end_date`, `revision_start_date`, `revision_end_date`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2021 Sep HND', 'Autumn Term', 1, '2022-07-01', '2023-01-31', 3, '2023-01-05', '2023-01-31', '2023-01-26', '2023-01-30', 1, 1, NULL, '2023-01-26 04:23:31', '2023-02-24 06:05:14'),
(2, 1, '2022 Jan HND', 'Winter Term', 2, '2023-01-01', '2023-01-31', 12, '2023-01-01', '2023-01-31', '2023-01-01', '2023-01-31', 1, 1, NULL, '2023-01-27 04:33:01', '2023-01-27 04:58:29'),
(3, 1, 'April 2021', 'Spring Term', 1, '2022-07-01', '2022-11-30', 3, '2023-01-05', '2023-01-31', '2023-01-26', '2023-01-31', 1, 1, NULL, '2023-02-07 11:27:50', '2023-02-13 04:05:33'),
(4, 4, '2021 Sep HND', 'Spring Term', 1, '2023-03-01', '2023-03-31', 3, '2023-03-01', '2023-03-31', '2023-03-30', '2023-03-31', 1, NULL, NULL, '2023-02-24 05:59:41', '2023-02-24 05:59:41'),
(5, 5, '2021 Jan HND', 'Winter Term', 2, '2023-04-01', '2023-04-30', 5, '2023-04-01', '2023-04-30', '2023-04-29', '2023-04-30', 1, NULL, NULL, '2023-02-24 06:00:45', '2023-02-24 06:00:45');

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kins_relations`
--

INSERT INTO `kins_relations` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Brother', 1, '34', 1, '56', 1, 1, '2023-03-28 06:33:26', '2023-03-28 06:33:49', NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
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
(51, '2023_04_07_142849_create_applicants_table', 33),
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
(89, '2023_06_07_104156_create_applicant_notes_table', 88);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_categories`
--

INSERT INTO `permission_categories` (`id`, `name`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Permission Cat 01', 1, NULL, NULL, '2023-05-15 04:37:03', '2023-05-15 04:37:03'),
(2, 'Permission Cat 02', 1, NULL, NULL, '2023-05-15 04:37:13', '2023-05-15 04:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `permission_templates`
--

DROP TABLE IF EXISTS `permission_templates`;
CREATE TABLE IF NOT EXISTS `permission_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_category_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `R` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `W` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `D` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_templates_permission_category_id_index` (`permission_category_id`),
  KEY `permission_templates_role_id_index` (`role_id`),
  KEY `permission_templates_department_id_index` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_templates`
--

INSERT INTO `permission_templates` (`id`, `permission_category_id`, `role_id`, `department_id`, `type`, `R`, `W`, `D`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Persona Data', '1', '1', '0', 1, 1, NULL, '2023-05-15 04:46:17', '2023-05-15 04:54:23'),
(2, 1, 1, 1, 'Documents', '1', '1', '0', 1, NULL, NULL, '2023-05-15 04:54:01', '2023-05-15 04:54:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `course_id`, `module_creation_id`, `venue_id`, `rooms_id`, `group_id`, `name`, `start_time`, `end_time`, `label`, `sat`, `sun`, `mon`, `tue`, `wed`, `thu`, `fri`, `module_enrollment_key`, `submission_date`, `tutor_id`, `personal_tutor_id`, `virtual_room`, `note`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(15, 1, 25, 1, 1, 1, '', '10:15:00', '11:15:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 3, 4, NULL, 'Update Note', 1, 1, '2023-03-03 09:00:32', '2023-03-22 07:15:45', NULL),
(16, 1, 25, 1, 1, 2, '', '11:00:00', '12:00:00', '', 0, 0, 0, 1, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, 5, 'http://video.com', 'Hi, This is a test note.', 1, 1, '2023-03-03 09:00:32', '2023-03-22 08:09:10', NULL),
(17, 1, 25, 1, 3, 2, '', '13:00:00', '14:00:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 3, 8, NULL, NULL, 1, 1, '2023-03-03 09:00:32', '2023-03-22 08:05:23', NULL),
(18, 1, 25, 1, 1, 4, '', '10:11:00', '10:11:00', '', 0, 0, 0, 0, 1, 0, 0, 'ENKEY01', '2023-03-31', 9, 5, '', '', 1, 1, '2023-03-03 09:00:32', '2023-03-06 08:44:15', NULL),
(19, 1, 25, 1, 2, 3, '', '10:00:00', '10:00:00', '', 0, 0, 0, 0, 0, 1, 0, 'ENKEY01', '2023-03-31', 6, 4, '', '', 1, 1, '2023-03-03 09:00:32', '2023-03-06 08:44:15', NULL),
(20, 1, 25, 1, 3, 5, '', '14:00:00', '15:00:00', '', 0, 0, 0, 0, 0, 0, 1, 'ENKEY01', '2023-03-31', 5, 9, '', '', 1, 1, '2023-03-03 09:00:32', '2023-03-06 08:44:15', NULL),
(21, 1, 25, 1, 2, 4, '', '11:30:00', '12:30:00', '', 1, 0, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 5, 7, '', '', 1, 1, '2023-03-03 09:00:32', '2023-03-06 08:44:15', NULL),
(22, 1, 25, 1, 4, 4, '', '14:50:00', '15:50:00', '', 0, 1, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 2, 4, '', '', 1, 1, '2023-03-03 09:00:32', '2023-03-06 08:44:15', NULL),
(23, 1, 25, 2, 7, 2, '', '12:00:00', '13:00:00', '', 1, 0, 0, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 5, 7, '', '', 1, 1, '2023-03-03 09:04:15', '2023-03-06 08:44:15', NULL),
(24, 1, 25, 1, 3, 3, '', '12:00:00', '13:00:00', '', 0, 0, 1, 0, 0, 0, 0, 'ENKEY01', '2023-03-31', 8, 7, 'http://video.com', 'This is note.', 1, NULL, '2023-03-06 08:44:15', '2023-03-22 08:31:55', NULL);

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plans_date_lists_plan_id_foreign` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans_date_lists`
--

INSERT INTO `plans_date_lists` (`id`, `plan_id`, `name`, `date`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 15, 'Teaching', '2023-01-09', 1, NULL, '2023-03-17 09:46:55', '2023-03-17 09:46:55', NULL),
(2, 15, 'Teaching', '2023-01-16', 1, NULL, '2023-03-17 09:46:55', '2023-03-17 09:46:55', NULL),
(3, 15, 'Teaching', '2023-01-23', 1, NULL, '2023-03-17 09:46:55', '2023-03-17 09:46:55', NULL),
(4, 15, 'Revision', '2023-01-30', 1, NULL, '2023-03-17 09:46:56', '2023-03-17 09:46:56', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `process_lists`
--

INSERT INTO `process_lists` (`id`, `name`, `phase`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Applicant Process', 'Applicant', 1, NULL, NULL, '2023-05-15 07:30:08', '2023-05-15 07:37:13'),
(2, 'Register Process', 'Register', 1, NULL, NULL, '2023-05-15 07:37:00', '2023-05-15 07:37:00');

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Islam', 1, '34', 1, '56', 1, 1, '2023-03-29 03:46:47', '2023-03-29 03:47:12', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `display_name`, `type`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Staff Access Role', 'Staff', 1, NULL, NULL, '2023-05-10 04:30:12', '2023-05-10 04:30:12'),
(2, 'Tutor Access Role', 'Tutor', 1, NULL, NULL, '2023-05-10 04:30:30', '2023-05-10 04:30:30'),
(3, 'Admin Access', 'Admin', 1, NULL, NULL, '2023-05-10 04:30:45', '2023-05-10 04:30:45');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, 2, 'UP 05', 30, 1, NULL, NULL, '2023-02-28 06:08:31', '2023-02-28 06:08:31');

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
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 'January 2023', 1, '2022-12-13 06:17:01', 1, '2022-12-13 09:22:01', NULL),
(2, 'September 2022', 1, '2022-12-13 07:02:48', NULL, '2022-12-13 07:04:41', NULL),
(3, 'April 2022', 1, '2022-12-13 07:08:04', NULL, '2022-12-13 07:08:21', NULL),
(4, 'January 2022', 1, '2022-12-13 07:10:37', NULL, '2022-12-13 07:10:52', NULL);

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sexual_orientations`
--

INSERT INTO `sexual_orientations` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ORI', 1, '34', 1, '56', 1, 1, '2023-03-28 06:58:29', '2023-03-28 06:58:50', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `source_tuition_fees`
--

INSERT INTO `source_tuition_fees` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HSBC Bank', '0', NULL, '0', NULL, 1, NULL, '2023-01-05 05:40:00', '2023-01-05 05:40:00', NULL),
(2, 'HSBC Banks', '0', NULL, '0', NULL, 1, 1, '2023-01-05 05:40:12', '2023-01-05 05:54:23', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Status 01', 'Register', 1, 1, '2023-03-29 04:47:38', '2023-03-29 04:49:05', NULL),
(2, 'New', 'Applicant', 1, 1, '2023-04-10 05:46:45', '2023-04-11 09:48:19', NULL),
(3, 'Students', 'Student', 1, NULL, '2023-04-10 05:47:01', '2023-04-10 05:47:01', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(12, 2, 'BP', 'Please fill out the consent form', 'No', 'Yes', '0', NULL, 'Yes', 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_list_statuses`
--

INSERT INTO `task_list_statuses` (`id`, `task_list_id`, `task_status_id`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 12, 3, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(2, 12, 4, 1, NULL, NULL, '2023-05-30 07:20:51', '2023-05-30 07:20:51'),
(5, 5, 1, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26'),
(6, 5, 2, NULL, 1, NULL, '2023-05-30 08:29:26', '2023-05-30 08:29:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(28, 6, 13, NULL, 1, NULL, '2023-05-30 08:56:44', '2023-05-30 08:56:44');

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `titles`
--

INSERT INTO `titles` (`id`, `name`, `is_hesa`, `hesa_code`, `is_df`, `df_code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Mr', 0, NULL, 1, '56', 1, 1, '2023-03-27 07:32:30', '2023-03-28 03:29:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `photo`, `gender`, `active`, `deleted_at`, `remember_token`, `created_at`, `updated_at`, `social_id`, `social_type`) VALUES
(1, 'Left4code', 'midone@left4code.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'rXKChYhwFMusnSUj3kjO9eAhBGvbgMUzq18hBKtm5IlkAUX1J46LeyB1HZDG', NULL, NULL, NULL, NULL),
(2, 'Harrison Cremin', 'damion.fay@example.net', '2022-11-30 06:53:15', '$2y$10$F0ryXIQxB2eksXTTjX.uRe6dtUBWEMCMr9hMEAYyE37o.Qktn/p6G', 'Avatar_2_1684145272.jpg', 'Male', 1, NULL, 'ETYy4QlCjg', '2022-11-30 06:53:15', '2023-05-15 04:11:25', NULL, NULL),
(3, 'Zita Kerluke', 'padberg.jamar@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'LGdQXwI1f7', '2022-11-30 06:53:15', '2023-05-15 04:16:18', NULL, NULL),
(5, 'Celia Mueller', 'unique41@example.org', '2022-11-30 06:53:15', '$2y$10$ARzi56cRKRzb1mX/.xTooOcsJWxyfCS7S8GJ.aavTwN.29uWhNefO', 'Avatar_5_1684405386.jpg', 'Male', 1, NULL, 'L9mbprm5DK', '2022-11-30 06:53:15', '2023-05-18 04:23:06', NULL, NULL),
(6, 'Prof. Carli Bayer I', 'annamae43@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'ZSk0s3x4bc', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(7, 'Celine Rosenbaum DDS', 'koepp.nayeli@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'Fi72GjUCnk', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(8, 'Miss Simone Bergstrom', 'aturner@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'oQPnTIegwi', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(9, 'Glenda Sawayn DDS', 'ed73@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'bf7sibkrrH', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(10, 'Jewell Satterfield DDS', 'thowe@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'YQrg2ynerw', '2022-11-30 06:53:15', '2022-11-30 06:53:15', NULL, NULL),
(13, 'Abdul Latif', 'limon34@churchill.ac', NULL, '$2y$10$x8iTn58PwuPdusVpdI85jeaKvL.uRK6o.GOINjGvwCQHcXhc.L4wi', NULL, 'Male', 1, NULL, NULL, '2023-05-10 05:18:59', '2023-05-10 05:18:59', NULL, NULL),
(14, 'User Photos', 'userphoto@pht.com', NULL, '$2y$10$fHBnMmK7dT67Ek2lhArJF.ReZcwUN33U8LDLJhzfjKHrLsi.MXu3e', NULL, 'Male', 1, NULL, NULL, '2023-05-12 06:46:06', '2023-05-15 04:03:51', NULL, NULL),
(15, 'New Photo', 'newphoto@gmail.com', NULL, '$2y$10$QFZgU6HBrgVT2ZQ9dleKfOa8ePWQRAuBnJ.7fw00U/NNfQLLoNmiS', 'Avatar_15_1683895662.jpg', 'Male', 1, NULL, NULL, '2023-05-12 06:47:42', '2023-05-12 06:47:43', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_id`, `user_id`) VALUES
(1, 3, 13),
(2, 2, 13),
(3, 1, 13),
(4, 3, 15),
(15, 2, 14),
(17, 1, 2),
(18, 1, 5);

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
(2, 'Upton Park', '122', '2342334', 'werwerwer', 1, 1, NULL, '2023-02-13 08:37:09', '2023-02-28 06:06:09');

--
-- Constraints for dumped tables
--

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
-- Constraints for table `applicant_employments`
--
ALTER TABLE `applicant_employments`
  ADD CONSTRAINT `applicant_employments_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`);

--
-- Constraints for table `applicant_kin`
--
ALTER TABLE `applicant_kin`
  ADD CONSTRAINT `applicant_kin_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_kin_kins_relation_id_foreign` FOREIGN KEY (`kins_relation_id`) REFERENCES `kins_relations` (`id`);

--
-- Constraints for table `applicant_other_details`
--
ALTER TABLE `applicant_other_details`
  ADD CONSTRAINT `applicant_other_details_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `applicant_other_details_ethnicity_id_foreign` FOREIGN KEY (`ethnicity_id`) REFERENCES `ethnicities` (`id`);

--
-- Constraints for table `applicant_proposed_courses`
--
ALTER TABLE `applicant_proposed_courses`
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
-- Constraints for table `employment_references`
--
ALTER TABLE `employment_references`
  ADD CONSTRAINT `employment_references_applicant_employment_id_foreign` FOREIGN KEY (`applicant_employment_id`) REFERENCES `applicant_employments` (`id`);

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
-- Constraints for table `task_lists`
--
ALTER TABLE `task_lists`
  ADD CONSTRAINT `task_lists_process_list_id_foreign` FOREIGN KEY (`process_list_id`) REFERENCES `process_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
