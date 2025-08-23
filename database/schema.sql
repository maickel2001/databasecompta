-- Maickel Okereke Professional Website Database Schema
-- Created: 2024

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `maickel_website`
CREATE DATABASE IF NOT EXISTS `maickel_website` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `maickel_website`;

-- --------------------------------------------------------
-- Table structure for table `users` (admin only)
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `posts` (blog articles)
-- --------------------------------------------------------

CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text,
  `featured_image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `status` enum('draft','published') DEFAULT 'draft',
  `author_id` int(11) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `comments`
-- --------------------------------------------------------

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(100) NOT NULL,
  `author_avatar` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('pending','approved','spam') DEFAULT 'approved',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `likes`
-- --------------------------------------------------------

CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`, `ip_address`),
  KEY `post_id` (`post_id`),
  FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `messages` (contact form)
-- --------------------------------------------------------

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default admin user (password: admin123 - should be changed!)
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'maickel@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maickel Okereke', 'admin');

-- --------------------------------------------------------
-- Sample blog posts
-- --------------------------------------------------------

INSERT INTO `posts` (`title`, `content`, `excerpt`, `slug`, `status`, `author_id`) VALUES
('Welcome to My Professional Website', 'Welcome to my professional website! I am Maickel Okereke, a dedicated Accountant and Web Developer with years of experience in both fields. This website serves as a platform to share my expertise, showcase my work, and connect with potential clients and colleagues.', 'Welcome to my professional website! Learn about my journey as an Accountant and Web Developer.', 'welcome-to-my-professional-website', 'published', 1),
('The Future of Web Development', 'Web development is constantly evolving, with new technologies and frameworks emerging regularly. In this post, I explore the latest trends and what they mean for businesses and developers alike.', 'Exploring the latest trends in web development and their impact on businesses.', 'future-of-web-development', 'published', 1),
('Essential Accounting Tips for Small Businesses', 'Managing finances is crucial for small business success. Here are some essential accounting tips that every small business owner should know to maintain healthy finances and ensure compliance.', 'Essential accounting tips to help small businesses maintain healthy finances.', 'accounting-tips-small-businesses', 'published', 1);

COMMIT;