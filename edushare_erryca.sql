-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 06:21 PM
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
-- Database: `edushare_erryca`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `LoginUser` (IN `p_login` VARCHAR(100), IN `p_password` VARCHAR(100), OUT `p_error_message` VARCHAR(255))   BEGIN
    DECLARE stored_password VARCHAR(255);
    DECLARE user_id INT;
    
    -- Find user by username or email
    SELECT password, id
    INTO stored_password, user_id
    FROM users
    WHERE username = p_login OR email = p_login;

    -- If user not found, return error message
    IF user_id IS NULL THEN
        SET p_error_message = 'User not found';
    ELSE
        -- Verify password using bcrypt
        IF stored_password = p_password THEN
            SET p_error_message = ''; -- Clear error if password matches
        ELSE
            SET p_error_message = 'Invalid password';
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegisterUser` (IN `p_name` VARCHAR(100), IN `p_username` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(100), IN `p_user_type` VARCHAR(50), OUT `p_error_message` VARCHAR(255))   BEGIN
    DECLARE user_exists INT;

    -- Check if username or email already exists
    SELECT COUNT(*) INTO user_exists
    FROM users
    WHERE username = p_username OR email = p_email;

    IF user_exists > 0 THEN
        SET p_error_message = 'Username or email already exists';
    ELSE
        -- Insert new user into the users table with bcrypt password
        INSERT INTO users (name, username, email, password, user_type)
        VALUES (p_name, p_username, p_email, p_password, p_user_type);
        
        -- Set success message
        SET p_error_message = 'Registration successful';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('textbook','ebook','video','audio','presentation','worksheet','scholarship','other') NOT NULL,
  `resource_type` enum('file','link') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(255) DEFAULT NULL,
  `target_audience` enum('elementary','middle','high','college','teacher','all') NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_access`
--

CREATE TABLE `resource_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `access_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('school','donor','student','admin') NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `user_type`, `organization`, `created_at`) VALUES
(3, 'Admin', 'admin', 'admin@edushare.org', '$2y$10$8tPjdlv.K4A/zRs.uKUM9.XP1rQrHxgM7jUOFDHtLXJVvgiJvpBVe', 'admin', NULL, '2025-04-05 17:41:16'),
(4, 'Admin User', '', 'edushareadmin@edu.ph', '$2y$10$EYzqAyUhYOyZ8hVo48t.guHE5hgXqwZLKRuZ3IWYr1W/UbV4oHc7u', 'admin', NULL, '2025-04-05 17:51:48'),
(6, 'Erryca Bianca Metica Abistado', 'eca', 'erryca@hello.com', '$2y$10$qZxqBT.BIYoQpCxRCaI47.QlN3P7d1awOF3LEsrnyCuFxKyVIwcS.', 'student', 'bsu', '2025-04-25 12:57:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `resource_access`
--
ALTER TABLE `resource_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_access`
--
ALTER TABLE `resource_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_access`
--
ALTER TABLE `resource_access`
  ADD CONSTRAINT `resource_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resource_access_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`, `resource_id`),
  KEY `resource_id` (`resource_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Procedures for bookmarks
--

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ToggleBookmark` (IN `p_user_id` INT, IN `p_resource_id` INT, OUT `p_action` VARCHAR(10))   BEGIN
    DECLARE bookmark_exists INT;
    
    -- Check if bookmark exists
    SELECT COUNT(*) INTO bookmark_exists
    FROM bookmarks
    WHERE user_id = p_user_id AND resource_id = p_resource_id;
    
    IF bookmark_exists > 0 THEN
        -- Remove bookmark
        DELETE FROM bookmarks 
        WHERE user_id = p_user_id AND resource_id = p_resource_id;
        SET p_action = 'removed';
    ELSE
        -- Add bookmark
        INSERT INTO bookmarks (user_id, resource_id)
        VALUES (p_user_id, p_resource_id);
        SET p_action = 'added';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserBookmarks` (IN `p_user_id` INT)   BEGIN
    SELECT r.*, u.name as uploader_name, u.user_type as uploader_type, b.id as bookmark_id
    FROM bookmarks b
    JOIN resources r ON b.resource_id = r.id
    JOIN users u ON r.uploaded_by = u.id
    WHERE b.user_id = p_user_id
    ORDER BY r.title ASC;
END$$

DELIMITER ;
