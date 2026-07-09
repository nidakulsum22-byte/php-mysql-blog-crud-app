-- ApexPlanet Internship — Task 2: Database Setup
-- Run this in phpMyAdmin or the MySQL CLI to create the schema.

CREATE DATABASE IF NOT EXISTS blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog;

-- Task 4: users table extended with `role` for role-based access control
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed data (passwords are hashed versions of: admin123 / editor123)
-- Generated using PHP's password_hash() — see seed.php to regenerate safely.
INSERT INTO users (username, password, role) VALUES
('admin',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('editor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor');

INSERT INTO posts (title, content, author_id) VALUES
('Getting Started with PHP', 'PHP is a widely-used open source general-purpose scripting language that is especially suited for web development. In this post, we explore the basics of PHP syntax, variables, loops, and functions to help you get started.', 1),
('MySQL Joins Explained', 'Understanding SQL joins is crucial for any backend developer. INNER JOIN, LEFT JOIN, RIGHT JOIN, and FULL OUTER JOIN each serve different purposes when querying relational data.', 1),
('Securing Your PHP App', 'Web security is non-negotiable. SQL injection, XSS, and CSRF are among the most common attack vectors. Using prepared statements with PDO, sanitizing inputs, and implementing CSRF tokens are all essential practices.', 2),
('Building REST APIs with PHP', 'REST APIs power modern web and mobile applications. This guide covers how to design RESTful endpoints in PHP, handle HTTP methods, and return proper JSON responses.', 2);
