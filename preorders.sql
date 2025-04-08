-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 08:25 PM
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
-- Database: `resto_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `preorders`
--

CREATE TABLE `preorders` (
  `preorder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `order_status` enum('pending','confirmed','ready','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(20) DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preorders`
--

INSERT INTO `preorders` (`preorder_id`, `user_id`, `menu_id`, `quantity`, `pickup_date`, `pickup_time`, `order_status`, `total_amount`, `created_at`, `payment_method`, `payment_status`, `payment_id`) VALUES
(4, 11, 6, 1, '2025-02-25', '16:00:00', 'ready', 0.00, '2025-02-25 04:17:20', 'cash', 'pending', NULL),
(5, 11, 7, 1, '2025-02-25', '16:00:00', '', 0.00, '2025-02-25 04:17:20', 'cash', 'pending', NULL),
(6, 11, 7, 1, '2025-02-25', '12:00:00', 'confirmed', 0.00, '2025-02-25 05:21:36', 'cash', 'pending', NULL),
(7, 11, 6, 4, '2025-02-25', '09:00:00', 'ready', 0.00, '2025-02-25 05:32:47', 'cash', 'pending', NULL),
(8, 11, 7, 1, '2025-02-25', '09:00:00', '', 0.00, '2025-02-25 05:32:47', 'cash', 'pending', NULL),
(9, 9, 6, 1, '2025-02-25', '09:00:00', 'cancelled', 0.00, '2025-02-25 05:52:39', 'cash', 'pending', NULL),
(10, 9, 7, 1, '2025-02-25', '09:00:00', 'confirmed', 0.00, '2025-02-25 05:52:39', 'cash', 'pending', NULL),
(12, 9, 6, 2, '2025-03-14', '09:00:00', 'pending', 260.00, '2025-03-12 15:34:12', 'cash', 'pending', NULL),
(13, 9, 7, 1, '2025-03-14', '09:00:00', 'ready', 160.00, '2025-03-12 15:34:12', 'cash', 'pending', NULL),
(14, 9, 6, 1, '2025-03-14', '09:00:00', 'pending', 130.00, '2025-03-13 09:44:44', 'cash', 'pending', NULL),
(15, 9, 7, 2, '2025-03-14', '09:00:00', 'pending', 320.00, '2025-03-13 09:44:44', 'cash', 'pending', NULL),
(16, 9, 7, 1, '2025-03-13', '20:00:00', 'ready', 160.00, '2025-03-13 09:46:29', 'cash', 'pending', NULL),
(17, 11, 6, 1, '2025-03-17', '17:30:00', 'confirmed', 130.00, '2025-03-17 09:55:01', 'cash', 'pending', NULL),
(18, 11, 7, 1, '2025-03-17', '17:30:00', 'completed', 160.00, '2025-03-17 09:55:01', 'cash', 'pending', NULL),
(19, 11, 7, 1, '2025-03-18', '09:00:00', 'pending', 160.00, '2025-03-17 15:45:32', 'cash', 'pending', NULL),
(20, 11, 7, 1, '2025-03-18', '21:00:00', 'pending', 160.00, '2025-03-17 15:49:29', 'cash', 'pending', NULL),
(21, 11, 7, 1, '2025-03-18', '09:00:00', 'pending', 160.00, '2025-03-17 15:51:32', 'cash', 'pending', NULL),
(22, 11, 6, 1, '2025-03-18', '09:00:00', 'pending', 130.00, '2025-03-17 15:53:51', 'cash', 'pending', NULL),
(23, 11, 7, 1, '2025-03-18', '09:00:00', 'pending', 160.00, '2025-03-17 15:57:22', 'cash', 'pending', NULL),
(24, 11, 6, 1, '2025-03-25', '09:00:00', 'completed', 130.00, '2025-03-18 04:01:27', 'cash', 'pending', NULL),
(25, 11, 7, 1, '2025-03-25', '09:00:00', 'completed', 160.00, '2025-03-18 04:01:27', 'cash', 'pending', NULL),
(26, 11, 7, 2, '2025-03-21', '21:00:00', 'completed', 320.00, '2025-03-21 06:11:18', 'cash', 'pending', NULL),
(27, 11, 6, 1, '2025-03-21', '21:00:00', 'completed', 130.00, '2025-03-21 06:11:18', 'cash', 'pending', NULL),
(28, 11, 12, 1, '2025-03-21', '21:00:00', 'completed', 130.00, '2025-03-21 06:11:18', 'cash', 'pending', NULL),
(29, 11, 13, 1, '2025-03-21', '21:00:00', 'completed', 40.00, '2025-03-21 06:11:18', 'cash', 'pending', NULL),
(30, 15, 12, 1, '2025-03-27', '17:00:00', 'cancelled', 130.00, '2025-03-27 08:36:43', 'cash', 'pending', NULL),
(31, 15, 13, 1, '2025-03-27', '17:00:00', 'cancelled', 40.00, '2025-03-27 08:36:43', 'cash', 'pending', NULL),
(32, 15, 7, 2, '2025-03-27', '17:00:00', 'cancelled', 320.00, '2025-03-27 08:36:43', 'cash', 'pending', NULL),
(33, 15, 6, 1, '2025-03-27', '17:00:00', 'cancelled', 130.00, '2025-03-27 08:36:43', 'cash', 'pending', NULL),
(34, 11, 12, 2, '2025-03-27', '18:00:00', 'cancelled', 260.00, '2025-03-27 09:07:50', 'cash', 'pending', NULL),
(35, 11, 13, 1, '2025-03-27', '18:00:00', 'cancelled', 40.00, '2025-03-27 09:07:50', 'cash', 'pending', NULL),
(36, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:40:50', 'cash', 'pending', NULL),
(37, 11, 7, 1, '2025-04-02', '09:00:00', 'pending', 160.00, '2025-04-01 16:42:14', 'cash', 'pending', NULL),
(38, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:42:14', 'cash', 'pending', NULL),
(39, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:43:44', 'cash', 'pending', NULL),
(40, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:43:44', 'cash', 'pending', NULL),
(41, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:46:50', 'cash', 'pending', NULL),
(42, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:46:50', 'cash', 'pending', NULL),
(43, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:49:11', 'cash', 'pending', NULL),
(44, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:49:11', 'cash', 'pending', NULL),
(45, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:54:06', 'cash', 'paid', 1),
(46, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:54:06', 'cash', 'pending', NULL),
(47, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 16:59:52', 'cash', 'pending', NULL),
(48, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 16:59:52', 'cash', 'pending', NULL),
(49, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 17:01:42', 'cash', 'pending', NULL),
(50, 11, 12, 2, '2025-04-02', '09:00:00', 'pending', 260.00, '2025-04-01 18:14:17', 'cash', 'pending', NULL),
(51, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 18:14:17', 'cash', 'pending', NULL),
(52, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 18:15:25', 'cash', 'pending', NULL),
(53, 11, 12, 1, '2025-04-03', '09:00:00', 'pending', 130.00, '2025-04-01 18:17:52', 'cash', 'paid', 2),
(54, 11, 12, 1, '2025-04-04', '09:00:00', 'pending', 130.00, '2025-04-01 18:19:08', 'cash', 'paid', 3),
(55, 11, 13, 1, '2025-04-04', '09:00:00', 'pending', 40.00, '2025-04-01 18:19:08', 'cash', 'pending', NULL),
(56, 11, 7, 1, '2025-04-03', '09:00:00', 'pending', 160.00, '2025-04-01 18:20:45', 'cash', 'paid', 4),
(57, 11, 6, 1, '2025-04-03', '09:00:00', 'pending', 130.00, '2025-04-01 18:20:45', 'cash', 'pending', NULL),
(58, 11, 12, 1, '2025-04-03', '09:00:00', 'pending', 130.00, '2025-04-01 18:23:12', 'cash', 'pending', NULL),
(59, 11, 13, 1, '2025-04-03', '09:00:00', 'pending', 40.00, '2025-04-01 18:23:12', 'cash', 'pending', NULL),
(60, 11, 12, 1, '2025-04-02', '09:00:00', 'pending', 130.00, '2025-04-01 18:25:04', 'cash', 'paid', 5),
(61, 11, 13, 1, '2025-04-02', '09:00:00', 'pending', 40.00, '2025-04-01 18:25:04', 'cash', 'pending', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `preorders`
--
ALTER TABLE `preorders`
  ADD PRIMARY KEY (`preorder_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `preorders`
--
ALTER TABLE `preorders`
  MODIFY `preorder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `preorders`
--
ALTER TABLE `preorders`
  ADD CONSTRAINT `preorders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `preorders_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menuitems` (`menu_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
