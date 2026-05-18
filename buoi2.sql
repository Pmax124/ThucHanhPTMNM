-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
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

-- Dumping structure for table my_store.category
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.category: ~6 rows (approximately)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
	(1, 'Điện thoại', 'Danh mục các loại điện thoại'),
	(2, 'Laptop', 'Danh mục các loại laptop'),
	(3, 'Máy tính bảng', 'Danh mục các loại máy tính bảng'),
	(4, 'Phụ kiện', 'Danh mục phụ kiện điện tử'),
	(5, 'Thiết bị âm thanh', 'Danh mục loa, tai nghe, micro'),
	(6, 'Đồng hồ ', 'Thiết bị đeo tay');

-- Dumping structure for table my_store.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.product: ~3 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`, `updated_at`) VALUES
	(1, 'IP 17 123455', 'Sang trọng, quý phái', 17000000.00, 'uploads/iphone-17e-256gb-hong-thumb-600x600.jpg', 1, '2026-05-18 02:07:37', '2026-05-18 04:02:54'),
	(3, 'Samsung', 'Công nghệ mới, hiện đại ', 14000000.00, 'uploads/samsung-galaxy-a36-5g-green-thumb-600x600.jpg', 1, '2026-05-18 02:07:37', '2026-05-18 04:02:27'),
	(5, 'MSI 124154', 'Hiệu năng vượt trội', 35000000.00, 'uploads/msi-gaming-katana-15-hx-b14wgk-i7-14650hx-023vn-thumb-638992308782855954-600x600.jpg', 2, '2026-05-18 03:32:36', '2026-05-18 04:02:03'),
	(6, 'IP 13 453636', 'Sang trọng, sắc sảo', 25000000.00, 'uploads/iphone-17e-512gb-den-thumb-1-600x600.jpg', 1, '2026-05-18 04:01:50', '2026-05-18 04:01:50'),
	(7, 'Đồng hồ EDIFICE ', 'Năng động, trẻ trung', 7000000.00, 'uploads/edifice-eqb-1200hg-1adr-nam-1-fix-750x500.jpg', 6, '2026-05-18 04:04:53', '2026-05-18 04:04:53'),
	(8, 'Loa Bluetooth JBL Partybox Encore 2Mic', 'Âm thanh trong trẻo', 5000000.00, 'uploads/loa-bluetooth-jbl-partybox-encore-2mic-5-750x500.jpg', 5, '2026-05-18 04:06:13', '2026-05-18 04:06:13'),
	(9, 'Đồng hồ MWC', 'lịch lãm, sang trọng', 3000000.00, 'uploads/mvw-ml090-02-nam-1-638702194498133609-750x500.jpg', 6, '2026-05-18 04:08:12', '2026-05-18 04:08:31'),
	(10, 'Laptop Dell 15 DC15255', 'Nhẹ, hiệu năng ổn định', 22000000.00, 'uploads/dell-15-dc15255-r5-7530u-dc5r5802w1-thumb-638920698565049808-600x600.jpg', 2, '2026-05-18 04:10:04', '2026-05-18 04:10:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.reviews: ~2 rows (approximately)
INSERT INTO `reviews` (`id`, `product_id`, `customer_name`, `customer_email`, `rating`, `comment`, `created_at`) VALUES
	(1, 1, 'Phúc', 'phuc@gmail.com', 5, 'Sản phẩm tốt', '2026-05-18 02:49:25'),
	(2, 1, 'Phúc Mai', 'MaiPhuc@gmai.com', 1, 'Sản phẩm tệ', '2026-05-18 02:51:12');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
