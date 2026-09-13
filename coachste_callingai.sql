-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 13, 2026 at 05:54 AM
-- Server version: 11.8.9-MariaDB
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coachste_callingai`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` varchar(64) NOT NULL,
  `call_id` varchar(128) DEFAULT NULL,
  `tool_call_id` varchar(128) DEFAULT NULL,
  `agent_id` varchar(128) DEFAULT NULL,
  `customer_name` varchar(190) NOT NULL,
  `customer_phone` varchar(40) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `service` varchar(190) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('confirmed','pending','completed','cancelled') NOT NULL DEFAULT 'confirmed',
  `source` varchar(40) NOT NULL DEFAULT 'voicebip',
  `raw_arguments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_arguments`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `call_events`
--

CREATE TABLE `call_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(128) NOT NULL,
  `event_type` varchar(80) NOT NULL,
  `agent_id` varchar(128) DEFAULT NULL,
  `call_id` varchar(128) DEFAULT NULL,
  `from_number` varchar(40) DEFAULT NULL,
  `to_number` varchar(40) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_booking_id` (`booking_id`),
  ADD UNIQUE KEY `uq_slot` (`appointment_date`,`appointment_time`),
  ADD UNIQUE KEY `uq_tool_call_id` (`tool_call_id`),
  ADD KEY `idx_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`customer_phone`);

--
-- Indexes for table `call_events`
--
ALTER TABLE `call_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_id` (`event_id`),
  ADD KEY `idx_call_id` (`call_id`),
  ADD KEY `idx_event_type` (`event_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `call_events`
--
ALTER TABLE `call_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
