-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2026 at 09:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kusina_namin`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Almusal (Breakfast)'),
(2, 'Gulay (Vegetables)'),
(3, 'Kakanin (Rice Cakes)'),
(4, 'Meryenda (Snacks)'),
(6, 'Pancit (Noodles)'),
(5, 'Panghimagas (Desserts)'),
(7, 'Sabaw (Soups)'),
(8, 'Ulam (Main Dishes)');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `body` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `recipe_id`, `user_id`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'i love it <3', '2026-10-09 15:03:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `recipe_id`, `created_at`) VALUES
(2, 1, '2026-10-09 15:03:28'),
(2, 2, '2026-10-09 15:03:16');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `name`) VALUES
(1, 1, '2 lbs chicken, cut into serving pieces (bone-in thighs and drumsticks work best)'),
(2, 1, '1/2 cup soy sauce'),
(3, 1, '4 tablespoons white vinegar or cane vinegar'),
(4, 1, '10 cloves garlic, crushed'),
(5, 1, '3 to 5 pieces dried bay leaves'),
(6, 1, '1 tablespoon whole black peppercorns'),
(7, 1, '1 1/2 cups water'),
(8, 1, '3 to 4 tablespoons cooking oil'),
(9, 1, '1 teaspoon sugar (optional, to balance the acidity)'),
(10, 2, '10 pieces large egg yolks'),
(11, 2, '1 can (14 oz) condensed milk'),
(12, 2, '1 can (12 oz) evaporated milk'),
(13, 2, '1 teaspoon vanilla extract (or citrus zest)'),
(14, 2, '1/2 cup granulated sugar (for the caramel)');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(300) NOT NULL,
  `steps` text NOT NULL,
  `cook_time_minutes` smallint(5) UNSIGNED NOT NULL,
  `servings` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `category_id`, `title`, `description`, `steps`, `cook_time_minutes`, `servings`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 'Classic Filipino Chicken Adobo', 'Classic Filipino Chicken Adobo is a rich, savory, and tangy braised dish featuring chicken simmered in soy sauce, vinegar, and plenty of garlic. It is widely considered the unofficial national dish of the Philippines and tastes even better the next day.', 'Marinate: Combine chicken, soy sauce, and crushed garlic in a bowl, then marinate for at least 1 hour.\r\nSear: Heat oil in a large pot, remove chicken from marinade (save the liquid), and sear both sides until lightly browned.\r\nSimmer: Pour in the reserved marinade, water, peppercorns, and bay leaves, then cover and simmer for 30 minutes.\r\nSour: Pour in the vinegar, let it boil uncovered without stirring for 10 minutes to cook off the sharp acidity.\r\nReduce: Stir in the optional sugar and simmer until the sauce reduces to your preferred thickness before serving hot.', 45, 5, '2026-10-09 14:59:47', NULL),
(2, 2, 5, 'Leche Flan', 'Leche Flan is a rich, creamy, and velvety Filipino custard topped with a golden caramel syrup. It is a beloved celebration dessert made primarily from egg yolks and condensed milk.', 'Caramelize: Divide the sugar equally between two metal molds (llaneras), place them directly over low stovetop heat, and melt the sugar until it turns into a golden liquid caramel. Spread evenly and let cool until hard.\r\nMix: Gently whisk the egg yolks, condensed milk, evaporated milk, and vanilla extract together in a large bowl until fully combined, avoiding creating too many air bubbles.\r\nStrain: Pour the mixture through a fine-mesh strainer or cheesecloth directly into the caramel-lined molds to ensure a perfectly smooth texture.\r\nCover: Seal the top of each mold tightly with aluminum foil to prevent steam water from dripping into the custard.\r\nSteam: Place the molds in a steamer over medium heat and steam for 30 to 35 minutes until the center is set but slightly jiggly, then cool completely and refrigerate before flipping onto a plate to serve.', 45, 6, '2026-10-09 15:03:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'HenriDillo', 'henjpdillo@addu.edu.ph', '$2y$10$7IUFOFyIOXDdPYR6gaxDIeoycQTqWSAEIrdETaQaSY2G4llPRWD1u', '2026-10-09 14:40:23'),
(2, 'Luis', 'gmail@gmail.com', '$2y$10$zIblVKVLtxIP9FIaVLB/Ze.NuIgPf5ufAmo/.A.1l06OJbvU5d8rm', '2026-10-09 15:00:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_recipe` (`recipe_id`),
  ADD KEY `fk_comments_user` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `idx_favorites_recipe` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ingredients_recipe` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipes_created` (`created_at`),
  ADD KEY `fk_recipes_user` (`user_id`),
  ADD KEY `fk_recipes_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
