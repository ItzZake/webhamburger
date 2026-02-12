-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2026 at 10:44 PM
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
-- Database: `power gym`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `Address_ID` int(11) NOT NULL,
  `Label` varchar(50) NOT NULL,
  `Full_Name` varchar(100) NOT NULL,
  `Phone_Number` int(11) NOT NULL,
  `Address_line1` varchar(100) NOT NULL,
  `Address_line2` varchar(100) NOT NULL,
  `City` varchar(50) NOT NULL,
  `Governorate` varchar(50) NOT NULL,
  `Postal_code` int(11) NOT NULL,
  `Is_Default_Shipping` int(11) NOT NULL,
  `Is_Default_Billing` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adminactionlog`
--

CREATE TABLE `adminactionlog` (
  `Log_ID` int(11) NOT NULL,
  `Target_Entity_Type` varchar(50) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `Action_Type` varchar(500) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Admin_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminactionlog`
--

INSERT INTO `adminactionlog` (`Log_ID`, `Target_Entity_Type`, `Description`, `Action_Type`, `Created_at`, `Admin_ID`, `is_deleted`) VALUES
(1, 'member', 'Added member Please Work', 'add', '2025-12-16 22:32:25', 1, 0),
(2, 'member', 'Added member cabron Work', 'add', '2025-12-16 22:32:44', 1, 0),
(3, 'member', 'added member Please ugly', 'add', '2025-12-16 22:35:05', 1, 0),
(4, 'coach', 'added coach Micheal Johnson', 'add', '2025-12-16 22:38:03', 1, 0),
(5, 'member', 'deleted member with ID 12', 'delete', '2025-12-16 22:40:29', 1, 0),
(6, 'member', 'deleted member with ID 11', 'delete', '2025-12-16 22:40:31', 1, 0),
(7, 'member', 'deleted member with ID 10', 'delete', '2025-12-16 22:40:32', 1, 0),
(8, 'member', 'deleted member with ID 9', 'delete', '2025-12-16 22:40:34', 1, 0),
(9, 'member', 'deleted member with ID 8', 'delete', '2025-12-16 22:40:41', 1, 0),
(10, 'member', 'updated member with ID 2', 'update', '2025-12-16 22:44:54', 1, 0),
(11, 'nutritionist', 'added nutritionist Phellip gustavo', 'add', '2025-12-16 22:48:21', 1, 0),
(12, 'member', 'deleted member with ID 14', 'delete', '2025-12-16 22:48:43', 1, 0),
(13, 'nutritionist', 'deleted nutritionist with ID 14', 'delete', '2025-12-16 22:52:41', 1, 0),
(14, 'coach', 'deleted coach with ID 13', 'delete', '2025-12-16 22:54:21', 1, 0),
(15, 'member', 'updated member with ID 4', 'update', '2025-12-17 00:50:33', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `adminprofile`
--

CREATE TABLE `adminprofile` (
  `Admin_ID` int(11) NOT NULL,
  `Job_Title` varchar(50) NOT NULL,
  `Can_Manage_Users` int(11) NOT NULL,
  `Can_Manage_Memberships` int(11) NOT NULL,
  `Can_Manage_Store` int(11) NOT NULL,
  `Can_Manage_Nutritionists` int(11) NOT NULL,
  `Can_Manage_Coaches` int(11) NOT NULL,
  `Can_Manage_Appointments` int(11) NOT NULL,
  `Can_View_Reports` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `Can_Receive_Contact_Messages` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminprofile`
--

INSERT INTO `adminprofile` (`Admin_ID`, `Job_Title`, `Can_Manage_Users`, `Can_Manage_Memberships`, `Can_Manage_Store`, `Can_Manage_Nutritionists`, `Can_Manage_Coaches`, `Can_Manage_Appointments`, `Can_View_Reports`, `Created_at`, `Updated_at`, `is_deleted`, `Can_Receive_Contact_Messages`) VALUES
(1, 'Store Manager', 1, 1, 1, 1, 1, 1, 1, '2025-12-12 20:56:16', '2025-12-12 20:56:16', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `Appointment_ID` int(11) NOT NULL,
  `Appointment_Type` varchar(50) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Notes_From_Member` varchar(300) NOT NULL,
  `Location_Details` varchar(100) NOT NULL,
  `Location_Type` varchar(100) NOT NULL,
  `Notes_From_Staff_After_Session` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Staff_User_ID` int(11) NOT NULL,
  `Availability_ID` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `Cart_ID` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cartitem`
--

CREATE TABLE `cartitem` (
  `Cart_Item_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Unit_price_at_add_time` int(11) NOT NULL,
  `Subtotal_amount` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Cart_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coachprofile`
--

CREATE TABLE `coachprofile` (
  `Coach_ID` int(11) NOT NULL,
  `Bio` varchar(500) NOT NULL,
  `Certifications` varchar(500) NOT NULL,
  `rating_count` float NOT NULL,
  `Avg_rating` int(11) NOT NULL,
  `Is_Accepting_new` int(11) NOT NULL,
  `Max_Clients` int(11) NOT NULL,
  `Specialization_Main` varchar(100) NOT NULL,
  `Specialization_Other` varchar(100) NOT NULL,
  `Youtube_Url` varchar(500) NOT NULL,
  `Instagram_Url` varchar(500) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Updated_At` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coachprofile`
--

INSERT INTO `coachprofile` (`Coach_ID`, `Bio`, `Certifications`, `rating_count`, `Avg_rating`, `Is_Accepting_new`, `Max_Clients`, `Specialization_Main`, `Specialization_Other`, `Youtube_Url`, `Instagram_Url`, `Created_At`, `Updated_At`, `is_deleted`) VALUES
(3, 'Bruh Moment IDK', 'i dont got any', 30, 9, 1, 10, 'BodyBuilding', '', '', '', '2025-12-12 23:34:45', '2025-12-12 23:34:45', 0),
(5, 'i love coaching', '', 0, 0, 0, 0, '', '', '', '', '2025-12-16 19:19:48', '2025-12-16 19:19:48', 0),
(13, 'fagag', 'ga', 0, 0, 0, 0, 'agsdag', '', '', '', '2025-12-16 22:38:03', '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `contactmessage`
--

CREATE TABLE `contactmessage` (
  `Contact_ID` int(11) NOT NULL,
  `Full_Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Message` text NOT NULL,
  `Submitted_At` datetime DEFAULT current_timestamp(),
  `Is_Read` int(11) DEFAULT 0,
  `Read_At` datetime DEFAULT NULL,
  `Admin_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contactmessage`
--

INSERT INTO `contactmessage` (`Contact_ID`, `Full_Name`, `Email`, `Message`, `Submitted_At`, `Is_Read`, `Read_At`, `Admin_ID`) VALUES
(1, 'Test User', 'test@example.com', 'This is a test message', '2025-12-20 09:55:17', 0, NULL, NULL),
(2, 'Test', 'test@test.com', 'Test message', '2025-12-20 09:55:54', 0, NULL, NULL),
(3, 'Test User 1766217636', 'test1766217636@example.com', 'This is a test message at 2025-12-20 09:00:36', '2025-12-20 10:00:36', 0, NULL, NULL),
(4, 'Test User 1766217649', 'test1766217649@example.com', 'This is a test message at 2025-12-20 09:00:49', '2025-12-20 10:00:49', 0, NULL, NULL),
(5, 'Test', 'test@test.com', 'Test message', '2025-12-20 10:06:19', 0, NULL, NULL),
(7, 'Ahmed Maher', 'Ahmedmaher500024@gmail.com', 'OK GARMINNNNNN', '2025-12-20 10:08:01', 1, '2025-12-23 23:10:33', NULL),
(8, 'Test User', 'test@example.com', 'Test message', '2025-12-20 10:09:17', 0, NULL, NULL),
(12, 'Final Test', 'final@example.com', 'Final test message', '2025-12-20 10:12:59', 1, '2025-12-20 10:18:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `Conversation_ID` int(11) NOT NULL,
  `Conversation_Type` varchar(50) NOT NULL,
  `Is_archived` int(11) NOT NULL,
  `Last_message_at` date NOT NULL,
  `unread_count_member` int(11) NOT NULL,
  `unread_count_staff` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Staff_User_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `corder`
--

CREATE TABLE `corder` (
  `Order_ID` int(11) NOT NULL,
  `Order_date` date NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Total_items` int(11) NOT NULL,
  `Subtotal_amount` float NOT NULL,
  `tax_amount` float NOT NULL,
  `shipping` varchar(100) NOT NULL,
  `discount_amount` float NOT NULL,
  `total_amount` float NOT NULL,
  `currency` int(11) NOT NULL,
  `notes` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercise`
--

CREATE TABLE `exercise` (
  `Exercise_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Difficultly` varchar(50) NOT NULL,
  `Target_Muscle_Group` varchar(50) NOT NULL,
  `Secondary_Muscles` varchar(50) NOT NULL,
  `Instuctions` varchar(200) NOT NULL,
  `Equipment_Required` varchar(200) NOT NULL,
  `Video_url` varchar(200) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise`
--

INSERT INTO `exercise` (`Exercise_ID`, `Name`, `Description`, `Difficultly`, `Target_Muscle_Group`, `Secondary_Muscles`, `Instuctions`, `Equipment_Required`, `Video_url`, `Created_at`, `Updated_at`, `is_deleted`) VALUES
(1, 'Bodyweight Squats', 'A fundamental lower body exercise that targets the quadriceps, glutes, and hamstrings. It builds fou', 'Beginner', 'Quadriceps', 'Glutes, Hamstrings, Calves', 'Stand with your feet shoulder-width apart, toes pointing slightly outwards. Keep your chest up and core engaged. Initiate the movement by pushing your hips back, then bending your knees as if sitting ', 'Bodyweight', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(2, 'Knee Push-ups', 'A modified push-up suitable for beginners to build chest, shoulder, and tricep strength before progr', 'Beginner', 'Chest', 'Triceps, Shoulders', 'Start on all fours, placing your hands slightly wider than shoulder-width apart and directly under your shoulders. Walk your knees back until your body forms a straight line from your head to your kne', 'Bodyweight', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(3, 'Dumbbell Bent-Over Rows', 'A compound exercise targeting the back muscles, primarily the lats, rhomboids, and traps, while also', 'Beginner', 'Lats', 'Rhomboids, Biceps, Traps', 'Hold a dumbbell in each hand with palms facing each other. Hinge forward at your hips, keeping your back straight and core engaged, until your torso is almost parallel to the floor. Let the dumbbells ', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(4, 'Dumbbell Overhead Press (Seated)', 'An excellent exercise for building strength and size in the shoulders and triceps. Performing it sea', 'Beginner', 'Shoulders', 'Triceps', 'Sit on a bench with back support or a sturdy chair, holding a dumbbell in each hand at shoulder height, palms facing forward. Your elbows should be bent and slightly in front of your body. Exhale and ', 'Dumbbells, Bench/Chair', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(5, 'Plank', 'A fantastic isometric exercise for strengthening the entire core, including the abdominals, obliques', 'Beginner', 'Core', 'Shoulders, Glutes', 'Start in a push-up position, then lower down onto your forearms. Your body should form a straight line from your head to your heels. Keep your abs tight, glutes squeezed, and avoid letting your hips s', 'Bodyweight', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(6, 'Dumbbell Goblet Squats', 'A variation of the squat where you hold one dumbbell vertically against your chest, which can help i', 'Beginner', 'Quadriceps', 'Glutes, Hamstrings, Core', 'Stand with your feet slightly wider than shoulder-width, toes pointing slightly out. Hold one dumbbell vertically with both hands against your chest. Keeping your chest up and back straight, lower int', 'Dumbbell', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(7, 'Dumbbell Bench Press (Floor Press)', 'A chest exercise performed on the floor, which limits the range of motion slightly, making it safer ', 'Beginner', 'Chest', 'Triceps, Shoulders', 'Lie on your back on the floor with your knees bent and feet flat. Hold a dumbbell in each hand, palms facing each other, above your chest with arms extended. Slowly lower the dumbbells towards your ch', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(8, 'Dumbbell Romanian Deadlifts (RDLs)', 'An excellent exercise for strengthening the hamstrings, glutes, and lower back. It emphasizes the hi', 'Beginner', 'Hamstrings', 'Glutes, Lower Back', 'Stand tall, holding a dumbbell in each hand in front of your thighs, palms facing your body. Keep a slight bend in your knees throughout the exercise. Hinge forward at your hips, pushing your glutes b', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(9, 'Dumbbell Bicep Curls', 'An isolation exercise specifically targeting the biceps, helping to build arm strength and size.', 'Beginner', 'Biceps', 'Forearms', 'Stand tall or sit on a bench, holding a dumbbell in each hand, palms facing forward, arms extended by your sides. Keeping your elbows tucked into your sides and stationary, curl the dumbbells up towar', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(10, 'Overhead Dumbbell Triceps Extension', 'An effective isolation exercise for targeting all three heads of the triceps, contributing to overal', 'Beginner', 'Triceps', 'Shoulders (stabilizers)', 'Sit or stand, holding one dumbbell with both hands (cupping one end with both palms). Extend the dumbbell overhead, arms straight. Keeping your elbows close to your head and pointing forward, slowly l', 'Dumbbell', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(11, 'Dumbbell Lunges (Alternating)', 'A unilateral leg exercise that improves balance, coordination, and targets the quadriceps, glutes, a', 'Beginner', 'Quadriceps', 'Glutes, Hamstrings, Calves, Core', 'Stand tall, holding a dumbbell in each hand by your sides. Step forward with your right leg, lowering your hips until both knees are bent at approximately a 90-degree angle. Ensure your front knee is ', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(12, 'Incline Push-ups (Hands on elevated surface)', 'An easier variation of push-ups that places less stress on the chest and triceps, making it an excel', 'Beginner', 'Chest', 'Triceps, Shoulders', 'Place your hands shoulder-width apart on a sturdy elevated surface (e.g., bench, sturdy chair, low wall). Your body should form a straight line from head to heels. Lower your chest towards the elevate', 'Elevated surface (bench, chair)', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(13, 'Dumbbell One-Arm Rows', 'A unilateral exercise that targets the back muscles, primarily the lats and rhomboids. It helps addr', 'Beginner', 'Lats', 'Rhomboids, Biceps', 'Place your left knee and left hand on a flat bench. Keep your back flat and parallel to the floor, with your right foot firmly on the ground. Hold a dumbbell in your right hand, letting it hang straig', 'Dumbbell, Bench/Chair', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(14, 'Dumbbell Lateral Raises', 'An isolation exercise that specifically targets the lateral deltoids, which are responsible for the ', 'Beginner', 'Shoulders (Lateral Deltoid)', 'Traps', 'Stand tall, holding a dumbbell in each hand by your sides, palms facing your body. Keep a slight bend in your elbows. Slowly raise the dumbbells out to the sides, keeping your arms extended, until you', 'Dumbbells', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(15, 'Crunches', 'A classic abdominal exercise that primarily targets the rectus abdominis, helping to strengthen the ', 'Beginner', 'Abdominals', 'Obliques', 'Lie on your back with your knees bent, feet flat on the floor, hip-width apart. Place your hands lightly behind your head or crossed over your chest. Engage your core, and gently lift your head and sh', 'Bodyweight', '', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 0),
(16, 'Goblet Squat', 'A foundational lower body exercise that targets the quads, glutes, and hamstrings, while also engagi', 'Beginner', 'Quadriceps', 'Glutes, Hamstrings, Core', '1. Stand with feet shoulder-width apart, toes slightly out. 2. Hold a single dumbbell vertically against your chest with both hands. 3. Brace your core and push your hips back, bending your knees to l', 'Dumbbell', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(17, 'Push-ups', 'A classic bodyweight exercise that strengthens the chest, shoulders, and triceps. Can be modified fo', 'Beginner', 'Chest', 'Triceps, Shoulders', '1. Start in a plank position with hands slightly wider than shoulder-width apart, fingers pointing forward. 2. Keep your body in a straight line from head to heels. 3. Lower your chest towards the flo', 'Bodyweight', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(18, 'Single-Arm Dumbbell Row', 'An effective exercise for building back thickness and strength, while also working the biceps and fo', 'Beginner', 'Back (Lats)', 'Biceps, Rear Deltoids, Forearms', '1. Place your left knee and left hand on a bench (or sturdy elevated surface). 2. Keep your back flat and parallel to the floor. 3. Hold a dumbbell in your right hand, letting it hang straight down. 4', 'Dumbbell, Bench (optional)', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(19, 'Dumbbell Overhead Press (Standing)', 'A compound exercise that builds strength in the shoulders and triceps, also engaging the core for st', 'Beginner', 'Shoulders', 'Triceps, Upper Chest, Core', '1. Stand with feet shoulder-width apart, holding a dumbbell in each hand at shoulder height, palms facing forward. 2. Brace your core and keep a slight bend in your knees. 3. Press the dumbbells strai', 'Dumbbells', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(20, 'Dumbbell Lunges', 'A unilateral exercise that targets the quads, glutes, and hamstrings, while improving balance and st', 'Beginner', 'Quadriceps', 'Glutes, Hamstrings, Calves, Core', '1. Stand tall with a dumbbell in each hand, arms at your sides. 2. Step forward with one leg, lowering your hips until both knees are bent at approximately a 90-degree angle. 3. Ensure your front knee', 'Dumbbells', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(21, 'Dumbbell Floor Press', 'A chest exercise performed lying on the floor, which limits the range of motion and puts less stress', 'Beginner', 'Chest', 'Triceps, Shoulders', '1. Lie on your back on the floor, knees bent and feet flat. 2. Hold a dumbbell in each hand, palms facing each other (or slightly angled), with elbows resting on the floor at your sides. 3. Press the ', 'Dumbbells', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(22, 'Resistance Band Pull-Aparts', 'An excellent exercise for strengthening the upper back, rear deltoids, and improving posture. Helps ', 'Beginner', 'Rear Deltoids', 'Upper Back, Rhomboids', '1. Stand tall with feet shoulder-width apart, holding a resistance band with both hands, palms down, at shoulder-width apart. 2. Keep your arms straight (slight bend in elbows is okay) and at chest he', 'Resistance Band', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(23, 'Lying Leg Raises', 'An effective exercise for targeting the lower abdominal muscles and building core strength.', 'Beginner', 'Lower Abs', 'Hip Flexors', '1. Lie flat on your back on the floor, arms by your sides or hands tucked slightly under your glutes for support. 2. Keep your legs straight or slightly bent at the knees. 3. Engage your core to lift ', 'Bodyweight', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(24, 'Romanian Deadlift (Dumbbell RDL)', 'A hamstring and glute-focused exercise that also strengthens the lower back. Excellent for developin', 'Beginner', 'Hamstrings', 'Glutes, Lower Back', '1. Stand with feet hip-width apart, holding a dumbbell in each hand in front of your thighs, palms facing your body. 2. Keep a slight bend in your knees, but maintain this bend throughout the movement', 'Dumbbells', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(25, 'Incline Push-ups (Elevated Surface)', 'A modified push-up that reduces the difficulty by elevating the hands. Targets the chest, shoulders,', 'Beginner', 'Chest', 'Triceps, Shoulders', '1. Stand facing a sturdy elevated surface (e.g., bench, sturdy chair, counter). 2. Place your hands on the edge of the surface, slightly wider than shoulder-width apart. 3. Step your feet back until y', 'Elevated Surface (e.g., bench, chair)', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(26, 'Resistance Band Face Pulls', 'An excellent exercise for strengthening the upper back, rear deltoids, and rotator cuff muscles, cru', 'Beginner', 'Rear Deltoids', 'Upper Back, Rotator Cuff', '1. Anchor a resistance band at chest height (e.g., around a sturdy pole, door anchor). 2. Stand a few feet away, facing the anchor, holding the band with an overhand grip (palms down), hands shoulder-', 'Resistance Band, Anchor Point', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(27, 'Bodyweight Calf Raises', 'A simple yet effective exercise to target the calf muscles, improving ankle stability and lower leg ', 'Beginner', 'Calves', 'Ankles', '1. Stand with your feet hip-width apart, perhaps holding onto a wall or chair for balance. 2. Slowly raise yourself up onto the balls of your feet, lifting your heels as high as possible. 3. Squeeze y', 'Bodyweight', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(28, 'Side Plank', 'An excellent exercise for strengthening the obliques and other core stabilizers, improving lateral c', 'Beginner', 'Obliques', 'Transverse Abdominis, Shoulders, Hips', '1. Lie on your side with your forearm on the floor, elbow directly under your shoulder. 2. Stack your feet on top of each other, or place the top foot in front of the bottom foot for easier balance. 3', 'Bodyweight', '', '2025-12-13 21:02:29', '2025-12-13 21:02:29', 0),
(29, 'Dumbbell Rows', 'An excellent exercise for targeting the muscles of the back, promoting thickness and strength.', 'Beginner', 'Back (Lats)', 'Biceps, Rear Deltoids', '1. Stand with a dumbbell in one hand. 2. Hinge at your hips, keeping a straight back, until your torso is nearly parallel to the floor. Your free hand can rest on a bench or your knee for support. 3. ', 'Dumbbells, Bench (optional)', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(30, 'Reverse Lunges', 'A variation of the lunge that helps build lower body strength and balance, focusing on glutes and qu', 'Beginner', 'Glutes', 'Quadriceps, Hamstrings, Core', '1. Stand tall with your feet hip-width apart. 2. Step one leg straight back, landing on the ball of your foot. 3. Lower your back knee towards the floor until both knees are bent at approximately 90-d', 'Bodyweight (Dumbbells optional for progression)', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(31, 'Resistance Band Rows', 'An effective back exercise using a resistance band, great for beginners to build pulling strength an', 'Beginner', 'Back (Rhomboids, Lats)', 'Biceps, Rear Deltoids', '1. Sit on the floor with your legs extended, or slightly bent if needed. 2. Loop a resistance band around your feet, holding one end in each hand with an overhand grip. 3. Keep your back straight and ', 'Resistance Band', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(32, 'Seated Dumbbell Overhead Press', 'Targets the shoulders and triceps, building upper body pressing strength in a seated, more stable po', 'Beginner', 'Shoulders (Deltoids)', 'Triceps', '1. Sit on a bench with back support, holding a dumbbell in each hand at shoulder height, palms facing forward. 2. Keep your core tight and back pressed against the support. 3. Press the dumbbells stra', 'Dumbbells, Bench', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(33, 'Glute Bridges', 'An excellent exercise for activating and strengthening the glutes and hamstrings, without putting st', 'Beginner', 'Glutes', 'Hamstrings, Core', '1. Lie on your back with your knees bent and feet flat on the floor, hip-width apart. Your heels should be a few inches from your glutes. 2. Keep your arms by your sides, palms down. 3. Engage your co', 'Bodyweight', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(34, 'Incline Push-ups', 'A modified push-up that places less stress on the upper body, making it easier than a standard push-', 'Beginner', 'Chest (Lower)', 'Triceps, Shoulders', '1. Stand facing a sturdy elevated surface (e.g., a bench, sturdy chair, or wall). 2. Place your hands on the surface, slightly wider than shoulder-width apart. 3. Step your feet back until your body f', 'Elevated Surface (Bench, Chair, Wall)', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(35, 'Dumbbell Triceps Kickbacks', 'An isolation exercise that effectively targets the triceps, helping to build arm definition and stre', 'Beginner', 'Triceps', 'Shoulders', '1. Hold a dumbbell in one hand and hinge forward at your hips, keeping your back straight and core engaged. You can support your free hand on a bench or your knee. 2. Keep your upper arm locked close ', 'Dumbbell, Bench (optional)', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0),
(36, 'Standing Calf Raises', 'Targets the calf muscles, specifically the gastrocnemius, to improve lower leg strength and definiti', 'Beginner', 'Calves', '', '1. Stand with your feet hip-width apart, holding onto a wall or sturdy object for balance if needed. 2. Slowly raise yourself up onto the balls of your feet, lifting your heels as high as possible. 3.', 'Bodyweight (Optional light dumbbells for progression)', '', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `fooditem`
--

CREATE TABLE `fooditem` (
  `Name` varchar(50) NOT NULL,
  `Brand` int(11) NOT NULL,
  `Food_Item_ID` int(11) NOT NULL,
  `Serving_Size` int(11) NOT NULL,
  `Calories` int(11) NOT NULL,
  `Sugar_Grams` int(11) NOT NULL,
  `Fats_Grams` int(11) NOT NULL,
  `Fiber_Grams` int(11) NOT NULL,
  `Protein_Grams` int(11) NOT NULL,
  `Carbs_Grams` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `Inventory_ID` int(11) NOT NULL,
  `Quantity_in_stock` int(11) NOT NULL,
  `safety_stock_level` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `warehouse_location` varchar(50) NOT NULL,
  `last_restocked_at` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `Product_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal`
--

CREATE TABLE `meal` (
  `Meal_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Sequence_Order` int(11) NOT NULL,
  `Target_Time_of_Day` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Meal_Plan_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meallog`
--

CREATE TABLE `meallog` (
  `Meal_Log_ID` int(11) NOT NULL,
  `Log_date` date NOT NULL,
  `Notes` varchar(200) NOT NULL,
  `Adherence_Percentage` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Meal_Plan_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mealplan`
--

CREATE TABLE `mealplan` (
  `Meal_Plan_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Total_daily_Calories` int(11) NOT NULL,
  `Carbs_grams_per_day` int(11) NOT NULL,
  `Protein_grams_per_day` int(11) NOT NULL,
  `Fats_grams_per_day` int(11) NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Nutritionist_ID` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mealplan`
--

INSERT INTO `mealplan` (`Meal_Plan_ID`, `Title`, `Description`, `Total_daily_Calories`, `Carbs_grams_per_day`, `Protein_grams_per_day`, `Fats_grams_per_day`, `Status`, `Start_Date`, `End_Date`, `Created_at`, `Updated_at`, `Nutritionist_ID`, `Member_Id`, `is_deleted`) VALUES
(1, '12', '', 0, 0, 0, 0, '', '0000-00-00', '0000-00-00', '2025-12-17 11:51:29', '2025-12-17 11:51:29', 14, 4, 0),
(2, '1', '', 0, 0, 0, 0, '', '0000-00-00', '0000-00-00', '2025-12-17 11:51:43', '2025-12-17 11:51:43', 14, 4, 1),
(3, 'FOOOOOOOD', '', 0, 0, 0, 0, '', '0000-00-00', '0000-00-00', '2025-12-17 13:56:37', '2025-12-17 13:56:37', 14, 4, 0),
(4, 'gooooooooooooood', '', 0, 0, 0, 0, '', '0000-00-00', '0000-00-00', '2025-12-17 13:57:31', '2025-12-17 13:57:31', 14, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mealplanitem`
--

CREATE TABLE `mealplanitem` (
  `Meal_Plan_Item_id` int(11) NOT NULL,
  `Quantity_Servings` int(11) NOT NULL,
  `Notes` varchar(100) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Meal_ID` int(11) NOT NULL,
  `Food_Item_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicalrecord`
--

CREATE TABLE `medicalrecord` (
  `Has_Diabetes` int(11) NOT NULL,
  `Has_Heart_Condition` int(11) NOT NULL,
  `Has_Asthma` int(11) NOT NULL,
  `Has_Thyroid_Disorder` int(11) NOT NULL,
  `Has_High_Cholesterol` int(11) NOT NULL,
  `Has_Back_Injury` int(11) NOT NULL,
  `Has_Neck_Injury` int(11) NOT NULL,
  `Has_lactose_intolerance` int(11) NOT NULL,
  `Has_gluten_intolerance` int(11) NOT NULL,
  `Has_nut_Allergy` int(11) NOT NULL,
  `Has_egg_allergy` int(11) NOT NULL,
  `has_recent_surgery` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicalrecord`
--

INSERT INTO `medicalrecord` (`Has_Diabetes`, `Has_Heart_Condition`, `Has_Asthma`, `Has_Thyroid_Disorder`, `Has_High_Cholesterol`, `Has_Back_Injury`, `Has_Neck_Injury`, `Has_lactose_intolerance`, `Has_gluten_intolerance`, `Has_nut_Allergy`, `Has_egg_allergy`, `has_recent_surgery`, `updated_at`, `created_at`, `Member_Id`) VALUES
(1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, '2025-12-17 02:29:06', '2025-12-17 02:00:32', 2),
(1, 1, 1, 1, 0, 1, 1, 0, 1, 1, 0, 1, '2025-12-17 02:58:54', '2025-12-17 02:58:21', 4);

-- --------------------------------------------------------

--
-- Table structure for table `memberprofile`
--

CREATE TABLE `memberprofile` (
  `Member_Id` int(11) NOT NULL,
  `Em_Contact_Num` int(11) NOT NULL,
  `EM_Contact_Name` varchar(50) NOT NULL,
  `Body_fat` float NOT NULL,
  `Height` int(11) NOT NULL,
  `Weight` float NOT NULL,
  `BMI` float NOT NULL,
  `Experience_Level` varchar(100) NOT NULL,
  `Training_Goals` varchar(500) NOT NULL,
  `Injuries` varchar(500) NOT NULL,
  `Medical_Condition` varchar(500) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberprofile`
--

INSERT INTO `memberprofile` (`Member_Id`, `Em_Contact_Num`, `EM_Contact_Name`, `Body_fat`, `Height`, `Weight`, `BMI`, `Experience_Level`, `Training_Goals`, `Injuries`, `Medical_Condition`, `Created_at`, `Updated_at`, `is_deleted`) VALUES
(2, 491525135, 'Cabron HElp', 10.2, 180, 75.5, 24, 'Beginner', 'AGAG', 'AGDSGDASG', 'GASDSGAD', '2025-12-12 23:31:52', '2025-12-16 22:44:54', 0),
(4, 0, '', 0, 175, 100, 0, 'Beginner', '', '', '', '2025-12-16 19:09:51', '2025-12-17 00:50:33', 0),
(15, 0, '', 0, 0, 0, 0, 'Beginner', 'General fitness', '', '', '2026-01-11 22:36:55', '2026-01-11 22:36:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `membershipfreeze`
--

CREATE TABLE `membershipfreeze` (
  `Freeze_ID` int(11) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Actual_End_Date` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `Reason` varchar(500) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Subscription_ID` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membershipplan`
--

CREATE TABLE `membershipplan` (
  `Plan_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Tier` varchar(50) NOT NULL,
  `Price` int(11) NOT NULL,
  `Duration` int(11) NOT NULL,
  `Coach_Access` int(11) NOT NULL,
  `Nutritionist_Access` int(11) NOT NULL,
  `Is_Active` int(11) NOT NULL,
  `Max_Nutritionist_Session` int(11) NOT NULL,
  `Max_Coach_Sessions` int(11) NOT NULL,
  `Max_Freeze_Length_days` int(11) NOT NULL,
  `Max_Freezes_Allowed` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membershipplan`
--

INSERT INTO `membershipplan` (`Plan_ID`, `Name`, `Tier`, `Price`, `Duration`, `Coach_Access`, `Nutritionist_Access`, `Is_Active`, `Max_Nutritionist_Session`, `Max_Coach_Sessions`, `Max_Freeze_Length_days`, `Max_Freezes_Allowed`, `Created_at`, `Updated_at`, `is_deleted`) VALUES
(1, 'Silver', 'Basic', 350, 30, 0, 0, 1, 0, 1, 3, 1, '2025-12-17 04:30:57', '2025-12-17 04:30:57', 0),
(2, 'Gold', 'Standard', 600, 30, 1, 0, 1, 0, 5, 7, 2, '2025-12-17 04:30:57', '2025-12-17 04:30:57', 0),
(3, 'Platinum', 'Premium', 1000, 30, 1, 1, 1, 5, 10, 14, 3, '2025-12-17 04:30:57', '2025-12-17 04:30:57', 0);

-- --------------------------------------------------------

--
-- Table structure for table `membershipsubscription`
--

CREATE TABLE `membershipsubscription` (
  `Subscription_ID` int(11) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` int(11) NOT NULL,
  `Cancel_Date` date NOT NULL,
  `Cancel_Reason` varchar(100) NOT NULL,
  `Is_Frozen` int(11) NOT NULL,
  `Total_Frozen_Days` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Plan_ID` int(11) NOT NULL,
  `Cancelled_by_User_ID` int(11) NOT NULL,
  `Cancelled_by_Admin_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membershipsubscription`
--

INSERT INTO `membershipsubscription` (`Subscription_ID`, `Start_Date`, `End_Date`, `Status`, `Cancel_Date`, `Cancel_Reason`, `Is_Frozen`, `Total_Frozen_Days`, `Created_at`, `Updated_at`, `Member_Id`, `Plan_ID`, `Cancelled_by_User_ID`, `Cancelled_by_Admin_ID`, `is_deleted`) VALUES
(1, '2025-12-17', '2026-01-16', 1, '9999-12-31', '', 0, 0, '2025-12-17 04:35:19', '2025-12-17 04:35:19', 2, 1, 2, 1, 0),
(2, '2025-12-17', '2026-01-16', 1, '9999-12-31', '', 0, 0, '2025-12-17 04:35:19', '2025-12-17 04:35:19', 4, 2, 4, 1, 0),
(3, '2026-01-11', '2026-02-10', 1, '1970-01-01', '', 0, 0, '2026-01-11 23:36:55', '2026-01-11 23:36:55', 15, 3, 15, 15, 0);

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `Message_ID` int(11) NOT NULL,
  `Message_Text` varchar(500) NOT NULL,
  `Attachment_Url` int(11) NOT NULL,
  `Attachment_Type` int(11) NOT NULL,
  `Sent_at` int(11) NOT NULL,
  `is_read` int(11) NOT NULL,
  `read_at` int(11) NOT NULL,
  `is_deleted` int(11) NOT NULL,
  `deleted_at` int(11) NOT NULL,
  `Conversation_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nutritionistprofile`
--

CREATE TABLE `nutritionistprofile` (
  `Nutritionist_ID` int(11) NOT NULL,
  `Licence_Number` int(11) NOT NULL,
  `Bio` varchar(500) NOT NULL,
  `Certifications` varchar(500) NOT NULL,
  `rating_count` float NOT NULL,
  `Avg_rating` float NOT NULL,
  `Is_accepting_new` int(11) NOT NULL,
  `Years_Experience` int(11) NOT NULL,
  `Specialization_Main` varchar(500) NOT NULL,
  `Clinic_Location` varchar(100) NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Created_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nutritionistprofile`
--

INSERT INTO `nutritionistprofile` (`Nutritionist_ID`, `Licence_Number`, `Bio`, `Certifications`, `rating_count`, `Avg_rating`, `Is_accepting_new`, `Years_Experience`, `Specialization_Main`, `Clinic_Location`, `Updated_at`, `Created_at`, `is_deleted`) VALUES
(14, 0, 'JUNIOR', 'IDK', 0, 0, 0, 0, 'Yarab', '', '0000-00-00 00:00:00', '2025-12-16 22:48:21', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE `orderitem` (
  `Order_Item_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Unit_Price` float NOT NULL,
  `Discount_amount` float NOT NULL,
  `line_total_amount` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `Order_ID` int(11) NOT NULL,
  `Product_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Payment_provider` varchar(50) NOT NULL,
  `transaction_reference` varchar(50) NOT NULL,
  `Payment_date` date NOT NULL,
  `Amount` float NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Failure_reason` varchar(50) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Order_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `Product_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Brand` varchar(50) NOT NULL,
  `Sku` int(11) NOT NULL,
  `Price` float NOT NULL,
  `Cost_price` float NOT NULL,
  `Tax_rate` float NOT NULL,
  `is_active` int(11) NOT NULL,
  `thumbnail_url` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `Category_ID` int(11) NOT NULL,
  `rating` float NOT NULL DEFAULT 0,
  `reviews` int(11) NOT NULL DEFAULT 0,
  `is_new` tinyint(1) NOT NULL DEFAULT 0,
  `is_sale` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `productcategory`
--

CREATE TABLE `productcategory` (
  `Category_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productcategory`
--

INSERT INTO `productcategory` (`Category_ID`, `Name`, `Description`, `Created_at`, `Updated_at`) VALUES
(1, 'Supplements', 'Protein, creatine, and performance enhancers', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(2, 'Snacks', 'Healthy protein snacks and mixes', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(3, 'Vitamins', 'Essential vitamins and fish oil', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(4, 'Accessories', 'Gym accessories and support gear', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(5, 'Equipment', 'Training equipment and bags', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(6, 'Cardio', 'Cardio and jump ropes', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(7, 'Strength', 'Strength training tools', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(8, 'Apparel', 'Gym clothing and socks', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(9, 'Recovery', 'Recovery supplements', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(10, 'Stimulants', 'Energy stimulants', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(11, 'Hydration', 'Hydration products', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(12, 'Fat Loss', 'Fat burning supplements', '2025-12-16 17:56:28', '2025-12-16 17:56:28'),
(13, 'Plant Protein', 'Plant-based proteins', '2025-12-16 17:56:28', '2025-12-16 17:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `productvariant`
--

CREATE TABLE `productvariant` (
  `Variant_ID` int(11) NOT NULL,
  `Variant_Name` int(11) NOT NULL,
  `Sku` int(11) NOT NULL,
  `Price_Override` int(11) NOT NULL,
  `Weight_Grams` int(11) NOT NULL,
  `Color` int(11) NOT NULL,
  `Size` int(11) NOT NULL,
  `Flavour` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Product_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staffavailability`
--

CREATE TABLE `staffavailability` (
  `Availability_ID` int(11) NOT NULL,
  `Is_Recurring` int(11) NOT NULL,
  `WeekDay` int(11) NOT NULL,
  `Available_Date` date NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `Max_Bookins_in_slot` int(11) NOT NULL,
  `Is_active` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Staff_User_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userprofile`
--

CREATE TABLE `userprofile` (
  `User_ID` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Last_Login` date NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Phone_Number` int(11) NOT NULL,
  `DOB` date NOT NULL,
  `Role` varchar(50) NOT NULL,
  `Gender` varchar(50) NOT NULL,
  `Is_Active` int(11) NOT NULL,
  `Profile_pic_url` varchar(200) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userprofile`
--

INSERT INTO `userprofile` (`User_ID`, `Email`, `Password`, `Last_Login`, `First_Name`, `Last_Name`, `Phone_Number`, `DOB`, `Role`, `Gender`, `Is_Active`, `Profile_pic_url`, `Created_at`, `Updated_at`, `is_deleted`) VALUES
(1, 'Admin@gmail.com', '$2y$10$mHjl76xGXml7CpBmtiqRyOOtCUKuj5NQtBakgKqJLecqMaOHIH.QS', '2026-01-11', 'Ahmed', 'Maher', 250325, '2006-09-01', 'Admin', 'Male', 1, '', '2025-12-12 20:54:21', '2025-12-12 20:54:21', 0),
(2, 'Member@gmail.com', '$2y$10$.cNi0oi4s3rDQKzfycVMOedtNOCm6e/b5ETWZqJ9Ur0Yii49WGtwG', '2025-12-20', 'Pablo', 'Escobar', 2147483647, '2006-09-01', 'member', 'Male', 1, 'images/profile_2_1765933775.jpg', '2025-12-12 23:30:34', '2025-12-16 22:44:54', 0),
(3, 'Coach@gmail.com', '$2y$10$2tnwuncNuoFx4LpQwVhqKOBdi0oaasE/pDr1cNqhCK.S2tJ1JzqBq', '2026-01-11', 'Mohamed', 'Sami', 141245, '2006-09-01', 'coach', 'Male', 1, '', '2025-12-12 23:33:34', '2025-12-12 23:33:34', 0),
(4, 'adelehab@gmail.com', '', '0000-00-00', 'Adel', 'Ehab', 1153584874, '2006-02-18', 'member', 'Female', 1, 'images/profile_4_1765936745.jpg', '2025-12-16 19:09:51', '2025-12-17 00:50:33', 0),
(5, 'AhmedMaher@gmail.com', '$2y$10$cggZU5D8nGRtoYCOyHprAu82abOADzUVnhvN1.9AVQKKXwrfJnX/S', '0000-00-00', 'Ahmed', 'Maher', 0, '2000-01-01', 'coach', 'Male', 1, '', '2025-12-16 19:19:48', '2025-12-16 19:19:48', 0),
(6, 'Ahmedhassan@gmail.com', '$2y$10$TLYZ2bGYhk9rSFdO4.3YaeNqCGg3wEndNBP3yWz5l3B3Ae7exFs/C', '0000-00-00', 'Ahmed', 'Hassan', 0, '2000-01-01', 'nutritionist', 'Male', 1, '', '2025-12-16 20:11:03', '2025-12-16 20:11:03', 1),
(7, 'aifhaf@gmail.com', '$2y$10$hjbI7hW9W5NTwTbxy6sWguq1RTyoC154tcNU3pdbHNyk5uBG3CGGu', '0000-00-00', 'baboooon', 'ugly', 0, '2000-01-01', 'member', 'Male', 0, '', '2025-12-16 21:53:06', '0000-00-00 00:00:00', 0),
(8, 'test@example.com', '$2y$10$a16zypc2u9btIQOy7DspFO.elrA5z5.qumnFX1NNbzJoPbQWluXf.', '2025-12-16', 'Test', 'User', 0, '2000-01-01', 'member', 'Male', 1, '', '2025-12-16 22:24:01', '2025-12-16 22:24:01', 1),
(9, 'Adby@gmail.com', '$2y$10$fXXKdzfE.empGwXQ3fwKW.ufZjSLSQUIQ3mh2ALfIBFXBXYfk9nYy', '2025-12-16', 'cabron', 'ugly', 0, '2000-01-01', 'member', 'Male', 1, '', '2025-12-16 22:27:22', '2025-12-16 22:27:22', 1),
(10, 'YARAB@gmail.com', '$2y$10$5tZE1tq8Xe7CRn/DNp9fjugwWUcnVoCz1ULL6WwYgisdhJvWc2NXG', '2025-12-16', 'Please', 'Work', 0, '2000-01-01', 'member', 'Male', 1, '', '2025-12-16 22:32:25', '2025-12-16 22:32:25', 1),
(11, 'YOOOO@gmail.com', '$2y$10$Jt6Bp3Y0fSwvtDtJhCs4DuZI5X/W0aMYTLqRzCWECru9zcJ74OxKi', '2025-12-16', 'cabron', 'Work', 0, '2000-01-01', 'member', 'Male', 1, '', '2025-12-16 22:32:44', '2025-12-16 22:32:44', 1),
(12, 'BRUUUH@gmail.com', '$2y$10$RgKhpZx9Y7w9Ua8nTJpQZ.FOOGILOmi33Mi.Cu/8FnI0iaiKu0oYe', '2025-12-16', 'Please', 'ugly', 0, '2000-01-01', 'member', 'Male', 1, '', '2025-12-16 22:35:05', '2025-12-16 22:35:05', 1),
(13, 'Pablooooo@gmail.com', '$2y$10$s.m3W/RVqHT9HNjIpnAi6uOaEVIe1vkQ9YNtMgBNSm.O2pvKTecAa', '2025-12-16', 'Micheal', 'Johnson', 0, '2000-01-01', 'coach', 'Male', 1, '', '2025-12-16 22:38:03', '2025-12-16 22:38:03', 0),
(14, 'Gustavo@gmail.com', '$2y$10$9YfnR02hd60nBsQHf0fn/.3OKIdkR0akVZqqSeHfdTuczI/BlBOuO', '2026-01-11', 'Phellip', 'gustavo', 0, '2000-01-01', 'nutritionist', 'Male', 1, '', '2025-12-16 22:48:21', '2025-12-16 22:48:21', 0),
(15, 'ahmedmaher500024@gmail.com', '$2y$10$L.sP2ZoMfeKYQUNovd8cNOpFx4P4oaoiLIgS.Joyq35URQgQsnuOW', '2026-01-11', 'Ahmed', 'Maher', 0, '2000-01-01', 'Member', 'Male', 1, '', '2025-12-17 05:14:53', '2025-12-17 05:14:53', 0),
(16, 'hanan@gmail.com', '$2y$10$E/42E0m6dnoTsPLO8kaygerDJBObPbKAYW7h.TmQ6CYkMZFrEqfNC', '2025-12-17', 'hanan', 'kk', 0, '2000-01-01', 'Member', 'Male', 1, '', '2025-12-17 15:02:29', '2025-12-17 15:02:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `workout`
--

CREATE TABLE `workout` (
  `Workout_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Duration_Minutes` int(11) DEFAULT 0,
  `Difficulty` varchar(50) DEFAULT NULL,
  `Coach_Id` int(11) DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp(),
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workoutexercise`
--

CREATE TABLE `workoutexercise` (
  `Workout_Exercise_ID` int(11) NOT NULL,
  `Sequence_Order` int(11) NOT NULL,
  `Day_Number` varchar(50) NOT NULL,
  `Sets` int(11) NOT NULL,
  `Reps` int(11) NOT NULL,
  `Rest_Time` varchar(50) NOT NULL,
  `Notes` varchar(100) NOT NULL,
  `Exercise_ID` int(11) NOT NULL,
  `Workout_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workoutexercise`
--

INSERT INTO `workoutexercise` (`Workout_Exercise_ID`, `Sequence_Order`, `Day_Number`, `Sets`, `Reps`, `Rest_Time`, `Notes`, `Exercise_ID`, `Workout_ID`, `is_deleted`) VALUES
(1, 1, '1', 3, 12, '60 seconds', 'Focus on control throughout the movement. If you find it hard to go deep, only go as far as comforta', 1, 1, 0),
(2, 2, '1', 3, 10, '60 seconds', 'Ensure your body moves as one unit, avoiding hip sagging. Progress to standard push-ups when you can', 2, 1, 0),
(3, 3, '1', 3, 10, '90 seconds', 'Maintain a flat back throughout the movement; avoid rounding your spine. Focus on initiating the pul', 3, 1, 0),
(4, 4, '1', 3, 10, '90 seconds', 'Keep your core tight to prevent your lower back from arching. Start with light weights to master the', 4, 1, 0),
(5, 5, '1', 3, 0, '60 seconds', 'Aim for holds of 30-60 seconds per set. Focus on maintaining perfect form throughout the hold rather', 5, 1, 0),
(6, 6, '3', 3, 10, '60 seconds', 'Keep the dumbbell close to your chest throughout the movement. This exercise is great for improving ', 6, 1, 0),
(7, 7, '3', 3, 10, '90 seconds', 'The floor prevents your elbows from going too far back, which can be beneficial for shoulder health.', 7, 1, 0),
(8, 8, '3', 3, 10, '90 seconds', 'Focus on the hip hinge movement rather than simply bending over. Your back should remain flat and ne', 8, 1, 0),
(9, 9, '3', 3, 10, '60 seconds', 'Avoid swinging the weights or using momentum. The movement should be slow and controlled, focusing s', 9, 1, 0),
(10, 10, '3', 3, 12, '60 seconds', 'Keep your core tight and avoid arching your back. Use a weight that allows for full range of motion ', 10, 1, 0),
(11, 11, '5', 3, 8, '60 seconds', 'Focus on maintaining balance and keeping your torso upright throughout the movement. Aim for control', 11, 1, 0),
(12, 12, '5', 3, 12, '60 seconds', 'This exercise is a good progression from knee push-ups. As you get stronger, use a lower elevation t', 12, 1, 0),
(13, 13, '5', 3, 10, '90 seconds', 'Keep your core tight and avoid twisting your torso. Focus on pulling with your back muscles, not jus', 13, 1, 0),
(14, 14, '5', 3, 12, '60 seconds', 'Use light weight and focus on strict form. Avoid shrugging your shoulders or using momentum to lift ', 14, 1, 0),
(15, 15, '5', 3, 15, '60 seconds', 'Focus on contracting your abdominal muscles to lift your upper body, rather than pulling with your n', 15, 1, 0),
(31, 1, '1', 3, 10, '60 seconds', 'Focus on controlled movement. If deeper squats are difficult, start with partial squats and graduall', 1, 3, 0),
(32, 2, '1', 3, 8, '60 seconds', 'Keep your core tight to maintain a straight body line. If knee push-ups are too easy, try incline pu', 2, 3, 0),
(33, 3, '1', 3, 10, '60 seconds', 'Avoid shrugging your shoulders or using momentum. Focus on pulling with your back muscles. Use a wei', 29, 3, 0),
(34, 4, '1', 3, 30, '60 seconds', 'Maintain steady breathing throughout the hold. If holding for 30 seconds is too difficult, start wit', 5, 3, 0),
(35, 5, '3', 3, 8, '60 seconds', 'Maintain an upright torso and keep your core engaged. Take a controlled step back. If balance is an ', 30, 3, 0),
(36, 6, '3', 3, 10, '60 seconds', 'Keep your lower back pressed into the floor. Control the movement both up and down. Avoid bouncing t', 21, 3, 0),
(37, 7, '3', 3, 12, '60 seconds', 'Ensure the band has enough tension throughout the movement. Focus on squeezing your shoulder blades,', 31, 3, 0),
(38, 8, '3', 3, 10, '60 seconds', 'Choose a weight that allows you to maintain good form without arching your back excessively. Avoid l', 32, 3, 0),
(39, 9, '5', 3, 12, '60 seconds', 'Focus on squeezing your glutes at the top of the movement. Avoid arching your lower back too much. F', 33, 3, 0),
(40, 10, '5', 3, 8, '60 seconds', 'Keep your core tight and body straight. To make it harder, use a lower elevated surface. If too easy', 34, 3, 0),
(41, 11, '5', 3, 10, '60 seconds', 'Avoid swinging the weights or using momentum. Focus on a controlled squeeze and slow eccentric (lowe', 9, 3, 0),
(42, 12, '5', 3, 12, '60 seconds', 'The key is to keep your upper arm stationary and only move your forearm. Use a lighter weight to ens', 35, 3, 0),
(43, 13, '5', 3, 15, '60 seconds', 'Focus on a full range of motion. For increased difficulty, perform on the edge of a step to allow a ', 36, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `workoutlog`
--

CREATE TABLE `workoutlog` (
  `Log_ID` int(11) NOT NULL,
  `Sets_Completed` int(11) NOT NULL,
  `Reps_per_set` int(11) NOT NULL,
  `Weight_per_set` int(11) NOT NULL,
  `Notes` int(11) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Workout_ID` int(11) NOT NULL,
  `Workout_Exercise_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workoutprogram`
--

CREATE TABLE `workoutprogram` (
  `Workout_ID` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Goal` varchar(100) NOT NULL,
  `Weeks_Duration` int(11) NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Status` varchar(50) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Updated_at` datetime NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Coach_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workoutprogram`
--

INSERT INTO `workoutprogram` (`Workout_ID`, `Title`, `Description`, `Goal`, `Weeks_Duration`, `Start_Date`, `End_Date`, `Status`, `Created_at`, `Updated_at`, `Member_Id`, `Coach_ID`, `is_deleted`) VALUES
(1, '1-Week Beginner Muscle Building Plan', 'A beginner-friendly 1-week workout plan designed to introduce fundamental movements and stimulate mu', 'Build Muscle', 1, '2025-12-13', '2025-12-20', 'Active', '2025-12-13 20:59:48', '2025-12-13 20:59:48', 2, 3, 1),
(3, 'Pablo\'s Beginner Muscle Building Blueprint', 'This 1-week program is designed for a beginner-level trainee, Pablo Escobar, with the primary goal o', 'Build Muscle', 1, '2026-01-11', '2026-01-18', 'Active', '2026-01-11 14:58:38', '2026-01-11 14:58:38', 2, 3, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`Address_ID`),
  ADD KEY `Member_Id` (`Member_Id`);

--
-- Indexes for table `adminactionlog`
--
ALTER TABLE `adminactionlog`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`);

--
-- Indexes for table `adminprofile`
--
ALTER TABLE `adminprofile`
  ADD PRIMARY KEY (`Admin_ID`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`Appointment_ID`),
  ADD KEY `Staff_User_ID` (`Staff_User_ID`),
  ADD KEY `Availability_ID` (`Availability_ID`),
  ADD KEY `Member_Id` (`Member_Id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Cart_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `cartitem`
--
ALTER TABLE `cartitem`
  ADD PRIMARY KEY (`Cart_Item_ID`),
  ADD KEY `Cart_ID` (`Cart_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `coachprofile`
--
ALTER TABLE `coachprofile`
  ADD PRIMARY KEY (`Coach_ID`);

--
-- Indexes for table `contactmessage`
--
ALTER TABLE `contactmessage`
  ADD PRIMARY KEY (`Contact_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`Conversation_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Staff_User_ID` (`Staff_User_ID`);

--
-- Indexes for table `corder`
--
ALTER TABLE `corder`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `Member_Id` (`Member_Id`);

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`Exercise_ID`);

--
-- Indexes for table `fooditem`
--
ALTER TABLE `fooditem`
  ADD PRIMARY KEY (`Food_Item_ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`Inventory_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `meal`
--
ALTER TABLE `meal`
  ADD PRIMARY KEY (`Meal_ID`),
  ADD KEY `Meal_Plan_ID` (`Meal_Plan_ID`);

--
-- Indexes for table `meallog`
--
ALTER TABLE `meallog`
  ADD PRIMARY KEY (`Meal_Log_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Meal_Plan_ID` (`Meal_Plan_ID`);

--
-- Indexes for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD PRIMARY KEY (`Meal_Plan_ID`),
  ADD KEY `Nutritionist_ID` (`Nutritionist_ID`),
  ADD KEY `Member_Id` (`Member_Id`);

--
-- Indexes for table `mealplanitem`
--
ALTER TABLE `mealplanitem`
  ADD PRIMARY KEY (`Meal_Plan_Item_id`),
  ADD KEY `Meal_ID` (`Meal_ID`),
  ADD KEY `Food_Item_ID` (`Food_Item_ID`);

--
-- Indexes for table `medicalrecord`
--
ALTER TABLE `medicalrecord`
  ADD PRIMARY KEY (`Member_Id`);

--
-- Indexes for table `memberprofile`
--
ALTER TABLE `memberprofile`
  ADD PRIMARY KEY (`Member_Id`);

--
-- Indexes for table `membershipfreeze`
--
ALTER TABLE `membershipfreeze`
  ADD PRIMARY KEY (`Freeze_ID`),
  ADD KEY `Subscription_ID` (`Subscription_ID`),
  ADD KEY `Member_Id` (`Member_Id`);

--
-- Indexes for table `membershipplan`
--
ALTER TABLE `membershipplan`
  ADD PRIMARY KEY (`Plan_ID`);

--
-- Indexes for table `membershipsubscription`
--
ALTER TABLE `membershipsubscription`
  ADD PRIMARY KEY (`Subscription_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Plan_ID` (`Plan_ID`),
  ADD KEY `Cancelled_by_User_ID` (`Cancelled_by_User_ID`),
  ADD KEY `Cancelled_by_Admin_ID` (`Cancelled_by_Admin_ID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`Message_ID`),
  ADD KEY `Conversation_ID` (`Conversation_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `nutritionistprofile`
--
ALTER TABLE `nutritionistprofile`
  ADD PRIMARY KEY (`Nutritionist_ID`);

--
-- Indexes for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`Order_Item_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Product_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `productcategory`
--
ALTER TABLE `productcategory`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `productvariant`
--
ALTER TABLE `productvariant`
  ADD PRIMARY KEY (`Variant_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `staffavailability`
--
ALTER TABLE `staffavailability`
  ADD PRIMARY KEY (`Availability_ID`),
  ADD KEY `Staff_User_ID` (`Staff_User_ID`);

--
-- Indexes for table `userprofile`
--
ALTER TABLE `userprofile`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `workout`
--
ALTER TABLE `workout`
  ADD PRIMARY KEY (`Workout_ID`);

--
-- Indexes for table `workoutexercise`
--
ALTER TABLE `workoutexercise`
  ADD PRIMARY KEY (`Workout_Exercise_ID`),
  ADD KEY `Exercise_ID` (`Exercise_ID`),
  ADD KEY `Workout_ID` (`Workout_ID`);

--
-- Indexes for table `workoutlog`
--
ALTER TABLE `workoutlog`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Workout_ID` (`Workout_ID`),
  ADD KEY `Workout_Exercise_ID` (`Workout_Exercise_ID`);

--
-- Indexes for table `workoutprogram`
--
ALTER TABLE `workoutprogram`
  ADD PRIMARY KEY (`Workout_ID`),
  ADD KEY `Member_Id` (`Member_Id`),
  ADD KEY `Coach_ID` (`Coach_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `Address_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adminactionlog`
--
ALTER TABLE `adminactionlog`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `adminprofile`
--
ALTER TABLE `adminprofile`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `Cart_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cartitem`
--
ALTER TABLE `cartitem`
  MODIFY `Cart_Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coachprofile`
--
ALTER TABLE `coachprofile`
  MODIFY `Coach_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contactmessage`
--
ALTER TABLE `contactmessage`
  MODIFY `Contact_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `Conversation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `corder`
--
ALTER TABLE `corder`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `Exercise_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `fooditem`
--
ALTER TABLE `fooditem`
  MODIFY `Food_Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `Inventory_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal`
--
ALTER TABLE `meal`
  MODIFY `Meal_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meallog`
--
ALTER TABLE `meallog`
  MODIFY `Meal_Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mealplan`
--
ALTER TABLE `mealplan`
  MODIFY `Meal_Plan_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mealplanitem`
--
ALTER TABLE `mealplanitem`
  MODIFY `Meal_Plan_Item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicalrecord`
--
ALTER TABLE `medicalrecord`
  MODIFY `Member_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `memberprofile`
--
ALTER TABLE `memberprofile`
  MODIFY `Member_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `membershipfreeze`
--
ALTER TABLE `membershipfreeze`
  MODIFY `Freeze_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membershipplan`
--
ALTER TABLE `membershipplan`
  MODIFY `Plan_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membershipsubscription`
--
ALTER TABLE `membershipsubscription`
  MODIFY `Subscription_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `Message_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nutritionistprofile`
--
ALTER TABLE `nutritionistprofile`
  MODIFY `Nutritionist_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `Order_Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `productvariant`
--
ALTER TABLE `productvariant`
  MODIFY `Variant_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staffavailability`
--
ALTER TABLE `staffavailability`
  MODIFY `Availability_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userprofile`
--
ALTER TABLE `userprofile`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `workout`
--
ALTER TABLE `workout`
  MODIFY `Workout_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workoutexercise`
--
ALTER TABLE `workoutexercise`
  MODIFY `Workout_Exercise_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `workoutlog`
--
ALTER TABLE `workoutlog`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workoutprogram`
--
ALTER TABLE `workoutprogram`
  MODIFY `Workout_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `adminactionlog`
--
ALTER TABLE `adminactionlog`
  ADD CONSTRAINT `adminactionlog_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `adminprofile` (`Admin_ID`);

--
-- Constraints for table `adminprofile`
--
ALTER TABLE `adminprofile`
  ADD CONSTRAINT `adminprofile_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`Staff_User_ID`) REFERENCES `userprofile` (`User_ID`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`Availability_ID`) REFERENCES `staffavailability` (`Availability_ID`),
  ADD CONSTRAINT `appointment_ibfk_3` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`Order_ID`) REFERENCES `corder` (`Order_ID`);

--
-- Constraints for table `cartitem`
--
ALTER TABLE `cartitem`
  ADD CONSTRAINT `cartitem_ibfk_1` FOREIGN KEY (`Cart_ID`) REFERENCES `cart` (`Cart_ID`),
  ADD CONSTRAINT `cartitem_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `coachprofile`
--
ALTER TABLE `coachprofile`
  ADD CONSTRAINT `coachprofile_ibfk_1` FOREIGN KEY (`Coach_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `contactmessage`
--
ALTER TABLE `contactmessage`
  ADD CONSTRAINT `contactmessage_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `adminprofile` (`Admin_ID`);

--
-- Constraints for table `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `conversation_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `conversation_ibfk_2` FOREIGN KEY (`Staff_User_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `corder`
--
ALTER TABLE `corder`
  ADD CONSTRAINT `corder_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `meal`
--
ALTER TABLE `meal`
  ADD CONSTRAINT `meal_ibfk_1` FOREIGN KEY (`Meal_Plan_ID`) REFERENCES `mealplan` (`Meal_Plan_ID`);

--
-- Constraints for table `meallog`
--
ALTER TABLE `meallog`
  ADD CONSTRAINT `meallog_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `meallog_ibfk_2` FOREIGN KEY (`Meal_Plan_ID`) REFERENCES `mealplan` (`Meal_Plan_ID`);

--
-- Constraints for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD CONSTRAINT `mealplan_ibfk_1` FOREIGN KEY (`Nutritionist_ID`) REFERENCES `nutritionistprofile` (`Nutritionist_ID`),
  ADD CONSTRAINT `mealplan_ibfk_2` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `mealplanitem`
--
ALTER TABLE `mealplanitem`
  ADD CONSTRAINT `mealplanitem_ibfk_1` FOREIGN KEY (`Meal_ID`) REFERENCES `meal` (`Meal_ID`),
  ADD CONSTRAINT `mealplanitem_ibfk_2` FOREIGN KEY (`Food_Item_ID`) REFERENCES `fooditem` (`Food_Item_ID`);

--
-- Constraints for table `medicalrecord`
--
ALTER TABLE `medicalrecord`
  ADD CONSTRAINT `medicalrecord_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `memberprofile`
--
ALTER TABLE `memberprofile`
  ADD CONSTRAINT `memberprofile_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `membershipfreeze`
--
ALTER TABLE `membershipfreeze`
  ADD CONSTRAINT `membershipfreeze_ibfk_1` FOREIGN KEY (`Subscription_ID`) REFERENCES `membershipsubscription` (`Subscription_ID`),
  ADD CONSTRAINT `membershipfreeze_ibfk_2` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`);

--
-- Constraints for table `membershipsubscription`
--
ALTER TABLE `membershipsubscription`
  ADD CONSTRAINT `membershipsubscription_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `membershipsubscription_ibfk_2` FOREIGN KEY (`Plan_ID`) REFERENCES `membershipplan` (`Plan_ID`),
  ADD CONSTRAINT `membershipsubscription_ibfk_3` FOREIGN KEY (`Cancelled_by_User_ID`) REFERENCES `userprofile` (`User_ID`),
  ADD CONSTRAINT `membershipsubscription_ibfk_4` FOREIGN KEY (`Cancelled_by_Admin_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`Conversation_ID`) REFERENCES `conversation` (`Conversation_ID`),
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `nutritionistprofile`
--
ALTER TABLE `nutritionistprofile`
  ADD CONSTRAINT `nutritionistprofile_ibfk_1` FOREIGN KEY (`Nutritionist_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderitem_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `corder` (`Order_ID`),
  ADD CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `corder` (`Order_ID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `productcategory` (`Category_ID`);

--
-- Constraints for table `productvariant`
--
ALTER TABLE `productvariant`
  ADD CONSTRAINT `productvariant_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `product` (`Product_ID`);

--
-- Constraints for table `staffavailability`
--
ALTER TABLE `staffavailability`
  ADD CONSTRAINT `staffavailability_ibfk_1` FOREIGN KEY (`Staff_User_ID`) REFERENCES `userprofile` (`User_ID`);

--
-- Constraints for table `workoutexercise`
--
ALTER TABLE `workoutexercise`
  ADD CONSTRAINT `workoutexercise_ibfk_1` FOREIGN KEY (`Exercise_ID`) REFERENCES `exercise` (`Exercise_ID`),
  ADD CONSTRAINT `workoutexercise_ibfk_2` FOREIGN KEY (`Workout_ID`) REFERENCES `workoutprogram` (`Workout_ID`);

--
-- Constraints for table `workoutlog`
--
ALTER TABLE `workoutlog`
  ADD CONSTRAINT `workoutlog_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `workoutlog_ibfk_2` FOREIGN KEY (`Workout_ID`) REFERENCES `workoutprogram` (`Workout_ID`),
  ADD CONSTRAINT `workoutlog_ibfk_3` FOREIGN KEY (`Workout_Exercise_ID`) REFERENCES `workoutexercise` (`Workout_Exercise_ID`);

--
-- Constraints for table `workoutprogram`
--
ALTER TABLE `workoutprogram`
  ADD CONSTRAINT `workoutprogram_ibfk_1` FOREIGN KEY (`Member_Id`) REFERENCES `memberprofile` (`Member_Id`),
  ADD CONSTRAINT `workoutprogram_ibfk_2` FOREIGN KEY (`Coach_ID`) REFERENCES `coachprofile` (`Coach_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
