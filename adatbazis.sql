-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Máj 28. 22:38
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";

CREATE TABLE `users`
(
    `id`                int(11) NOT NULL AUTO_INCREMENT,
    `username`          varchar(100) NOT NULL,
    `email`             varchar(255) NOT NULL,
    `password_hash`     varchar(255) NOT NULL,
    `is_banned`         tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=active, 1=banned',
    `created_at`        datetime DEFAULT current_timestamp() COMMENT 'User registration date',
    `email_verified_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories`
(
    `id`   int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT 'Name of the recipe category (e.g., Breakfast, Dessert)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verifications`
(
    `user_id`    int(11) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`user_id`),
    CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `favorites`
(
    `user_id`    int(11) NOT NULL,
    `recipe_id`  int(11) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`user_id`, `recipe_id`),
    KEY          `recipe_id` (`recipe_id`),
    CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fridge_items`
(
    `user_id`       int(11) NOT NULL,
    `ingredient_id` int(11) NOT NULL,
    `quantity`      decimal(10, 2) DEFAULT 0.00 COMMENT 'Amount of ingredient in user''s fridge',
    PRIMARY KEY (`user_id`, `ingredient_id`),
    KEY             `ingredient_id` (`ingredient_id`),
    CONSTRAINT `fridge_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fridge_items_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ingredients`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT,
    `name`             varchar(100) NOT NULL COMMENT 'Name of the ingredient',
    `default_quantity` decimal(10, 2) DEFAULT 0.00 COMMENT 'Default quantity for the ingredient',
    `unit_id`          int(11) NOT NULL COMMENT 'Measurement unit reference',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    KEY                `unit_id` (`unit_id`),
    CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menus`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `user_id`     int(11) NOT NULL,
    `recipe_id`   int(11) NOT NULL,
    `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL COMMENT 'Planned day for the recipe',
    `created_at`  datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY           `user_id` (`user_id`),
    KEY           `recipe_id` (`recipe_id`),
    CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets`
(
    `user_id`    int(11) NOT NULL COMMENT 'FK to users.id',
    `token`      varchar(255) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`user_id`),
    KEY          `idx_token` (`token`),
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recipes`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `user_id`      int(11) NOT NULL COMMENT 'Creator of the recipe',
    `title`        varchar(255) NOT NULL COMMENT 'Recipe title',
    `description`  text     DEFAULT NULL COMMENT 'Detailed description of the recipe',
    `instructions` text     DEFAULT NULL COMMENT 'Cooking instructions',
    `prep_time`    int(11) DEFAULT NULL COMMENT 'Preparation time in minutes',
    `cook_time`    int(11) DEFAULT NULL COMMENT 'Cooking time in minutes',
    `servings`     int(11) DEFAULT NULL COMMENT 'Number of servings',
    `category_id`  int(11) DEFAULT NULL COMMENT 'Recipe category',
    `created_at`   datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY            `user_id` (`user_id`),
    KEY            `category_id` (`category_id`),
    CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recipe_ingredients`
(
    `recipe_id`     int(11) NOT NULL,
    `ingredient_id` int(11) NOT NULL,
    `quantity`      decimal(10, 2) DEFAULT NULL COMMENT 'Amount of the ingredient used',
    PRIMARY KEY (`recipe_id`, `ingredient_id`),
    KEY             `ingredient_id` (`ingredient_id`),
    CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `units`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `name`         varchar(50) NOT NULL COMMENT 'Name of the unit (e.g., gram, liter, piece)',
    `abbreviation` varchar(10) NOT NULL COMMENT 'Short form of the unit (e.g., g, l, pcs)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `abbreviation` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



COMMIT;
