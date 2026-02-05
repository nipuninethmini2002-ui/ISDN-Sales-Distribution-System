-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 04:39 PM
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
-- Database: `isdn_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `customer_branch` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `phone`, `address`, `user_id`, `customer_branch`) VALUES
(1, '0755564330', 'no.155,kaduwela,colombo', 1, 'Colombo');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `delivery_person_id` int(11) DEFAULT NULL,
  `schedule_date` datetime DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `current_lat` double DEFAULT NULL,
  `current_lng` double DEFAULT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`delivery_id`, `order_id`, `delivery_person_id`, `schedule_date`, `status`, `current_lat`, `current_lng`, `last_update`) VALUES
(1, 1, 16, '2026-02-07 00:00:00', 'On the way', 6.9271, 79.8612, '2026-02-05 14:31:03'),
(8, 2, 16, '2026-02-09 00:00:00', 'On the way', 6.9271, 79.8612, '2026-02-05 14:31:03');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_person`
--

CREATE TABLE `delivery_person` (
  `delivery_person_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL DEFAULT 'Main'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_person`
--

INSERT INTO `delivery_person` (`delivery_person_id`, `user_id`, `phone`, `vehicle_type`, `status`, `created_at`, `customer_name`, `branch`) VALUES
(16, 21, '0743424561', NULL, 'Active', '2026-02-03 01:13:03', '', 'Colombo');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `order_id`, `invoice_date`, `total_amount`, `status`) VALUES
(1, 1, '2026-02-03 20:08:55', 2500.00, 'Paid'),
(2, 2, '2026-02-03 21:44:17', 450.00, 'Paid'),
(3, 3, '2026-02-03 21:54:03', 2500.00, 'Paid'),
(4, 4, '2026-02-05 12:59:42', 120.00, 'Paid'),
(5, 5, '2026-02-05 14:35:47', 360.00, 'Paid'),
(6, 7, '2026-02-05 19:51:09', 240.00, 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Customer #1 placed an order (#1) for Rice 5kg (Qty: 1).', 'order', 0, '2026-02-03 20:08:47'),
(2, 1, 'Payment successful for Order #1 (Rs. 2,500.00)', 'payment', 0, '2026-02-03 20:08:55'),
(3, 1, 'Customer #1 placed an order (#2) for Shampoo (Qty: 1).', 'order', 0, '2026-02-03 21:44:07'),
(4, 1, 'Payment successful for Order #2 (Rs. 450.00)', 'payment', 0, '2026-02-03 21:44:17'),
(5, 1, 'Customer #1 placed an order (#3) for Rice 5kg (Qty: 1).', 'order', 0, '2026-02-03 21:53:53'),
(6, 1, 'Payment successful for Order #3 (Rs. 2,500.00)', 'payment', 0, '2026-02-03 21:54:03'),
(7, 1, 'Customer #1 placed an order (#4) for Soap (Qty: 1).', 'order', 0, '2026-02-05 12:59:35'),
(8, 1, 'Payment successful for Order #4 (Rs. 120.00)', 'payment', 0, '2026-02-05 12:59:42'),
(9, 1, 'Customer #1 placed an order (#5) for Soap (Qty: 3).', 'order', 0, '2026-02-05 14:32:57'),
(10, 1, 'Payment successful for Order #5 (Rs. 360.00)', 'payment', 0, '2026-02-05 14:35:47'),
(11, 1, 'Customer #1 placed an order (#6) for Soap (Qty: 2).', 'order', 0, '2026-02-05 19:26:34'),
(12, 1, 'Customer #1 placed an order (#7) for Soap (Qty: 2).', 'order', 0, '2026-02-05 19:49:58'),
(13, 1, 'Payment successful for Order #7 (Rs. 240.00)', 'payment', 0, '2026-02-05 19:51:09'),
(14, 1, 'Customer #1 placed an order (#8) for Soap (Qty: 2).', 'order', 0, '2026-02-05 20:15:33');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `branch` varchar(50) NOT NULL DEFAULT 'Main'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `status`, `total_amount`, `branch`) VALUES
(1, 1, '2026-02-03 20:08:47', 'Paid', 0.00, 'Colombo'),
(2, 1, '2026-02-03 21:44:07', 'Paid', 0.00, 'Colombo'),
(3, 1, '2026-02-03 21:53:53', 'Paid', 0.00, 'Colombo'),
(4, 1, '2026-02-05 12:59:35', 'Paid', 0.00, 'Colombo'),
(5, 1, '2026-02-05 14:32:57', 'Paid', 0.00, 'Colombo'),
(6, 1, '2026-02-05 19:26:34', 'Pending', 0.00, 'Colombo'),
(7, 1, '2026-02-05 19:49:58', 'Paid', 0.00, 'Colombo'),
(8, 1, '2026-02-05 20:15:33', 'Pending', 0.00, 'Colombo');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 2, 1),
(2, 2, 3, 1),
(3, 3, 2, 1),
(4, 4, 1, 1),
(5, 5, 1, 3),
(6, 6, 1, 2),
(7, 7, 1, 2),
(8, 8, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `amount`, `payment_method`, `payment_date`) VALUES
(26, 1, 2500.00, 'Online Transfer', '2026-02-03 20:08:55'),
(27, 2, 450.00, 'Online Transfer', '2026-02-03 21:44:17'),
(28, 3, 2500.00, 'Cash on Delivery', '2026-02-03 21:54:03'),
(29, 4, 120.00, 'Cash on Delivery', '2026-02-05 12:59:42'),
(30, 5, 360.00, 'Cash on Delivery', '2026-02-05 14:35:47'),
(31, 6, 240.00, 'Cash on Delivery', '2026-02-05 19:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `branch` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock_quantity`, `branch`, `category`) VALUES
(1, 'Soap', 'Bathing soap', 120.00, 32, 'Colombo', ''),
(2, 'Rice 5kg', 'Premium rice', 2500.00, 26, '', ''),
(3, 'Shampoo', 'Hair shampoo', 450.00, 55, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Customer','RDC_Staff','HO_Admin','Delivery_Person') NOT NULL,
  `email` varchar(50) NOT NULL,
  `branch` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `email`, `branch`) VALUES
(1, 'nipuni nethmini', '$2y$10$UxwNM1Bbi0SMA4XHnaBBiOuU2TVyMuVgxszZ3WAQ1kBVG7YWQNPVq', 'Customer', 'nipuninethmini2002@gmail.com', 'Colombo'),
(2, 'admin', '$2y$10$m1CV/la8hjF/UdpwmMU9nOFU100a8AmbquvBjtfwb9VFHnBaCgK82', 'HO_Admin', 'admin@gmail.com', 'Colombo'),
(21, 'delivery', '$2y$10$Z/fGrqKqSqosHfjP4hPVtOWTGAh4PD6tOu.Bxcrwz.SC9Tx5Zi9ia', 'Delivery_Person', 'delivery@gmail.com', NULL),
(24, 'rdc', '$2y$10$8h1NgruyBqyj.hes2pOQjuv6wWnzwwLNnDf1sAxYB.xg7vJgXaQHO', 'RDC_Staff', 'rdc@gmail.com', 'Colombo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `fk_customers_users` (`user_id`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `delivery_person`
--
ALTER TABLE `delivery_person`
  ADD PRIMARY KEY (`delivery_person_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_ibfk_1` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_items_ibfk_1` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_invoice` (`invoice_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `delivery_person`
--
ALTER TABLE `delivery_person`
  MODIFY `delivery_person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `delivery_person`
--
ALTER TABLE `delivery_person`
  ADD CONSTRAINT `delivery_person_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_invoice_payment` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
