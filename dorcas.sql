-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 01, 2018 at 11:56 AM
-- Server version: 5.7.22-0ubuntu0.16.04.1
-- PHP Version: 7.2.4-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hostville_dorcas`
--

-- --------------------------------------------------------

--
-- Table structure for table `bill_payments`
--

CREATE TABLE `bill_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `reference` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processor` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `amount` decimal(12,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `json_data` text COLLATE utf8mb4_unicode_ci,
  `is_successful` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bill_payments`
--

INSERT INTO `bill_payments` (`id`, `company_id`, `plan_id`, `reference`, `processor`, `currency`, `amount`, `json_data`, `is_successful`, `updated_at`, `created_at`) VALUES
  (1, 1, 2, 'T169686633148908', 'paystack', 'NGN', '5000.00', '{\"gateway_response\":\"Successful\",\"channel\":\"card\",\"source_ip\":\"41.58.223.17\",\"custom_data\":[{\"display_name\":\"Mobile Number\",\"variable_name\":\"mobile_number\",\"value\":\"08136680801\"},{\"display_name\":\"Business\",\"variable_name\":\"business\",\"value\":\"ABC Limited\"},{\"display_name\":\"Plan\",\"variable_name\":\"plan\",\"value\":\"classic\"},{\"display_name\":\"Plan Type\",\"variable_name\":\"plan_type\",\"value\":\"monthly\"}],\"card\":{\"auth_code\":\"AUTH_xahf7wgqho\",\"last4\":\"4081\",\"exp_month\":\"01\",\"exp_year\":\"2020\",\"card_type\":\"visa DEBIT\"}}', 1, '2018-04-03 06:11:18', '2018-04-03 06:11:18'),
  (2, 1, 2, '7vr5o945xiuag2z', 'paystack', 'NGN', '5000.00', '{\"gateway_response\":\"Successful\",\"channel\":\"card\",\"source_ip\":\"auto_biller\",\"custom_data\":[],\"card\":{\"auth_code\":\"AUTH_xahf7wgqho\",\"last4\":\"4081\",\"exp_month\":\"01\",\"exp_year\":\"2020\",\"card_type\":\"visa DEBIT\"}}', 1, '2018-04-03 11:30:32', '2018-04-03 11:30:32'),
  (3, 27, 3, 'T804571682573959', 'paystack', 'NGN', '7500.00', '{\"gateway_response\":\"Successful\",\"channel\":\"card\",\"source_ip\":\"154.113.89.194\",\"custom_data\":[{\"display_name\":\"Mobile Number\",\"variable_name\":\"mobile_number\",\"value\":\"09087654567\"},{\"display_name\":\"Business\",\"variable_name\":\"business\",\"value\":\"Mayphem Ventures\"},{\"display_name\":\"Plan\",\"variable_name\":\"plan\",\"value\":\"premium\"},{\"display_name\":\"Plan Type\",\"variable_name\":\"plan_type\",\"value\":\"monthly\"}],\"card\":{\"auth_code\":\"AUTH_0ppq76xpt7\",\"last4\":\"4081\",\"exp_month\":\"01\",\"exp_year\":\"2020\",\"card_type\":\"visa DEBIT\"}}', 1, '2018-04-04 13:18:49', '2018-04-04 13:18:49');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `reg_number` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_type` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `extra_data` text COLLATE utf8mb4_unicode_ci,
  `logo_url` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_expires_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `uuid`, `plan_id`, `reg_number`, `name`, `phone`, `email`, `website`, `plan_type`, `extra_data`, `logo_url`, `access_expires_at`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, '609be2be-f330-11e7-8ff0-68b599e6dae8', 2, '1478990', 'ABC Limited', '08123320811', 'help@abclimited.ng', 'https://abclimited.com.ng', 'monthly', '{\"paystack_authorization_code\":\"AUTH_xahf7wgqho\"}', 'business-logos/qiV1ORZKgGwF8wBKOb6PyyM5wgo7dcy3LeOZhFe3.svg', '2018-05-03 22:59:59', NULL, '2018-04-10 15:38:42', '2018-01-06 22:24:36'),
  (4, '1f67fbb8-f473-11e7-99d3-68b599e6dae8', 1, NULL, 'Hostville Nigeria Ltd.', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-01-08 12:54:54', '2018-01-08 12:54:54'),
  (5, '88dd9224-0107-11e8-9a6d-68b599e6dae8', 1, NULL, 'Rabotesh Nigeria Ltd.', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-01-24 13:07:30', '2018-01-24 13:07:30'),
  (6, '417b6332-0109-11e8-bcba-68b599e6dae8', 1, NULL, 'Rabotesh Nigeria Ltd.', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-01-24 13:19:49', '2018-01-24 13:19:49'),
  (21, 'e36ee55c-269c-11e8-bba3-68b599e6dae8', 1, NULL, 'Brass Payments Ltd', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-03-13 08:59:50', '2018-03-13 08:59:50'),
  (24, '515b00ac-2dee-11e8-aa6c-0024d75f326c', 1, NULL, 'Go My Way Ltd', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-03-22 16:30:22', '2018-03-22 16:30:22'),
  (25, 'e76cf454-2f71-11e8-b108-0024d75f326c', 1, NULL, 'Sola Akindolu', NULL, NULL, NULL, 'monthly', NULL, NULL, NULL, NULL, '2018-03-24 14:44:49', '2018-03-24 14:44:49'),
  (27, '112071fe-37ce-11e8-9e33-68b599e6dae8', 3, '1478990', 'Mayphem Ventures', '090123456789', 'hello@maphemi.com.ng', 'https://mayphemi.com.ng', 'monthly', '{\"paystack_authorization_code\":\"AUTH_0ppq76xpt7\"}', NULL, '2018-05-04 22:59:59', NULL, '2018-04-04 14:10:16', '2018-04-04 06:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `company_service`
--

CREATE TABLE `company_service` (
  `company_id` int(11) UNSIGNED NOT NULL,
  `service_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_fields`
--

CREATE TABLE `contact_fields` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `name` char(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_fields`
--

INSERT INTO `contact_fields` (`id`, `uuid`, `company_id`, `name`, `updated_at`, `created_at`) VALUES
  (1, '49c42d1e-f49b-11e7-959e-68b599e6dae8', 1, 'Work Phone', '2018-03-20 11:36:26', '2018-01-08 17:42:25'),
  (2, 'e22f3aaa-f52a-11e7-b395-68b599e6dae8', 1, 'Work Email', '2018-03-20 11:36:18', '2018-01-09 10:50:19'),
  (7, 'e83a70d0-0b51-11e8-b0e2-68b599e6dae8', 1, 'Gender', '2018-02-06 15:25:05', '2018-02-06 15:25:05'),
  (8, 'ed996b1c-0b51-11e8-9aaf-68b599e6dae8', 1, 'Date of Birth', '2018-02-06 15:25:14', '2018-02-06 15:25:14'),
  (9, '25d52c5e-0bdf-11e8-bff1-68b599e6dae8', 1, 'Home Address', '2018-03-24 01:15:15', '2018-02-07 08:16:07'),
  (11, 'f9437ef6-2c32-11e8-b1e0-68b599e6dae8', 1, 'Twitter ID', '2018-03-20 11:36:47', '2018-03-20 11:36:47'),
  (12, '694f9156-3810-11e8-b10f-68b599e6dae8', 27, 'Middlename', '2018-04-04 13:59:37', '2018-04-04 13:59:37'),
  (13, 'bf0d5efc-3810-11e8-9efc-68b599e6dae8', 27, 'Home Address', '2018-04-04 14:02:01', '2018-04-04 14:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dialing_code` char(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `uuid`, `name`, `iso_code`, `dialing_code`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, '1f139cdb-b95e-11e7-8bef-a8e06b771503', 'Nigeria', 'NG', '+234', NULL, NULL, '2017-10-25 08:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `firstname` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `uuid`, `company_id`, `firstname`, `lastname`, `phone`, `email`, `updated_at`, `created_at`) VALUES
  (2, '77bf17f6-ff66-11e7-b45d-0024d75f326c', 1, 'Jacob', 'Ijehs', '0811889105', 'emmanix2002@gmail.com', '2018-02-07 12:21:10', '2018-01-08 15:42:48'),
  (3, '8c2116bc-1229-11e8-98c2-0024d75f326c', 1, 'Dugu', 'Xinfeng', '08123320811', 'dugu@china.com', '2018-02-15 08:23:48', '2018-02-15 08:23:48'),
  (4, 'ac5b2e94-122a-11e8-bf5c-0024d75f326c', 1, 'Xiaochun', 'Bai', '08093320811', 'bai.xiaochun@hotmail.com', '2018-02-15 08:31:52', '2018-02-15 08:31:52'),
  (6, '86ad7a68-122e-11e8-a5d7-0024d75f326c', 1, 'Yunhai', 'Ke', '09098765678', 'yunhai.ke@ancient-immortal-sect.cn', '2018-02-15 08:59:27', '2018-02-15 08:59:27');

-- --------------------------------------------------------

--
-- Table structure for table `customer_contacts`
--

CREATE TABLE `customer_contacts` (
  `contact_field_id` bigint(15) UNSIGNED NOT NULL,
  `customer_id` bigint(15) UNSIGNED NOT NULL,
  `value` char(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_contacts`
--

INSERT INTO `customer_contacts` (`contact_field_id`, `customer_id`, `value`) VALUES
  (1, 2, '08166666666'),
  (1, 4, '08142339088'),
  (2, 4, 'max-power@baibai.com'),
  (2, 6, 'ke.yunhai@issth.cn'),
  (7, 2, 'Male'),
  (7, 4, 'Male'),
  (7, 6, 'Male'),
  (8, 4, '25/03/1994'),
  (9, 4, '234, Street Blvd., Ikeja, Lagos'),
  (11, 2, 'schoolbaseapp'),
  (11, 4, 'baixiaochun');

-- --------------------------------------------------------

--
-- Table structure for table `customer_group`
--

CREATE TABLE `customer_group` (
  `customer_id` bigint(15) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_group`
--

INSERT INTO `customer_group` (`customer_id`, `group_id`, `created_at`) VALUES
  (2, 1, '2018-03-06 11:32:15'),
  (4, 1, '2018-03-06 11:32:15');

-- --------------------------------------------------------

--
-- Table structure for table `customer_notes`
--

CREATE TABLE `customer_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint(15) UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_notes`
--

INSERT INTO `customer_notes` (`id`, `uuid`, `customer_id`, `message`, `updated_at`, `created_at`) VALUES
  (2, '5f896606-2c44-11e8-949b-68b599e6dae8', 4, 'Sample note sample test.', '2018-03-20 13:41:20', '2018-03-20 13:41:20'),
  (5, 'b19e35da-2c48-11e8-bb55-96ebcdc5ac77', 4, 'Customer prefers orders home delivered T+1 after order is completed.', '2018-03-20 14:12:16', '2018-03-20 14:12:16'),
  (6, '33134fe0-2c4b-11e8-9fc1-96ebcdc5ac77', 4, 'Another note, to see how it get\'s aligned.', '2018-03-20 14:30:12', '2018-03-20 14:30:12'),
  (8, 'd2ac9b74-2c4b-11e8-a0ce-96ebcdc5ac77', 4, 'In this video, we work through how to put your PHP application in a subdirectory of another site.\n\nFor example, we may have an application running at example.org but need a second application running at example.org/blog.\n\nThis feels like it should be simple, but it turns out to be more complex and fraught with confusing Nginx configurations! To make matter worse (or, perhaps, to illustrate this point), a quick Google search reveals a TON of confusing, non-working examples.', '2018-03-20 14:34:40', '2018-03-20 14:34:40'),
  (9, '8a46ff4e-382d-11e8-abc1-68b599e6dae8', 4, 'A short note.', '2018-04-04 17:28:07', '2018-04-04 17:28:07'),
  (10, '9221be16-382d-11e8-8cd5-68b599e6dae8', 4, 'Another one to be saved!', '2018-04-04 17:28:21', '2018-04-04 17:28:21');

-- --------------------------------------------------------

--
-- Table structure for table `customer_order`
--

CREATE TABLE `customer_order` (
  `customer_id` bigint(15) UNSIGNED NOT NULL,
  `order_id` bigint(15) UNSIGNED NOT NULL,
  `is_paid` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_order`
--

INSERT INTO `customer_order` (`customer_id`, `order_id`, `is_paid`, `paid_at`) VALUES
  (2, 2, 0, NULL),
  (2, 4, 1, '2018-03-30 14:34:45'),
  (2, 5, 1, '2018-03-29 23:00:00'),
  (2, 7, 1, '2018-03-30 07:30:40');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `uuid`, `company_id`, `name`, `description`, `updated_at`, `created_at`) VALUES
  (2, '9c9b050c-291c-11e8-b176-0024d75f326c', 1, 'Technology', 'This team is in-charge of all product development activities, and planning.', '2018-03-16 14:17:23', '2018-03-16 13:19:09'),
  (3, 'd56f6616-291c-11e8-a9a9-0024d75f326c', 1, 'Customer Support', 'The customer support department.', '2018-03-20 07:51:49', '2018-03-16 13:20:45'),
  (4, 'e0136e32-291c-11e8-8743-0024d75f326c', 1, 'Finance', 'Finance department.', '2018-03-28 09:41:33', '2018-03-16 13:21:02');

-- --------------------------------------------------------

--
-- Table structure for table `domains`
--

CREATE TABLE `domains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domainable_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domainable_id` int(11) NOT NULL,
  `domain` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuration_json` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_issuances`
--

CREATE TABLE `domain_issuances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` bigint(20) UNSIGNED DEFAULT NULL,
  `domainable_type` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domainable_id` int(11) UNSIGNED DEFAULT NULL,
  `prefix` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `domain_issuances`
--

INSERT INTO `domain_issuances` (`id`, `uuid`, `domain_id`, `domainable_type`, `domainable_id`, `prefix`, `updated_at`, `created_at`) VALUES
  (4, 'c5e80130-4898-11e8-97f5-0024d75f326c', NULL, 'App\\Models\\Company', 1, 'edc', '2018-04-25 14:56:02', '2018-04-25 14:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `department_id` int(11) UNSIGNED DEFAULT NULL,
  `location_id` bigint(15) UNSIGNED DEFAULT NULL,
  `firstname` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('female','male') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary_amount` decimal(10,2) UNSIGNED DEFAULT NULL,
  `salary_period` enum('month','year') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'month',
  `staff_code` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` char(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hired_at` date DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `uuid`, `company_id`, `department_id`, `location_id`, `firstname`, `lastname`, `gender`, `salary_amount`, `salary_period`, `staff_code`, `job_title`, `email`, `phone`, `hired_at`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, 'a249bc66-2a23-11e8-83a1-0024d75f326c', 1, 4, NULL, 'Michael', 'Olubajo', 'male', '120000.00', 'month', '090001', 'Product Manager', 'michael.olubajo@abclimited.com', '09012345678', NULL, NULL, '2018-03-20 07:52:07', '2018-03-17 20:41:56'),
  (2, '0ef5a3a2-2b7d-11e8-930e-ba2e77413619', 1, 3, 1, 'Yemisi', 'Solomon', 'female', '78900.00', 'month', '090002', 'Support Personnel', 'help@abclimited.ng', '08123320811', NULL, NULL, '2018-03-19 22:42:10', '2018-03-19 13:54:35'),
  (3, '72f828fa-2cf6-11e8-89ce-0024d75f326c', 1, 4, NULL, 'Josephine', 'Nzekube', 'female', '98700.00', 'month', '090004', 'Marketing Manager', 'bai.xiaochun@hotmail.com', '08093320811', NULL, NULL, '2018-03-28 09:41:15', '2018-03-21 10:56:03'),
  (4, 'a0f807e2-2cf7-11e8-b522-0024d75f326c', 1, 2, NULL, 'Tony', 'Stark', NULL, '980900.00', 'month', '090005', 'CTO', 'tony@abclimited.ng', '08123456789', NULL, NULL, '2018-03-21 11:04:30', '2018-03-21 11:04:30');

-- --------------------------------------------------------

--
-- Table structure for table `employee_team`
--

CREATE TABLE `employee_team` (
  `employee_id` bigint(15) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_team`
--

INSERT INTO `employee_team` (`employee_id`, `team_id`) VALUES
  (1, 2),
  (2, 2),
  (1, 3),
  (1, 4),
  (1, 5),
  (2, 5),
  (3, 5),
  (4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
  (1, 'database', 'default', '{\"displayName\":\"App\\\\Jobs\\\\Billing\\\\ChargeForPlan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\Billing\\\\ChargeForPlan\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\Billing\\\\ChargeForPlan\\\":8:{s:7:\\\"company\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";i:1;s:10:\\\"connection\\\";N;}s:6:\\\"\\u0000*\\u0000job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\Billing\\ChargeForPlan has been attempted too many times or run too long. The job may have previously timed out. in /var/www/dorcas/api/vendor/illuminate/queue/Worker.php:394\nStack trace:\n#0 /var/www/dorcas/api/vendor/illuminate/queue/Worker.php(314): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), 1)\n#1 /var/www/dorcas/api/vendor/illuminate/queue/Worker.php(270): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#2 /var/www/dorcas/api/vendor/illuminate/queue/Worker.php(114): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#3 /var/www/dorcas/api/vendor/illuminate/queue/Console/WorkCommand.php(101): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#4 /var/www/dorcas/api/vendor/illuminate/queue/Console/WorkCommand.php(85): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#5 [internal function]: Illuminate\\Queue\\Console\\WorkCommand->handle()\n#6 /var/www/dorcas/api/vendor/illuminate/container/BoundMethod.php(29): call_user_func_array(Array, Array)\n#7 /var/www/dorcas/api/vendor/illuminate/container/BoundMethod.php(87): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#8 /var/www/dorcas/api/vendor/illuminate/container/BoundMethod.php(31): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Laravel\\Lumen\\Application), Array, Object(Closure))\n#9 /var/www/dorcas/api/vendor/illuminate/container/Container.php(549): Illuminate\\Container\\BoundMethod::call(Object(Laravel\\Lumen\\Application), Array, Array, NULL)\n#10 /var/www/dorcas/api/vendor/illuminate/console/Command.php(183): Illuminate\\Container\\Container->call(Array)\n#11 /var/www/dorcas/api/vendor/symfony/console/Command/Command.php(252): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#12 /var/www/dorcas/api/vendor/illuminate/console/Command.php(170): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#13 /var/www/dorcas/api/vendor/symfony/console/Application.php(946): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#14 /var/www/dorcas/api/vendor/symfony/console/Application.php(248): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#15 /var/www/dorcas/api/vendor/symfony/console/Application.php(148): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#16 /var/www/dorcas/api/vendor/illuminate/console/Application.php(88): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#17 /var/www/dorcas/api/vendor/laravel/lumen-framework/src/Console/Kernel.php(84): Illuminate\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#18 /var/www/dorcas/api/artisan(35): Laravel\\Lumen\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#19 {main}', '2018-04-03 11:30:28');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `uuid`, `company_id`, `name`, `description`, `updated_at`, `created_at`) VALUES
  (1, '52b5de04-1264-11e8-8e68-0024d75f326c', 1, 'Debtors', 'This list is for customers that owe us some money.', '2018-02-15 15:31:38', '2018-02-15 15:24:32');

-- --------------------------------------------------------

--
-- Table structure for table `integrations`
--

CREATE TABLE `integrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `type` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuration` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `integrations`
--

INSERT INTO `integrations` (`id`, `uuid`, `company_id`, `type`, `name`, `configuration`, `updated_at`, `created_at`) VALUES
  (4, '97a0e726-366c-11e8-a04b-0024d75f326c', 1, 'payment', 'paystack', '[{\"name\":\"public_key\",\"label\":\"Public Key\",\"value\":\"pk_test_652939ec61ff9f838c8f5566a09276990d3d91c4\"},{\"name\":\"private_key\",\"label\":\"Secret Key\",\"value\":\"sk_test_76e35c57b835df800f7ba8c418c31b7624038ac3\"}]', '2018-04-02 11:54:26', '2018-04-02 11:54:26');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
  (1, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522821019, 1522821019),
  (2, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:26;}s:10:\\\"connection\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522821245, 1522821245),
  (3, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:25;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522821245, 1522821245),
  (4, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:27;}s:10:\\\"connection\\\";N;}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522821882, 1522821882),
  (5, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:26;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522821882, 1522821882),
  (6, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522836410, 1522836410),
  (7, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:27;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522847884, 1522847884),
  (8, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:22:\\\"App\\\\Models\\\\BillPayment\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522847929, 1522847929),
  (9, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:27;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522847929, 1522847929),
  (10, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:27;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522851017, 1522851017),
  (11, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:23:\\\"App\\\\Models\\\\CustomerNote\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522862888, 1522862888),
  (12, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:23:\\\"App\\\\Models\\\\CustomerNote\\\";s:2:\\\"id\\\";a:1:{i:0;i:10;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522862901, 1522862901),
  (13, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522922562, 1522922562),
  (14, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1522923466, 1522923466),
  (15, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1523365880, 1523365880),
  (16, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1523369546, 1523369546),
  (17, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Company\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1523374722, 1523374722),
  (18, 'default', '{\"displayName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\",\"command\":\"O:33:\\\"Laravel\\\\Scout\\\\Jobs\\\\MakeSearchable\\\":7:{s:6:\\\"models\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":3:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:10:\\\"connection\\\";s:8:\\\"database\\\";s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"}}', 0, NULL, 1524754978, 1524754978);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `state_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address1` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address2` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `uuid`, `company_id`, `state_id`, `name`, `address1`, `address2`, `city`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, 'f0a7c616-2855-11e8-a036-68b599e6dae8', 1, 24, 'Head Office', '146, Awolowo way', 'Off Tafawa Balewa Square', 'Ikoyi', NULL, '2018-03-15 13:37:00', '2018-03-15 13:37:00');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
  (1, '2014_10_12_000000_create_users_table', 1),
  (2, '2014_10_12_100000_create_password_resets_table', 1),
  (3, '2016_06_01_000001_create_oauth_auth_codes_table', 2),
  (4, '2016_06_01_000002_create_oauth_access_tokens_table', 2),
  (5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 2),
  (6, '2016_06_01_000004_create_oauth_clients_table', 2),
  (7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 2),
  (8, '2018_01_22_103547_create_jobs_table', 3),
  (9, '2018_03_30_073407_create_failed_jobs_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
  ('0006a89533a640243fec7fa7e24f128545edae6b6d1dc88fe7789aee967d8d292614dcffc62505d8', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:34:26', '2018-04-26 13:34:26', '2019-04-26 14:34:26'),
  ('018d72d955e6804f731096a5a1fdcaec611c1d392bbe288f9bef4c0d050fa8eb55552fa48722c648', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:44:38', '2018-04-26 13:44:38', '2019-04-26 14:44:38'),
  ('04063bef0b18a5d0fd0b49066039145b8755ef814265eb2bd5453323ffaffd2d8f256937359d287e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-03 17:51:25', '2018-04-03 17:51:25', '2019-04-03 18:51:25'),
  ('05089722043ac2d6aca2e65cd504d6d450dae1ca857f35008475221a7bbe9c4a03285b55c08dcd45', 1, 2, NULL, '[\"*\"]', 0, '2018-04-04 05:35:53', '2018-04-04 05:35:53', '2019-04-04 06:35:53'),
  ('05f742c479931a67a26aa0dc34fdca5c352381f0e0e2afb87c480f71aa27cea54e0e154625a65cc2', 1, 2, NULL, '[\"*\"]', 0, '2018-03-26 08:40:13', '2018-03-26 08:40:13', '2019-03-26 09:40:13'),
  ('062c75caf4f840fb29d571e66cdecebfaad05c3d04d1f066ba4e526c11ab36cb50078418e8545fac', 1, 2, NULL, '[\"*\"]', 0, '2018-04-04 09:41:41', '2018-04-04 09:41:41', '2019-04-04 10:41:41'),
  ('08fa438b6ed899c6e07659d43ca3b236e47216359593e0c61238d133482cacfa506239ab1688ebf4', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 22:48:25', '2018-03-22 22:48:25', '2019-03-22 23:48:25'),
  ('0a96a3d12ff187c373e07bbbb0d6a70ab95aa6c5d7cc97dc2eecc72cd3ce627b40452f8befb37f6e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:49:41', '2018-04-26 14:49:41', '2019-04-26 15:49:41'),
  ('0ba5aafd605c2eebadad947939c475d3cf403cb33d2981ef856796dd2c7808b263ca856dc04fa32e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:14:36', '2018-04-26 14:14:36', '2019-04-26 15:14:36'),
  ('0cd2644b002f73a48b97e14c9d2a703dba93f4bc75ba28ee71334619c07336bbd7b89541e0925bbe', 1, 2, NULL, '[\"*\"]', 0, '2018-04-04 05:12:11', '2018-04-04 05:12:11', '2019-04-04 06:12:11'),
  ('0dffdad4e73e6e068f62277719cbd7a7a8a1c35b4f004f92729474f6d7c5e8e6223dfc69d494ebe0', 1, 2, NULL, '[\"*\"]', 0, '2018-04-24 11:41:14', '2018-04-24 11:41:14', '2019-04-24 12:41:14'),
  ('1020eb6d9fc49568322b4c9a016c1f2a2616ffbcc489478770aa592ee09635711cd95778f845657a', 1, 2, NULL, '[\"*\"]', 0, '2018-04-02 23:45:41', '2018-04-02 23:45:41', '2019-04-03 00:45:41'),
  ('10b38d1669ada37a93fa5e02478a004c58a41677e290a72ae2c2258965eecac9dfd82254335ec2fb', 1, 2, NULL, '[\"*\"]', 0, '2018-04-04 17:22:56', '2018-04-04 17:22:56', '2019-04-04 18:22:56'),
  ('10d32682e2746688ddc99be27eea1c2418350da08269d5b3da8aac7fdf98004f7cf7a5112b50b281', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:39:02', '2018-04-26 13:39:02', '2019-04-26 14:39:02'),
  ('131a006382b5482aa280bddf870151ab58c17cd0bbbe72f07fdcea74fbb7da156310d9d6ea4c62c1', 1, 2, NULL, '[\"*\"]', 0, '2018-04-25 14:54:28', '2018-04-25 14:54:28', '2019-04-25 15:54:28'),
  ('151300836c85be0be0f86210fd4109e4557cfd588c3a65e02453fbc762521a6bbcfd27b3b2e30ccb', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:32:23', '2018-04-26 13:32:23', '2019-04-26 14:32:23'),
  ('15d3863e58c84aba56e3a1118cbd7f2aa991b97fcb23721da9c35cac56f0aec8a8bc3590525ed79a', 1, 2, NULL, '[\"*\"]', 0, '2018-04-03 05:48:24', '2018-04-03 05:48:24', '2019-04-03 06:48:24'),
  ('16229830130a70a885ba7767f109df350f61c0e0c65b7aac12b912ede6ff950a841778128181a4fc', 1, 2, NULL, '[\"*\"]', 0, '2018-03-29 12:30:33', '2018-03-29 12:30:33', '2019-03-29 13:30:33'),
  ('24326d6e9e09abea554891e5f5f4a55dab6d190f3208f77da2ef1b7f00dde33988e5a31ca67678fc', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:33:18', '2018-04-26 13:33:18', '2019-04-26 14:33:18'),
  ('28c58ddad0d4a46427372522b08220542b01900617087d0b2360abb511857930d15f68b5fc5d9afc', 1, 2, NULL, '[\"*\"]', 0, '2018-04-05 08:50:09', '2018-04-05 08:50:09', '2019-04-05 09:50:09'),
  ('2a2c1f5a5ac6b56422c564f942c267cde7579ccd0d2f04d5cb035c34b12fefd3b41be0dcaec95f4f', 1, 2, NULL, '[\"*\"]', 0, '2018-04-10 14:06:58', '2018-04-10 14:06:58', '2019-04-10 15:06:58'),
  ('2b3998a75743180eb54e96e50be3fdd4eefd4b9b0dd1571e87640935867d5eb3d981c9c03f648185', 1, 2, NULL, '[\"*\"]', 0, '2018-03-27 07:31:29', '2018-03-27 07:31:29', '2019-03-27 08:31:29'),
  ('2b6ecddc44660120c7fb2da1fb1e7a30fe2f1dce7b6c40f28ca0240cf1e802f38300cc7fb331432e', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:32:48', '2018-03-22 18:32:48', '2019-03-22 19:32:48'),
  ('2e409be9a65173082b2d6477cd3ce506f2e6eacebeb1afd1ea24b7002dce68d49cd6c5404b8f027b', 1, 2, NULL, '[\"*\"]', 0, '2018-03-29 06:17:59', '2018-03-29 06:17:59', '2019-03-29 07:17:59'),
  ('33a0d77bb96387a77debbb22a35ef088c8e4314c2823735f406a5442399dde3779d6d803700f3108', 23, 2, NULL, '[\"*\"]', 0, '2018-03-23 07:17:03', '2018-03-23 07:17:03', '2019-03-23 08:17:03'),
  ('3585d34396eed6733ee88fac1f39c3b61aeb7e8428c18c108ad8b58f68cfa1f5d2f098ef12896768', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:36:46', '2018-04-26 13:36:46', '2019-04-26 14:36:46'),
  ('3b226d7b9a53824a576fdc6cf1abe08f3e1081e1296071a02e50ba4aa5ce0587a39969f63cbe920f', 1, 2, NULL, '[\"*\"]', 0, '2018-04-10 11:59:24', '2018-04-10 11:59:24', '2019-04-10 12:59:24'),
  ('3cd984e33fe18d43f68a2f930d3022946a424e03ffbe1b3dfcdd474256612923b77e670b30638e57', 1, 2, NULL, '[\"*\"]', 0, '2018-04-03 11:33:57', '2018-04-03 11:33:57', '2019-04-03 12:33:57'),
  ('4056c74bb2b6e257f5f7be9ba181d89379d7c29829ff6d7ca4985373b4c68fb55cc70a19f7dc5f84', 1, 2, NULL, '[\"*\"]', 0, '2018-03-23 23:17:50', '2018-03-23 23:17:50', '2019-03-24 00:17:50'),
  ('444ff1b7e207e6eea5a7e66511a769bd986f6ce2bcefbe4abcfa5af441f9750034315f7f00800a4e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-24 13:42:19', '2018-04-24 13:42:19', '2019-04-24 14:42:19'),
  ('4ec15260d28cbcdfee8a36a326fb3dc0150f182652f3a098cefa60ba44d070ae377357bbc58788b4', 1, 2, NULL, '[\"*\"]', 0, '2018-04-10 11:59:06', '2018-04-10 11:59:06', '2019-04-10 12:59:06'),
  ('53d4059c1652fa7aae3c9008617a98a73b50c4e0622532ab37b7f52f18318a9c19f05b8537eb3a4f', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 12:11:06', '2018-04-26 12:11:06', '2019-04-26 13:11:06'),
  ('58fa5fdeb8d97c441452aaaf5013f586de34c8142d97d3ad49ecd440d99db0542eca42ceb0b50035', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:11:48', '2018-04-26 14:11:48', '2019-04-26 15:11:48'),
  ('5a20630671e94a14a1ed4ccc6d18035dbdf9766ff5c438a401b942a65846730ecd56ee5c589e9a0a', 1, 2, NULL, '[\"*\"]', 0, '2018-03-28 13:14:44', '2018-03-28 13:14:44', '2019-03-28 14:14:44'),
  ('5d7bf07671268871184256ca5f6b3866967eeefd9f834755b659436b119cdac9393da6483c907677', 1, 2, NULL, '[\"*\"]', 0, '2018-03-28 11:14:23', '2018-03-28 11:14:23', '2019-03-28 12:14:23'),
  ('5da946024055f2dc87670f8e6933dc694e81756459eadc46aa2bb53b38eb3d5d0eb5fa9cd1cbc54d', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:51:33', '2018-04-26 14:51:33', '2019-04-26 15:51:33'),
  ('619d8e3672233dd6bd1c366c73399c955704c9b15411ec80d179f59e47a1b752ae16c233b399049f', 1, 2, NULL, '[\"*\"]', 0, '2018-03-30 06:24:30', '2018-03-30 06:24:30', '2019-03-30 07:24:30'),
  ('65ec3e22c46307ae98f749068ce2ba131541d3c06bb989fb24cc4338d2a4abed71063ffdd9b90fda', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:21:44', '2018-04-26 14:21:44', '2019-04-26 15:21:44'),
  ('6a858e559db629f65397bcc31d740349ba8740dc491dcd0aaa5b53debb903c81d10f8249475bbdcc', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 12:12:59', '2018-04-26 12:12:59', '2019-04-26 13:12:59'),
  ('7271b6967f6ba51dc725c1c705fcb03c251dd9efb1e9336a5334a1f0a5a59b96ba48c9e267b302cd', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:16:14', '2018-04-26 14:16:14', '2019-04-26 15:16:14'),
  ('73df9898a5cad6ab39ad02af31af18a03b65329fa62e654871babd38aa784c6ca6782753ea5f2fdb', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:26:53', '2018-04-26 14:26:53', '2019-04-26 15:26:53'),
  ('73e60e4df61a459269aa621f93f4ebd7088530ad01bce5932f7aa1a4668ffbcae68d6f2bec3bf6bc', 1, 2, NULL, '[\"*\"]', 0, '2018-04-10 09:56:31', '2018-04-10 09:56:31', '2019-04-10 10:56:31'),
  ('7b267256408dbcb8981d942f4a6efe42c9312a59cf37419bdd77e7251175c707187f3152b2e67c9b', 1, 2, NULL, '[\"*\"]', 0, '2018-04-19 15:08:16', '2018-04-19 15:08:16', '2019-04-19 16:08:16'),
  ('7e0421266ff5f3b658912d1a99353d51c8b439e9c10a1980ed219e11c30a231b44b273aefc64c54b', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:47:29', '2018-04-26 14:47:29', '2019-04-26 15:47:29'),
  ('7faa8cd9865369e13f077ce747a4b4cdb1ef685f556a65b17c1c7533aa5b4e4effe920561a4851cf', 1, 2, NULL, '[\"*\"]', 0, '2018-04-18 15:52:40', '2018-04-18 15:52:40', '2019-04-18 16:52:40'),
  ('8207500d094e5e3dfba14910cfb0387de0d671c3ad7b99c42e8662b73e127045f7e3739a08136304', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:05:53', '2018-04-26 13:05:53', '2019-04-26 14:05:53'),
  ('8a6820c1eb0bdec44140d83b259779be7a98aaea1a5f7edde4b1e0b6cccdee18117fddd72a82f640', 1, 2, NULL, '[\"*\"]', 0, '2018-03-26 12:53:52', '2018-03-26 12:53:52', '2019-03-26 13:53:52'),
  ('8b4bc601e715a1c5fcb15a71f2c1c825d0f64dff3e20ed0d9c2f40a451dda11cdebdf3146c96ba42', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:18:12', '2018-04-26 14:18:12', '2019-04-26 15:18:12'),
  ('8d302703ca95250c45cd393900c3cb1125c4972d5f5f23b8c4eaad85ed018580f4d7e5a92f6c87cb', 1, 2, NULL, '[\"*\"]', 0, '2018-04-02 11:49:08', '2018-04-02 11:49:08', '2019-04-02 12:49:08'),
  ('913cf1f1b6072b4ecdbd73aad7bb1dd697ad95ad7748d688b06a45d597d2f4aee1115a4384c882b4', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:38:33', '2018-04-26 13:38:33', '2019-04-26 14:38:33'),
  ('93490ee76da60b625e8edb38ad0bddeb446c4f851ed6930c726eb0ea9dc3149c322a62b8ac577afd', 1, 2, NULL, '[\"*\"]', 0, '2018-04-19 12:48:29', '2018-04-19 12:48:29', '2019-04-19 13:48:29'),
  ('964b2fe0d1572c358990e95c8afe556280f335cb29d52a384df7af352d1c2f4d9a54cd296b4e6ed9', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:31:39', '2018-04-26 13:31:39', '2019-04-26 14:31:39'),
  ('97ee2446f832ae36d85e2ae2219bbb3358f955ccd9efda4e66a5e9d53f7127a5eaf892b668e92d8c', 1, 2, NULL, '[\"*\"]', 0, '2018-04-24 11:41:02', '2018-04-24 11:41:02', '2019-04-24 12:41:02'),
  ('9ad129a7e2fb3d1bef15249c4fd2c890d11385015c3eb6b9826db64b23f99bd5f9c0828ad67d1a93', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 12:10:48', '2018-04-26 12:10:48', '2019-04-26 13:10:48'),
  ('9c3630b6dc31e45901dfa74bd944209885b03bcbfb97df2aa901fab1636ac33c8def06eb6db475ab', 1, 2, NULL, '[\"*\"]', 0, '2018-03-23 21:05:00', '2018-03-23 21:05:00', '2019-03-23 22:05:00'),
  ('9ec172ddc884ab21d4317ad01a02ecfb24a0a973c2f69ccc92cdae1eac2464e43e5145dd5d368f4f', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:27:45', '2018-03-22 18:27:45', '2019-03-22 19:27:45'),
  ('a6d2483e4ebd090946fc72a7ae2d6420fd6be446adb9bb88915851357c0ad94bea09ed46abe1e561', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:39:17', '2018-04-26 13:39:17', '2019-04-26 14:39:17'),
  ('a855b682c9aa80906cc0db770b39292a462e79f5059801228f6f7e9cf3ae3270b48340dac5d5523f', 26, 2, NULL, '[\"*\"]', 0, '2018-04-04 12:44:34', '2018-04-04 12:44:34', '2019-04-04 13:44:34'),
  ('a86f1dfabf7d3887abed34582abf9d9e2910c93a36e008ca94166a8a2680bc67ed386ebca00c5086', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:45:35', '2018-04-26 13:45:35', '2019-04-26 14:45:35'),
  ('aa3993c7b55ad9fa927a8d2e0c0f79fbe6983ba0549d3815703a8126de2bf2e7b9a273ca0e0fccdb', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:03:41', '2018-04-26 13:03:41', '2019-04-26 14:03:41'),
  ('ad71c7da635f6df2b727eadddd90a55bda46d55f3f13486379ac40971d7eec5ec2e6e0d26ac4a93e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:45:53', '2018-04-26 14:45:53', '2019-04-26 15:45:53'),
  ('b22214077177e631ba0ed29731d5260ec35cb2440f1da688f2d0d8043829a80a9d141991cd083780', 1, 2, NULL, '[\"*\"]', 0, '2018-04-24 08:58:30', '2018-04-24 08:58:30', '2019-04-24 09:58:30'),
  ('b4d94870faf05a91d637f4649f0d5c0ab142374882d462b55a113d8bdf90b8c3094a80aadd2ef394', 1, 2, NULL, '[\"*\"]', 0, '2018-04-24 16:01:03', '2018-04-24 16:01:03', '2019-04-24 17:01:03'),
  ('b9f2931649c0d5599719cec8ec749a942353e3ef062f55aa7e4844a16a999ec3e9e895511f12d1d3', 1, 2, NULL, '[\"*\"]', 0, '2018-03-30 08:26:21', '2018-03-30 08:26:21', '2019-03-30 09:26:21'),
  ('bac5f973290a747ec92fc306c7e9c3372f26b78d93e4e4448a57b125092212555a07f03eebc03eb0', 1, 2, NULL, '[\"*\"]', 0, '2018-04-02 21:44:22', '2018-04-02 21:44:22', '2019-04-02 22:44:22'),
  ('c05870bd61cc1e26c1c3cb8a1e50b4d1d27d04aabe686c20edf1b5efe2554535cd7b6ffed18f8332', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:28:00', '2018-04-26 14:28:00', '2019-04-26 15:28:00'),
  ('c0e4e26a4054d197643c4adc89e8ba5d3c8a4d20a3b357b799df50f715491cfdc8a6f370f2cb47b2', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:44:02', '2018-04-26 14:44:02', '2019-04-26 15:44:02'),
  ('c16b607365a04f38b0d1022ec441afd6ea956034b196fb89bfecf4310c564cd3a4f221fe66487699', 1, 2, NULL, '[\"*\"]', 0, '2018-03-23 07:36:36', '2018-03-23 07:36:36', '2019-03-23 08:36:36'),
  ('c1c6c6b48a986f1e2a2a4a4e290922d21b7cb37aebcd6b6358ac9e8e5a657bcb6d4f20ee3f860759', 26, 2, NULL, '[\"*\"]', 0, '2018-04-04 10:07:15', '2018-04-04 10:07:15', '2019-04-04 11:07:15'),
  ('c3717fb6f2937e35910d100b0b8382ff4e22e51ed7820c84f4a1cf6ea89fe88d200ab4cf9085f677', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:07:01', '2018-04-26 14:07:01', '2019-04-26 15:07:01'),
  ('c8c0a43151b04b1fe26f034b6298c65c95eb8c5510437b115fcabb857da83701b7c9bc875ef8ad16', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:09:47', '2018-03-22 18:09:47', '2019-03-22 19:09:47'),
  ('c91078ca3a3d1bc54c664e43a37a39096c234d020d68837e887dd2fd3e328cd98a8f0f827c553022', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 12:12:32', '2018-04-26 12:12:32', '2019-04-26 13:12:32'),
  ('cba27ebfd87d3ebeb78c828a0d9355d8c0dcb031218f61429e9c4118cd11e16c2acf3ef5464aa862', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:26:49', '2018-04-26 13:26:49', '2019-04-26 14:26:49'),
  ('cf0f401bb8402b948a266682ada9be438f1cf8f7335817230aa264b19a85b8e3bec8ac86c9e12af4', 1, 2, NULL, '[\"*\"]', 0, '2018-04-18 10:28:29', '2018-04-18 10:28:29', '2019-04-18 11:28:29'),
  ('d0dcc54076b866858905adbb4cf0d34fb828821757d191dfe6599db7857bb8f8e9c9b02565811908', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:25:35', '2018-04-26 14:25:35', '2019-04-26 15:25:35'),
  ('d17b47cb55b8d890f310224eaf393280d7dfa01b42abb7ff537b2f1d5e37a56d661a97021b23b4c4', 1, 2, NULL, '[\"*\"]', 0, '2018-04-04 14:55:06', '2018-04-04 14:55:06', '2019-04-04 15:55:06'),
  ('d1bc1df28a556a74dc45a70f1c68e916c958aa24e04f81f839b829eff6d84615b91d15f0730fb56c', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:28:25', '2018-04-26 13:28:25', '2019-04-26 14:28:25'),
  ('d4adc66867d00dff6279be183c4f81c50cedd57558f2f93ae4736f7edcc370841b0798ff6729006b', 1, 2, NULL, '[\"*\"]', 0, '2018-03-30 09:07:06', '2018-03-30 09:07:06', '2019-03-30 10:07:06'),
  ('d67aab78fb8962b3baae7c8cc63002ddca2ec60b8ae03d0dcde00d3074b1d260e037e3e23f1eaf51', 1, 2, NULL, '[\"*\"]', 0, '2018-03-26 10:53:38', '2018-03-26 10:53:38', '2019-03-26 11:53:38'),
  ('d96bd1280c86691482b264b574f98f20813e9f6a58d7b49d96d8a149a876a426a01cf6332fa6bdcd', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 15:14:31', '2018-04-26 15:14:31', '2019-04-26 16:14:31'),
  ('da39f2b636df3cf1eb1c4b75ac95c2afc6571ca0bc850746f6dd576ca8bfa64a92a9b1dcd7fafd6e', 26, 2, NULL, '[\"*\"]', 0, '2018-04-04 06:04:42', '2018-04-04 06:04:42', '2019-04-04 07:04:42'),
  ('db754c865c7831bb96afed921383a18ede767bd7daa92512a58d0bdd7027b8706f25c9008bab9945', 1, 2, NULL, '[\"*\"]', 0, '2018-04-10 12:00:07', '2018-04-10 12:00:07', '2019-04-10 13:00:07'),
  ('dc3753ed35f1e331ee9f3c3c818df6dac7d03ae07f3ebc301fb266b0f24376f400a110042597759e', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 12:51:18', '2018-04-26 12:51:18', '2019-04-26 13:51:18'),
  ('df1b589591bf46a697af93e58390f7774370eb5c0b5b2f53ac29e413f6403a7d15f1916e1daa752e', 23, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:53:50', '2018-03-22 18:53:50', '2019-03-22 19:53:50'),
  ('e2d936ff8702ea51933407661fa4d6ac9242853c9204ad8651b06dc6c7496af0e2333069f4c6591e', 1, 2, NULL, '[\"*\"]', 0, '2018-03-30 14:44:46', '2018-03-30 14:44:46', '2019-03-30 15:44:46'),
  ('e31693230702e4d0085370d44da02aa2b1fb05a78c6af56c9a567f44852726a08f0a230b2fca4584', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:22:56', '2018-04-26 14:22:56', '2019-04-26 15:22:56'),
  ('e361f1bd6994e828b7a04a35962975d9437698bf624c998c87cf9db7ab957e704082d446fbbc1b78', 1, 2, NULL, '[\"*\"]', 0, '2018-03-28 09:04:14', '2018-03-28 09:04:14', '2019-03-28 10:04:14'),
  ('e9161932796ad082aa14d0bb83f7ef733474291e36015e1459362ffeb578e60b787155a299cc17a8', 1, 2, NULL, '[\"*\"]', 0, '2018-03-23 15:28:07', '2018-03-23 15:28:07', '2019-03-23 16:28:07'),
  ('e9c272b0a567295ff64198e0b974b37c7eb7e318910deeef8fdcee16d3a81b9f8f38df1b3bbb441f', 23, 2, NULL, '[\"*\"]', 0, '2018-03-23 07:35:45', '2018-03-23 07:35:45', '2019-03-23 08:35:45'),
  ('ea0d8d41660f9cf590ab60acaf6c33bb072df2b4604a2bd975a8d50deb288c610f66a784b046cf4d', 24, 2, NULL, '[\"*\"]', 0, '2018-03-24 14:45:08', '2018-03-24 14:45:08', '2019-03-24 15:45:08'),
  ('eeb6fbc0528a5fe24b6700366f218e18d4a8e489e7e65d87f2a9a20917b8de620d62042c25196ab4', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 14:27:07', '2018-04-26 14:27:07', '2019-04-26 15:27:07'),
  ('f05a6e79cc74af16805020c97c02020cd8a801970ee45d66a35467e35a71bbecf0af313ef9bf46ba', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:25:58', '2018-03-22 18:25:58', '2019-03-22 19:25:58'),
  ('f1aaec83529c615ceeab42854e429445538aa00f522aa850f837de5b805d52083bcc8021527cf29f', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:34:09', '2018-04-26 13:34:09', '2019-04-26 14:34:09'),
  ('f30ac1c43af69fffb4e2c18c51f1bc19025487cac440b56ac8d0310b6ef662e01e398fe74763db0f', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:14:59', '2018-03-22 18:14:59', '2019-03-22 19:14:59'),
  ('f5c2ffc30490a4a99325bfac2b6443b2f5a7fb705efdfd51cd410724f57946b903dacc5b0e29ea5d', 1, 2, NULL, '[\"*\"]', 0, '2018-04-26 13:25:11', '2018-04-26 13:25:11', '2019-04-26 14:25:11'),
  ('f9391cbff902d6ba823976d2812fd40b73360d8307533f2fbdaf5e6195a2dfbcb7bf01e289b2b136', 1, 2, NULL, '[\"*\"]', 0, '2018-03-22 18:45:49', '2018-03-22 18:45:49', '2019-03-22 19:45:49'),
  ('fa5680fbd984bfa5c9da4b596cbcb762204ffa3b88f027c0009d3603d37d730ba5b8511af72a52eb', 1, 2, NULL, '[\"*\"]', 0, '2018-03-24 13:35:29', '2018-03-24 13:35:30', '2019-03-24 14:35:29');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
  (1, NULL, ' Personal Access Client', 'jA4GG1wJ8bFIKApWviFuyfNAN29737s258nVVCjk', 'http://localhost', 1, 0, 0, '2017-12-11 14:49:38', '2017-12-11 14:49:38'),
  (2, NULL, ' Password Grant Client', 'hFWx5xkPbVKXvLwD17Lbl5MFczORgKZwvawKOzpc', 'http://localhost', 0, 1, 0, '2017-12-11 14:49:39', '2017-12-11 14:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
  (1, 1, '2017-12-11 14:49:38', '2017-12-11 14:49:38');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `oauth_refresh_tokens`
--

INSERT INTO `oauth_refresh_tokens` (`id`, `access_token_id`, `revoked`, `expires_at`) VALUES
  ('01546ab814f0c979a3c28626802e5d6e3c63369521db9c6796f568351a4eaa0476bdeda72b8981cf', 'a86f1dfabf7d3887abed34582abf9d9e2910c93a36e008ca94166a8a2680bc67ed386ebca00c5086', 0, '2019-04-26 14:45:35'),
  ('0aac1294a53c3bf0e6286f80d625fd7bfb56a31a961a2e9648b096b889ed3e909e449a4e3056de90', '9ec172ddc884ab21d4317ad01a02ecfb24a0a973c2f69ccc92cdae1eac2464e43e5145dd5d368f4f', 0, '2019-03-22 19:27:45'),
  ('0ae8cf0c2e1d587cac5a18099deccbf000bccd2976c0398cf26b74a475a12040b52735205df3a0d3', 'c1c6c6b48a986f1e2a2a4a4e290922d21b7cb37aebcd6b6358ac9e8e5a657bcb6d4f20ee3f860759', 0, '2019-04-04 11:07:15'),
  ('0ba9669f2c09b65456ffc80baa15142bd5ecb335e585bbcab8de48a01922b77c4ec26132cd4385c8', 'e9c272b0a567295ff64198e0b974b37c7eb7e318910deeef8fdcee16d3a81b9f8f38df1b3bbb441f', 0, '2019-03-23 08:35:45'),
  ('0cdbc46413e449fe3cf341947d25449cc008dec602ed620c435607ac636e73d8d074c8807c57689f', 'c16b607365a04f38b0d1022ec441afd6ea956034b196fb89bfecf4310c564cd3a4f221fe66487699', 0, '2019-03-23 08:36:36'),
  ('0cfa43b7ee1ea4d4ec87a4eb4a7f0a74877fda56063b16e9b38b7161bb74cc46c8ae9e58baed128b', '73e60e4df61a459269aa621f93f4ebd7088530ad01bce5932f7aa1a4668ffbcae68d6f2bec3bf6bc', 0, '2019-04-10 10:56:32'),
  ('0de5a064f1d4bcc50bb5c93192e48a543ac45fdcb4b00d853087cbdcb5bdb0bfa51e8bcd94e0b373', 'e31693230702e4d0085370d44da02aa2b1fb05a78c6af56c9a567f44852726a08f0a230b2fca4584', 0, '2019-04-26 15:22:56'),
  ('0e4efda5b55d50daa0714c36a6f084324aa6580a9af840d7216deed2c429ab3e40a6a04fe99dc19f', '2a2c1f5a5ac6b56422c564f942c267cde7579ccd0d2f04d5cb035c34b12fefd3b41be0dcaec95f4f', 0, '2019-04-10 15:06:58'),
  ('0f03a6d6bbf72b31bbc7b67bf3a7addc75c71ee3ac9d008958fe9af1a8b2b8342c828aba0df9ac5d', '0a96a3d12ff187c373e07bbbb0d6a70ab95aa6c5d7cc97dc2eecc72cd3ce627b40452f8befb37f6e', 0, '2019-04-26 15:49:41'),
  ('1069df61b1e02232c2f1fbe3eb1d1d36e320249eb2015124056d255330ef8add928a09f374da253e', 'db754c865c7831bb96afed921383a18ede767bd7daa92512a58d0bdd7027b8706f25c9008bab9945', 0, '2019-04-10 13:00:07'),
  ('121ea94e4f9e745455b3e81bf7dad908888e2e3381ff3aab2aeb0ce84de32b038c7179bcb77708ac', '0cd2644b002f73a48b97e14c9d2a703dba93f4bc75ba28ee71334619c07336bbd7b89541e0925bbe', 0, '2019-04-04 06:12:11'),
  ('1289fb71cdfa1daecbe6dce554e8535d083a31ce5e52ff1903c8ce61d21109ebc833d255315ddce8', '73df9898a5cad6ab39ad02af31af18a03b65329fa62e654871babd38aa784c6ca6782753ea5f2fdb', 0, '2019-04-26 15:26:53'),
  ('129ffe236d5a240a733aa0ecc9adde07e4cc24a91e88085bc617b032ac0735b3979787271b8f9005', 'e2d936ff8702ea51933407661fa4d6ac9242853c9204ad8651b06dc6c7496af0e2333069f4c6591e', 0, '2019-03-30 15:44:46'),
  ('1717eccf45521e08420e54489fc0491c3f7f16d97189870b83d5e9390d67edf795788c4b5c5d5983', '33a0d77bb96387a77debbb22a35ef088c8e4314c2823735f406a5442399dde3779d6d803700f3108', 0, '2019-03-23 08:17:03'),
  ('188668f73774085782aa9e3e22ab8ed3c3705fb0e09d171d8579c407accbf93eb367863d3b7d012c', 'c0e4e26a4054d197643c4adc89e8ba5d3c8a4d20a3b357b799df50f715491cfdc8a6f370f2cb47b2', 0, '2019-04-26 15:44:02'),
  ('1a2363621856d91126cb7212b684b1674c4893a9d2a230d2046299ef9d7863e9a32f67e1085920f6', '10d32682e2746688ddc99be27eea1c2418350da08269d5b3da8aac7fdf98004f7cf7a5112b50b281', 0, '2019-04-26 14:39:02'),
  ('1be3abd5da62c8096ea86caf2eafb6ce74bf38267964421e45daee2d563b53b4bff519f8ef57c33d', '6a858e559db629f65397bcc31d740349ba8740dc491dcd0aaa5b53debb903c81d10f8249475bbdcc', 0, '2019-04-26 13:12:59'),
  ('25e2294a836352ab744c1f886e3d48c762c43a7a5e7a91029e7faf297e3bbc29ba1ead0432ba7a97', '8b4bc601e715a1c5fcb15a71f2c1c825d0f64dff3e20ed0d9c2f40a451dda11cdebdf3146c96ba42', 0, '2019-04-26 15:18:12'),
  ('265dbd91b16b962369ccfe159ccaab359f51a8929256d20929874033372a2f7678c14b2193c00c8c', '3585d34396eed6733ee88fac1f39c3b61aeb7e8428c18c108ad8b58f68cfa1f5d2f098ef12896768', 0, '2019-04-26 14:36:46'),
  ('268a1f60d716965d086e5f44a98d2c8ffd0679a79bbcb801fa95ff1872b4fa971856412b8173d192', 'a855b682c9aa80906cc0db770b39292a462e79f5059801228f6f7e9cf3ae3270b48340dac5d5523f', 0, '2019-04-04 13:44:34'),
  ('2ac8cbdeea31f8385e9f8dcbf2b31a68c59d0de7e51d2a4d50c7a39da3f05b7cc0b23ee69dd130c5', '04063bef0b18a5d0fd0b49066039145b8755ef814265eb2bd5453323ffaffd2d8f256937359d287e', 0, '2019-04-03 18:51:25'),
  ('2b66a4cfb1d7b2ef4e72e7e4a67859ccfb1d7a8125e01fcfd73b617f513a41e395eb8bcb14276f33', '97ee2446f832ae36d85e2ae2219bbb3358f955ccd9efda4e66a5e9d53f7127a5eaf892b668e92d8c', 0, '2019-04-24 12:41:02'),
  ('2dbfebcd580dff11bbbc1970686aa29e2014f0adca24076b5757a02088fdfdcc9256fda0e55a4259', '5a20630671e94a14a1ed4ccc6d18035dbdf9766ff5c438a401b942a65846730ecd56ee5c589e9a0a', 0, '2019-03-28 14:14:44'),
  ('2e2118947b351a4a643140298e2cd3fa50a0904693bf41ed2e34be57212e01311275d28b335ae806', '08fa438b6ed899c6e07659d43ca3b236e47216359593e0c61238d133482cacfa506239ab1688ebf4', 0, '2019-03-22 23:48:25'),
  ('2f06a06c2c3e7701b2ae71234c4660d8ee6eb59806087019ea2e2b1e9552fec7a699525bc09cc21f', 'f9391cbff902d6ba823976d2812fd40b73360d8307533f2fbdaf5e6195a2dfbcb7bf01e289b2b136', 0, '2019-03-22 19:45:49'),
  ('2fa8a8fbc9e74a3ac76f130f9329007047d0ba34b9405920fdf68267d55e8c31e42377c72964e610', '4056c74bb2b6e257f5f7be9ba181d89379d7c29829ff6d7ca4985373b4c68fb55cc70a19f7dc5f84', 0, '2019-03-24 00:17:50'),
  ('312ef8c778186edb7ebc78f1ec30793be4ec219212878d50ef358d013c174bf583426d328729e501', '05089722043ac2d6aca2e65cd504d6d450dae1ca857f35008475221a7bbe9c4a03285b55c08dcd45', 0, '2019-04-04 06:35:53'),
  ('31b25b4f6698b80e294781dceae03ebedc57e10d7f9eb9b4bd4d87190f208aa464c52f470239b49c', '24326d6e9e09abea554891e5f5f4a55dab6d190f3208f77da2ef1b7f00dde33988e5a31ca67678fc', 0, '2019-04-26 14:33:18'),
  ('320ba3d7a6c2f69e03c1dc1a6c6d96f1f24af5d85b5e06a72cbe97e67fca87e361d1eaa4ac1a4c20', '2b6ecddc44660120c7fb2da1fb1e7a30fe2f1dce7b6c40f28ca0240cf1e802f38300cc7fb331432e', 0, '2019-03-22 19:32:48'),
  ('332c852375706f49f300d5d58c0000c9d7a411119216e4839718a7ea9aac84f1a5991dfa36270c83', '2b3998a75743180eb54e96e50be3fdd4eefd4b9b0dd1571e87640935867d5eb3d981c9c03f648185', 0, '2019-03-27 08:31:30'),
  ('3439ab74cb0850758af1acea4642a261253ad94965b160448d883f8ebfd6ffd037262a0c4b52f804', '1020eb6d9fc49568322b4c9a016c1f2a2616ffbcc489478770aa592ee09635711cd95778f845657a', 0, '2019-04-03 00:45:41'),
  ('34a5ce1e4eb2905bbc1c0f41681555f01cc510d0f0241de047ef27f7974e5d4b8646a24ebb6e1b74', '3cd984e33fe18d43f68a2f930d3022946a424e03ffbe1b3dfcdd474256612923b77e670b30638e57', 0, '2019-04-03 12:33:58'),
  ('357b027a6370c80fe52b8decb5fb20ede184c1cd6429fcfdce79fcc8e8fd81b2ff8318a4e8f9c96a', '58fa5fdeb8d97c441452aaaf5013f586de34c8142d97d3ad49ecd440d99db0542eca42ceb0b50035', 0, '2019-04-26 15:11:48'),
  ('3a7eedc0ff952475dacfc0a3827ac4c2d5d42faba83d339016d94f683ae380d622e40fa5430966bb', 'd1bc1df28a556a74dc45a70f1c68e916c958aa24e04f81f839b829eff6d84615b91d15f0730fb56c', 0, '2019-04-26 14:28:25'),
  ('3d43afe6730b5fdc3df6d626cad0d27bef526c1295dfb7782c641daa4c8846c193509189323123b2', '53d4059c1652fa7aae3c9008617a98a73b50c4e0622532ab37b7f52f18318a9c19f05b8537eb3a4f', 0, '2019-04-26 13:11:06'),
  ('3e635b363936dffbeb27ac458581de25d80e4cca713ddca06df31ae08f96cf49514eb1e46b467a3f', 'c05870bd61cc1e26c1c3cb8a1e50b4d1d27d04aabe686c20edf1b5efe2554535cd7b6ffed18f8332', 0, '2019-04-26 15:28:00'),
  ('3fe286773f7aa7c7835a874e3e933cfbf35e4dd47e14b3cb75c021b5b0677f72e97a0c88274c50f8', 'f1aaec83529c615ceeab42854e429445538aa00f522aa850f837de5b805d52083bcc8021527cf29f', 0, '2019-04-26 14:34:09'),
  ('40ee747314b412299a13c51703b4b24277f3a163a851799613d68a84cfc586b94f7e0fbdffb7a904', 'da39f2b636df3cf1eb1c4b75ac95c2afc6571ca0bc850746f6dd576ca8bfa64a92a9b1dcd7fafd6e', 0, '2019-04-04 07:04:43'),
  ('49495c51ed06dbbe86bc9b951fb469d2fd69ad2fd33316f1b26c9de9ad44f0176bddb952cfe6bfae', '9ad129a7e2fb3d1bef15249c4fd2c890d11385015c3eb6b9826db64b23f99bd5f9c0828ad67d1a93', 0, '2019-04-26 13:10:48'),
  ('49ef8d62e262a3775f9eb7e091ef825cd3874394d64f302c326b9c905e5f244558e8ad608e421c6a', '4ec15260d28cbcdfee8a36a326fb3dc0150f182652f3a098cefa60ba44d070ae377357bbc58788b4', 0, '2019-04-10 12:59:07'),
  ('50ae6abbf22802c03542ab220a8645f11a96b79e0c903abed65248e737d2cd4a2c1c08e85e2ff07c', 'bac5f973290a747ec92fc306c7e9c3372f26b78d93e4e4448a57b125092212555a07f03eebc03eb0', 0, '2019-04-02 22:44:22'),
  ('51ba917e347e8938aede993ad0ffd5d2254d531bbf36364cd1882d0d9fc3fb6bba57eec4b640b71b', 'aa3993c7b55ad9fa927a8d2e0c0f79fbe6983ba0549d3815703a8126de2bf2e7b9a273ca0e0fccdb', 0, '2019-04-26 14:03:41'),
  ('535470ee6a465fb547ad1410c3370bd2ef20a63cb8848c043317f712df4a169e9057bd2f6425ed15', 'd4adc66867d00dff6279be183c4f81c50cedd57558f2f93ae4736f7edcc370841b0798ff6729006b', 0, '2019-03-30 10:07:06'),
  ('56a2ea3b618f64b215e8121adf2723d1ed74c4beb48c3b152e18eb4cf0698186bb1177166f920c71', '5da946024055f2dc87670f8e6933dc694e81756459eadc46aa2bb53b38eb3d5d0eb5fa9cd1cbc54d', 0, '2019-04-26 15:51:33'),
  ('59fafda21590628a5d540770d76cc067179ab76eb46315ee44d598bf9025e468f4140aadb155e9e3', '9c3630b6dc31e45901dfa74bd944209885b03bcbfb97df2aa901fab1636ac33c8def06eb6db475ab', 0, '2019-03-23 22:05:00'),
  ('5cd8d731bc78e8a64651174be5b48056daf08629f488346765f7c1ed12115860e99959d1b8dee6a1', '16229830130a70a885ba7767f109df350f61c0e0c65b7aac12b912ede6ff950a841778128181a4fc', 0, '2019-03-29 13:30:33'),
  ('5dc4fdf9adededf97c9119c2fe433015bcd1504e4a5a9a8da81ce855c76151af1e66d0c9d692fd11', '131a006382b5482aa280bddf870151ab58c17cd0bbbe72f07fdcea74fbb7da156310d9d6ea4c62c1', 0, '2019-04-25 15:54:28'),
  ('61704d6d7a68ccd315cbf134f666a6d4606a1f58070eacfb00efa476c9dfd3a58ca5709394215b59', '28c58ddad0d4a46427372522b08220542b01900617087d0b2360abb511857930d15f68b5fc5d9afc', 0, '2019-04-05 09:50:10'),
  ('61c47b0ea2cb9e708ed7557d6c8eee3f8809a5a45f1842ee18599196599b18c118424fe03ba4cb03', 'c8c0a43151b04b1fe26f034b6298c65c95eb8c5510437b115fcabb857da83701b7c9bc875ef8ad16', 0, '2019-03-22 19:09:47'),
  ('65525b38c57870e68dea3ba7b123c52b6f55e840bf23f74e685450281a8efa59605959d9247873ab', '7faa8cd9865369e13f077ce747a4b4cdb1ef685f556a65b17c1c7533aa5b4e4effe920561a4851cf', 0, '2019-04-18 16:52:40'),
  ('6799022fdab6197bbd8bb16f286154168e15eeac9fc6960b953719545bcf1fa4b969916e01b8e953', '10b38d1669ada37a93fa5e02478a004c58a41677e290a72ae2c2258965eecac9dfd82254335ec2fb', 0, '2019-04-04 18:22:56'),
  ('698921f1f5668d1dd0f92edbf7fa5628e5376d5902854cd41701bc50336ea501981675ee1fa03222', '7271b6967f6ba51dc725c1c705fcb03c251dd9efb1e9336a5334a1f0a5a59b96ba48c9e267b302cd', 0, '2019-04-26 15:16:14'),
  ('6c30c983207e530b7aa0bdc773be37b661c053cf3e66106a562453ad44b8f798088d3882faba2f06', '444ff1b7e207e6eea5a7e66511a769bd986f6ce2bcefbe4abcfa5af441f9750034315f7f00800a4e', 0, '2019-04-24 14:42:20'),
  ('6e45b2246d0ece6b1bcbcebf6e077e96bb281fc685dea1c06dc1fcdcad68511a681889e5279eb81e', '619d8e3672233dd6bd1c366c73399c955704c9b15411ec80d179f59e47a1b752ae16c233b399049f', 0, '2019-03-30 07:24:30'),
  ('757cbb35634a9f966d382db8e267e0f01c9a280cf18daa37d1b282c7127ed64a0799117b72673ff5', 'f05a6e79cc74af16805020c97c02020cd8a801970ee45d66a35467e35a71bbecf0af313ef9bf46ba', 0, '2019-03-22 19:25:58'),
  ('7626812dcac14b5ce82515a8c658b46e3f047d32d7f8297516a4b512cec825508dddd5de68347cf0', '3b226d7b9a53824a576fdc6cf1abe08f3e1081e1296071a02e50ba4aa5ce0587a39969f63cbe920f', 0, '2019-04-10 12:59:24'),
  ('7ae9bb84c9894d4e8ee0ae84b00163695fc2a69a27b38db4e952c5661a158a83f9f45b196eae5c49', 'd96bd1280c86691482b264b574f98f20813e9f6a58d7b49d96d8a149a876a426a01cf6332fa6bdcd', 0, '2019-04-26 16:14:31'),
  ('7d942066014d4d985ee6618b43d3140c45b56f82c6105bd25a5d833702db2254b3ee84afb518e6c7', '65ec3e22c46307ae98f749068ce2ba131541d3c06bb989fb24cc4338d2a4abed71063ffdd9b90fda', 0, '2019-04-26 15:21:44'),
  ('7e5dc12a987c6e0f60b42bdf6f8b181c639cbf2dee089a76c0044a9401c8f094e6823444bf6b76db', 'e9161932796ad082aa14d0bb83f7ef733474291e36015e1459362ffeb578e60b787155a299cc17a8', 0, '2019-03-23 16:28:07'),
  ('7e68a83f9fce68f6934c7641ac9928d357561142f1b576117c08be41250c2d7b58493ecf6733be71', '151300836c85be0be0f86210fd4109e4557cfd588c3a65e02453fbc762521a6bbcfd27b3b2e30ccb', 0, '2019-04-26 14:32:23'),
  ('7ef292c3ba7e63d77db1f0a6ef25fa978ffb3e2bdd6213121011102986212b44e2b85bbc23033f04', 'd67aab78fb8962b3baae7c8cc63002ddca2ec60b8ae03d0dcde00d3074b1d260e037e3e23f1eaf51', 0, '2019-03-26 11:53:39'),
  ('8e40b7bdd589c5130378e80779958785564fd555e71790d559ff0c8aec3ba74ccea9e171cc6c5ef9', 'e361f1bd6994e828b7a04a35962975d9437698bf624c998c87cf9db7ab957e704082d446fbbc1b78', 0, '2019-03-28 10:04:14'),
  ('91caf62521cced5784217530c2b5668e5a1e050d4f28472a2f6b5e6ed1d164685b04e132e93a0bcd', 'df1b589591bf46a697af93e58390f7774370eb5c0b5b2f53ac29e413f6403a7d15f1916e1daa752e', 0, '2019-03-22 19:53:50'),
  ('93debb2e43b7fce5c967fe27a7c41ea4f60f04faee7e23fde03443f6bb404d0feb11e96a043b6767', 'fa5680fbd984bfa5c9da4b596cbcb762204ffa3b88f027c0009d3603d37d730ba5b8511af72a52eb', 0, '2019-03-24 14:35:30'),
  ('964d08512fe20be076a8a92f56ee012548177ddecf2db1b926b5d696eb251d70a93d6c284cfaf95f', 'c3717fb6f2937e35910d100b0b8382ff4e22e51ed7820c84f4a1cf6ea89fe88d200ab4cf9085f677', 0, '2019-04-26 15:07:02'),
  ('97037aac018de4d4d1de235f13c6daccecec772be8c06397d4b18e1277fa7f887b5ca559a23f063d', '15d3863e58c84aba56e3a1118cbd7f2aa991b97fcb23721da9c35cac56f0aec8a8bc3590525ed79a', 0, '2019-04-03 06:48:24'),
  ('98ee6caefd7c6e39b5567f3576ce67d411932233b66cb4f3851a7125bbb97678893b16e862957457', '0006a89533a640243fec7fa7e24f128545edae6b6d1dc88fe7789aee967d8d292614dcffc62505d8', 0, '2019-04-26 14:34:26'),
  ('991d593f4f645c77d22543459e1033cc0d5372ad0bafc3e9fda5b5a3ca1514e7f47223bdc1823a1e', '964b2fe0d1572c358990e95c8afe556280f335cb29d52a384df7af352d1c2f4d9a54cd296b4e6ed9', 0, '2019-04-26 14:31:39'),
  ('9f21b57284fcea45373be73418474e22d5761e42ce2ae1a69cfa3c5a14d48cd54edee885f10adfc8', '05f742c479931a67a26aa0dc34fdca5c352381f0e0e2afb87c480f71aa27cea54e0e154625a65cc2', 0, '2019-03-26 09:40:13'),
  ('a44b09789d6559d74baf3684598800f5b15b56e7c4defa9d6ed2454d06d4c58d71a5b042b2c15899', 'ad71c7da635f6df2b727eadddd90a55bda46d55f3f13486379ac40971d7eec5ec2e6e0d26ac4a93e', 0, '2019-04-26 15:45:53'),
  ('b5c5a173232263cba6a2f6e34c3f8aec346bc12418919be0d76e8d560ff5b54c4c2aa3a46e907bff', 'b9f2931649c0d5599719cec8ec749a942353e3ef062f55aa7e4844a16a999ec3e9e895511f12d1d3', 0, '2019-03-30 09:26:21'),
  ('bc2ddf3089609f737f6281fb1ea1411397e58d900623aa5bf83a4647f9483a6be95b0918d3dd3e73', 'dc3753ed35f1e331ee9f3c3c818df6dac7d03ae07f3ebc301fb266b0f24376f400a110042597759e', 0, '2019-04-26 13:51:18'),
  ('bc5ebbc70683007855c1d40e1377838d6ff1a623310103529e132ae592099bdc898a21cd89662c36', 'f5c2ffc30490a4a99325bfac2b6443b2f5a7fb705efdfd51cd410724f57946b903dacc5b0e29ea5d', 0, '2019-04-26 14:25:11'),
  ('bdbc86bd6ff45bf7ebca4c3884f036c734496f635f62cf0b79c9d34601cfb8eb92110d31226a118b', '062c75caf4f840fb29d571e66cdecebfaad05c3d04d1f066ba4e526c11ab36cb50078418e8545fac', 0, '2019-04-04 10:41:42'),
  ('bf757e89d37b90d61ee90ef2d1a9e0de1274679834a22fedefc97dfe5f1372b0d88145a2c0c5dbf4', '0ba5aafd605c2eebadad947939c475d3cf403cb33d2981ef856796dd2c7808b263ca856dc04fa32e', 0, '2019-04-26 15:14:36'),
  ('c699c644cbce882145306f647a514e67aa2b5791f2260a37542411501ba886ff122c3150080a853f', 'a6d2483e4ebd090946fc72a7ae2d6420fd6be446adb9bb88915851357c0ad94bea09ed46abe1e561', 0, '2019-04-26 14:39:17'),
  ('cb95ee2dfcff35b9bb0b581e91f712f721506c2cc20fba414a3d4aad6f3ca2cb79030fc2b87dcf13', '93490ee76da60b625e8edb38ad0bddeb446c4f851ed6930c726eb0ea9dc3149c322a62b8ac577afd', 0, '2019-04-19 13:48:29'),
  ('cc26ef1528d478069a26fdc80c67f4c22186422213d7e13871e4d37a91bbbc7db32b90ccec7ce657', '018d72d955e6804f731096a5a1fdcaec611c1d392bbe288f9bef4c0d050fa8eb55552fa48722c648', 0, '2019-04-26 14:44:38'),
  ('d07dce96260171a48eb3f5997f13e8b3c80e4a79a7d857c21e0338e7d433fe9a85d83631c6bfa82a', 'c91078ca3a3d1bc54c664e43a37a39096c234d020d68837e887dd2fd3e328cd98a8f0f827c553022', 0, '2019-04-26 13:12:32'),
  ('d6329f2daa66dc1f19ea8b4f2a38f1e9d9083314f4712fe9c2af9deca848351c2dc09e29f955985f', '5d7bf07671268871184256ca5f6b3866967eeefd9f834755b659436b119cdac9393da6483c907677', 0, '2019-03-28 12:14:23'),
  ('d6c10a73681b1d1c3e7a4594dae3fdf47f1ad859d225988e958be740e8b04d07f135d1a85c1de40b', '7e0421266ff5f3b658912d1a99353d51c8b439e9c10a1980ed219e11c30a231b44b273aefc64c54b', 0, '2019-04-26 15:47:29'),
  ('ddf8a7e8c4f953714ada525236ef1faff0f0f1e390c412e047d27cf1d45fd78c75263e115e7eecda', 'd17b47cb55b8d890f310224eaf393280d7dfa01b42abb7ff537b2f1d5e37a56d661a97021b23b4c4', 0, '2019-04-04 15:55:07'),
  ('de75d66bac44e6fc5a19fa8a00874f9ae20fed82ecd180b9f306ac3c6d68bc99b61dec4d095c50e3', '8207500d094e5e3dfba14910cfb0387de0d671c3ad7b99c42e8662b73e127045f7e3739a08136304', 0, '2019-04-26 14:05:53'),
  ('e3a8c9ecd1283bb424fd5684c49834202b4935732a188cac3630bf542a7d6ab80143c50e194709f8', 'b4d94870faf05a91d637f4649f0d5c0ab142374882d462b55a113d8bdf90b8c3094a80aadd2ef394', 0, '2019-04-24 17:01:03'),
  ('e4c5fa91cf029002e76e102daa32291bf1bc141795421157ab5e6346c2f6e05e7167bfafc93f804e', 'cba27ebfd87d3ebeb78c828a0d9355d8c0dcb031218f61429e9c4118cd11e16c2acf3ef5464aa862', 0, '2019-04-26 14:26:49'),
  ('e7dcc5108e052460cbd5f8a2a499a6dd23c62f16ebe5c76060e1c40c47513403e1c4aea7b70c42ea', 'd0dcc54076b866858905adbb4cf0d34fb828821757d191dfe6599db7857bb8f8e9c9b02565811908', 0, '2019-04-26 15:25:35'),
  ('eaca3df32696207c56fc8bae674285a97794c30e558b7c9c3e7b0ec0c4ef8232ac80acda8ba8c894', 'b22214077177e631ba0ed29731d5260ec35cb2440f1da688f2d0d8043829a80a9d141991cd083780', 0, '2019-04-24 09:58:30'),
  ('ebcfa6415cb45ca057736b5cae8bed022df60b3aedac41516da6a2db6f4af14a6c7dff6bd91fd60f', '8d302703ca95250c45cd393900c3cb1125c4972d5f5f23b8c4eaad85ed018580f4d7e5a92f6c87cb', 0, '2019-04-02 12:49:08'),
  ('ef503eddedd5bb0263ac1db8eb7f248201c5738a4e15ffee76555bfbcbc146ab77c5d8f3995a7a3d', '2e409be9a65173082b2d6477cd3ce506f2e6eacebeb1afd1ea24b7002dce68d49cd6c5404b8f027b', 0, '2019-03-29 07:17:59'),
  ('f04baead44268c555d9f0333c628649858465ca1e8f21ff945161254b318f3f8f642175b8c74155f', 'f30ac1c43af69fffb4e2c18c51f1bc19025487cac440b56ac8d0310b6ef662e01e398fe74763db0f', 0, '2019-03-22 19:14:59'),
  ('f1e8ddcd864223ccf60f9ecad1d62206e15f893666cb0dcb180ac1c62d4007e0e2a64d7f22386e1f', 'cf0f401bb8402b948a266682ada9be438f1cf8f7335817230aa264b19a85b8e3bec8ac86c9e12af4', 0, '2019-04-18 11:28:30'),
  ('f8e23d1ca707c1c8e671124b7f8e94a3bba159de4977cd23e53470f8ab756bb5ab21ed45449d2afb', 'ea0d8d41660f9cf590ab60acaf6c33bb072df2b4604a2bd975a8d50deb288c610f66a784b046cf4d', 0, '2019-03-24 15:45:08'),
  ('fb412c4d4244f791596d6a703de44d7741e4e0929d4202b0a96f08babd3ac5c66afd7a5feab34350', 'eeb6fbc0528a5fe24b6700366f218e18d4a8e489e7e65d87f2a9a20917b8de620d62042c25196ab4', 0, '2019-04-26 15:27:07'),
  ('fc9cb330f3cb27d134fd3f6d93138d5d33e3a7b1b01f5d0b440a252038a147324d3e4f99de538510', '0dffdad4e73e6e068f62277719cbd7a7a8a1c35b4f004f92729474f6d7c5e8e6223dfc69d494ebe0', 0, '2019-04-24 12:41:14'),
  ('fcdd61ffbdac0d38a52da9dcc8e31f9a1e907ae513981beff15a0774caf2372d4c4309d59713a54e', '7b267256408dbcb8981d942f4a6efe42c9312a59cf37419bdd77e7251175c707187f3152b2e67c9b', 0, '2019-04-19 16:08:17'),
  ('fe9f096fc9c8401abd351b59880e05b5f270095baf00ef62abc6b346d4b2ac5457ec6a3f8f8a0ee9', '8a6820c1eb0bdec44140d83b259779be7a98aaea1a5f7edde4b1e0b6cccdee18117fddd72a82f640', 0, '2019-03-26 13:53:52'),
  ('ffb48c3ec7012a931a1c6d88a03caef3b797551fc05cbbf369414d0b82edc831df129226286c56e9', '913cf1f1b6072b4ecdbd73aad7bb1dd697ad95ad7748d688b06a45d597d2f4aee1115a4384c882b4', 0, '2019-04-26 14:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `title` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `product_name` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_description` text COLLATE utf8mb4_unicode_ci,
  `quantity` int(8) NOT NULL DEFAULT '0',
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `due_at` date DEFAULT NULL,
  `reminder_on` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `uuid`, `company_id`, `title`, `description`, `product_name`, `product_description`, `quantity`, `unit_price`, `currency`, `amount`, `due_at`, `reminder_on`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (2, 'f68b2ae8-f61c-11e7-a222-68b599e6dae8', 1, 'Hyundai Battery Sale', 'Updated description', 'Car battery', '45kW', 2, '16250.00', 'NGN', '32500.00', '2018-03-21', 0, NULL, '2018-01-22 09:17:45', '2018-03-10 15:43:11'),
  (4, '31ea082e-ff56-11e7-a4e6-68b599e6dae8', 1, 'Rug Sale #03', 'Switching from product items to inline products', 'Infinix X570', 'Devices bought at the counter', 3, '45750.00', 'NGN', '137250.00', NULL, 0, NULL, '2018-01-22 10:01:28', '2018-03-11 10:00:32'),
  (5, 'c13ed4e4-ff58-11e7-87f4-68b599e6dae8', 1, 'Rug Sale #04', 'Purchase of 1 units of our headline product. \nIt was a sale from our Computer village outlet.', NULL, NULL, 0, '0.00', 'NGN', '100.00', '2018-04-23', 1, NULL, '2018-03-23 23:23:37', '2018-03-12 09:43:52'),
  (7, '03892750-2efc-11e8-84d4-0024d75f326c', 1, 'Air Jordan\'s Sale (x3 units) -  Blue/Black', 'Dugu X, and Jacob I both bought 3 units each of the Air Jordans 2018 with a discount of NGN 50/unit', NULL, NULL, 0, '0.00', 'NGN', '86100.00', '2018-03-24', 0, NULL, '2018-03-24 00:40:56', '2018-03-24 00:40:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(18) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint(15) UNSIGNED NOT NULL,
  `product_id` bigint(15) UNSIGNED NOT NULL,
  `quantity` int(7) UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `uuid`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
  (4, 'c344fad8-ff59-11e7-8646-68b599e6dae8', 5, 1, 2, '50.00'),
  (5, '038875bc-2efc-11e8-891a-0024d75f326c', 7, 3, 3, '28700.00');

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_url` varchar(600) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `uuid`, `name`, `slug`, `logo_url`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, 'f34adcb8-37c9-11e8-af9f-3431592bce85', 'Enterprise Development Centre', 'edc', NULL, NULL, NULL, '2018-04-04 05:35:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` bigint(18) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint(15) UNSIGNED NOT NULL,
  `customer_id` bigint(15) UNSIGNED NOT NULL,
  `channel` char(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paystack',
  `amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `reference` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_code` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_description` char(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `json_payload` text COLLATE utf8mb4_unicode_ci,
  `is_successful` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `uuid`, `order_id`, `customer_id`, `channel`, `amount`, `currency`, `reference`, `response_code`, `response_description`, `json_payload`, `is_successful`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, '13e5e77f-3425-11e8-a4a9-0024d75f326c', 7, 2, 'paystack', '86100.00', 'NGN', 'm6736rp5n1', '00', 'Successful', '{\"transaction_date\":\"2018-03-30T07:12:58.000Z\",\"fee\":1391.5,\"card\":{\"last4\":\"4081\",\"exp_month\":\"01\",\"exp_year\":\"2020\",\"channel\":\"card\"}}', 1, NULL, '2018-03-30 07:30:40', '2018-03-30 07:30:40'),
  (2, '13e6a217-3425-11e8-a4a9-0024d75f326c', 4, 2, 'paystack', '137250.00', 'NGN', 'bf75r6slax', '00', 'Successful', '{\"transaction_date\":\"2018-03-30T09:13:22.000Z\",\"fee\":2000,\"card\":{\"last4\":\"4081\",\"exp_month\":\"01\",\"exp_year\":\"2020\",\"channel\":\"card\"}}', 1, NULL, '2018-03-30 14:34:44', '2018-03-30 09:14:46');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_monthly` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `price_yearly` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `uuid`, `name`, `price_monthly`, `price_yearly`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, 'c5bfeaee-3671-11e8-ad57-0024d75f326c', 'starter', '0.00', '0.00', NULL, NULL, '2018-04-02 12:31:31'),
  (2, 'c5c06d9b-3671-11e8-ad57-0024d75f326c', 'classic', '5000.00', '50000.00', NULL, NULL, '2018-04-02 12:31:31'),
  (3, 'd9e2e546-3671-11e8-ad57-0024d75f326c', 'premium', '7500.00', '75000.00', NULL, NULL, '2018-04-02 12:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(15) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `unit_price` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `inventory` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `uuid`, `company_id`, `name`, `description`, `unit_price`, `inventory`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, '1bf0b624-f549-11e7-b227-68b599e6dae8', 1, '25 inch Centre Rug', 'A small center rug for your sitting room. It has a diagonal length of 25 inches across.', '8900.00', 230, NULL, '2018-04-19 15:58:18', '2018-03-01 14:26:40'),
  (2, '2e9403ee-26c7-11e8-9379-68b599e6dae8', 1, '55 inch Flat Screen TV', 'A small centre rug for your sitting room.', '0.00', 0, NULL, '2018-03-13 14:02:35', '2018-03-13 14:02:35'),
  (3, 'a1e752d2-2c9f-11e8-9cc9-68b599e6dae8', 1, 'Air Jordans 2018 (Size 15)', 'The new Air Jordans for the Year 2018, the men\'s version.', '0.00', 0, NULL, '2018-03-21 08:05:05', '2018-03-21 00:34:36'),
  (4, '0eeb7ec4-2ca2-11e8-b689-68b599e6dae8', 1, 'Deleted in Few', 'A new product solely for the purpose of being deleted.', '0.00', 0, '2018-03-21 00:56:32', '2018-03-21 00:56:32', '2018-03-21 00:51:58'),
  (5, 'd46db284-2ca2-11e8-94da-68b599e6dae8', 1, 'Deleted in Few', 'This product will be deleted in about 5 monites.', '0.00', 0, '2018-03-21 00:57:44', '2018-03-21 00:57:44', '2018-03-21 00:57:29');

-- --------------------------------------------------------

--
-- Table structure for table `product_prices`
--

CREATE TABLE `product_prices` (
  `id` bigint(18) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint(15) UNSIGNED NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) UNSIGNED DEFAULT '0.00',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_prices`
--

INSERT INTO `product_prices` (`id`, `uuid`, `product_id`, `currency`, `unit_price`, `updated_at`, `created_at`) VALUES
  (4, '2ee17b10-26c7-11e8-909a-68b599e6dae8', 2, 'NGN', '550150.00', '2018-03-21 08:02:32', '2018-03-13 14:02:36'),
  (5, '2ef28e00-26c7-11e8-8161-68b599e6dae8', 2, 'USD', '1280.99', '2018-03-21 08:02:32', '2018-03-13 14:02:36'),
  (7, 'a20314cc-2c9f-11e8-af2f-68b599e6dae8', 3, 'NGN', '28750.00', '2018-03-27 07:44:31', '2018-03-21 00:34:36'),
  (8, '0f4d7890-2ca2-11e8-bcb4-68b599e6dae8', 4, 'NGN', '580.00', '2018-03-21 00:51:58', '2018-03-21 00:51:58'),
  (9, 'd47d730e-2ca2-11e8-a1f8-68b599e6dae8', 5, 'NGN', '4500.00', '2018-03-21 00:57:29', '2018-03-21 00:57:29'),
  (10, '114e07e2-2cdc-11e8-bb41-0024d75f326c', 1, 'EUR', '14.53', '2018-03-23 23:21:53', '2018-03-21 07:47:12'),
  (11, '11553e0e-2cdc-11e8-9488-0024d75f326c', 1, 'NGN', '7800.00', '2018-03-23 23:21:53', '2018-03-21 07:47:13'),
  (12, '1155c2d4-2cdc-11e8-8d34-0024d75f326c', 1, 'USD', '20.25', '2018-03-23 23:21:53', '2018-03-21 07:47:13'),
  (13, 'af69d158-3192-11e8-8bdf-68b599e6dae8', 3, 'EUR', '12.35', '2018-03-27 07:44:31', '2018-03-27 07:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(15) UNSIGNED NOT NULL,
  `action` char(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'add',
  `quantity` int(11) UNSIGNED NOT NULL,
  `comment` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `product_id`, `action`, `quantity`, `comment`, `updated_at`, `created_at`) VALUES
  (1, 1, 'add', 55, 'Just got delivery of some products from the supplier', '2018-04-19 15:49:41', '2018-04-19 15:49:41'),
  (2, 1, 'subtract', 10, 'Sold a few offline.', '2018-04-19 15:55:50', '2018-04-19 15:55:50'),
  (3, 1, 'add', 185, 'More pulled out from our Ikeja warehouse.', '2018-04-19 15:58:18', '2018-04-19 15:58:18');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code` char(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `uuid`, `country_id`, `name`, `iso_code`, `deleted_at`, `updated_at`, `created_at`) VALUES
  (1, '8d07f37e-e29a-11e7-8285-0024d75f326c', 1, 'Abia', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (2, '8d08d37e-e29a-11e7-8285-0024d75f326c', 1, 'Abuja', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (3, '8d0b9140-e29a-11e7-8285-0024d75f326c', 1, 'Adamawa', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (4, '8d0b9422-e29a-11e7-8285-0024d75f326c', 1, 'Akwa ibom', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (5, '8d0b9568-e29a-11e7-8285-0024d75f326c', 1, 'Anambra', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (6, '8d0b967b-e29a-11e7-8285-0024d75f326c', 1, 'Bauchi', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (7, '8d0b9758-e29a-11e7-8285-0024d75f326c', 1, 'Bayelsa', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (8, '8d194404-e29a-11e7-8285-0024d75f326c', 1, 'Benue', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (9, '8d1946b9-e29a-11e7-8285-0024d75f326c', 1, 'Borno', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (10, '8d19488a-e29a-11e7-8285-0024d75f326c', 1, 'Cross river', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (11, '8d194a73-e29a-11e7-8285-0024d75f326c', 1, 'Delta', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (12, '8d194cff-e29a-11e7-8285-0024d75f326c', 1, 'Ebonyi', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (13, '8d194f3c-e29a-11e7-8285-0024d75f326c', 1, 'Edo', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (14, '8d195166-e29a-11e7-8285-0024d75f326c', 1, 'Ekiti', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (15, '8d19537c-e29a-11e7-8285-0024d75f326c', 1, 'Enugu', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (16, '8d19559c-e29a-11e7-8285-0024d75f326c', 1, 'Imo', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (17, '8d1957f1-e29a-11e7-8285-0024d75f326c', 1, 'Jigawa', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (18, '8d195a25-e29a-11e7-8285-0024d75f326c', 1, 'Kaduna', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (19, '8d195c44-e29a-11e7-8285-0024d75f326c', 1, 'Kano', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (20, '8d195e64-e29a-11e7-8285-0024d75f326c', 1, 'Katsina', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (21, '8d19609b-e29a-11e7-8285-0024d75f326c', 1, 'Kebbi', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (22, '8d1962b6-e29a-11e7-8285-0024d75f326c', 1, 'Kogi', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (23, '8d1964db-e29a-11e7-8285-0024d75f326c', 1, 'Kwara', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (24, '8d1966fd-e29a-11e7-8285-0024d75f326c', 1, 'Lagos', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (25, '8d196934-e29a-11e7-8285-0024d75f326c', 1, 'Nassarawa', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (26, '8d196b53-e29a-11e7-8285-0024d75f326c', 1, 'Niger', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (27, '8d196d78-e29a-11e7-8285-0024d75f326c', 1, 'Ogun', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (28, '8d196f8f-e29a-11e7-8285-0024d75f326c', 1, 'Ondo', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (29, '8d1971bf-e29a-11e7-8285-0024d75f326c', 1, 'Osun', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (30, '8d1976b0-e29a-11e7-8285-0024d75f326c', 1, 'Oyo', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (31, '8d1978f8-e29a-11e7-8285-0024d75f326c', 1, 'Plateau', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (32, '8d197b1f-e29a-11e7-8285-0024d75f326c', 1, 'Rivers', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (33, '8d197d71-e29a-11e7-8285-0024d75f326c', 1, 'Sokoto', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (34, '8d197f9f-e29a-11e7-8285-0024d75f326c', 1, 'Taraba', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (35, '8d1981c9-e29a-11e7-8285-0024d75f326c', 1, 'Yobe', NULL, NULL, NULL, '2017-12-16 19:51:47'),
  (36, '8d1983f1-e29a-11e7-8285-0024d75f326c', 1, 'Zamfara', NULL, NULL, NULL, '2017-12-16 19:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `uuid` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `name` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `uuid`, `company_id`, `name`, `description`, `updated_at`, `created_at`) VALUES
  (2, '8499a6c8-291e-11e8-abc5-0024d75f326c', 1, 'Dorcas Marketing', 'This team hold all members of the marketing team for Dorcas, and all its associated products', '2018-03-16 14:22:57', '2018-03-16 13:32:48'),
  (3, 'ef4da5f0-2c11-11e8-86d0-68b599e6dae8', 1, 'SMEToolkit', NULL, '2018-03-20 07:40:17', '2018-03-20 07:40:17'),
  (4, 'f5128fd2-2c11-11e8-9751-68b599e6dae8', 1, 'EDC Learn', 'Learn portal by Enterprise Development Centre', '2018-03-20 07:51:14', '2018-03-20 07:40:27'),
  (5, '1ab71c4e-2c12-11e8-a710-68b599e6dae8', 1, 'General', 'A general group', '2018-03-20 07:49:20', '2018-03-20 07:41:30'),
  (6, '40e12c06-2c13-11e8-b814-68b599e6dae8', 1, 'iAfford Dev', NULL, '2018-03-20 07:49:43', '2018-03-20 07:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `uuid` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `firstname` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` char(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('female','male') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` char(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_partner` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_professional` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `partner_id` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `company_id`, `firstname`, `lastname`, `email`, `password`, `remember_token`, `gender`, `phone`, `photo_url`, `is_verified`, `is_partner`, `is_professional`, `partner_id`, `deleted_at`, `created_at`, `updated_at`) VALUES
  (1, '60d22bb2-f330-11e7-81e6-68b599e6dae8', 1, 'Ibukunoluwa', 'Okeke', 'emmanix2002@gmail.com', '$2y$10$nEPxRHUeCZgENkVCtgJiPO/o9BpqJ/ZN1BvZRgCGkHluWiSSjLbqK', 'jegvOJaSF2uR57MgdcziRP2Ov4uRHPaOfg7uzKfZRuxghy30iBK9KVNjcGCP', 'male', '08136680801', NULL, 0, 0, 0, 0, NULL, '2018-01-06 22:24:36', '2018-04-26 15:02:58'),
  (4, '1f78148a-f473-11e7-ae8d-68b599e6dae8', 4, 'Bolaji', 'Olawoye', 'emmanix20.0.2@gmail.com', '$2y$10$nEPxRHUeCZgENkVCtgJiPO/o9BpqJ/ZN1BvZRgCGkHluWiSSjLbqK', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, '2018-01-08 12:54:54', '2018-01-08 12:54:54'),
  (5, '89352520-0107-11e8-8102-68b599e6dae8', 5, 'Jamiu', 'Salau', 'emm.anix2002@gmail.com', '$2y$10$jwhRuVpFzpr8b1AmFTFk6OyRL/wOa6nOBPde0wjciPSXNwZ.0Mi0C', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, '2018-01-24 13:07:31', '2018-01-24 13:07:31'),
  (6, '41adc1c4-0109-11e8-a487-68b599e6dae8', 6, 'Jamiu', 'Salau', 'emm.ani.x2002@gmail.com', '$2y$10$6iHHrjHBzHnAbZ/SmHdJhuNvbgPS2N8l4AkjL8BPZqN5hZP.Wlwu2', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, '2018-01-24 13:19:50', '2018-01-24 13:19:50'),
  (21, 'e392e0a6-269c-11e8-a848-68b599e6dae8', 21, 'Sola', 'Akindolu', 'emma.nix.2002@gmail.com', '$2y$10$.5Q54.5GLaKGSJZ3vcbpDO0UFGHmDZk9IxxH7Sac8rOQ4rwZzyggC', '0XYg4CVtlmkQkkv2lv7eGMkZxDO5yMg02Yebqw5i9rpT77azDVoncmupE4ud', NULL, '08136680801', NULL, 0, 0, 0, 0, NULL, '2018-03-13 08:59:50', '2018-03-13 09:12:08'),
  (23, '517acedc-2dee-11e8-83b6-0024d75f326c', 24, 'Sola', 'Sobowale', 'e.m.m.a.n.i.x.2002@gmail.com', '$2y$10$BKZkY4tLd86c.cL7JezidOv02VossWfHbhdaAC3AfC8/.sForhmBi', 'HpukxXffdqoemNawH8VB4DFQIDA6bmvLMcfPuT55nFv2fIp4cNGTWQ6VPHfg', NULL, '08123320811', NULL, 0, 0, 0, NULL, NULL, '2018-03-22 16:30:22', '2018-03-23 07:36:00'),
  (24, 'e78a3474-2f71-11e8-ad48-0024d75f326c', 25, 'Sola', 'Akindolu', 'emmanix2.0.0.2@gmail.com', '$2y$10$CStTJD105Xg18auH8uGbB./4nhGhSFzNd8u0rgoJJiPDQs2zzwZPO', '9DisqkrZmFrvqfovNUKxcnrXnAdfijsi32AzQGUWvfaVgr4BUfWGUIA2Wy3Z', NULL, '08136680801', NULL, 0, 0, 0, NULL, NULL, '2018-03-24 14:44:49', '2018-03-24 14:50:27'),
  (26, '11352202-37ce-11e8-84c8-68b599e6dae8', 27, 'Femi', 'Ogunleye', 'e.m.m.anix.200.2@gmail.com', '$2y$10$WEDvMEVFM3GUFXS14jgsnenT7PIXnzB9Bqedjuqi2/7eTw18ngbhG', NULL, NULL, '09087654567', NULL, 0, 0, 0, 1, NULL, '2018-04-04 06:04:42', '2018-04-04 06:04:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill_payments`
--
ALTER TABLE `bill_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_payments_index` (`company_id`,`plan_id`),
  ADD KEY `transaction_search_index` (`reference`,`processor`,`currency`,`amount`,`is_successful`),
  ADD KEY `bills_plan_foreign_key` (`plan_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `access_expires_at` (`access_expires_at`),
  ADD KEY `plan_id` (`plan_id`,`plan_type`,`name`,`phone`,`email`) USING BTREE;

--
-- Indexes for table `company_service`
--
ALTER TABLE `company_service`
  ADD PRIMARY KEY (`company_id`,`service_id`);

--
-- Indexes for table `contact_fields`
--
ALTER TABLE `contact_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact_field_uuid` (`uuid`),
  ADD KEY `company_contact_fields_index` (`company_id`,`name`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `country_uuid` (`uuid`),
  ADD UNIQUE KEY `country_iso_code` (`iso_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_uuid` (`uuid`),
  ADD KEY `company_customers_index` (`company_id`,`firstname`,`lastname`,`phone`,`email`) USING BTREE;

--
-- Indexes for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD PRIMARY KEY (`contact_field_id`,`customer_id`),
  ADD KEY `cuscon_customer_foreign_key` (`customer_id`);

--
-- Indexes for table `customer_group`
--
ALTER TABLE `customer_group`
  ADD PRIMARY KEY (`customer_id`,`group_id`),
  ADD KEY `cgrp_group_foreign_key` (`group_id`);

--
-- Indexes for table `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `note_uuid` (`uuid`),
  ADD KEY `customer_notes_index` (`customer_id`);

--
-- Indexes for table `customer_order`
--
ALTER TABLE `customer_order`
  ADD PRIMARY KEY (`customer_id`,`order_id`),
  ADD KEY `co_order_foreign_key` (`order_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departmenr_uuid` (`uuid`),
  ADD KEY `company_departments_index` (`company_id`,`name`);

--
-- Indexes for table `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_uuid` (`uuid`),
  ADD KEY `domain_owner_index` (`domainable_type`,`domainable_id`),
  ADD KEY `domain_search_index` (`domain`,`created_at`);

--
-- Indexes for table `domain_issuances`
--
ALTER TABLE `domain_issuances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `issuance_uuid` (`uuid`),
  ADD KEY `domain_issuance_index` (`domain_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_uuid` (`uuid`),
  ADD KEY `employee_location_foreign_key` (`location_id`),
  ADD KEY `employee_department_foreign_key` (`department_id`),
  ADD KEY `company_employees_index` (`company_id`,`department_id`,`location_id`) USING BTREE,
  ADD KEY `email` (`firstname`,`lastname`,`email`,`phone`) USING BTREE;

--
-- Indexes for table `employee_team`
--
ALTER TABLE `employee_team`
  ADD PRIMARY KEY (`employee_id`,`team_id`),
  ADD KEY `et_team_foreign_key` (`team_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `list_uuid` (`uuid`),
  ADD KEY `company_lists_index` (`company_id`,`name`) USING BTREE;

--
-- Indexes for table `integrations`
--
ALTER TABLE `integrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `integration_uuid` (`uuid`),
  ADD KEY `company_integrations_index` (`company_id`,`type`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_uuid` (`uuid`),
  ADD KEY `company_locations_index` (`company_id`),
  ADD KEY `state_locations_index` (`state_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_uuid` (`uuid`),
  ADD KEY `reminder_on` (`reminder_on`),
  ADD KEY `company_orders_index` (`company_id`,`title`,`product_name`,`currency`,`amount`,`due_at`) USING BTREE;

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_item_uuid` (`uuid`),
  ADD KEY `order_items_index` (`order_id`,`product_id`) USING BTREE,
  ADD KEY `order_items_product_foreign_key` (`product_id`);

--
-- Indexes for table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `partner_uuid` (`uuid`),
  ADD UNIQUE KEY `partner_slug` (`slug`),
  ADD KEY `parner_search_index` (`name`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_transaction_uuid` (`uuid`),
  ADD KEY `transaction_ref_index` (`reference`),
  ADD KEY `order_transactions` (`order_id`,`customer_id`,`is_successful`),
  ADD KEY `channel` (`channel`),
  ADD KEY `txn_customer_foreign_key` (`customer_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_uuid` (`uuid`),
  ADD KEY `plan_search_index` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_uuid` (`uuid`),
  ADD KEY `company_products_index` (`company_id`,`name`,`inventory`) USING BTREE;

--
-- Indexes for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `price_uuid` (`uuid`),
  ADD KEY `product_prices_index` (`product_id`,`currency`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_stock_index` (`product_id`,`action`,`quantity`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `servide_uuid` (`uuid`),
  ADD UNIQUE KEY `service_name` (`name`) USING BTREE;

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `state_uuid` (`uuid`),
  ADD KEY `country_states_index` (`country_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_uuid` (`uuid`),
  ADD KEY `company_teams_index` (`company_id`,`name`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `user_uuid` (`uuid`),
  ADD KEY `company_users_index` (`company_id`,`is_partner`,`is_professional`,`partner_id`) USING BTREE,
  ADD KEY `users_partner_foreign_key` (`partner_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill_payments`
--
ALTER TABLE `bill_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `contact_fields`
--
ALTER TABLE `contact_fields`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `domains`
--
ALTER TABLE `domains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `domain_issuances`
--
ALTER TABLE `domain_issuances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `integrations`
--
ALTER TABLE `integrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(15) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_prices`
--
ALTER TABLE `product_prices`
  MODIFY `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bill_payments`
--
ALTER TABLE `bill_payments`
  ADD CONSTRAINT `bills_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_plan_foreign_key` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `company_plan_foreign_key` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `contact_fields`
--
ALTER TABLE `contact_fields`
  ADD CONSTRAINT `cf_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customer_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_contacts`
--
ALTER TABLE `customer_contacts`
  ADD CONSTRAINT `cuscon_contact_field_foreign_key` FOREIGN KEY (`contact_field_id`) REFERENCES `contact_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cuscon_customer_foreign_key` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_group`
--
ALTER TABLE `customer_group`
  ADD CONSTRAINT `cgrp_customer_foreign_key` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cgrp_group_foreign_key` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD CONSTRAINT `cnotes_customer_foreign_key` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer_order`
--
ALTER TABLE `customer_order`
  ADD CONSTRAINT `co_customer_foreign_key` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `co_order_foreign_key` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `department_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `domain_issuances`
--
ALTER TABLE `domain_issuances`
  ADD CONSTRAINT `issuance_domain_foreign_key` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employee_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_department_foreign_key` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_location_foreign_key` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_team`
--
ALTER TABLE `employee_team`
  ADD CONSTRAINT `et_employee_foreign_key` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `et_team_foreign_key` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `group_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `integrations`
--
ALTER TABLE `integrations`
  ADD CONSTRAINT `integrations_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `location_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `location_state_foreign_key` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `order_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_foreign_key` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_product_foreign_key` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `txn_customer_foreign_key` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `txn_order_foreign_key` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `product_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD CONSTRAINT `pprices_product_foreign_key` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD CONSTRAINT `product_stock_product_foreign_key` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `state_country_foreign_key` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `team_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_foreign_key` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_partner_foreign_key` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;