-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 09, 2026 at 10:08 AM
-- Server version: 8.0.42-0ubuntu0.20.04.1
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sntc_rice_app_staging`
--
CREATE DATABASE IF NOT EXISTS `sntc_rice_app_staging` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `sntc_rice_app_staging`;

-- --------------------------------------------------------

--
-- Table structure for table `bag_vendor`
--

CREATE TABLE `bag_vendor` (
  `id` int NOT NULL,
  `vendor_name` varchar(256) DEFAULT NULL,
  `email` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `vendor_address` varchar(256) DEFAULT NULL,
  `contact_person` varchar(256) DEFAULT NULL,
  `contact_number` varchar(256) DEFAULT NULL,
  `specialised` varchar(256) DEFAULT NULL,
  `vendor_type` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `bid` (
  `id` int NOT NULL,
  `query_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `bid_amount` varchar(256) NOT NULL,
  `counter_amount` varchar(256) NOT NULL DEFAULT '0',
  `validTill` varchar(256) NOT NULL,
  `counter_status` int NOT NULL DEFAULT '0',
  `accept_status` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `brands_milestone3` (
  `id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `image` varchar(256) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `orders` int NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `brand_attachment_milestone3` (
  `id` int NOT NULL,
  `brand_id` int NOT NULL,
  `attachment` text NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `brand_availability` (
  `id` int NOT NULL,
  `brand_id` int NOT NULL,
  `state_id` int NOT NULL,
  `city_id` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `brand_interest` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `brand_id` int NOT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_number` varchar(20) NOT NULL,
  `basmati_monthly` varchar(100) DEFAULT NULL,
  `non_basmati_monthly` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `brand_interest_map` (
  `id` int NOT NULL,
  `brand_interest_id` int NOT NULL,
  `already_working_with_brand_name` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `buyers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `buyer_packing_INR` (
  `id` int NOT NULL,
  `packing` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `status` int DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `buy_query` (
  `id` int NOT NULL,
  `PackingType` varchar(256) NOT NULL,
  `mobile` varchar(256) NOT NULL,
  `partyName` varchar(256) NOT NULL,
  `portName` varchar(256) NOT NULL,
  `qualityName` varchar(256) NOT NULL,
  `quantity` varchar(256) NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `qualityType` varchar(256) NOT NULL,
  `validDays` int NOT NULL,
  `validDate` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `grade` int DEFAULT NULL,
  `farming` int DEFAULT NULL,
  `user` int NOT NULL,
  `length` varchar(256) DEFAULT NULL,
  `purity` varchar(256) DEFAULT NULL,
  `moisture` varchar(256) DEFAULT NULL,
  `broken` varchar(256) DEFAULT NULL,
  `kett` varchar(256) DEFAULT NULL,
  `dd` varchar(256) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `buy_query_milestone3` (
  `id` int NOT NULL,
  `quality_type` int NOT NULL COMMENT 'basmati , non basmati',
  `quality` int NOT NULL,
  `quality_form` int NOT NULL,
  `grade` int NOT NULL,
  `packing_type` int DEFAULT '0',
  `packing` int NOT NULL,
  `quantity` varchar(256) NOT NULL,
  `additional_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `remarks` text,
  `farming` varchar(256) DEFAULT NULL,
  `contactPerson` varchar(256) DEFAULT NULL,
  `contactMobile` varchar(256) DEFAULT NULL,
  `type` varchar(256) DEFAULT 'app',
  `status` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `category` (
  `id` int NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `order` int DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `category_role_map` (
  `id` int NOT NULL,
  `role` int DEFAULT NULL,
  `category` int DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `chartinterval` (
  `id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `chatStatus` (
  `id` int NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cities` (
  `id` bigint UNSIGNED NOT NULL,
  `city_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `city_zones` (
  `id` bigint UNSIGNED NOT NULL,
  `zone_area` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_us` (
  `id` int NOT NULL,
  `phone` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `status` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `cooking_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `sntc_no` bigint UNSIGNED NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `coupon` (
  `id` int NOT NULL,
  `coupon_name` varchar(256) NOT NULL,
  `coupon_feature` varchar(256) NOT NULL,
  `coupon_description` text NOT NULL,
  `coupon_percentage` varchar(256) NOT NULL,
  `coupon_expiry` varchar(256) NOT NULL,
  `maxDiscount` varchar(256) NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `couriers` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `samples` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_via` bigint UNSIGNED NOT NULL,
  `details` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `deals` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `sntc_no` bigint UNSIGNED DEFAULT NULL,
  `contract_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller` bigint UNSIGNED NOT NULL,
  `buyer` bigint UNSIGNED NOT NULL,
  `quality` bigint UNSIGNED NOT NULL,
  `is_direct_deal` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `deal_lab_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `sntc_no` bigint UNSIGNED NOT NULL,
  `length` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad_mixture` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_ad_mixture` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moisture` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kett` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `broken` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dd` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chalky` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `brown_layer` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `inmature` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `broken_pin` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cooking` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `default_value` (
  `id` int NOT NULL,
  `localcharges` varchar(256) NOT NULL,
  `financecost` varchar(256) NOT NULL,
  `dollarvalue` varchar(256) NOT NULL,
  `bagcost` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `designations` (
  `id` bigint UNSIGNED NOT NULL,
  `designation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `orders` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `documents` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `contract_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `truck_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_copy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_copy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bilty_copy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kanta_parchi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_days` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `domestictransport` (
  `id` int NOT NULL,
  `from` varchar(256) NOT NULL,
  `to` varchar(256) NOT NULL,
  `upto` varchar(256) NOT NULL,
  `pmt` varchar(256) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `field_runners` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `zone` bigint UNSIGNED NOT NULL,
  `designation` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `freeTrialMonths` (
  `id` int NOT NULL,
  `month` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `future_buy_query_milestone3` (
  `id` int NOT NULL,
  `quality_type` int NOT NULL COMMENT 'basmati , non basmati',
  `quality` int NOT NULL,
  `quality_form` int NOT NULL,
  `year` varchar(255) DEFAULT NULL,
  `grade` int NOT NULL,
  `packing_type` int DEFAULT '0',
  `packing` int NOT NULL,
  `quantity` varchar(256) NOT NULL,
  `additional_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `expectedPackingSchedule` varchar(256) DEFAULT NULL,
  `bagStatus` varchar(256) DEFAULT NULL,
  `expectedBagDelivery` varchar(256) DEFAULT NULL,
  `remarks` text,
  `farming` varchar(256) DEFAULT NULL,
  `contactPerson` varchar(256) DEFAULT NULL,
  `contactMobile` varchar(256) DEFAULT NULL,
  `type` varchar(256) DEFAULT 'app',
  `status` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `future_sell_query_milestone3` (
  `id` int NOT NULL,
  `quality_type` int NOT NULL COMMENT 'basmati , non basmati',
  `quality` int NOT NULL,
  `quality_form` int NOT NULL,
  `year` varchar(255) DEFAULT NULL,
  `grade` int NOT NULL,
  `packing_type` int DEFAULT '0',
  `packing` int NOT NULL,
  `quantity` varchar(256) NOT NULL,
  `additional_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `remarks` text,
  `farming` varchar(256) DEFAULT NULL,
  `contactPerson` varchar(256) DEFAULT NULL,
  `contactMobile` varchar(256) DEFAULT NULL,
  `extra_file` varchar(255) DEFAULT NULL,
  `type` varchar(256) DEFAULT 'app',
  `status` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `gallery` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment2` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `grade` (
  `id` int NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `hotdealaccept` (
  `id` int NOT NULL,
  `hotdeal_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `hotdeals` (
  `id` int NOT NULL,
  `title` varchar(256) NOT NULL,
  `quality` varchar(256) NOT NULL,
  `fob` varchar(256) NOT NULL,
  `qty` varchar(256) NOT NULL,
  `packing` varchar(256) NOT NULL,
  `message` text NOT NULL,
  `validDate` varchar(256) NOT NULL,
  `status` int NOT NULL DEFAULT '1' COMMENT '{0 : taken, 1: active(default) ,2: sold  }',
  `attachment1` varchar(256) DEFAULT NULL,
  `attachment2` varchar(256) DEFAULT NULL,
  `length` varchar(256) DEFAULT NULL,
  `purity` varchar(256) DEFAULT NULL,
  `moisture` varchar(256) DEFAULT NULL,
  `broken` varchar(256) DEFAULT NULL,
  `kett` varchar(256) DEFAULT NULL,
  `dd` varchar(256) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `live_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `tradeFor` int NOT NULL DEFAULT '1',
  `farmingType` int NOT NULL DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cropGrade` int NOT NULL,
  `cropYear` int NOT NULL,
  `min_price` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_price` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `up_down` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `opening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `closing` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthStart` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthEnd` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `is_updated_by_admin` int NOT NULL DEFAULT '0',
  `state_order` int DEFAULT NULL,
  `name_order` int DEFAULT NULL,
  `form_order` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `import_port` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` bigint UNSIGNED DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` bigint UNSIGNED DEFAULT NULL,
  `usd_role` int NOT NULL DEFAULT '0',
  `bagCategory` int DEFAULT '0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_on` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_usd_active` int NOT NULL DEFAULT '0',
  `is_INR_active` int NOT NULL DEFAULT '0',
  `is_active_by_admin` int NOT NULL DEFAULT '0',
  `otp` int DEFAULT NULL,
  `has_validation` text COLLATE utf8mb4_unicode_ci,
  `status` int NOT NULL DEFAULT '1',
  `is_viewed_by_admin` int NOT NULL DEFAULT '0',
  `api_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `transaction_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `planId` int DEFAULT '0',
  `userType` int NOT NULL DEFAULT '1',
  `user_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'app',
  `stripe_customer_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_payment_method` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendorcategory` (
  `id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




CREATE TABLE `vendor_user_map` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(256) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(256) NOT NULL,
  `remarks` text,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `version` (
  `id` int NOT NULL,
  `version` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `wand` (
  `id` int NOT NULL,
  `RiceNameId` int NOT NULL,
  `wandTypeId` int NOT NULL,
  `value` varchar(256) NOT NULL,
  `order` int DEFAULT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `wandType` (
  `id` int NOT NULL,
  `type` varchar(256) NOT NULL,
  `order` int NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_brands` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_year` year DEFAULT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci,
  `product_mode` longtext COLLATE utf8mb4_unicode_ci,
  `logo` longtext COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `web_brand_variant` (
  `id` bigint UNSIGNED NOT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `quality_id` bigint UNSIGNED NOT NULL,
  `form_id` bigint UNSIGNED NOT NULL,
  `grade` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `packing` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cut_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `web_business_details` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `company_name` varchar(256) DEFAULT NULL,
  `product` text,
  `contactPerson` varchar(255) DEFAULT NULL,
  `contactMobile` varchar(256) DEFAULT NULL,
  `designation` varchar(256) DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `registered_email` varchar(256) DEFAULT NULL,
  `phone` varchar(256) DEFAULT NULL,
  `selected_category` varchar(256) DEFAULT NULL,
  `locality` varchar(256) DEFAULT NULL,
  `landmark` varchar(256) DEFAULT NULL,
  `state` varchar(256) DEFAULT NULL,
  `city` varchar(256) DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_cities` (
  `id` int NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `state_id` int NOT NULL,
  `is_capital` tinyint(1) DEFAULT '0',
  `population` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




CREATE TABLE `web_news_runner` (
  `id` int NOT NULL,
  `title` text NOT NULL,
  `type` varchar(20) NOT NULL,
  `newsType` varchar(255) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_other_service_provider` (
  `id` int UNSIGNED NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `web_personal_details` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `firstname` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `lastname` varchar(256) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `phone_number` varchar(256) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `district` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `farmer_unique_id` varchar(256) DEFAULT NULL,
  `pan_card` varchar(256) DEFAULT NULL,
  `avatar` varchar(256) DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




CREATE TABLE `web_plan` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` longtext,
  `description` longtext,
  `is_INR` int DEFAULT '0',
  `is_USD` int DEFAULT '0',
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_plan_keys` (
  `id` int NOT NULL,
  `key` varchar(255) NOT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_plan_keys_map` (
  `id` int NOT NULL,
  `plan_id` int NOT NULL,
  `key_id` int NOT NULL,
  `value` int DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_states` (
  `id` int NOT NULL,
  `state_code` char(3) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `order_no` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `web_user_attachment` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `farmer_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `panCard` varchar(256) DEFAULT NULL,
  `gstCard` varchar(256) DEFAULT NULL,
  `fssaiCard` varchar(256) DEFAULT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_user_subscription` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `order_id` varchar(256) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `subscription_type` varchar(255) NOT NULL,
  `status` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `web_vendorcategory` (
  `id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


--
-- Indexes for table `bag_vendor`
--
ALTER TABLE `bag_vendor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bid`
--
ALTER TABLE `bid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands_milestone3`
--
ALTER TABLE `brands_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brand_attachment_milestone3`
--
ALTER TABLE `brand_attachment_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brand_availability`
--
ALTER TABLE `brand_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brand_interest`
--
ALTER TABLE `brand_interest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brand_interest_map`
--
ALTER TABLE `brand_interest_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyers_user_id_index` (`user_id`);

--
-- Indexes for table `buyer_packing_INR`
--
ALTER TABLE `buyer_packing_INR`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buy_query`
--
ALTER TABLE `buy_query`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buy_query_milestone3`
--
ALTER TABLE `buy_query_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_role_map`
--
ALTER TABLE `category_role_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chartinterval`
--
ALTER TABLE `chartinterval`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chatStatus`
--
ALTER TABLE `chatStatus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cities_city_name_unique` (`city_name`);

--
-- Indexes for table `city_zones`
--
ALTER TABLE `city_zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_zones_city_index` (`city`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cooking_reports`
--
ALTER TABLE `cooking_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cooking_reports_sntc_no_index` (`sntc_no`);

--
-- Indexes for table `coupon`
--
ALTER TABLE `coupon`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deals_sntc_no_index` (`sntc_no`),
  ADD KEY `deals_seller_index` (`seller`),
  ADD KEY `deals_buyer_index` (`buyer`),
  ADD KEY `deals_quality_index` (`quality`),
  ADD KEY `deals_user_id_index` (`user_id`);

--
-- Indexes for table `deal_lab_reports`
--
ALTER TABLE `deal_lab_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_lab_reports_sntc_no_index` (`sntc_no`);

--
-- Indexes for table `default_value`
--
ALTER TABLE `default_value`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_user_id_index` (`user_id`);

--
-- Indexes for table `domestictransport`
--
ALTER TABLE `domestictransport`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `field_runners`
--
ALTER TABLE `field_runners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_runners_user_id_index` (`user_id`),
  ADD KEY `field_runners_zone_index` (`zone`),
  ADD KEY `designation` (`designation`);

--
-- Indexes for table `freeTrialMonths`
--
ALTER TABLE `freeTrialMonths`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `future_buy_query_milestone3`
--
ALTER TABLE `future_buy_query_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `future_sell_query_milestone3`
--
ALTER TABLE `future_sell_query_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotdealaccept`
--
ALTER TABLE `hotdealaccept`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotdeals`
--
ALTER TABLE `hotdeals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_prices`
--
ALTER TABLE `live_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `live_prices_name_index` (`name`),
  ADD KEY `live_prices_form_index` (`form`);

--
-- Indexes for table `live_price_closing`
--
ALTER TABLE `live_price_closing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_price_current_status`
--
ALTER TABLE `live_price_current_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loading_reports`
--
ALTER TABLE `loading_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loading_reports_sntc_no_index` (`sntc_no`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from` (`from`),
  ADD KEY `to` (`to`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mill_statuses`
--
ALTER TABLE `mill_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mill_statuses_seller_index` (`seller`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `negotiation_bid`
--
ALTER TABLE `negotiation_bid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_runner`
--
ALTER TABLE `news_runner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ocean_freight`
--
ALTER TABLE `ocean_freight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offers_sntc_no_index` (`sntc_no`),
  ADD KEY `offers_user_id_index` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transection_id` (`transaction_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `packings`
--
ALTER TABLE `packings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packing_types`
--
ALTER TABLE `packing_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paddyMandi`
--
ALTER TABLE `paddyMandi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paddyStates`
--
ALTER TABLE `paddyStates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paddy_prices`
--
ALTER TABLE `paddy_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paidEmails`
--
ALTER TABLE `paidEmails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`(191));

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_reminders_buyer_index` (`buyer`),
  ADD KEY `payment_reminders_seller_index` (`seller`),
  ADD KEY `payment_reminders_user_id_index` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permissions_role_id_index` (`role_id`),
  ADD KEY `permissions_module_id_index` (`module_id`),
  ADD KEY `permissions_designation_index` (`designation`);

--
-- Indexes for table `plan`
--
ALTER TABLE `plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ports`
--
ALTER TABLE `ports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `port_images`
--
ALTER TABLE `port_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `public_packing_milestone3`
--
ALTER TABLE `public_packing_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qualities`
--
ALTER TABLE `qualities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quality_master`
--
ALTER TABLE `quality_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_brand_form`
--
ALTER TABLE `rice_brand_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_cooking`
--
ALTER TABLE `rice_cooking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_forms`
--
ALTER TABLE `rice_forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_form_milestone3`
--
ALTER TABLE `rice_form_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_names`
--
ALTER TABLE `rice_names`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rice_types`
--
ALTER TABLE `rice_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`id`),
  ADD KEY `samples_supplier_index` (`supplier`),
  ADD KEY `samples_quality_index` (`quality`),
  ADD KEY `samples_packing_index` (`packing`),
  ADD KEY `samples_packing_type_index` (`packing_type`),
  ADD KEY `courier_id` (`courier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sample_lab_reports`
--
ALTER TABLE `sample_lab_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sample_lab_reports_sntc_no_index` (`sntc_no`);

--
-- Indexes for table `sample_outwards`
--
ALTER TABLE `sample_outwards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sample_outwards_buyer_index` (`buyer`),
  ADD KEY `sample_outwards_quality_index` (`quality`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sample_registers`
--
ALTER TABLE `sample_registers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sample_registers_supplier_index` (`supplier`),
  ADD KEY `sample_registers_quality_index` (`quality`),
  ADD KEY `sample_registers_packing_index` (`packing`),
  ADD KEY `sample_registers_packing_type_index` (`packing_type`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sellerPackingINR`
--
ALTER TABLE `sellerPackingINR`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sellers_user_id_index` (`user_id`);

--
-- Indexes for table `sell_query_milestone3`
--
ALTER TABLE `sell_query_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_provider_user_map`
--
ALTER TABLE `service_provider_user_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `states_country_id_index` (`country_id`);

--
-- Indexes for table `sub_plan`
--
ALTER TABLE `sub_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonial`
--
ALTER TABLE `testimonial`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonial_video`
--
ALTER TABLE `testimonial_video`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_current_status`
--
ALTER TABLE `trade_current_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_intrested`
--
ALTER TABLE `trade_intrested`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_like`
--
ALTER TABLE `trade_like`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_query_milestone3`
--
ALTER TABLE `trade_query_milestone3`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_status_message`
--
ALTER TABLE `trade_status_message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trialPeriod`
--
ALTER TABLE `trialPeriod`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `USDPlan`
--
ALTER TABLE `USDPlan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `USD_defaultmaster`
--
ALTER TABLE `USD_defaultmaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `USD_prices`
--
ALTER TABLE `USD_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usd_defaultMaster_id` (`usd_defaultMaster_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role` (`role`),
  ADD KEY `state` (`state`);

--
-- Indexes for table `vendorcategory`
--
ALTER TABLE `vendorcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_user_map`
--
ALTER TABLE `vendor_user_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `version`
--
ALTER TABLE `version`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wand`
--
ALTER TABLE `wand`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wandType`
--
ALTER TABLE `wandType`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_brands`
--
ALTER TABLE `web_brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_brand_variant`
--
ALTER TABLE `web_brand_variant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_business_details`
--
ALTER TABLE `web_business_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_cities`
--
ALTER TABLE `web_cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_news_runner`
--
ALTER TABLE `web_news_runner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_other_service_provider`
--
ALTER TABLE `web_other_service_provider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_personal_details`
--
ALTER TABLE `web_personal_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_plan`
--
ALTER TABLE `web_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_plan_keys`
--
ALTER TABLE `web_plan_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_plan_keys_map`
--
ALTER TABLE `web_plan_keys_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_states`
--
ALTER TABLE `web_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `state_code` (`state_code`),
  ADD UNIQUE KEY `state_name` (`state_name`);

--
-- Indexes for table `web_user_attachment`
--
ALTER TABLE `web_user_attachment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_user_subscription`
--
ALTER TABLE `web_user_subscription`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_vendorcategory`
--
ALTER TABLE `web_vendorcategory`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bag_vendor`
--
ALTER TABLE `bag_vendor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT for table `bid`
--
ALTER TABLE `bid`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `brands_milestone3`
--
ALTER TABLE `brands_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `brand_attachment_milestone3`
--
ALTER TABLE `brand_attachment_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `brand_availability`
--
ALTER TABLE `brand_availability`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `brand_interest`
--
ALTER TABLE `brand_interest`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `brand_interest_map`
--
ALTER TABLE `brand_interest_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `buyer_packing_INR`
--
ALTER TABLE `buyer_packing_INR`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `buy_query`
--
ALTER TABLE `buy_query`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `buy_query_milestone3`
--
ALTER TABLE `buy_query_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1340;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `category_role_map`
--
ALTER TABLE `category_role_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `chartinterval`
--
ALTER TABLE `chartinterval`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `chatStatus`
--
ALTER TABLE `chatStatus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `city_zones`
--
ALTER TABLE `city_zones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cooking_reports`
--
ALTER TABLE `cooking_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupon`
--
ALTER TABLE `coupon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `deal_lab_reports`
--
ALTER TABLE `deal_lab_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `default_value`
--
ALTER TABLE `default_value`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `domestictransport`
--
ALTER TABLE `domestictransport`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_runners`
--
ALTER TABLE `field_runners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `freeTrialMonths`
--
ALTER TABLE `freeTrialMonths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `future_buy_query_milestone3`
--
ALTER TABLE `future_buy_query_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `future_sell_query_milestone3`
--
ALTER TABLE `future_sell_query_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `grade`
--
ALTER TABLE `grade`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hotdealaccept`
--
ALTER TABLE `hotdealaccept`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `hotdeals`
--
ALTER TABLE `hotdeals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `live_prices`
--
ALTER TABLE `live_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6778975;

--
-- AUTO_INCREMENT for table `live_price_closing`
--
ALTER TABLE `live_price_closing`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24142;

--
-- AUTO_INCREMENT for table `live_price_current_status`
--
ALTER TABLE `live_price_current_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loading_reports`
--
ALTER TABLE `loading_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7666;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `mill_statuses`
--
ALTER TABLE `mill_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `negotiation_bid`
--
ALTER TABLE `negotiation_bid`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_runner`
--
ALTER TABLE `news_runner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=491;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7282612;

--
-- AUTO_INCREMENT for table `ocean_freight`
--
ALTER TABLE `ocean_freight`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2395;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `packings`
--
ALTER TABLE `packings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `packing_types`
--
ALTER TABLE `packing_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `paddyMandi`
--
ALTER TABLE `paddyMandi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `paddyStates`
--
ALTER TABLE `paddyStates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `paddy_prices`
--
ALTER TABLE `paddy_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3727;

--
-- AUTO_INCREMENT for table `paidEmails`
--
ALTER TABLE `paidEmails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `plan`
--
ALTER TABLE `plan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ports`
--
ALTER TABLE `ports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91086;

--
-- AUTO_INCREMENT for table `port_images`
--
ALTER TABLE `port_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `public_packing_milestone3`
--
ALTER TABLE `public_packing_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `qualities`
--
ALTER TABLE `qualities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=572;

--
-- AUTO_INCREMENT for table `quality_master`
--
ALTER TABLE `quality_master`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `rice_brand_form`
--
ALTER TABLE `rice_brand_form`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `rice_cooking`
--
ALTER TABLE `rice_cooking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rice_forms`
--
ALTER TABLE `rice_forms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `rice_form_milestone3`
--
ALTER TABLE `rice_form_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `rice_names`
--
ALTER TABLE `rice_names`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `rice_types`
--
ALTER TABLE `rice_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `samples`
--
ALTER TABLE `samples`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sample_lab_reports`
--
ALTER TABLE `sample_lab_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sample_outwards`
--
ALTER TABLE `sample_outwards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sample_registers`
--
ALTER TABLE `sample_registers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sellerPackingINR`
--
ALTER TABLE `sellerPackingINR`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `sell_query_milestone3`
--
ALTER TABLE `sell_query_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=328;

--
-- AUTO_INCREMENT for table `service_provider_user_map`
--
ALTER TABLE `service_provider_user_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `sub_plan`
--
ALTER TABLE `sub_plan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonial`
--
ALTER TABLE `testimonial`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `testimonial_video`
--
ALTER TABLE `testimonial_video`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trade_current_status`
--
ALTER TABLE `trade_current_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trade_intrested`
--
ALTER TABLE `trade_intrested`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=565;

--
-- AUTO_INCREMENT for table `trade_like`
--
ALTER TABLE `trade_like`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15565;

--
-- AUTO_INCREMENT for table `trade_query_milestone3`
--
ALTER TABLE `trade_query_milestone3`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2045;

--
-- AUTO_INCREMENT for table `trade_status_message`
--
ALTER TABLE `trade_status_message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trialPeriod`
--
ALTER TABLE `trialPeriod`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `USDPlan`
--
ALTER TABLE `USDPlan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `USD_defaultmaster`
--
ALTER TABLE `USD_defaultmaster`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `USD_prices`
--
ALTER TABLE `USD_prices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1183458;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22614;

--
-- AUTO_INCREMENT for table `vendorcategory`
--
ALTER TABLE `vendorcategory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vendor_user_map`
--
ALTER TABLE `vendor_user_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `version`
--
ALTER TABLE `version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wand`
--
ALTER TABLE `wand`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=554;

--
-- AUTO_INCREMENT for table `wandType`
--
ALTER TABLE `wandType`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `web_brands`
--
ALTER TABLE `web_brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `web_brand_variant`
--
ALTER TABLE `web_brand_variant`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `web_business_details`
--
ALTER TABLE `web_business_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `web_cities`
--
ALTER TABLE `web_cities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=485;

--
-- AUTO_INCREMENT for table `web_news_runner`
--
ALTER TABLE `web_news_runner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `web_other_service_provider`
--
ALTER TABLE `web_other_service_provider`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `web_personal_details`
--
ALTER TABLE `web_personal_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `web_plan`
--
ALTER TABLE `web_plan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `web_plan_keys`
--
ALTER TABLE `web_plan_keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `web_plan_keys_map`
--
ALTER TABLE `web_plan_keys_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `web_states`
--
ALTER TABLE `web_states`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `web_user_attachment`
--
ALTER TABLE `web_user_attachment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `web_user_subscription`
--
ALTER TABLE `web_user_subscription`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `web_vendorcategory`
--
ALTER TABLE `web_vendorcategory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
