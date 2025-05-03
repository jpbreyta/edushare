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
  `id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `category` ENUM('textbook', 'ebook', 'presentation', 'worksheet') NOT NULL,
  `resource_type` ENUM('file', 'link') NOT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `external_link` VARCHAR(255) DEFAULT NULL,
  `target_audience` ENUM('cics', 'cte', 'cit', 'cas', 'cabe') NOT NULL,
  `uploaded_by` INT(11) NOT NULL,
  `upload_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `is_visible` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
(4, 'Admin User', '', 'edushareadmin@edu.ph', '$2y$10$EYzqAyUhYOyZ8hVo48t.guHE5hgXqwZLKRuZ3IWYr1W/UbV4oHc7u', 'admin', NULL, '2025-04-05 17:51:48');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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

/* Bookmarks */

CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, resource_id)
);

/* Feedback */

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


-- Procedure to get uploaded resources count
DELIMITER //
CREATE PROCEDURE GetUploadedResourcesCount(IN userId INT)
BEGIN
    SELECT COUNT(*) AS count FROM resources WHERE uploaded_by = userId;
END //
DELIMITER ;

-- Procedure to get accessed resources count
DELIMITER //
CREATE PROCEDURE GetAccessedResourcesCount(IN userId INT)
BEGIN
    SELECT COUNT(*) AS count FROM resource_access WHERE user_id = userId;
END //
DELIMITER ;

-- Procedure to get recent resources
DELIMITER //
CREATE PROCEDURE GetRecentResources()
BEGIN
    SELECT r.*, u.name AS uploader_name, u.user_type AS uploader_type 
    FROM resources r 
    JOIN users u ON r.uploaded_by = u.id 
    ORDER BY r.upload_date DESC 
    LIMIT 5;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE GetFilteredResources(
    IN in_category VARCHAR(255),
    IN in_audience VARCHAR(255),
    IN in_search VARCHAR(255)
)
BEGIN
    SELECT r.*, u.name AS uploader_name, u.user_type AS uploader_type
    FROM resources r
    JOIN users u ON r.uploaded_by = u.id
    WHERE r.is_visible = 1
      AND (in_category IS NULL OR r.category = in_category)
      AND (in_audience IS NULL OR r.target_audience = in_audience)
      AND (
          in_search IS NULL OR 
          r.title LIKE CONCAT('%', in_search, '%') OR 
          r.description LIKE CONCAT('%', in_search, '%')
      )
    ORDER BY r.upload_date DESC;
END //
DELIMITER ;

-- Inserting value -- 
DELIMITER //
CREATE PROCEDURE UploadResource(
    IN in_title VARCHAR(255),
    IN in_description TEXT,
    IN in_category VARCHAR(255),
    IN in_resource_type VARCHAR(50),
    IN in_file_path VARCHAR(500),
    IN in_external_link VARCHAR(500),
    IN in_target_audience VARCHAR(255),
    IN in_uploaded_by INT
)
BEGIN
    INSERT INTO resources (
        title, description, category, resource_type, file_path, external_link, target_audience, uploaded_by
    ) VALUES (
        in_title, in_description, in_category, in_resource_type, in_file_path, in_external_link, in_target_audience, in_uploaded_by
    );
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE GetResourceDetails(IN in_resource_id INT)
BEGIN
    SELECT r.*, u.name AS uploader_name, u.user_type AS uploader_type
    FROM resources r
    JOIN users u ON r.uploaded_by = u.id
    WHERE r.id = in_resource_id;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE CheckRecentResourceAccess(IN in_user_id INT, IN in_resource_id INT)
BEGIN
    SELECT id 
    FROM resource_access 
    WHERE user_id = in_user_id 
      AND resource_id = in_resource_id 
      AND access_date > DATE_SUB(NOW(), INTERVAL 1 DAY);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE LogResourceAccess(IN in_user_id INT, IN in_resource_id INT)
BEGIN
    INSERT INTO resource_access (user_id, resource_id)
    VALUES (in_user_id, in_resource_id);
END //
DELIMITER ;