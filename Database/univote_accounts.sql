-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 05:04 PM
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
-- Database: `univote_accounts`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `student_id` varchar(8) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `middlename` varchar(200) NOT NULL,
  `year_level` varchar(3) NOT NULL,
  `course` varchar(100) NOT NULL,
  `department` varchar(255) NOT NULL,
  `role` enum('voter','admin','electoral_manager','') NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `student_id`, `lastname`, `firstname`, `middlename`, `year_level`, `course`, `department`, `role`, `email`, `password`) VALUES
(1, '22-00548', 'Garapan', 'Jhon Francis', 'Encinas', '3rd', 'BSIT', 'CITE', 'voter', 'jhongarapan@gmail.com', '04102003'),
(2, '22-00175', 'Buhat', 'Justine', 'Bernal', '3rd', 'BSIT', 'CITE', 'electoral_manager', 'justinebuhat2004@gmail.com', '04022004'),
(3, '22-00536', 'Santillan', 'Christian Jolo', 'A', '3rd', 'BSIT', 'CITE', 'admin', 'santillan.christianjolo@gmail.com', '01082000'),
(4, '22-00323', 'Boncales', 'Cherry', 'T', '3rd', 'BSIT', 'CITE', 'electoral_manager', 'selacnobrey@gmail.com', '26082004'),
(5, '25-00001', 'Cruz', 'Juan', 'Dela', '1st', 'BSIT', 'CITE', 'voter', 'juandelacruz@gmail.com', '01022003');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_no` int(11) NOT NULL,
  `student_id` varchar(8) NOT NULL,
  `POSITION` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `middlename` varchar(200) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `course` varchar(200) NOT NULL,
  `year_level` enum('1st','2nd','3rd','4th') NOT NULL,
  `candidate_picture` varchar(255) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `partylist` varchar(200) NOT NULL,
  `partylist_id` int(11) DEFAULT NULL,
  FOREIGN KEY (`partylist_id`) REFERENCES `partylists` (`partylist_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `election_events`
--

CREATE TABLE `election_events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_type` enum('School-Wide','College-Based','Class-Based') DEFAULT NULL,
  `organized_by` varchar(255) DEFAULT NULL,
  `voting_mode` enum('Online','On-Site','Hybrid') DEFAULT NULL,
  `positions` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `event_datetime_start` datetime NOT NULL,
  `event_datetime_end` datetime NOT NULL,
  `event_banner` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `number_of_candidates` int(11) NOT NULL DEFAULT 0,
  `number_of_partylists` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_events`
--

INSERT INTO `election_events` (`id`, `event_name`, `description`, `event_type`, `organized_by`, `voting_mode`, `positions`, `department`, `event_datetime_start`, `event_datetime_end`, `event_banner`, `is_archived`, `number_of_candidates`, `number_of_partylists`) VALUES
(12, 'CITE Election 2025', 'College of Information Technology Education - Election 2025', '', 'COMELEC', 'Online', 10, 'CITE', '2025-04-22 07:00:00', '2025-04-22 19:00:00', 'uploads/ChatGPT Image Apr 20, 2025, 07_22_52 PM.png', 0, 0, 0),
(13, 'CAS Election 2025', 'College of Arts and Sciences', '', 'COMELEC', 'Online', 10, 'CAS', '2025-04-21 07:00:00', '2025-04-21 19:00:00', 'uploads/ChatGPT Image Apr 20, 2025, 09_48_35 PM.png', 0, 0, 0),
(18, 'CON Election 2025', 'College of Nursing = Election 2025', '', 'COMELEC', 'Online', 10, 'CON', '2025-04-21 07:00:00', '2025-04-21 19:00:00', 'uploads/ChatGPT Image Apr 20, 2025, 11_42_40 PM.png', 0, 30, 2),
(19, 'SSC Election 2025', 'Supreme Student Council - Election 2025', '', 'COMELEC', 'Online', 10, 'SSC', '2025-04-22 07:00:00', '2025-04-22 19:00:00', 'uploads/ChatGPT Image Apr 20, 2025, 10_20_59 PM.png', 0, 30, 2);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `POSITION` varchar(50) NOT NULL,
  `candidate_no` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `student_id`, `event_id`, `POSITION`, `candidate_no`, `created_at`) VALUES
(26, '22-00175', 6, 'President', 18, '2025-04-12 05:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `partylists`
--

CREATE TABLE `partylists` (
  `partylist_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`partylist_id`),
  FOREIGN KEY (`event_id`) REFERENCES `election_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`