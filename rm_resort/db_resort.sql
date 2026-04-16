-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 03:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_resort`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `check_in` date NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `check_in_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `room_id`, `guest_name`, `guest_phone`, `phone`, `check_in`, `status`, `check_in_date`) VALUES
(15, 6, 'Krystelle M. Liray', '09265377452', '', '0000-00-00', 'Pending', '2027-01-09'),
(16, 6, 'Karylle Liray', '09265377452', '', '0000-00-00', 'Pending', '2026-11-13');

-- --------------------------------------------------------

--
-- Table structure for table `entrance_rates`
--

CREATE TABLE `entrance_rates` (
  `id` int(11) NOT NULL,
  `age_group` varchar(50) DEFAULT NULL,
  `day_price` decimal(10,2) DEFAULT NULL,
  `night_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrance_rates`
--

INSERT INTO `entrance_rates` (`id`, `age_group`, `day_price`, `night_price`) VALUES
(1, 'Adult', 102.00, 150.00),
(2, 'Teen', 50.00, 100.00),
(3, 'Kids', 30.00, 75.00);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `caption`, `category`, `image`) VALUES
(10, 'Swimming Pool', NULL, 'uploads/gallery/1776094873_pool2.jpg'),
(11, 'Garden', NULL, 'uploads/gallery/1776157540_view9.jpg'),
(12, 'Sunrise', NULL, 'uploads/gallery/1776232196_view5.jpg'),
(13, 'Rainbow', NULL, 'uploads/gallery/1776233243_view10.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `resort_rates`
--

CREATE TABLE `resort_rates` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `adult_price` decimal(10,2) DEFAULT NULL,
  `kids_price` decimal(10,2) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resort_rates`
--

INSERT INTO `resort_rates` (`id`, `title`, `adult_price`, `kids_price`, `type`) VALUES
(1, 'Swimming Pool', 100.00, 50.00, 'Day Tour');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `room_type` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Available','Occupied','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `price`, `room_type`, `image`, `status`) VALUES
(6, 'Circular Concrete Table 1', 500.00, NULL, 'uploads/1776082808_table1.jpg', 'Available'),
(9, 'Circular Conrete Table 2', 500.00, NULL, 'uploads/1776157498_table3.jpg', 'Available'),
(10, 'Titanic Cottage', 500.00, NULL, 'uploads/1776232129_titanic cottage.jpg', 'Available'),
(11, 'Mushroom Cottage', 400.00, NULL, 'uploads/1776233022_mushroomcottage.jpg', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(2, 'admin', 'admin123', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `entrance_rates`
--
ALTER TABLE `entrance_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resort_rates`
--
ALTER TABLE `resort_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `entrance_rates`
--
ALTER TABLE `entrance_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `resort_rates`
--
ALTER TABLE `resort_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
