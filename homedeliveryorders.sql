-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 08:49 PM
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
-- Table structure for table `homedeliveryorders`
--

CREATE TABLE `homedeliveryorders` (
  `delivery_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `delivery_time` datetime NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `food_status` enum('ordered','preparing','ready','out_for_delivery','delivered','cancelled') DEFAULT 'ordered',
  `payment_method` varchar(20) DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homedeliveryorders`
--

INSERT INTO `homedeliveryorders` (`delivery_id`, `user_id`, `menu_id`, `quantity`, `delivery_time`, `address`, `total_amount`, `created_at`, `food_status`, `payment_method`, `payment_status`, `payment_id`) VALUES
(2, 9, 6, 1, '2025-02-22 12:00:00', 'njaravelil(h) ,thalayolaparambu,kottayam', 0.00, '2025-02-17 16:08:02', 'ordered', 'cash', 'pending', NULL),
(3, 9, 6, 2, '2025-02-17 09:00:00', 'njaravelil(h) ,thalayolaparambu,kottayam', 0.00, '2025-02-17 16:10:54', 'ordered', 'cash', 'pending', NULL),
(4, 11, 6, 1, '2025-02-26 12:00:00', 'njaravelil', 0.00, '2025-02-25 04:11:03', 'ordered', 'cash', 'pending', NULL),
(5, 11, 7, 1, '2025-02-26 12:00:00', 'njaravelil', 0.00, '2025-02-25 04:11:03', 'ordered', 'cash', 'pending', NULL),
(6, 11, 6, 1, '2025-02-25 15:00:00', 'njaravelil', 0.00, '2025-02-25 04:13:44', 'ordered', 'cash', 'pending', NULL),
(7, 11, 7, 1, '2025-02-25 15:00:00', 'njaravelil', 0.00, '2025-02-25 04:13:44', 'ordered', 'cash', 'pending', NULL),
(8, 11, 7, 1, '2025-02-25 20:00:00', 'njaravelil', 0.00, '2025-02-25 04:31:13', 'ordered', 'cash', 'pending', NULL),
(9, 11, 7, 1, '2025-02-25 12:00:00', 'njaravelil', 0.00, '2025-02-25 04:43:14', 'ordered', 'cash', 'pending', NULL),
(10, 9, 7, 2, '2025-02-25 09:00:00', 'njaravelil(h) ,thalayolaparambu,kottayam', 0.00, '2025-02-25 05:59:54', 'ordered', 'cash', 'pending', NULL),
(11, 9, 6, 1, '2025-03-28 09:00:00', 'njaravelil(h) ,thalayolaparambu,kottayam', 130.00, '2025-03-12 15:45:01', 'delivered', 'cash', 'pending', NULL),
(12, 9, 7, 1, '2025-03-28 09:00:00', 'njaravelil(h) ,thalayolaparambu,kottayam', 160.00, '2025-03-12 15:45:01', 'delivered', 'cash', 'pending', NULL),
(13, 11, 12, 3, '2025-04-02 01:00:00', 'njaravelil', 390.00, '2025-04-01 18:46:23', '', 'cash', 'pending', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `homedeliveryorders`
--
ALTER TABLE `homedeliveryorders`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `homedeliveryorders`
--
ALTER TABLE `homedeliveryorders`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `homedeliveryorders`
--
ALTER TABLE `homedeliveryorders`
  ADD CONSTRAINT `homedeliveryorders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homedeliveryorders_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menuitems` (`menu_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
