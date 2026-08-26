-- =====================================================================
-- NBK Travel — nbk_travels_db schema
-- Exported from phpMyAdmin, reflecting the live database exactly.
-- =====================================================================
--
-- KNOWN LIMITATION (for report — Design/Testing section):
-- These tables were created via phpMyAdmin using the default MyISAM
-- storage engine. MyISAM does NOT support foreign key constraints —
-- although FOREIGN KEY clauses were written during initial design
-- (see Phase 1 design notes), MySQL silently created plain indexes
-- (KEY) instead of enforced constraints on this engine.
--
-- Practical effect: referential integrity (e.g. preventing a booking
-- from referencing a non-existent customer) is currently enforced
-- only at the application layer (PHP), not the database layer.
-- The application has not encountered integrity issues in testing,
-- since all inserts go through validated PHP logic using real,
-- fetched IDs.
--
-- Remediation (not yet applied): converting affected tables to the
-- InnoDB engine (ALTER TABLE <table> ENGINE=InnoDB;) would enable
-- proper foreign key enforcement. Left as a documented limitation /
-- future improvement rather than an in-scope fix for this iteration.
-- =====================================================================

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 26, 2026 at 08:49 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nbk_travels_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `cust_id` int NOT NULL,
  `driver_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `pickup` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dropoff` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `pax` int NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`booking_id`),
  KEY `cust_id` (`cust_id`),
  KEY `driver_id` (`driver_id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `cust_id`, `driver_id`, `vehicle_id`, `pickup`, `dropoff`, `booking_date`, `booking_time`, `pax`, `fare`, `status`) VALUES
(1, 2, NULL, NULL, 'sandton', 'Mabopame', '2026-12-12', '15:50:00', 4, 6000.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `cust_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_num` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferences` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`cust_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`cust_id`, `user_id`, `full_name`, `phone_num`, `preferences`) VALUES
(2, 5, 'sk', '1111111111', 'wndow seat');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE IF NOT EXISTS `drivers` (
  `driver_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_num` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `availability` enum('available','unavailable') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`driver_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `driver_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  PRIMARY KEY (`schedule_id`),
  KEY `booking_id` (`booking_id`),
  KEY `driver_id` (`driver_id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rendani Rendy', 'rendy@gmail.com', '', '$2y$10$Bspb2CUeY39sGRyxF9CfKesxqNqwl2AC.gs426PTXptqiHVkV44Xu', 'customer', 'pending', '2026-08-25 10:34:06', '2026-08-26 00:01:42'),
(2, 'Murendeni Makhavhu', 'makhavhu@nbktravels', '', '$2y$10$jF7mLvDfeEbpQX42KYrSm.vC1GJG3BRrmcOPNLcD0xXPOlNS73WGG', 'admin', 'verified', '2026-08-25 21:37:45', '2026-08-25 21:37:45'),
(3, 'za', 'zz@gmail.com', '1234567', '$2y$10$Kt4YdD2jPLKWiciIdxGKM.QY22GTTgUUquU2tOtkyOVko2UaCFr3m', 'customer', 'verified', '2026-08-26 00:05:42', '2026-08-26 00:42:22'),
(4, 'ma', 'ma@gmail.com', '0825221212', '$2y$10$FBOmiKXo30G3pu4c0Ti6D.Fr2HRhewdsplYu1kIEtR1puvoL1Cl..', 'customer', 'verified', '2026-08-26 00:25:36', '2026-08-26 00:25:43'),
(5, 'sk', 'sk@gmail.com', '1111111111', '$2y$10$w/jdocoE29XQAelfHksL3u9xtfgaKYrT48JEM53b6WdnfoEqbIXr2', 'customer', 'verified', '2026-08-26 00:52:43', '2026-08-26 00:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `make` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_num` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seating_capacity` int NOT NULL,
  `availability` enum('available','unavailable') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `registration_num` (`registration_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;