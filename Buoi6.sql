-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for my_store
CREATE DATABASE IF NOT EXISTS `my_store` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `my_store`;

-- Dumping structure for table my_store.account
CREATE TABLE IF NOT EXISTS `account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `google_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `fullname` varchar(255) NOT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `google_id` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.account: ~8 rows (approximately)
INSERT INTO `account` (`id`, `google_id`, `username`, `email`, `phone`, `address`, `fullname`, `avatar`, `password`, `reset_token`, `reset_token_expires`, `login_attempts`, `locked_until`, `role`, `status`, `created_at`, `updated_at`) VALUES
	(1, '104792073004664545850', 'phuc1555', 'phuc15052005@gmail.com', '092572572', 'Bến Lức', 'Nguyễn Hoàng Phúc', 'https://lh3.googleusercontent.com/a/ACg8ocLvuVezP-4BhfHsYgUUkOx8dOiCZFFIN-QDyFT6kGNwehrFMQ_d=s96-c', '$2y$10$GjY.mbpPrPM/UokJUXzLDeYrLZIs3YQMBN0eIgp4bBfNAS3CN.WH6', NULL, NULL, 0, NULL, 'admin', 1, '2026-06-01 02:51:36', '2026-06-07 23:37:14'),
	(2, NULL, 'Quỳnh', 'Quynh@gmail.com', '0985285624', 'Long An', 'Lê Thị Như Quỳnh', 'uploads/avatars/avatar_2_1780281456.jfif', '$2y$10$8.vgiab2YgpZyuwn94eZNO50ZKJs4u1qloZZqcoHXk4ds6Ruk/rQe', NULL, NULL, 0, NULL, 'user', 1, '2026-06-01 03:05:08', '2026-06-01 09:37:36'),
	(3, NULL, 'Duy12345', 'Duy@gmail.com', '0375735219', '1/4 Cảng 1', 'Trần Anh Hoàng Duy', 'uploads/avatars/avatar_3_1780282185.jfif', '$2y$10$yskAOrVUr58kQJEOX7NB3OXlyo6y/X52zRlWTReiI2VvKIzSOWLL.', NULL, NULL, 0, NULL, 'user', 1, '2026-06-01 03:11:29', '2026-06-01 09:49:45'),
	(4, '113436201051867320941', 'google_c1d48e57', 'nguyenhoangphuc15.05.05@gmail.com', '0978753553', 'Tây Ninh', 'Phúc Nguyễn', 'https://lh3.googleusercontent.com/a/ACg8ocI3RLWn0OnXl1xfgxoH0mLv2omx89EP-oKWibKzJvkpdPxHkw=s96-c', '$2y$10$QHKgeZbVl/quwnHrgNns8.WJvcNAkLxTaLxGUvk2P1iYFOiH8ZVM6', NULL, NULL, 0, NULL, 'user', 1, '2026-06-01 04:04:18', '2026-06-07 23:39:01'),
	(6, NULL, 'Van@gmail.com', 'Van@gmail.com', '0985765635', 'Thủ Đức', 'Phong Văn', 'uploads/avatars/avatar_6_1780850191.jpg', '$2y$10$Scms8.MJHgibIc2xQ5DbuuPnINxopaGtlUWHLJluqnIDB/KIeSzdS', NULL, NULL, 0, NULL, 'user', 1, '2026-06-07 13:21:52', '2026-06-08 02:08:41'),
	(9, NULL, 'admin', 'admin@example.com', '0901234567', 'Hà Nội', 'Administrator', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, NULL, 'admin', 1, '2026-06-14 01:57:53', '2026-06-14 01:57:53'),
	(10, NULL, 'user', 'user@example.com', '0909876543', 'TP.HCM', 'Normal User', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 0, NULL, 'user', 1, '2026-06-14 01:57:53', '2026-06-14 01:57:53'),
	(12, NULL, 'AAA', 'A@gmail.com', '034966363', '123 Thủ Đức', 'Nguyễn Văn A', NULL, '$2y$10$vMlKBv6v1a5cLudwIXncXudAOiUoRphrRGZghL9FNp5iWXsZTv4FK', NULL, NULL, 0, NULL, 'user', 1, '2026-06-14 22:05:39', '2026-06-14 22:44:23');

-- Dumping structure for table my_store.brand
CREATE TABLE IF NOT EXISTS `brand` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.brand: ~0 rows (approximately)

-- Dumping structure for table my_store.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.cart: ~0 rows (approximately)

-- Dumping structure for table my_store.category
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.category: ~9 rows (approximately)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
	(1, 'Điện thoại', 'Danh mục các loại điện thoại'),
	(2, 'Laptop', 'Danh mục các loại laptop'),
	(3, 'Máy tính bảng', 'Danh mục các loại máy tính bảng'),
	(4, 'Phụ kiện', 'Danh mục phụ kiện điện tử'),
	(5, 'Thiết bị âm thanh', 'Danh mục loa, tai nghe, micro'),
	(6, 'Đồng hồ ', 'Thiết bị đeo tay'),
	(7, 'PC', 'Máy tính để bàn'),
	(8, 'Tai Nghe', 'Thiết bị nghe âm thanh'),
	(9, 'Bàn phím', 'Thiết bị đánh chữ');

-- Dumping structure for table my_store.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `note` text,
  `payment_method` varchar(50) DEFAULT 'cod',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) DEFAULT '0.00',
  `voucher_code` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.orders: ~6 rows (approximately)
INSERT INTO `orders` (`id`, `name`, `phone`, `email`, `city`, `address`, `note`, `payment_method`, `total_amount`, `discount_amount`, `voucher_code`, `status`, `created_at`, `updated_at`) VALUES
	(6, 'Pmaxx', '0375735219', 'phuc15052005@gmail.com', 'TP. Hồ Chí Minh', 'Tây Ninh', NULL, 'cod', 3000000.00, 0.00, NULL, 'pending', '2026-05-29 21:44:28', '2026-05-30 20:47:18'),
	(7, 'PinkThanos', '034693663', 'pink@gmail.com', 'Đà Nẵng', 'Đằng Nẵng', '1111111111111111', 'cod', 12000000.00, 0.00, NULL, 'completed', '2026-05-30 20:24:52', '2026-05-30 20:47:39'),
	(8, 'PinkThanos', '0797897897', 'pink@gmail.com', 'Đà Nẵng', 'Tây Ninh', NULL, 'cod', 12000000.00, 1080000.00, 'SALEFBYNQQQ4', 'pending', '2026-05-30 21:45:45', '2026-05-30 21:45:45'),
	(9, 'Quỳnh', '098252556', 'Quynh@gmail.com', 'TP. Hồ Chí Minh', 'Tây Ninh', NULL, 'cod', 24000000.00, 2160000.00, 'SALEFBYNQQQ4', 'pending', '2026-05-30 23:05:52', '2026-05-30 23:05:52'),
	(10, 'Pmaxx', '0375735219', 'phuc15052005@gmail.com', NULL, 'Tây Ninh', NULL, 'cod', 12000000.00, 0.00, NULL, 'pending', '2026-05-31 00:05:14', '2026-05-31 00:05:14'),
	(12, 'Normal User', '0909876543', 'user@example.com', '', 'TP.HCM', 'Giao nhanh', 'cod', 34000000.00, 0.00, '', 'completed', '2026-06-14 14:11:32', '2026-06-14 14:24:18'),
	(13, 'Nguyễn Văn A', '034966363', 'A@gmail.com', '', '123 Thủ Đức', 'Giao nhanh', 'cod', 34000000.00, 0.00, '', 'completed', '2026-06-14 15:25:55', '2026-06-14 15:27:22');

-- Dumping structure for table my_store.order_details
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.order_details: ~7 rows (approximately)
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
	(6, 6, 9, 1, 3000000.00),
	(7, 7, 11, 1, 12000000.00),
	(8, 8, 11, 1, 12000000.00),
	(9, 9, 11, 2, 12000000.00),
	(10, 10, 11, 1, 12000000.00);

-- Dumping structure for table my_store.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.password_resets: ~0 rows (approximately)
INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
	(6, 'Van@gmail.com', '8419f694d3a78c76baa039c0386d072faee8860ca15b53926ee7643b4be535f6', '2026-06-08 03:08:29', 1, '2026-06-07 12:08:29');

-- Dumping structure for table my_store.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_code` varchar(50) NOT NULL,
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cod','transfer','momo','zalopay') DEFAULT 'cod',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_code` (`payment_code`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.payments: ~0 rows (approximately)
INSERT INTO `payments` (`id`, `payment_code`, `order_id`, `user_id`, `amount`, `payment_method`, `status`, `transaction_id`, `paid_at`, `notes`, `created_at`) VALUES
	(1, 'PAY-20260614-944092', 12, 10, 34000000.00, 'cod', 'completed', NULL, '2026-06-14 14:24:18', '', '2026-06-14 14:19:55'),
	(2, 'PAY-20260614-890097', 13, 12, 34000000.00, 'cod', 'completed', NULL, '2026-06-14 15:27:22', '', '2026-06-14 15:26:24');

-- Dumping structure for table my_store.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '100',
  `image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.product: ~9 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_id`, `created_at`, `updated_at`) VALUES
	(1, 'IP 17 123455', 'Sang trọng, quý phái', 17000000.00, 100, 'uploads/iphone-17e-256gb-hong-thumb-600x600.jpg', 1, '2026-05-16 08:07:37', '2026-05-16 10:02:54'),
	(3, 'Samsung', 'Công nghệ mới, hiện đại ', 26000000.00, 100, 'uploads/samsung-galaxy-a36-5g-green-thumb-600x600.jpg', 1, '2026-05-16 08:07:37', '2026-06-07 09:31:52'),
	(5, 'MSI 124154', 'Hiệu năng vượt trội', 35000000.00, 100, 'uploads/msi-gaming-katana-15-hx-b14wgk-i7-14650hx-023vn-thumb-638992308782855954-600x600.jpg', 2, '2026-05-16 09:32:36', '2026-05-16 10:02:03'),
	(6, 'IP 13 453636', 'Sang trọng, sắc sảo', 25000000.00, 100, 'uploads/iphone-17e-512gb-den-thumb-1-600x600.jpg', 1, '2026-05-16 10:01:50', '2026-05-16 10:01:50'),
	(7, 'Đồng hồ EDIFICE ', 'Năng động, trẻ trung', 7000000.00, 100, 'uploads/edifice-eqb-1200hg-1adr-nam-1-fix-750x500.jpg', 6, '2026-05-16 10:04:53', '2026-05-16 10:04:53'),
	(8, 'Loa Bluetooth JBL Partybox Encore 2Mic', 'Âm thanh trong trẻo', 5000000.00, 100, 'uploads/loa-bluetooth-jbl-partybox-encore-2mic-5-750x500.jpg', 5, '2026-05-16 10:06:13', '2026-05-16 10:06:13'),
	(9, 'Đồng hồ MWC', 'lịch lãm, sang trọng', 3000000.00, 100, 'uploads/mvw-ml090-02-nam-1-638702194498133609-750x500.jpg', 6, '2026-05-16 10:08:12', '2026-05-16 10:08:31'),
	(10, 'Laptop Dell 15 DC15255', 'Nhẹ, hiệu năng ổn định', 22000000.00, 100, 'uploads/dell-15-dc15255-r5-7530u-dc5r5802w1-thumb-638920698565049808-600x600.jpg', 2, '2026-05-16 10:10:04', '2026-05-16 10:10:04'),
	(11, 'Đồng hồ CITIZEN Mechanical 40 mm Nam NH8353-00H', 'Sang trọng, lịch lãm ', 12000000.00, 100, 'uploads/1779675124_6a13aff4d9077.jpg', 6, '2026-05-23 22:12:04', '2026-05-23 22:12:04'),
	(22, 'Samsung Galaxy S24', 'Điện thoại cao cấp', 26000000.00, 100, 'uploads/samsung-galaxy-s24_20__1.webp', 1, '2026-06-14 15:48:03', '2026-06-14 15:48:03');

-- Dumping structure for table my_store.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.product_images: ~3 rows (approximately)
INSERT INTO `product_images` (`id`, `product_id`, `image_path`) VALUES
	(1, 11, 'uploads/1779675124_6a13aff4d9077.jpg'),
	(2, 11, 'uploads/1779675124_6a13aff4db268.jpg'),
	(3, 11, 'uploads/1779675124_6a13aff4db638.jpg');

-- Dumping structure for table my_store.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.reviews: ~3 rows (approximately)
INSERT INTO `reviews` (`id`, `product_id`, `customer_name`, `customer_email`, `rating`, `comment`, `created_at`) VALUES
	(1, 1, 'Phúc', 'phuc@gmail.com', 5, 'Sản phẩm tốt', '2026-05-16 08:49:25'),
	(2, 1, 'Phúc Mai', 'MaiPhuc@gmai.com', 1, 'Sản phẩm tệ', '2026-05-16 08:51:12'),
	(3, 11, 'Phúc', 'Phuc@gmail.com', 5, 'Sản phâm tốt', '2026-05-23 22:16:43');

-- Dumping structure for table my_store.vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.vouchers: ~0 rows (approximately)
INSERT INTO `vouchers` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
	(1, 'SALEFBYNQQQ4', 'qqqqqqqqqqqqqqq', 'percent', 9.00, 9999000.00, 5000000.00, NULL, 2, '2026-06-01 01:39:00', '2026-06-06 01:39:00', 1, '2026-05-30 21:39:36');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
