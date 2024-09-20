-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2024 at 03:03 AM
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
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'FashionCo', 'Leading fashion brand for men and women', '2024-09-19 03:57:28', '2024-09-19 03:57:28'),
(2, 'StyleWear', 'Modern clothing brand with timeless designs', '2024-09-19 03:57:28', '2024-09-19 03:57:28'),
(3, 'FootFit', 'Popular brand specializing in shoes and footwear', '2024-09-19 03:57:28', '2024-09-19 03:57:28');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_uuid` char(32) NOT NULL,
  `user_uuid` char(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_uuid`, `user_uuid`, `created_at`, `updated_at`) VALUES
('cart_001', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', '2024-09-19 04:02:24', '2024-09-19 04:02:24'),
('cart_002', 'b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5a6', '2024-09-19 04:02:24', '2024-09-19 04:02:24'),
('cart_003', 'c1d2e3f4g5h6i7j8k9l0m1n2o3p4q5r6', '2024-09-19 04:02:24', '2024-09-19 04:02:24'),
('cart_004', 'd1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6', '2024-09-19 04:02:24', '2024-09-19 04:02:24'),
('cart_005', 'e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6', '2024-09-19 04:02:24', '2024-09-19 04:02:24');

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `cart_item_id` int(11) NOT NULL,
  `cart_uuid` char(32) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cartitems`
--

INSERT INTO `cartitems` (`cart_item_id`, `cart_uuid`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 'cart_001', 1, 2, '2024-09-19 04:02:49', '2024-09-19 04:02:49'),
(2, 'cart_001', 2, 1, '2024-09-19 04:02:49', '2024-09-19 04:02:49'),
(3, 'cart_002', 3, 1, '2024-09-19 04:02:49', '2024-09-19 04:02:49'),
(4, 'cart_003', 4, 3, '2024-09-19 04:02:49', '2024-09-19 04:02:49'),
(5, 'cart_004', 5, 2, '2024-09-19 04:02:49', '2024-09-19 04:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Shirts', 'Various styles of shirts for all occasions', '2024-09-19 03:45:01', '2024-09-19 03:45:01'),
(2, 'Pants', 'Comfortable and stylish pants for everyday wear', '2024-09-19 03:45:01', '2024-09-19 03:45:01'),
(3, 'Shoes', 'Footwear for casual and formal occasions', '2024-09-19 03:45:01', '2024-09-19 03:45:01'),
(4, 'Outerwear', 'Jackets, coats, and more to keep you warm', '2024-09-19 03:45:01', '2024-09-19 03:45:01'),
(5, 'Dresses', 'Elegant dresses for special events', '2024-09-19 03:45:01', '2024-09-19 03:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `warehouse_location` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `quantity`, `warehouse_location`, `last_updated`) VALUES
(1, 1, 100, NULL, '2024-09-19 04:02:09'),
(2, 2, 50, NULL, '2024-09-19 04:02:09'),
(3, 3, 30, NULL, '2024-09-19 04:02:09'),
(4, 4, 75, NULL, '2024-09-19 04:02:09'),
(5, 5, 120, NULL, '2024-09-19 04:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `order_item_id` int(11) NOT NULL,
  `order_uuid` char(32) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`order_item_id`, `order_uuid`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 'k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 1, 2, 19.99, '2024-09-19 03:58:38', '2024-09-19 03:58:38'),
(2, 'k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 2, 1, 49.99, '2024-09-19 03:58:38', '2024-09-19 03:58:38'),
(3, 'l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6', 3, 1, 79.99, '2024-09-19 03:58:38', '2024-09-19 03:58:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_uuid` char(32) NOT NULL,
  `user_uuid` char(32) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','shipped','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` varchar(255) DEFAULT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer','COD') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_uuid`, `user_uuid`, `total_amount`, `status`, `order_date`, `shipping_address`, `payment_method`, `created_at`, `updated_at`) VALUES
('k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 119.99, 'pending', '2024-09-19 03:58:25', '123 Main St', 'credit_card', '2024-09-19 03:58:25', '2024-09-19 03:58:25'),
('l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6', 'b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5a6', 69.99, 'shipped', '2024-09-19 03:58:25', '456 Elm St', 'paypal', '2024-09-19 03:58:25', '2024-09-19 03:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_uuid` char(32) DEFAULT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer','COD') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_uuid`, `payment_method`, `amount`, `payment_date`, `status`) VALUES
(18, 'k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 'credit_card', 119.99, '2024-09-19 04:24:39', 'completed'),
(19, 'l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6', 'paypal', 69.99, '2024-09-19 04:24:39', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `size` enum('XS','S','M','L','XL','XXL') DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `category_id`, `brand_id`, `stock_quantity`, `size`, `color`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'T-Shirt', 'Comfortable cotton T-shirt', 19.99, 1, 1, 50, 'M', 'Red', 'https://example.com/images/tshirt_red.jpg', '2024-09-19 03:57:58', NULL),
(2, 'Jeans', 'Classic denim jeans', 49.99, 2, 2, 30, 'L', 'Blue', 'https://example.com/images/jeans_blue.jpg', '2024-09-19 03:57:58', NULL),
(3, 'Sneakers', 'Stylish sneakers', 79.99, 3, 3, 20, 'XL', 'White', 'https://example.com/images/sneakers_white.jpg', '2024-09-19 03:57:58', NULL),
(4, 'Jacket', 'Warm winter jacket', 99.99, 4, 1, 10, 'L', 'Black', 'https://example.com/images/jacket_black.jpg', '2024-09-19 03:57:58', NULL),
(5, 'Dress', 'Elegant evening dress', 59.99, 5, 2, 15, 'S', 'Blue', 'https://example.com/images/dress_blue.jpg', '2024-09-19 03:57:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessiontokens`
--

CREATE TABLE `sessiontokens` (
  `session_id` int(11) NOT NULL,
  `user_uuid` char(32) DEFAULT NULL,
  `session_token` varchar(255) NOT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `session_expires_at` timestamp NULL DEFAULT NULL,
  `otp_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessiontokens`
--

INSERT INTO `sessiontokens` (`session_id`, `user_uuid`, `session_token`, `otp_code`, `otp_expires_at`, `session_expires_at`, `otp_verified`, `created_at`) VALUES
(1, 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 'session_token_1', '123456', '2024-09-19 04:03:29', '2024-09-19 04:53:29', 1, '2024-09-19 03:53:29'),
(2, 'b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5a6', 'session_token_2', '654321', NULL, NULL, 1, '2024-09-19 03:57:00'),
(3, 'c1d2e3f4g5h6i7j8k9l0m1n2o3p4q5r6', 'session_token_3', '111111', NULL, NULL, 0, '2024-09-19 03:57:00'),
(4, 'd1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6', 'session_token_4', '222222', NULL, NULL, 1, '2024-09-19 03:57:00'),
(5, 'e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6', 'session_token_5', '333333', NULL, NULL, 0, '2024-09-19 03:57:00'),
(6, 'f1g2h3i4j5k6l7m8n9o0p1q2r3s4t5u6', 'session_token_6', '444444', NULL, NULL, 1, '2024-09-19 03:57:00'),
(7, 'g1h2i3j4k5l6m7n8o9p0q1r2s3t4u5v6', 'session_token_7', '555555', NULL, NULL, 0, '2024-09-19 03:57:00'),
(8, 'h1i2j3k4l5m6n7o8p9q0r1s2t3u4v5w6', 'session_token_8', '666666', NULL, NULL, 1, '2024-09-19 03:57:00'),
(9, 'i1j2k3l4m5n6o7p8q9r0s1t2u3v4w5x6', 'session_token_9', '777777', NULL, NULL, 0, '2024-09-19 03:57:00'),
(10, 'j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6', 'session_token_10', '888888', NULL, NULL, 1, '2024-09-19 03:57:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactiondetails`
--

CREATE TABLE `transactiondetails` (
  `transaction_id` int(11) NOT NULL,
  `user_uuid` char(32) DEFAULT NULL,
  `order_uuid` char(32) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactiondetails`
--

INSERT INTO `transactiondetails` (`transaction_id`, `user_uuid`, `order_uuid`, `payment_id`, `transaction_amount`, `transaction_date`, `status`, `created_at`) VALUES
(3, 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 'k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 18, 119.99, '2024-09-19 04:29:03', 'completed', '2024-09-19 04:29:03'),
(4, 'b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5a6', 'l1m2n3o4p5q6r7s8t9u0v1w2x3y4z5a6', 19, 69.99, '2024-09-19 04:29:03', 'pending', '2024-09-19 04:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_uuid` char(32) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_uuid`, `first_name`, `last_name`, `email`, `password`, `address`, `phone_number`, `role`, `created_at`, `updated_at`) VALUES
('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 'John', 'Doe', 'john.doe@example.com', 'password123', '123 Main St', '555-1234', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5a6', 'Jane', 'Smith', 'jane.smith@example.com', 'password456', '456 Elm St', '555-5678', 'admin', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('c1d2e3f4g5h6i7j8k9l0m1n2o3p4q5r6', 'Michael', 'Johnson', 'michael.johnson@example.com', 'password789', '789 Oak St', '555-7890', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6', 'Emily', 'Davis', 'emily.davis@example.com', 'password987', '123 Pine St', '555-1111', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6', 'Daniel', 'Martinez', 'daniel.martinez@example.com', 'password654', '456 Maple St', '555-2222', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('f1g2h3i4j5k6l7m8n9o0p1q2r3s4t5u6', 'Olivia', 'Garcia', 'olivia.garcia@example.com', 'password321', '789 Cedar St', '555-3333', 'admin', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('g1h2i3j4k5l6m7n8o9p0q1r2s3t4u5v6', 'David', 'Wilson', 'david.wilson@example.com', 'password234', '123 Birch St', '555-4444', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('h1i2j3k4l5m6n7o8p9q0r1s2t3u4v5w6', 'Sophia', 'Lopez', 'sophia.lopez@example.com', 'password345', '456 Walnut St', '555-5555', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('i1j2k3l4m5n6o7p8q9r0s1t2u3v4w5x6', 'Liam', 'Brown', 'liam.brown@example.com', 'password456', '789 Poplar St', '555-6666', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46'),
('j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6', 'Isabella', 'Jones', 'isabella.jones@example.com', 'password567', '123 Willow St', '555-7777', 'customer', '2024-09-19 03:45:46', '2024-09-19 03:45:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_uuid`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_uuid` (`cart_uuid`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_uuid` (`order_uuid`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_uuid`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_uuid` (`order_uuid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `sessiontokens`
--
ALTER TABLE `sessiontokens`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Indexes for table `transactiondetails`
--
ALTER TABLE `transactiondetails`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_uuid` (`user_uuid`),
  ADD KEY `order_uuid` (`order_uuid`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_uuid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sessiontokens`
--
ALTER TABLE `sessiontokens`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactiondetails`
--
ALTER TABLE `transactiondetails`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`user_uuid`);

--
-- Constraints for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`cart_uuid`) REFERENCES `cart` (`cart_uuid`),
  ADD CONSTRAINT `cartitems_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`order_uuid`) REFERENCES `orders` (`order_uuid`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`user_uuid`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_uuid`) REFERENCES `orders` (`order_uuid`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`);

--
-- Constraints for table `sessiontokens`
--
ALTER TABLE `sessiontokens`
  ADD CONSTRAINT `sessiontokens_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`user_uuid`);

--
-- Constraints for table `transactiondetails`
--
ALTER TABLE `transactiondetails`
  ADD CONSTRAINT `transactiondetails_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`user_uuid`),
  ADD CONSTRAINT `transactiondetails_ibfk_2` FOREIGN KEY (`order_uuid`) REFERENCES `orders` (`order_uuid`),
  ADD CONSTRAINT `transactiondetails_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
