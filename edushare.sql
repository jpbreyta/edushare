-- Create the database
CREATE DATABASE IF NOT EXISTS edushare;
USE edushare;

-- Create users table first (needed for foreign keys)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'school', 'donor') NOT NULL,
    school_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create schools table
CREATE TABLE IF NOT EXISTS `schools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` enum('elementary','middle','high','college') NOT NULL,
  `region` enum('north','south','east','west','central') NOT NULL,
  `address` text NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 

-- Create resource_categories table
CREATE TABLE IF NOT EXISTS resource_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create resources table
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    resource_type ENUM('document', 'video', 'link', 'other') NOT NULL,
    file_path VARCHAR(255),
    external_link VARCHAR(255),
    target_audience VARCHAR(50),
    uploaded_by INT NOT NULL,
    school_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES resource_categories(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (school_id) REFERENCES schools(id)
);

-- Create donations table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(100) NOT NULL,
    donor_email VARCHAR(100) NOT NULL,
    resource_type ENUM('document', 'video', 'link', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    external_link VARCHAR(255),
    school_id INT NOT NULL,
    donation_date DATE NOT NULL,
    purpose TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id)
);

-- Create resource_downloads table
CREATE TABLE IF NOT EXISTS resource_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create remember_tokens table
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create bookmarks table
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Create feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Create resource_access table
CREATE TABLE IF NOT EXISTS resource_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    access_type ENUM('view', 'download', 'edit') NOT NULL,
    granted_by INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (name, username, email, password, user_type) 
VALUES ('Admin User', 'admin', 'admin@edushare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default resource categories
INSERT INTO resource_categories (name, description) VALUES
('Textbooks', 'Educational textbooks and reference materials'),
('Worksheets', 'Practice worksheets and exercises'),
('Videos', 'Educational videos and tutorials'),
('Presentations', 'Slide presentations and lecture materials'),
('Lesson Plans', 'Teacher lesson plans and guides'),
('Assessments', 'Tests, quizzes, and assessment materials'),
('Activities', 'Classroom activities and projects'),
('Other', 'Other educational resources');

-- Insert sample schools
INSERT INTO schools (name, level, region, address, contact_person, email, phone) VALUES
('Green Valley Primary School', 'primary', 'Central', '123 Education Street, Green Valley', 'John Smith', 'gvps@example.com', '1234567890'),
('Sunshine Secondary School', 'secondary', 'East', '456 Learning Avenue, Sunshine', 'Sarah Johnson', 'sunshine@example.com', '2345678901'),
('Mountain View High School', 'secondary', 'West', '789 Knowledge Road, Mountain View', 'Michael Brown', 'mvhs@example.com', '3456789012'),
('Riverside Elementary', 'primary', 'North', '321 Wisdom Lane, Riverside', 'Emily Davis', 'riverside@example.com', '4567890123'),
('Oceanview College', 'tertiary', 'South', '654 Academic Boulevard, Oceanview', 'David Wilson', 'oceanview@example.com', '5678901234');

-- Insert sample resources
INSERT INTO resources (title, description, category_id, resource_type, file_path, target_audience, uploaded_by, school_id, status) VALUES
('Mathematics Grade 5 Workbook', 'Comprehensive workbook covering basic arithmetic and geometry', 1, 'document', '/uploads/math_grade5.pdf', 'Primary', 1, 1, 'approved'),
('Science Experiments Video Series', '10 engaging science experiments for middle school students', 3, 'video', '/uploads/science_experiments.mp4', 'Secondary', 1, 2, 'approved'),
('English Grammar Worksheets', 'Practice worksheets for English grammar concepts', 2, 'document', '/uploads/grammar_worksheets.pdf', 'Primary', 1, 1, 'approved'),
('History Presentation: Ancient Civilizations', 'Interactive presentation on ancient civilizations', 4, 'document', '/uploads/ancient_civs.pptx', 'Secondary', 1, 2, 'approved'),
('Physics Lab Manual', 'Complete physics laboratory manual for high school', 1, 'document', '/uploads/physics_lab.pdf', 'Secondary', 1, 3, 'approved'),
('Art Activities Collection', 'Creative art activities for primary students', 7, 'document', '/uploads/art_activities.pdf', 'Primary', 1, 4, 'approved'),
('Chemistry Video Tutorials', 'Video tutorials covering basic chemistry concepts', 3, 'video', '/uploads/chemistry_tutorials.mp4', 'Secondary', 1, 2, 'approved'),
('Geography Quiz Bank', 'Collection of geography quizzes and assessments', 6, 'document', '/uploads/geography_quizzes.pdf', 'Secondary', 1, 3, 'approved');

-- Insert sample donations
INSERT INTO donations (donor_name, donor_email, resource_type, title, description, school_id, donation_date, purpose, status) VALUES
('ABC Publishing', 'contact@abcpublishing.com', 'document', 'Mathematics Textbooks', 'Set of 50 mathematics textbooks for primary students', 1, '2024-01-15', 'Support primary education', 'completed'),
('TechEd Solutions', 'info@teched.com', 'video', 'Science Learning Videos', 'Collection of 20 educational science videos', 2, '2024-02-01', 'Enhance science education', 'in_progress'),
('Book Donors Inc', 'donate@bookdonors.org', 'document', 'Library Books', 'Collection of 100 children\'s books', 4, '2024-01-20', 'Expand school library', 'completed'),
('EduTech Foundation', 'contact@edutech.org', 'link', 'Online Learning Platform', 'Access to premium online learning resources', 3, '2024-02-10', 'Digital education support', 'pending'),
('Local Business Association', 'info@localbusiness.org', 'other', 'Educational Supplies', 'Various school supplies and materials', 1, '2024-01-25', 'General school support', 'completed'),
('Science Foundation', 'donate@sciencefoundation.org', 'document', 'Science Lab Equipment', 'Set of laboratory equipment and manuals', 2, '2024-02-05', 'Enhance science facilities', 'in_progress'),
('Community Group', 'contact@communitygroup.org', 'other', 'Sports Equipment', 'Various sports equipment for physical education', 4, '2024-01-30', 'Support physical education', 'completed'),
('Tech Company', 'donate@techcompany.com', 'video', 'Coding Tutorials', 'Series of programming tutorials for students', 3, '2024-02-15', 'Promote digital literacy', 'pending'); 