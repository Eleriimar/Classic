-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 07:16 PM
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
-- Database: `onlinefoodphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adm_id` int(222) NOT NULL,
  `username` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `code` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adm_id`, `username`, `password`, `email`, `code`, `date`) VALUES
(1, 'admin', 'CAC29D7A34687EB14B37068EE4708E7B', 'admin@mail.com', '', '2022-05-27 13:21:52');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(222) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `status` enum('assigned','picked_up','in_transit','delivered','cancelled') DEFAULT 'assigned',
  `driver_latitude` decimal(10,8) DEFAULT NULL,
  `driver_longitude` decimal(11,8) DEFAULT NULL,
  `estimated_arrival` datetime DEFAULT NULL,
  `actual_arrival` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `d_id` int(222) NOT NULL,
  `rs_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `slogan` varchar(222) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `img` varchar(222) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`d_id`, `rs_id`, `title`, `slogan`, `price`, `img`) VALUES
(50, 1, 'Samosa (Beef/Veg)', 'Samosas are deep-fried, triangular pastries with a crispy outer shell and savory filling—usually spiced minced meat, vegetables, or lentils. They\'re a favorite snack or appetizer across East Africa, especially during Ramad', 40.00, '68765f0fe72a0.jpg'),
(52, 1, 'Rice Beef', 'A hearty and flavorful combination of tender beef chunks slowly simmered in a rich, savory sauce made with fresh tomatoes, onions, garlic, and a blend of traditional Kenyan spices.  It is served alongside a generous portio', 230.00, '68766e7868957.jpg'),
(53, 1, 'Mahamri/Ndazi', 'Mahamri are soft, slightly sweet, triangular Swahili doughnuts, often spiced with cardamom and made with coconut milk. They are deep-fried to a golden brown and commonly served with chai or bean stew (maharagwe) along the ', 10.00, '68766cdfb3020.jpg'),
(54, 1, 'Ugali Beef', 'A Kenyan staple combo—firm maize meal (ugali) served with rich beef stew. Filling and traditional.', 200.00, '687670cf6d2ff.jpg'),
(55, 1, 'Chips Nyama', 'French fries topped or mixed with fried beef strips, onions, and spices. A street-food favorite in Kenya.', 280.00, '6876709866632.jpg'),
(56, 1, 'Special pilau', 'A more flavorful version of pilau made with beef, peas, carrots, and a blend of Swahili spices. Rich and aromatic.', 200.00, '68766dee1fc51.jpg'),
(57, 1, 'chapati', 'Chapati is a soft, round, unleavened flatbread made from wheat flour, water, and oil, popular in Kenyan and other East African households. It\'s cooked on a flat griddle until golden brown and served with a variety of stews', 20.00, '68767101d7966.jpg'),
(58, 1, 'Brown Chapati ', 'Chapati is a soft, round, unleavened flatbread made from wheat flour, water, and oil, popular in Kenyan and other East African households. It\'s cooked on a flat griddle until golden brown and served with a variety of stews', 40.00, '68767139d7b63.jpg'),
(59, 1, 'Tea (regular)', 'Kenyan regular tea, or \"chai,\" is black tea brewed in water, sometimes sweetened with sugar. It’s commonly enjoyed during breakfast or tea breaks.', 30.00, '6876717a10d59.jpg'),
(60, 1, 'Plain Rice', 'Steamed white rice, soft and fluffy, served as a versatile side dish that pairs well with stews, curries, or vegetables.', 80.00, '687671bc1b102.jpg'),
(61, 1, 'Githeri', 'A traditional Kenyan meal made by boiling maize and beans together. It can be served plain or fried with onions, tomatoes, and spices.', 100.00, '6876720269f85.jpg'),
(62, 1, 'Mukimo plain', 'A Kikuyu delicacy made from mashed potatoes mixed with maize, peas, and pumpkin leaves or spinach, often served with stew.', 100.00, '6876722c14a30.jpg'),
(63, 1, 'Beef Plain', 'Tender pieces of beef cooked simply with salt, onions, and minimal seasoning—great for pairing with sides or sauces.', 150.00, '687672c529362.jpg'),
(64, 1, 'Smokie', 'A smokie is a pre-cooked, mildly spiced sausage, often sold by street vendors in Kenya. It\'s usually served with kachumbari (onion and tomato salad) and wrapped in a chapati or bun.', 50.00, '687673281834d.jpg'),
(65, 1, 'Special Tea ', 'Special tea combines black tea leaves, milk, sugar, and spices like ginger, cardamom, or cinnamon for a rich, aromatic flavor—similar to Indian chai masala.', 50.00, '6876735e6d1c6.jpg'),
(66, 1, 'Nduma', 'Boiled or steamed arrowroots with a starchy texture and earthy flavor. Often served for breakfast or as a healthy side.', 50.00, '6876737d4bfc9.jpg'),
(67, 1, 'Sweet Potatoes', 'These are boiled or roasted root vegetables with a naturally sweet flavor. Popular for breakfast in Kenyan homes, especially with tea.', 50.00, '6876741e1698a.jpg'),
(68, 1, 'Plain Fries', 'Deep-fried potato strips, crispy on the outside and soft inside, served with ketchup or chili sauce.', 130.00, '68767472eb791.jpg'),
(69, 1, 'Chips Masala', 'Fries tossed in a spicy tomato-based sauce, often with onions, garlic, and coriander. A flavorful twist to plain fries.', 180.00, '687674bb17344.jpg'),
(70, 0, 'Chips Mayai', 'A popular Kenyan street food—a combination of French fries and eggs, cooked together like an omelet.', 200.00, '687675568f4e4.jpg'),
(71, 1, 'Chips Mayai', 'A popular Kenyan street food—a combination of French fries and eggs, cooked together like an omelet.', 200.00, '6876759b5e3f0.jpg'),
(72, 1, 'Chapati Beef', 'Hearty beef stew paired with soft, flaky chapatis. A classic Kenyan comfort food.', 190.00, '687675ee4a23b.jpg'),
(73, 1, 'Chicken Deep Fry', 'Crispy, golden-brown fried chicken with a seasoned crust, typically served without sauce.', 200.00, '68767625694fd.jpg'),
(74, 1, 'Chicken Wet Fry', 'Chicken cooked in a rich, savory sauce made with tomatoes, onions, and spices. Juicy and full of flavor.', 250.00, '6876764247a56.jpg'),
(75, 1, 'Pilau Plain', 'A spiced rice dish with a rich aroma, cooked with  onions, garlic, and pilau masala. A Swahili coastal favorite.', 150.00, '687676a5d4e72.jpg'),
(76, 1, 'Lentils', 'Soft-cooked lentils simmered in a savory tomato and onion sauce, often seasoned with mild spices. A nutritious vegetarian option.', 60.00, '687676d6c5831.jpg'),
(77, 1, 'Beans', 'Red or yellow beans slow-cooked in a thick, flavorful stew of onions, tomatoes, and spices. Commonly eaten with rice or chapati.', 50.00, '687676f6e51fe.jpg'),
(78, 1, 'Ndengu', 'Protein-rich green grams (mung beans) stewed with onions, tomatoes, and spices. A healthy and popular vegetarian meal.', 50.00, '68767747183c5.jpg'),
(79, 1, 'Minji Plain (Peas)', 'Green peas cooked in a spiced tomato and onion base, often enjoyed with rice, chapati.', 100.00, '68767771e3136.jpg'),
(80, 1, 'Minji stew', 'A hearty combination of beef chunks and green peas in a rich tomato-onion sauce. Perfect with rice or chapati.', 250.00, '687677abec85d.jpg'),
(81, 1, 'Liver ', 'Sliced liver cooked either in a dry spiced sauté or a juicy tomato-onion sauce. Popular for its rich flavor and high iron content.', 150.00, '687678196b47f.jpg'),
(82, 1, 'Fish Wet Fry', 'Fish fillet or whole fish simmered in a spicy, tomato-based stew. Juicy and served hot with ugali or rice.', 250.00, '687678444980a.jpg'),
(83, 1, 'Fish Dry Fry', 'Whole or sliced fish deep-fried until crispy, seasoned with salt and spices. Served plain or with kachumbari.', 200.00, '6878a8613372b.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT 'driver123',
  `email` varchar(255) DEFAULT NULL,
  `vehicle_number` varchar(20) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `status` enum('available','busy','offline') DEFAULT 'available',
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `password`, `email`, `vehicle_number`, `vehicle_type`, `status`, `current_latitude`, `current_longitude`, `created_at`) VALUES
(1, 'John Driver', '+254700123456', 'driver123', 'john@example.com', 'KCA 123A', 'Motorcycle', 'available', -1.24035840, 36.87815530, '2025-08-05 16:42:43'),
(2, 'Jane Rider', '+254700789012', 'driver123', 'jane@example.com', 'KCB 456B', 'Motorcycle', 'available', NULL, NULL, '2025-08-05 16:42:43'),
(3, 'Mike Delivery', '+254700345678', 'driver123', 'mike@example.com', 'KCC 789C', 'Motorcycle', 'available', NULL, NULL, '2025-08-05 16:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_transactions`
--

CREATE TABLE `mpesa_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(222) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','cancelled') DEFAULT 'pending',
  `response_code` varchar(10) DEFAULT NULL,
  `response_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remark`
--

CREATE TABLE `remark` (
  `id` int(11) NOT NULL,
  `frm_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `remark` mediumtext NOT NULL,
  `remarkDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `remark`
--

INSERT INTO `remark` (`id`, `frm_id`, `status`, `remark`, `remarkDate`) VALUES
(15, 15, 'rejected', 'The order was cancelled because it was unavailable', '2025-07-20 07:13:50'),
(16, 15, 'rejected', 'Unavailable', '2025-07-20 07:14:27'),
(17, 15, 'closed', 'The food is being delivered', '2025-07-20 07:14:53'),
(18, 17, 'rejected', 'unavailable', '2025-07-20 07:15:53'),
(19, 24, 'in process', 'Your food is on the way\r\n', '2025-08-05 16:56:24');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `rs_id` int(222) NOT NULL,
  `c_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(222) NOT NULL,
  `url` varchar(222) NOT NULL,
  `o_hr` varchar(222) NOT NULL,
  `c_hr` varchar(222) NOT NULL,
  `o_days` varchar(222) NOT NULL,
  `address` text NOT NULL,
  `image` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`rs_id`, `c_id`, `title`, `email`, `phone`, `url`, `o_hr`, `c_hr`, `o_days`, `address`, `image`, `date`) VALUES
(1, 1, 'Classic Stakehouse', 'Stakehouse25@gmail.com', '0768343346', 'www.stakehouse.com', '6am', '3am', '24hr-x7', 'Outering Road', '6875914636070.png', '2025-07-20 06:00:35');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_locations`
--

CREATE TABLE `restaurant_locations` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(222) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` text DEFAULT NULL,
  `delivery_radius` decimal(5,2) DEFAULT 5.00 COMMENT 'Free delivery radius in km',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_locations`
--

INSERT INTO `restaurant_locations` (`id`, `restaurant_id`, `latitude`, `longitude`, `address`, `delivery_radius`, `created_at`) VALUES
(1, 1, -1.29210000, 36.82190000, 'Nairobi, Kenya', 5.00, '2025-08-05 16:42:43'),
(2, 2, -1.29210000, 36.82190000, 'Nairobi, Kenya', 5.00, '2025-08-05 16:42:43'),
(3, 3, -1.29210000, 36.82190000, 'Nairobi, Kenya', 5.00, '2025-08-05 16:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `res_category`
--

CREATE TABLE `res_category` (
  `c_id` int(222) NOT NULL,
  `c_name` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `res_category`
--

INSERT INTO `res_category` (`c_id`, `c_name`, `date`) VALUES
(1, 'Kenyan', '2025-07-14 23:09:14'),
(2, 'StakeGrill', '2025-07-20 15:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'google_maps_api_key', '', 'Google Maps API Key for location services', '2025-08-05 16:42:43'),
(2, 'mpesa_consumer_key', '', 'M-Pesa Consumer Key', '2025-08-05 16:42:43'),
(3, 'mpesa_consumer_secret', '', 'M-Pesa Consumer Secret', '2025-08-05 16:42:43'),
(4, 'mpesa_passkey', '', 'M-Pesa Passkey', '2025-08-05 16:42:43'),
(5, 'mpesa_environment', 'sandbox', 'M-Pesa Environment (sandbox/live)', '2025-08-05 16:42:43'),
(6, 'default_delivery_fee', '50.00', 'Default delivery fee in KES (base rate)', '2025-08-05 17:16:27'),
(7, 'free_delivery_radius', '5.00', 'Free delivery radius in km (within this distance delivery is free)', '2025-08-05 17:16:27'),
(8, 'max_delivery_distance', '25.00', 'Maximum delivery distance in km', '2025-08-05 17:16:27'),
(9, 'per_km_rate', '10.00', 'Rate per kilometer for delivery fee calculation', '2025-08-05 17:16:27'),
(10, 'zone_standard_multiplier', '1.0', 'Multiplier for standard delivery zone (5-10km)', '2025-08-05 17:16:27'),
(11, 'zone_extended_multiplier', '1.5', 'Multiplier for extended delivery zone (10-15km)', '2025-08-05 17:16:27'),
(12, 'zone_premium_multiplier', '2.0', 'Multiplier for premium delivery zone (15km+)', '2025-08-05 17:16:27'),
(13, 'delivery_zones_enabled', '1', 'Enable delivery zones feature (1=yes, 0=no)', '2025-08-05 17:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(222) NOT NULL,
  `username` varchar(222) NOT NULL,
  `f_name` varchar(222) NOT NULL,
  `l_name` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `address` text NOT NULL,
  `status` int(222) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `f_name`, `l_name`, `email`, `phone`, `password`, `address`, `status`, `date`) VALUES
(9, 'William', 'Harry', 'Denville', 'clempiris@gmail.com', '+1 46 973 90 55', '92ee3f8e5822c52948fb231e265bc2b2', 'Kasarani Seasons', 1, '2025-07-20 15:40:54'),
(10, 'John', 'Williams', 'Foland', 'willyjohn@gmail.com', '0745896428', '9a3e7e82f68ca7f2421d6b06a72d943a', 'Tsavo royal suburbs', 1, '2025-08-05 17:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `users_orders`
--

CREATE TABLE `users_orders` (
  `o_id` int(222) NOT NULL,
  `u_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `quantity` int(222) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `customer_latitude` decimal(10,8) DEFAULT NULL,
  `customer_longitude` decimal(11,8) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `estimated_delivery_time` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash',
  `payment_status` varchar(50) DEFAULT 'pending',
  `mpesa_transaction_id` varchar(100) DEFAULT NULL,
  `status` varchar(222) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users_orders`
--

INSERT INTO `users_orders` (`o_id`, `u_id`, `title`, `quantity`, `price`, `delivery_fee`, `delivery_address`, `customer_latitude`, `customer_longitude`, `driver_id`, `estimated_delivery_time`, `payment_method`, `payment_status`, `mpesa_transaction_id`, `status`, `date`) VALUES
(13, 7, 'Samosa (Beef/Veg)', 1, 40.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, '2025-07-15 19:05:48'),
(17, 8, 'Chicken Deep Fry', 1, 200.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, 'rejected', '2025-07-20 07:15:53'),
(18, 8, 'Rice Beef', 1, 230.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, '2025-07-20 07:10:40'),
(19, 8, 'Chips Nyama', 1, 280.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, '2025-07-20 07:10:40'),
(20, 8, 'Rice Beef', 1, 230.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, '2025-07-20 07:34:02'),
(21, 8, 'Mahamri/Ndazi', 1, 10.00, 0.00, NULL, NULL, NULL, NULL, NULL, 'cash', 'pending', NULL, NULL, '2025-07-20 07:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_locations`
--

CREATE TABLE `user_locations` (
  `id` int(11) NOT NULL,
  `user_id` int(222) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adm_id`);

--
-- Indexes for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_phone` (`phone`);

--
-- Indexes for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `remark`
--
ALTER TABLE `remark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`rs_id`);

--
-- Indexes for table `restaurant_locations`
--
ALTER TABLE `restaurant_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `res_category`
--
ALTER TABLE `res_category`
  ADD PRIMARY KEY (`c_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `users_orders`
--
ALTER TABLE `users_orders`
  ADD PRIMARY KEY (`o_id`);

--
-- Indexes for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adm_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `d_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mpesa_transactions`
--
ALTER TABLE `mpesa_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remark`
--
ALTER TABLE `remark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `rs_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `restaurant_locations`
--
ALTER TABLE `restaurant_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `res_category`
--
ALTER TABLE `res_category`
  MODIFY `c_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users_orders`
--
ALTER TABLE `users_orders`
  MODIFY `o_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_locations`
--
ALTER TABLE `user_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
