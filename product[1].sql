-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 17, 2025 at 09:59 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `power gym`
--

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `Product_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Description` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Brand` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Sku` int NOT NULL,
  `Price` float NOT NULL,
  `Cost_price` float NOT NULL,
  `Tax_rate` float NOT NULL,
  `is_active` int NOT NULL,
  `thumbnail_url` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `Category_ID` int NOT NULL,
  `rating` float NOT NULL DEFAULT '0',
  `reviews` int NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '0',
  `is_sale` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Product_ID`),
  KEY `Category_ID` (`Category_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`Product_ID`, `Name`, `Description`, `Brand`, `Sku`, `Price`, `Cost_price`, `Tax_rate`, `is_active`, `thumbnail_url`, `created_at`, `updated_at`, `Category_ID`, `rating`, `reviews`, `is_new`, `is_sale`) VALUES
(1, 'Creatine Monohydrate', 'Increases strength & muscle mass', '', 1, 25, 15, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.5, 128, 0, 1),
(2, 'Whey Protein', 'Muscle recovery & growth', '', 2, 40, 24, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.8, 256, 0, 0),
(3, 'Mass Gainer', 'Weight gain & energy boost', '', 3, 55, 33, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.3, 92, 1, 0),
(4, 'BCAA Powder', 'Muscle endurance & recovery', '', 4, 35, 21, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.6, 184, 0, 0),
(5, 'Pre-Workout', 'Energy & focus boost', '', 5, 30, 18, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.7, 215, 1, 1),
(6, 'L-Carnitine', 'Fat burning metabolizer', '', 6, 28, 16.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 17:56:28', '2025-12-16 17:56:28', 1, 4.2, 76, 0, 0),
(7, 'Protein Bars (Pack)', 'Healthy protein snacks', '', 7, 18, 10.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 2, 4.4, 142, 0, 0),
(8, 'Collagen Powder', 'Joint & skin support', '', 8, 33, 19.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 1, 4.5, 98, 1, 0),
(9, 'Fish Oil Capsules', 'Heart & inflammation support', '', 9, 19, 11.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 3, 4.6, 167, 0, 0),
(10, 'Gym Gloves', 'Anti-slip training gloves', '', 10, 15, 9, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.3, 64, 0, 0),
(11, 'Wrist Wraps', 'Support for heavy lifting', '', 11, 12, 7.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.7, 112, 0, 1),
(12, 'Knee Sleeves', 'Squats & leg support', '', 12, 22, 13.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.5, 89, 0, 0),
(13, 'Resistance Bands', 'Stretching & mobility', '', 13, 17, 10.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 5, 4.4, 145, 1, 0),
(14, 'Shaker Bottle', 'Protein mixing bottle', '', 14, 9, 5.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 2, 4.8, 203, 0, 0),
(15, 'Gym Bag', 'Carry all your gym stuff', '', 15, 27, 16.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 5, 4.6, 78, 0, 0),
(16, 'Jump Rope', 'Speed rope for fat-loss', '', 16, 11, 6.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 6, 4.3, 102, 0, 1),
(17, 'Push-Up Bars', 'Better push-up depth', '', 17, 14, 8.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 7, 4.5, 87, 0, 0),
(18, 'Perfomance Socks', 'Breathable ankle socks', '', 18, 8, 4.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 8, 4.2, 55, 0, 0),
(19, 'Compression Shirt', 'Sweat fit training shirt', '', 19, 16, 9.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 8, 4.4, 134, 1, 0),
(20, 'Compression Shorts', 'Stretch fit gym shorts', '', 20, 14, 8.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 8, 4.6, 121, 0, 0),
(21, 'Tank Top', 'Breathable sleeveless', '', 21, 10, 6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 8, 4.3, 68, 0, 0),
(22, 'Gym Towel', 'Microfiber gym towel', '', 22, 6, 3.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.7, 95, 0, 0),
(23, 'Elbow Sleeves', 'Press support', '', 23, 13, 7.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.4, 77, 0, 0),
(24, 'Hand Grippers', 'Grip strength trainer', '', 24, 7, 4.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 7, 4.5, 63, 1, 1),
(25, 'Multivitamins', 'Daily essential vitamins', '', 25, 21, 12.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 3, 4.6, 189, 0, 0),
(26, 'ZMA Capsules', 'Sleep & recovery', '', 26, 24, 14.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 9, 4.4, 106, 0, 0),
(27, 'Casein Protein', 'Night protein support', '', 27, 37, 22.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 1, 4.7, 198, 0, 0),
(28, 'Caffeine Pills', 'Pure energy boost', '', 28, 14, 8.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 10, 4.2, 82, 1, 0),
(29, 'Electrolyte Mix', 'Hydration salts', '', 29, 10, 6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 11, 4.5, 71, 0, 0),
(30, 'Glutamine Powder', 'Reduces soreness', '', 30, 26, 15.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 9, 4.3, 93, 0, 1),
(31, 'Fat Burner', 'Thermogenic fat loss', '', 31, 29, 17.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 12, 4.6, 156, 1, 0),
(32, 'Omega-3', 'Premium fish oil', '', 32, 22, 13.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 3, 4.7, 174, 0, 0),
(33, 'Protein Pancake Mix', 'High protein pancakes', '', 33, 15, 9, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 2, 4.4, 68, 0, 0),
(34, 'Rice Protein', 'Plant protein', '', 34, 36, 21.6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 13, 4.5, 54, 0, 0),
(35, 'C4 Pre-Workout', 'Extreme energy', '', 35, 32, 19.2, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 1, 4.8, 267, 0, 1),
(36, 'ISO-Whey', 'Clean isolate protein', '', 36, 48, 28.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 1, 4.7, 143, 1, 0),
(37, 'Training Belt', 'Lifting belt', '', 37, 20, 12, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.5, 112, 0, 0),
(38, 'Gym Headphones', 'Bluetooth sport headphones', '', 38, 34, 20.4, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.6, 124, 0, 0),
(39, 'Sports Water Bottle', '1L water bottle', '', 39, 10, 6, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 11, 4.4, 87, 0, 0),
(40, 'Powerlifting Straps', 'Deadlift support', '', 40, 13, 7.8, 0, 1, 'https://www.maxmuscleelite.com/web/image/product.product/6759/image_1920/%5B6222023700178%5D%20Max%20Muscle%20Creatine%2099.9%25Creapure-40Serv.-200G?unique=f1f1949', '2025-12-16 19:02:38', '2025-12-16 19:02:38', 4, 4.5, 101, 1, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `productcategory` (`Category_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
