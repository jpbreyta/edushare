-- Create the database
CREATE DATABASE IF NOT EXISTS edushare;
USE edushare;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS schools;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS activity_logs;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'school_admin', 'donor', 'teacher') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create schools table
CREATE TABLE schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    level ENUM('primary', 'secondary', 'tertiary') NOT NULL,
    region VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create resources table
CREATE TABLE resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    resource_type ENUM('textbook', 'ebook', 'video', 'other') NOT NULL,
    file_path VARCHAR(255),
    uploader_id INT,
    school_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
);

-- Create donations table
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2),
    donor_id INT,
    school_id INT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
);

-- Create activity_logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user
INSERT INTO users (name, email, password, user_type) VALUES 
('Admin User', 'admin@edushare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create stored procedures for schools
DELIMITER //

CREATE PROCEDURE sp_get_all_schools()
BEGIN
    SELECT * FROM schools ORDER BY name;
END //

CREATE PROCEDURE sp_add_school(
    IN p_name VARCHAR(100),
    IN p_level ENUM('primary', 'secondary', 'tertiary'),
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
    IN p_level ENUM('primary', 'secondary', 'tertiary'),
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

-- Create stored procedures for resources
CREATE PROCEDURE sp_get_all_resources()
BEGIN
    SELECT r.*, u.name as uploader_name, s.name as school_name
    FROM resources r
    LEFT JOIN users u ON r.uploader_id = u.id
    LEFT JOIN schools s ON r.school_id = s.id
    ORDER BY r.created_at DESC;
END //

CREATE PROCEDURE sp_add_resource(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_resource_type ENUM('textbook', 'ebook', 'video', 'other'),
    IN p_file_path VARCHAR(255),
    IN p_uploader_id INT,
    IN p_school_id INT
)
BEGIN
    INSERT INTO resources (title, description, resource_type, file_path, uploader_id, school_id)
    VALUES (p_title, p_description, p_resource_type, p_file_path, p_uploader_id, p_school_id);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_resource(
    IN p_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_resource_type ENUM('textbook', 'ebook', 'video', 'other'),
    IN p_file_path VARCHAR(255),
    IN p_status ENUM('pending', 'approved', 'rejected')
)
BEGIN
    UPDATE resources 
    SET title = p_title,
        description = p_description,
        resource_type = p_resource_type,
        file_path = p_file_path,
        status = p_status
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
    LEFT JOIN users u ON r.uploader_id = u.id
    LEFT JOIN schools s ON r.school_id = s.id
    WHERE r.id = p_id;
END //

-- Create stored procedures for donations
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
    IN p_status ENUM('pending', 'in_progress', 'completed', 'cancelled')
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

-- Create stored procedures for users
CREATE PROCEDURE sp_get_all_users()
BEGIN
    SELECT * FROM users ORDER BY name;
END //

CREATE PROCEDURE sp_add_user(
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255),
    IN p_user_type ENUM('admin', 'school_admin', 'donor', 'teacher')
)
BEGIN
    INSERT INTO users (name, email, password, user_type)
    VALUES (p_name, p_email, p_password, p_user_type);
    SELECT LAST_INSERT_ID() as id;
END //

CREATE PROCEDURE sp_update_user(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_user_type ENUM('admin', 'school_admin', 'donor', 'teacher')
)
BEGIN
    UPDATE users 
    SET name = p_name,
        email = p_email,
        user_type = p_user_type
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

-- Create stored procedure for activity logs
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

DELIMITER ;
