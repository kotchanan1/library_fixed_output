-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 08:37 AM
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
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `book_name` varchar(255) DEFAULT NULL,
  `type_name` varchar(100) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `book_name`, `type_name`, `author`, `status`) VALUES
(1, 'Database', 'คอมพิวเตอร์', 'Navathe', 'available'),
(2, 'สี่แผ่นดิน (เล่ม 1)', 'วรรณกรรมไทย', 'ม.ร.ว. คึกฤทธิ์ ปราโมช', NULL),
(3, 'คู่กรรม 2', 'วรรณกรรมไทย', 'ทมยันตี', NULL),
(4, 'Artificial Intelligence: A Modern Approach (Fourth Edition)', 'เทคโนโลยี / ปัญญาประดิษฐ์', 'Stuart Russell, Peter Norvig', NULL),
(5, 'แคลคูลัส 1 สำหรับวิศวกร', 'วิศวกรรม / คณิตศาสตร์', 'รศ. ดร.ธีระศักดิ์ อุรัจนานนท์', NULL),
(6, 'หลักการบัญชี', 'บัญชี / ธุรกิจ', 'ทีฆะทัศน์ ทองกูล', NULL),
(7, 'ประวัติศาสตร์ไทย', 'ประวัติศาสตร์ไทย', 'รงรอง วงศ์โอบอ้อม', NULL),
(8, 'อารยธรรมโลก', 'ประวัติศาสตร์โลก', 'พระครูปลัดสุวัฒนโพธิคุณ (สมชาย ฐานวุฑฺโฒ)', NULL),
(9, 'PHYSICS เจาะลึกพื้นฐานฟิสิกส์ ม.ต้น', 'วิทยาศาสตร์', 'ครูปอนด์ (END)', NULL),
(10, 'เพชรพระอุมา ตอน ไพรมหากาฬ (เล่ม 1)', 'วรรณกรรมไทย', 'พนมเทียน', NULL),
(11, 'PHP and MySQL Web Development (Fifth Edition)', 'โปรแกรมมิ่ง / เว็บ', 'Luke Welling, Laura Thomson', NULL),
(12, 'เปลี่ยนชีวิต ได้ทันใจ ด้วยความคิดบวก', 'พัฒนาตนเอง', 'พัฒน์สุกา สมิร', NULL),
(13, 'สารานุกรม ดนตรีและเพลงไทย', 'ดนตรี / ศิลปวัฒนธรรม', 'รศ. กาญจนา อินทรสุนานนท์', NULL),
(14, 'คู่มือ สุขภาพดี ดูแลได้ด้วยตนเอง', 'สุขภาพ', 'นพ. ธีระ ศิริอาชาวัฒนา', NULL),
(15, 'The Art of Portrait Photography', 'การถ่ายภาพ / ศิลปะ', 'ดร.ชลิดา ทรงประสิทธิ์', NULL),
(16, 'Data Science and Analytics with Python', 'Data Science', 'K. Munonye, N. Wonu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `book_types`
--

CREATE TABLE `book_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_types`
--

INSERT INTO `book_types` (`type_id`, `type_name`, `book_id`, `book_name`) VALUES
(1, 'คอมพิวเตอร์', 1, 'Database'),
(2, 'วรรณกรรมไทย', 1, 'สี่แผ่นดิน (เล่ม 1)'),
(3, 'วรรณกรรมไทย', 2, 'คู่กรรม 2'),
(4, 'เทคโนโลยี / ปัญญาประดิษฐ์', 3, 'Artificial Intelligence: A Modern Approach (Fourth Edition)'),
(5, 'คณิตศาสตร์ / วิศวกรรม', 4, 'แคลคูลัส 1 สำหรับวิศวกร'),
(6, 'บัญชี / ธุรกิจ', 5, 'หลักการบัญชี'),
(7, 'ประวัติศาสตร์', 6, 'ประวัติศาสตร์ไทย'),
(8, 'ประวัติศาสตร์ / สังคม', 7, 'อารยธรรมโลก'),
(9, 'วิทยาศาสตร์', 8, 'PHYSICS เจาะลึกพื้นฐานฟิสิกส์ ม.ต้น'),
(10, 'วรรณกรรมไทย', 9, 'เพชรพระอุมา ตอน ไพรมหากาฬ (เล่ม 1)'),
(11, 'โปรแกรมมิ่ง / เว็บ', 10, 'PHP and MySQL Web Development (Fifth Edition)'),
(12, 'จิตวิทยา / พัฒนาตนเอง', 11, 'เปลี่ยนชีวิต ได้ทันใจ ด้วยความคิดบวก'),
(13, 'ดนตรี / ศิลปวัฒนธรรม', 12, 'สารานุกรม ดนตรีและเพลงไทย'),
(14, 'สุขภาพ', 13, 'คู่มือ สุขภาพดี ดูแลได้ด้วยตนเอง'),
(15, 'การถ่ายภาพ / ศิลปะ', 14, 'The Art of Portrait Photography'),
(16, 'Data Science', 15, 'Data Science and Analytics with PYTHON: A Comprehensive Guide'),
(17, 'วรรณกรรมไทย', 1, 'สี่แผ่นดิน (เล่ม 1)'),
(18, 'วรรณกรรมไทย', 2, 'คู่กรรม 2'),
(19, 'เทคโนโลยี / ปัญญาประดิษฐ์', 3, 'Artificial Intelligence: A Modern Approach (Fourth Edition)'),
(20, 'วิศวกรรม / คณิตศาสตร์', 4, 'แคลคูลัส 1 สำหรับวิศวกร'),
(21, 'บัญชี / ธุรกิจ', 5, 'หลักการบัญชี'),
(22, 'ประวัติศาสตร์', 6, 'ประวัติศาสตร์ไทย'),
(23, 'ประวัติศาสตร์โลก', 7, 'อารยธรรมโลก'),
(24, 'วิทยาศาสตร์', 8, 'PHYSICS เจาะลึกพื้นฐานฟิสิกส์ ม.ต้น'),
(25, 'วรรณกรรมไทย', 9, 'เพชรพระอุมา ตอน ไพรมหากาฬ (เล่ม 1)'),
(26, 'โปรแกรมมิ่ง / เว็บ', 10, 'PHP and MySQL Web Development (Fifth Edition)'),
(27, 'พัฒนาตนเอง', 11, 'เปลี่ยนชีวิต ได้ทันใจ ด้วยความคิดบวก'),
(28, 'ดนตรี / ศิลปวัฒนธรรม', 12, 'สารานุกรม ดนตรีและเพลงไทย'),
(29, 'สุขภาพ', 13, 'คู่มือ สุขภาพดี ดูแลได้ด้วยตนเอง'),
(30, 'การถ่ายภาพ / ศิลปะ', 14, 'The Art of Portrait Photography'),
(31, 'Data Science', 15, 'Data Science and Analytics with Python');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_history`
--

CREATE TABLE `borrow_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_history`
--

INSERT INTO `borrow_history` (`history_id`, `user_id`, `book_id`, `borrow_date`, `return_date`, `status`) VALUES
(1, 1, 1, '2026-02-15', NULL, 'borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `fine_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `category` enum('เก่า','ขาด','ใหม่','หาย') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`fine_id`, `user_id`, `book_id`, `category`, `price`) VALUES
(1, 1, 1, 'หาย', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `age`, `email`, `password`) VALUES
(1, 'Somchai', 'Dee', 20, 'a@mail.com', '12345678'),
(2, 'admin', 'admin', 40, 'admin002@gmail.com', 'admin123456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `book_types`
--
ALTER TABLE `book_types`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `borrow_history`
--
ALTER TABLE `borrow_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `book_types`
--
ALTER TABLE `book_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `borrow_history`
--
ALTER TABLE `borrow_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_types`
--
ALTER TABLE `book_types`
  ADD CONSTRAINT `book_types_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `borrow_history`
--
ALTER TABLE `borrow_history`
  ADD CONSTRAINT `borrow_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrow_history_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
