-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 08:02 PM
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
-- Database: `edushare`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','school','donor','student') NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `level` enum('primary','secondary','tertiary') NOT NULL,
  `region` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('textbook','ebook','presentation','worksheet') NOT NULL,
  `resource_type` enum('file','link') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(255) DEFAULT NULL,
  `target_audience` enum('cics','cte','cit','cas','cabe') NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_visible` boolean NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resources_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_access`
--

CREATE TABLE `resource_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `access_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  CONSTRAINT `resource_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resource_access_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `amount` decimal(10,2) DEFAULT NULL,
  `donor_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `donor_id` (`donor_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`,`resource_id`),
  KEY `resource_id` (`resource_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `user_type`, `organization`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin', 'admin@edushare.org', '$2y$10$8tPjdlv.K4A/zRs.uKUM9.XP1rQrHxgM7jUOFDHtLXJVvgiJvpBVe', 'admin', NULL, 'active', '2025-04-05 17:41:16'),
(2, 'Admin User', 'edushareadmin', 'edushareadmin@edu.ph', '$2y$10$EYzqAyUhYOyZ8hVo48t.guHE5hgXqwZLKRuZ3IWYr1W/UbV4oHc7u', 'admin', NULL, 'active', '2025-04-05 17:51:48');

-- --------------------------------------------------------

--
-- Stored Procedures
--

DELIMITER //

-- User Procedures
CREATE PROCEDURE sp_get_all_users()
BEGIN
    SELECT * FROM users ORDER BY name;
END //

CREATE PROCEDURE sp_add_user(
    IN p_name VARCHAR(100),
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255),
    IN p_user_type ENUM('admin','school','donor','student'),
    IN p_organization VARCHAR(100)
)
BEGIN
    INSERT INTO users (name, username, email, password, user_type, organization)
    VALUES (p_name, p_username, p_email, p_password, p_user_type, p_organization);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_user(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_user_type ENUM('admin','school','donor','student'),
    IN p_organization VARCHAR(100),
    IN p_status ENUM('active','inactive')
)
BEGIN
    UPDATE users 
    SET name = p_name,
        username = p_username,
        email = p_email,
        user_type = p_user_type,
        organization = p_organization,
        status = p_status
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_delete_user(IN p_id INT)
BEGIN
    DELETE FROM users WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_get_user_by_id(IN p_id INT)
BEGIN
    SELECT * FROM users WHERE id = p_id;
END //

-- School Procedures
CREATE PROCEDURE sp_get_all_schools()
BEGIN
    SELECT * FROM schools ORDER BY name;
END //

CREATE PROCEDURE sp_add_school(
    IN p_name VARCHAR(100),
    IN p_level ENUM('primary','secondary','tertiary'),
    IN p_region VARCHAR(100),
    IN p_address TEXT,
    IN p_contact_person VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_phone VARCHAR(20)
)
BEGIN
    INSERT INTO schools (name, level, region, address, contact_person, email, phone)
    VALUES (p_name, p_level, p_region, p_address, p_contact_person, p_email, p_phone);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_school(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_level ENUM('primary','secondary','tertiary'),
    IN p_region VARCHAR(100),
    IN p_address TEXT,
    IN p_contact_person VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_phone VARCHAR(20)
)
BEGIN
    UPDATE schools 
    SET name = p_name,
        level = p_level,
        region = p_region,
        address = p_address,
        contact_person = p_contact_person,
        email = p_email,
        phone = p_phone
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_delete_school(IN p_id INT)
BEGIN
    DELETE FROM schools WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_get_school_by_id(IN p_id INT)
BEGIN
    SELECT * FROM schools WHERE id = p_id;
END //

-- Resource Procedures
CREATE PROCEDURE sp_get_all_resources()
BEGIN
    SELECT r.*, u.name as uploader_name, s.name as school_name
    FROM resources r
    LEFT JOIN users u ON r.uploaded_by = u.id
    LEFT JOIN schools s ON r.school_id = s.id
    ORDER BY r.upload_date DESC;
END //

CREATE PROCEDURE sp_add_resource(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_category ENUM('textbook','ebook','presentation','worksheet'),
    IN p_resource_type ENUM('file','link'),
    IN p_file_path VARCHAR(255),
    IN p_external_link VARCHAR(255),
    IN p_target_audience ENUM('cics','cte','cit','cas','cabe'),
    IN p_uploaded_by INT,
    IN p_school_id INT
)
BEGIN
    INSERT INTO resources (title, description, category, resource_type, file_path, external_link, target_audience, uploaded_by, school_id)
    VALUES (p_title, p_description, p_category, p_resource_type, p_file_path, p_external_link, p_target_audience, p_uploaded_by, p_school_id);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_resource(
    IN p_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_category ENUM('textbook','ebook','presentation','worksheet'),
    IN p_resource_type ENUM('file','link'),
    IN p_file_path VARCHAR(255),
    IN p_external_link VARCHAR(255),
    IN p_target_audience ENUM('cics','cte','cit','cas','cabe'),
    IN p_school_id INT,
    IN p_status ENUM('pending','approved','rejected'),
    IN p_is_visible BOOLEAN
)
BEGIN
    UPDATE resources 
    SET title = p_title,
        description = p_description,
        category = p_category,
        resource_type = p_resource_type,
        file_path = p_file_path,
        external_link = p_external_link,
        target_audience = p_target_audience,
        school_id = p_school_id,
        status = p_status,
        is_visible = p_is_visible
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_delete_resource(IN p_id INT)
BEGIN
    DELETE FROM resources WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_get_resource_by_id(IN p_id INT)
BEGIN
    SELECT r.*, u.name as uploader_name, s.name as school_name
    FROM resources r
    LEFT JOIN users u ON r.uploaded_by = u.id
    LEFT JOIN schools s ON r.school_id = s.id
    WHERE r.id = p_id;
END //

-- Donation Procedures
CREATE PROCEDURE sp_get_all_donations()
BEGIN
    SELECT d.*, u.name as donor_name, s.name as school_name
    FROM donations d
    LEFT JOIN users u ON d.donor_id = u.id
    LEFT JOIN schools s ON d.school_id = s.id
    ORDER BY d.created_at DESC;
END //

CREATE PROCEDURE sp_add_donation(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_amount DECIMAL(10,2),
    IN p_donor_id INT,
    IN p_school_id INT
)
BEGIN
    INSERT INTO donations (title, description, amount, donor_id, school_id)
    VALUES (p_title, p_description, p_amount, p_donor_id, p_school_id);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_donation(
    IN p_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_amount DECIMAL(10,2),
    IN p_status ENUM('pending','in_progress','completed','cancelled')
)
BEGIN
    UPDATE donations 
    SET title = p_title,
        description = p_description,
        amount = p_amount,
        status = p_status
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_delete_donation(IN p_id INT)
BEGIN
    DELETE FROM donations WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_get_donation_by_id(IN p_id INT)
BEGIN
    SELECT d.*, u.name as donor_name, s.name as school_name
    FROM donations d
    LEFT JOIN users u ON d.donor_id = u.id
    LEFT JOIN schools s ON d.school_id = s.id
    WHERE d.id = p_id;
END //

-- Activity Log Procedures
CREATE PROCEDURE sp_add_activity_log(
    IN p_user_id INT,
    IN p_action VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO activity_logs (user_id, action, description)
    VALUES (p_user_id, p_action, p_description);
    SELECT LAST_INSERT_ID() as id;
END //

-- Resource Access Procedures
CREATE PROCEDURE sp_log_resource_access(
    IN p_user_id INT,
    IN p_resource_id INT
)
BEGIN
    INSERT INTO resource_access (user_id, resource_id)
    VALUES (p_user_id, p_resource_id);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_get_resource_access_count(IN p_user_id INT)
BEGIN
    SELECT COUNT(*) as count FROM resource_access WHERE user_id = p_user_id;
END //

-- Bookmark Procedures
CREATE PROCEDURE sp_add_bookmark(
    IN p_user_id INT,
    IN p_resource_id INT
)
BEGIN
    INSERT INTO bookmarks (user_id, resource_id)
    VALUES (p_user_id, p_resource_id);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_remove_bookmark(
    IN p_user_id INT,
    IN p_resource_id INT
)
BEGIN
    DELETE FROM bookmarks WHERE user_id = p_user_id AND resource_id = p_resource_id;
    SELECT ROW_COUNT() as affected_rows;
END //

CREATE PROCEDURE sp_get_user_bookmarks(IN p_user_id INT)
BEGIN
    SELECT r.*, u.name as uploader_name, s.name as school_name
    FROM bookmarks b
    JOIN resources r ON b.resource_id = r.id
    LEFT JOIN users u ON r.uploaded_by = u.id
    LEFT JOIN schools s ON r.school_id = s.id
    WHERE b.user_id = p_user_id
    ORDER BY b.created_at DESC;
END //

-- Feedback Procedures
CREATE PROCEDURE sp_add_feedback(
    IN p_user_id INT,
    IN p_message TEXT
)
BEGIN
    INSERT INTO feedback (user_id, message)
    VALUES (p_user_id, p_message);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_get_all_feedback()
BEGIN
    SELECT f.*, u.name as user_name, u.user_type
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC;
END //

DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;