-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 17, 2025 at 10:02 AM
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
-- Table structure for table `productcategory`
--

DROP TABLE IF EXISTS `productcategory`;
CREATE TABLE IF NOT EXISTS `productcategory` (
  `Category_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Description` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  PRIMARY KEY (`Category_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productcategory`
--

INSERT INTO `productcategory` (`Category_ID`, `Name`, `Description`, `Created_at`, `Updated_at`) VALUES
(1, 'Supplements', '', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(2, 'Nutrition', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(3, 'Wellness', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(4, 'Accessories', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(5, 'Gear', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(6, 'Cardio', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(7, 'Training', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(8, 'Apparel', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(9, 'Recovery', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(10, 'Energy', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(11, 'Hydration', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(12, 'Weight-loss', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38'),
(13, 'Vegan', '', '2025-12-16 19:02:38', '2025-12-16 19:02:38');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
