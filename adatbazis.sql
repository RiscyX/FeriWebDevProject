-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Máj 28. 22:38
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `recipe`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `categories`
--

CREATE TABLE `categories`
(
    `id`   int(11) NOT NULL,
    `name` varchar(100) NOT NULL COMMENT 'Name of the recipe category (e.g., Breakfast, Dessert)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `email_verifications`
--

CREATE TABLE `email_verifications`
(
    `user_id`    int(11) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `favorites`
--

CREATE TABLE `favorites`
(
    `user_id`    int(11) NOT NULL,
    `recipe_id`  int(11) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `fridge_items`
--

CREATE TABLE `fridge_items`
(
    `user_id`       int(11) NOT NULL,
    `ingredient_id` int(11) NOT NULL,
    `quantity`      decimal(10, 2) DEFAULT 0.00 COMMENT 'Amount of ingredient in user''s fridge'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `ingredients`
--

CREATE TABLE `ingredients`
(
    `id`               int(11) NOT NULL,
    `name`             varchar(100) NOT NULL COMMENT 'Name of the ingredient',
    `default_quantity` decimal(10, 2) DEFAULT 0.00 COMMENT 'Default quantity for the ingredient',
    `unit_id`          int(11) NOT NULL COMMENT 'Measurement unit reference'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `menus`
--

CREATE TABLE `menus`
(
    `id`          int(11) NOT NULL,
    `user_id`     int(11) NOT NULL,
    `recipe_id`   int(11) NOT NULL,
    `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL COMMENT 'Planned day for the recipe',
    `created_at`  datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `password_resets`
--

CREATE TABLE `password_resets`
(
    `user_id`    INT(11)      NOT NULL COMMENT 'FK to users.id',
    `token`      VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    KEY          `idx_token` (`token`),
    CONSTRAINT `fk_password_resets_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
            ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `recipes`
--

CREATE TABLE `recipes`
(
    `id`           int(11) NOT NULL,
    `user_id`      int(11) NOT NULL COMMENT 'Creator of the recipe',
    `title`        varchar(255) NOT NULL COMMENT 'Recipe title',
    `description`  text     DEFAULT NULL COMMENT 'Detailed description of the recipe',
    `instructions` text     DEFAULT NULL COMMENT 'Cooking instructions',
    `prep_time`    int(11) DEFAULT NULL COMMENT 'Preparation time in minutes',
    `cook_time`    int(11) DEFAULT NULL COMMENT 'Cooking time in minutes',
    `servings`     int(11) DEFAULT NULL COMMENT 'Number of servings',
    `category_id`  int(11) DEFAULT NULL COMMENT 'Recipe category',
    `created_at`   datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients`
(
    `recipe_id`     int(11) NOT NULL,
    `ingredient_id` int(11) NOT NULL,
    `quantity`      decimal(10, 2) DEFAULT NULL COMMENT 'Amount of the ingredient used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `units`
--

CREATE TABLE `units`
(
    `id`           int(11) NOT NULL,
    `name`         varchar(50) NOT NULL COMMENT 'Name of the unit (e.g., gram, liter, piece)',
    `abbreviation` varchar(10) NOT NULL COMMENT 'Short form of the unit (e.g., g, l, pcs)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users`
(
    `id`            int(11) NOT NULL AUTO_INCREMENT,
    `username`      varchar(100) NOT NULL,
    `email`         varchar(255) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `is_banned`     tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=active, 1=banned',
    `created_at`    datetime DEFAULT current_timestamp() COMMENT 'User registration date',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `categories`
--
ALTER TABLE `categories`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- A tábla indexei `email_verifications`
--
ALTER TABLE `email_verifications`
    ADD PRIMARY KEY (`user_id`);

--
-- A tábla indexei `favorites`
--
ALTER TABLE `favorites`
    ADD PRIMARY KEY (`user_id`, `recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- A tábla indexei `fridge_items`
--
ALTER TABLE `fridge_items`
    ADD PRIMARY KEY (`user_id`, `ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- A tábla indexei `ingredients`
--
ALTER TABLE `ingredients`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `unit_id` (`unit_id`);

--
-- A tábla indexei `menus`
--
ALTER TABLE `menus`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- A tábla indexei `password_resets`
--
ALTER TABLE `password_resets`
    ADD PRIMARY KEY (`email`);

--
-- A tábla indexei `recipes`
--
ALTER TABLE `recipes`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- A tábla indexei `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
    ADD PRIMARY KEY (`recipe_id`, `ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- A tábla indexei `units`
--
ALTER TABLE `units`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `abbreviation` (`abbreviation`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `categories`
--
ALTER TABLE `categories`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `ingredients`
--
ALTER TABLE `ingredients`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `menus`
--
ALTER TABLE `menus`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `recipes`
--
ALTER TABLE `recipes`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `units`
--
ALTER TABLE `units`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `email_verifications`
--
ALTER TABLE `email_verifications`
    ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `favorites`
--
ALTER TABLE `favorites`
    ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON
DELETE
CASCADE;

--
-- Megkötések a táblához `fridge_items`
--
ALTER TABLE `fridge_items`
    ADD CONSTRAINT `fridge_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fridge_items_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON
DELETE
CASCADE;

--
-- Megkötések a táblához `ingredients`
--
ALTER TABLE `ingredients`
    ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Megkötések a táblához `menus`
--
ALTER TABLE `menus`
    ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON
DELETE
CASCADE;

--
-- Megkötések a táblához `recipes`
--
ALTER TABLE `recipes`
    ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Megkötések a táblához `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
    ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON
DELETE
CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
