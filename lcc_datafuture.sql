-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 27, 2023 at 12:33 PM
-- Server version: 5.7.36
-- PHP Version: 7.4.26

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
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

INSERT INTO `academic_years` (`id`, `name`, `code`, `from_date`, `to_date`, `target_date_hesa_report`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2022 - 2023', NULL, '2023-09-01', '2023-07-31', '2023-05-31', 1, NULL, NULL, '2023-01-10 08:46:32', '2023-01-10 10:13:01'),
(2, '2021-2022', NULL, '2021-09-01', '2022-04-30', '2022-02-28', 1, NULL, NULL, '2023-01-12 04:23:40', '2023-01-12 04:23:40');

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
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

INSERT INTO `awarding_bodies` (`id`, `name`, `code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'TRINITY COLLEGE LONDON', NULL, 1, NULL, NULL, '2023-01-05 05:18:55', NULL),
(3, 'NORTHUMBRIA UNIVERSITY', NULL, 1, NULL, NULL, NULL, NULL),
(4, 'PEARSON', NULL, 1, NULL, NULL, NULL, NULL),
(5, 'NCFE', NULL, 1, NULL, NULL, NULL, NULL),
(7, 'ASSOCIATION OF CHARTERED CERTIFIED ACCOUNTANTS', NULL, 1, NULL, NULL, NULL, NULL),
(8, 'LONDON CENTRE of  MARKETING', NULL, 1, NULL, NULL, NULL, NULL),
(9, 'MANCHESTER METROPOLITAN UNIVERSITY (IAW MDP)', NULL, 1, NULL, NULL, NULL, NULL),
(10, 'AABPS', NULL, 1, NULL, NULL, NULL, NULL),
(11, 'APBP', NULL, 1, 1, NULL, '2022-12-23 02:37:49', NULL),
(12, 'ATHE', NULL, 1, NULL, NULL, NULL, NULL),
(13, 'EBMA', NULL, 1, NULL, NULL, NULL, NULL),
(14, 'THE INSTITUTE FOR THE MANAGEMENT OF INFORMATION SYSTEMS', NULL, 1, NULL, NULL, NULL, NULL),
(15, 'ASCENTIS', NULL, 1, NULL, NULL, NULL, NULL),
(16, 'UNIVERSITY OF BEDFORDSHIRE', NULL, 1, NULL, NULL, NULL, NULL),
(17, 'ILM', NULL, 1, NULL, NULL, NULL, NULL),
(18, 'LONDON CHURCHILL', NULL, 1, NULL, '2022-12-21 22:05:19', '2022-12-21 22:05:19', NULL),
(19, 'Abdul Latif', NULL, 1, NULL, '2023-01-05 05:07:27', '2023-01-05 05:07:27', NULL);

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
(4, 6, 2, '2023-02-03', '2023-02-28', 5, 1, NULL, '2023-02-24 05:58:26', '2023-02-24 05:58:26', NULL),
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(37, '2023_03_27_103949_create_titles_table', 21);

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `source_tuition_fees`
--

DROP TABLE IF EXISTS `source_tuition_fees`;
CREATE TABLE IF NOT EXISTS `source_tuition_fees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

INSERT INTO `source_tuition_fees` (`id`, `name`, `code`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HSBC Bank', NULL, 1, NULL, '2023-01-05 05:40:00', '2023-01-05 05:40:00', NULL),
(2, 'HSBC Banks', NULL, 1, 1, '2023-01-05 05:40:12', '2023-01-05 05:54:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

DROP TABLE IF EXISTS `titles`;
CREATE TABLE IF NOT EXISTS `titles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `photo`, `gender`, `active`, `deleted_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Left4code', 'midone@left4code.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, '3UIdXwbqZzmFz5qK6LJjRJCfcTvnam7vM6letORAob2uzzKplyF73siwcg3i', NULL, NULL),
(2, 'Harrison Cremin', 'damion.fay@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'ETYy4QlCjg', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(3, 'Zita Kerluke', 'padberg.jamar@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'LGdQXwI1f7', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(4, 'Arielle Johnston V', 'gregory55@example.org', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'MLwvNE4U1N', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(5, 'Celia Mueller', 'unique41@example.org', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'L9mbprm5DK', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(6, 'Prof. Carli Bayer I', 'annamae43@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'ZSk0s3x4bc', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(7, 'Celine Rosenbaum DDS', 'koepp.nayeli@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'Fi72GjUCnk', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(8, 'Miss Simone Bergstrom', 'aturner@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'oQPnTIegwi', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(9, 'Glenda Sawayn DDS', 'ed73@example.net', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'female', 1, NULL, 'bf7sibkrrH', '2022-11-30 06:53:15', '2022-11-30 06:53:15'),
(10, 'Jewell Satterfield DDS', 'thowe@example.com', '2022-11-30 06:53:15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'male', 1, NULL, 'YQrg2ynerw', '2022-11-30 06:53:15', '2022-11-30 06:53:15');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
