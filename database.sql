-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 06, 2026 at 06:49 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `financeapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `type` enum('over','warning','info') NOT NULL DEFAULT 'info',
  `message` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `period` enum('monthly','weekly','custom') NOT NULL DEFAULT 'monthly',
  `global_limit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `is_shared` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `owner_id`, `name`, `period`, `global_limit`, `date_from`, `date_to`, `is_shared`, `created_at`) VALUES
(2, 2, 'Colocation Groupe 9', 'monthly', 2000.00, '2025-06-01', '2025-06-30', 1, '2026-06-05 18:36:21'),
(3, 1, 'Budget juin', 'monthly', 3000.00, '2026-06-01', '2026-06-30', 0, '2026-06-05 19:47:17'),
(4, 1, 'Budget Jan', 'monthly', 2800.00, '2026-01-01', '2026-01-30', 0, '2026-06-05 20:11:17'),
(5, 1, 'Budget Feb', 'monthly', 3000.00, '2026-02-01', '2026-02-28', 0, '2026-06-05 20:25:02'),
(6, 1, 'Budget Mar', 'monthly', 3000.00, '2026-03-01', '2026-03-30', 0, '2026-06-05 20:26:57'),
(7, 1, 'Budget Apr', 'monthly', 3000.00, '2026-04-01', '2026-04-30', 0, '2026-06-05 20:30:26'),
(8, 2, 'Budget Jan', 'monthly', 3000.00, '2026-01-01', '2026-01-30', 0, '2026-06-05 20:34:47'),
(9, 2, 'Budget Feb', 'monthly', 3000.00, '2026-03-01', '2026-03-30', 0, '2026-06-05 20:38:36'),
(10, 2, 'Budget Mar', 'monthly', 3000.00, '2026-03-01', '2026-03-30', 0, '2026-06-05 20:43:14'),
(11, 2, 'Budget Apr', 'monthly', 3000.00, '2026-04-01', '2026-04-30', 0, '2026-06-05 20:43:51'),
(12, 2, 'Budget May', 'monthly', 3000.00, '2026-05-01', '2026-05-30', 0, '2026-06-05 21:31:19'),
(14, 2, 'test', 'weekly', 1000.00, '2026-06-01', '2026-06-30', 1, '2026-06-06 13:33:16');

-- --------------------------------------------------------

--
-- Table structure for table `budget_categories`
--

CREATE TABLE `budget_categories` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `cat_limit` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_members`
--

CREATE TABLE `budget_members` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','member','readonly') NOT NULL DEFAULT 'member',
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_members`
--

INSERT INTO `budget_members` (`id`, `budget_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 2, 2, 'admin', '2026-06-05 18:36:21'),
(2, 2, 3, 'member', '2026-06-05 18:36:21'),
(3, 2, 4, 'member', '2026-06-05 18:36:21'),
(4, 3, 1, 'admin', '2026-06-05 19:47:17'),
(5, 4, 1, 'admin', '2026-06-05 20:11:17'),
(6, 5, 1, 'admin', '2026-06-05 20:25:02'),
(7, 6, 1, 'admin', '2026-06-05 20:26:57'),
(8, 7, 1, 'admin', '2026-06-05 20:30:26'),
(9, 8, 2, 'admin', '2026-06-05 20:34:47'),
(10, 9, 2, 'admin', '2026-06-05 20:38:36'),
(11, 10, 2, 'admin', '2026-06-05 20:43:14'),
(12, 11, 2, 'admin', '2026-06-05 20:43:51'),
(13, 12, 2, 'admin', '2026-06-05 21:31:19'),
(16, 14, 2, 'admin', '2026-06-06 13:33:16'),
(17, 14, 1, 'member', '2026-06-06 13:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `icon` varchar(10) DEFAULT '?',
  `color` varchar(20) DEFAULT '#DBEAFE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `icon`, `color`) VALUES
(1, NULL, 'Alimentation', '🍔', '#FEF9C3'),
(2, NULL, 'Transport', '🚗', '#DBEAFE'),
(3, NULL, 'Logement', '🏠', '#EDE9FE'),
(4, NULL, 'Santé', '💊', '#FEE2E2'),
(5, NULL, 'Loisirs', '🎮', '#D1FAE5'),
(6, NULL, 'Études', '📚', '#E0F2FE'),
(7, NULL, 'Salaire', '💼', '#DCFCE7'),
(8, NULL, 'Épargne', '🐖', '#FEF3C7');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `tx_date` date NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `budget_id`, `category_id`, `type`, `amount`, `description`, `tx_date`, `comment`, `created_at`) VALUES
(1, 2, NULL, 1, 'expense', 83.15, 'Supermarché Carrefour', '2026-06-14', NULL, '2026-06-05 18:36:21'),
(3, 2, NULL, 2, 'expense', 45.00, 'Abonnement TER', '2026-06-13', NULL, '2026-06-05 18:36:21'),
(4, 3, NULL, 5, 'expense', 15.49, 'Netflix', '2026-06-12', NULL, '2026-06-05 18:36:21'),
(5, 2, NULL, 4, 'expense', 28.50, 'Ordonnance', '2026-06-11', NULL, '2026-06-05 18:36:21'),
(7, 2, NULL, 6, 'expense', 65.00, 'Manuels scolaires', '2026-06-09', NULL, '2026-06-05 18:36:21'),
(8, 4, NULL, 2, 'expense', 55.00, 'Essence', '2026-06-08', NULL, '2026-06-05 18:36:21'),
(9, 3, NULL, 1, 'expense', 42.00, 'Restaurant', '2026-06-07', NULL, '2026-06-05 18:36:21'),
(10, 2, NULL, 5, 'expense', 24.00, 'Cinéma', '2026-06-06', NULL, '2026-06-05 18:36:21'),
(11, 2, NULL, 7, 'income', 2800.00, 'Salaire', '2026-06-01', NULL, '2026-06-05 18:36:21'),
(12, 4, NULL, 2, 'expense', 89.00, 'Train Paris', '2026-05-28', NULL, '2026-06-05 18:36:21'),
(14, 2, NULL, 1, 'expense', 32.50, 'Courses', '2026-06-01', NULL, '2026-06-05 19:29:38'),
(15, 2, NULL, 2, 'expense', 18.00, 'Bus', '2026-06-02', NULL, '2026-06-05 19:29:38'),
(16, 2, NULL, 5, 'expense', 25.00, 'Cinéma', '2026-06-03', NULL, '2026-06-05 19:29:38'),
(18, 3, 2, 1, 'expense', 54.00, 'Supermarché', '2026-06-02', NULL, '2026-06-05 19:29:38'),
(19, 3, 2, 5, 'expense', 12.99, 'Netflix', '2026-06-04', NULL, '2026-06-05 19:29:38'),
(20, 3, 2, 7, 'income', 1800.00, 'Salaire', '2026-06-01', NULL, '2026-06-05 19:29:38'),
(21, 4, 2, 2, 'expense', 75.00, 'Essence', '2026-06-05', NULL, '2026-06-05 19:29:38'),
(22, 4, 2, 3, 'expense', 600.00, 'Loyer', '2026-06-01', NULL, '2026-06-05 19:29:38'),
(23, 4, 2, 7, 'income', 2200.00, 'Salaire', '2026-06-01', NULL, '2026-06-05 19:29:38'),
(24, 2, 2, 1, 'expense', 120.00, 'Courses communes', '2026-06-01', NULL, '2026-06-05 19:29:54'),
(25, 3, 2, 1, 'expense', 95.00, 'Achats cuisine', '2026-06-03', NULL, '2026-06-05 19:29:54'),
(26, 4, 2, 2, 'expense', 60.00, 'Transport commun', '2026-06-05', NULL, '2026-06-05 19:29:54'),
(27, 2, NULL, 7, 'income', 2800.00, 'Salaire Janvier', '2026-01-01', '', '2026-06-05 19:34:20'),
(28, 2, NULL, 7, 'income', 2800.00, 'Salaire Février', '2026-02-01', NULL, '2026-06-05 19:34:20'),
(29, 2, NULL, 7, 'income', 2800.00, 'Salaire Mars', '2026-03-01', '', '2026-06-05 19:34:20'),
(30, 2, NULL, 7, 'income', 2800.00, 'Salaire Avril', '2026-04-01', '', '2026-06-05 19:34:20'),
(31, 2, NULL, 7, 'income', 2800.00, 'Salaire Mai', '2026-05-01', '', '2026-06-05 19:34:20'),
(33, 2, 8, 3, 'expense', 750.00, 'Loyer', '2026-01-05', '', '2026-06-05 19:34:35'),
(34, 2, 9, 3, 'expense', 750.00, 'Loyer', '2026-02-05', '', '2026-06-05 19:34:35'),
(35, 2, 10, 3, 'expense', 750.00, 'Loyer', '2026-03-05', '', '2026-06-05 19:34:35'),
(36, 2, 11, 3, 'expense', 750.00, 'Loyer', '2026-04-05', '', '2026-06-05 19:34:35'),
(37, 2, 12, 3, 'expense', 750.00, 'Loyer', '2026-05-05', '', '2026-06-05 19:34:35'),
(38, 2, NULL, 3, 'expense', 750.00, 'Loyer', '2026-06-05', NULL, '2026-06-05 19:34:35'),
(39, 2, 8, 1, 'expense', 95.00, 'Supermarché', '2026-01-10', '', '2026-06-05 19:34:35'),
(40, 2, 9, 1, 'expense', 110.00, 'Supermarché', '2026-02-10', '', '2026-06-05 19:34:35'),
(41, 2, 10, 1, 'expense', 120.00, 'Supermarché', '2026-03-10', '', '2026-06-05 19:34:35'),
(42, 2, 11, 1, 'expense', 90.00, 'Supermarché', '2026-04-10', '', '2026-06-05 19:34:35'),
(43, 2, 12, 1, 'expense', 130.00, 'Supermarché', '2026-05-10', '', '2026-06-05 19:34:35'),
(45, 3, 2, 7, 'income', 2200.00, 'Salaire Janvier', '2026-01-01', NULL, '2026-06-05 19:34:44'),
(46, 3, 2, 7, 'income', 2200.00, 'Salaire Février', '2026-02-01', NULL, '2026-06-05 19:34:44'),
(47, 3, 2, 7, 'income', 2200.00, 'Salaire Mars', '2026-03-01', NULL, '2026-06-05 19:34:44'),
(48, 3, 2, 7, 'income', 2200.00, 'Salaire Avril', '2026-04-01', NULL, '2026-06-05 19:34:44'),
(49, 3, 2, 7, 'income', 2200.00, 'Salaire Mai', '2026-05-01', NULL, '2026-06-05 19:34:44'),
(50, 3, 2, 7, 'income', 2200.00, 'Salaire Juin', '2026-06-01', NULL, '2026-06-05 19:34:44'),
(51, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-01-15', NULL, '2026-06-05 19:34:44'),
(52, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-02-15', NULL, '2026-06-05 19:34:44'),
(53, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-03-15', NULL, '2026-06-05 19:34:44'),
(54, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-04-15', NULL, '2026-06-05 19:34:44'),
(55, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-05-15', NULL, '2026-06-05 19:34:44'),
(56, 3, 2, 5, 'expense', 15.49, 'Netflix', '2026-06-15', NULL, '2026-06-05 19:34:44'),
(57, 4, 2, 7, 'income', 2600.00, 'Salaire Janvier', '2026-01-01', NULL, '2026-06-05 19:34:55'),
(58, 4, 2, 7, 'income', 2600.00, 'Salaire Février', '2026-02-01', NULL, '2026-06-05 19:34:55'),
(59, 4, 2, 7, 'income', 2600.00, 'Salaire Mars', '2026-03-01', NULL, '2026-06-05 19:34:55'),
(60, 4, 2, 7, 'income', 2600.00, 'Salaire Avril', '2026-04-01', NULL, '2026-06-05 19:34:55'),
(61, 4, 2, 7, 'income', 2600.00, 'Salaire Mai', '2026-05-01', NULL, '2026-06-05 19:34:55'),
(62, 4, 2, 7, 'income', 2600.00, 'Salaire Juin', '2026-06-01', NULL, '2026-06-05 19:34:55'),
(63, 4, 2, 2, 'expense', 70.00, 'Essence', '2026-01-12', NULL, '2026-06-05 19:34:55'),
(64, 4, 2, 2, 'expense', 65.00, 'Essence', '2026-02-12', NULL, '2026-06-05 19:34:55'),
(65, 4, 2, 2, 'expense', 80.00, 'Essence', '2026-03-12', NULL, '2026-06-05 19:34:55'),
(66, 4, 2, 2, 'expense', 75.00, 'Essence', '2026-04-12', NULL, '2026-06-05 19:34:55'),
(67, 4, 2, 2, 'expense', 90.00, 'Essence', '2026-05-12', NULL, '2026-06-05 19:34:55'),
(68, 4, 2, 2, 'expense', 55.00, 'Essence', '2026-06-12', NULL, '2026-06-05 19:34:55'),
(70, 3, 2, 1, 'expense', 95.00, 'Courses communes', '2026-06-06', NULL, '2026-06-05 19:35:04'),
(71, 4, 2, 2, 'expense', 60.00, 'Transport partagé', '2026-06-10', NULL, '2026-06-05 19:35:04'),
(72, 2, 2, 5, 'expense', 45.00, 'Sortie groupe', '2026-06-12', NULL, '2026-06-05 19:35:04'),
(73, 3, 2, 5, 'expense', 38.00, 'Cinéma groupe', '2026-06-15', NULL, '2026-06-05 19:35:04'),
(74, 1, NULL, 7, 'income', 4500.00, 'Salaire Janvier', '2026-01-01', '', '2026-06-05 19:45:40'),
(75, 1, 4, 3, 'expense', 1200.00, 'Loyer', '2026-01-03', '', '2026-06-05 19:45:40'),
(76, 1, 4, 1, 'expense', 320.00, 'Courses', '2026-01-05', '', '2026-06-05 19:45:40'),
(77, 1, 4, 2, 'expense', 150.00, 'Transport', '2026-01-10', '', '2026-06-05 19:45:40'),
(78, 1, 4, 5, 'expense', 180.00, 'Loisirs', '2026-01-15', '', '2026-06-05 19:45:40'),
(79, 1, NULL, 7, 'income', 4700.00, 'Salaire Février', '2026-02-01', NULL, '2026-06-05 19:45:51'),
(80, 1, 5, 3, 'expense', 1200.00, 'Loyer', '2026-02-03', '', '2026-06-05 19:45:51'),
(81, 1, 5, 1, 'expense', 410.00, 'Courses', '2026-02-07', '', '2026-06-05 19:45:51'),
(82, 1, 5, 4, 'expense', 85.00, 'Médecin', '2026-02-12', '', '2026-06-05 19:45:51'),
(83, 1, 5, 5, 'expense', 250.00, 'Restaurant', '2026-02-18', '', '2026-06-05 19:45:51'),
(84, 1, NULL, 7, 'income', 5000.00, 'Salaire Mars', '2026-03-01', NULL, '2026-06-05 19:46:04'),
(85, 1, 6, 3, 'expense', 1200.00, 'Loyer', '2026-03-03', '', '2026-06-05 19:46:04'),
(86, 1, 6, 2, 'expense', 240.00, 'Carburant', '2026-03-08', '', '2026-06-05 19:46:04'),
(87, 1, 6, 6, 'expense', 320.00, 'Formation PHP', '2026-03-15', '', '2026-06-05 19:46:04'),
(88, 1, 6, 5, 'expense', 130.00, 'Cinéma', '2026-03-22', '', '2026-06-05 19:46:04'),
(89, 1, NULL, 7, 'income', 5200.00, 'Salaire Avril', '2026-04-01', NULL, '2026-06-05 19:46:15'),
(90, 1, 7, 3, 'expense', 1200.00, 'Loyer', '2026-04-03', '', '2026-06-05 19:46:15'),
(91, 1, 7, 1, 'expense', 500.00, 'Courses', '2026-04-07', '', '2026-06-05 19:46:15'),
(92, 1, 7, 4, 'expense', 220.00, 'Dentiste', '2026-04-12', '', '2026-06-05 19:46:15'),
(93, 1, 7, 5, 'expense', 350.00, 'Weekend', '2026-04-24', '', '2026-06-05 19:46:15'),
(94, 1, NULL, 7, 'income', 4800.00, 'Salaire Mai', '2026-05-01', NULL, '2026-06-05 19:46:26'),
(95, 1, NULL, 3, 'expense', 1200.00, 'Loyer', '2026-05-03', NULL, '2026-06-05 19:46:26'),
(96, 1, NULL, 1, 'expense', 370.00, 'Courses', '2026-05-09', NULL, '2026-06-05 19:46:26'),
(97, 1, NULL, 2, 'expense', 190.00, 'Transport', '2026-05-14', NULL, '2026-06-05 19:46:26'),
(98, 1, NULL, 5, 'expense', 420.00, 'Vacances', '2026-05-21', NULL, '2026-06-05 19:46:26'),
(99, 1, NULL, 7, 'income', 5500.00, 'Salaire Juin', '2026-06-01', NULL, '2026-06-05 19:46:37'),
(100, 1, 3, 3, 'expense', 1200.00, 'Loyer', '2026-06-03', '', '2026-06-05 19:46:37'),
(101, 1, 3, 1, 'expense', 450.00, 'Courses', '2026-06-05', '', '2026-06-05 19:46:37'),
(102, 1, 3, 2, 'expense', 220.00, 'Essence', '2026-06-11', '', '2026-06-05 19:46:37'),
(104, 1, 3, 6, 'expense', 500.00, 'Certification', '2026-06-06', '', '2026-06-05 19:49:02'),
(105, 1, NULL, 5, 'expense', 200.00, 'games', '2026-06-06', '', '2026-06-05 22:45:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','pending','disabled') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin Système', 'admin', 'admin@financeapp.com', '$2y$10$ynPen3bl4OQayshXd2kizeZ/s1v.TDDrb9IjGYEEVWWZHTaoZ4lTe', 'admin', 'active', '2026-06-05 18:36:21', '2026-06-05 18:58:17'),
(2, 'Emna Kharrat', 'emna_k', 'emna@financeapp.com', '$2y$12$Y5tVc6fs1NMOnAo.G2AEv.EjW4fhrFXwwFIVpyul7QqTX5WXx14mG', 'user', 'active', '2026-06-05 18:36:21', '2026-06-06 13:35:44'),
(3, 'Marie Martin', 'marie_m', 'marie@financeapp.com', '$2y$10$nkhphL2KjIgDFKyKB2gwpuZ6fnyipGd.sdBT9R15EPVclYk8mnh9G', 'user', 'active', '2026-06-05 18:36:21', '2026-06-05 19:07:04'),
(4, 'Pierre Dupont', 'pierre_d', 'pierre@financeapp.com', '$2y$10$nkhphL2KjIgDFKyKB2gwpuZ6fnyipGd.sdBT9R15EPVclYk8mnh9G', 'user', 'active', '2026-06-05 18:36:21', '2026-06-05 19:07:04'),
(5, 'Lucas Petit', 'lucas_p', 'lucas@financeapp.com', '$2y$10$nkhphL2KjIgDFKyKB2gwpuZ6fnyipGd.sdBT9R15EPVclYk8mnh9G', 'user', 'disabled', '2026-06-05 18:36:21', '2026-06-05 19:50:09'),
(8, 'malek allala', 'malek_a', 'malek@financeapp.com', '$2y$12$giqtGGLbEDRBfXBw21XKGOHv0R7X3tWcLns/rNChtCsHnONXVWLii', 'user', 'active', '2026-06-06 13:30:20', '2026-06-06 13:36:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `budget_id` (`budget_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `budget_members`
--
ALTER TABLE `budget_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_bm` (`budget_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `budget_categories`
--
ALTER TABLE `budget_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `budget_members`
--
ALTER TABLE `budget_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD CONSTRAINT `budget_categories_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_members`
--
ALTER TABLE `budget_members`
  ADD CONSTRAINT `budget_members_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
