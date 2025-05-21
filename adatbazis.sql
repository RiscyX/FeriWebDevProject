
-- --------------------------------------------------
-- Recipe App Database Schema with Normalized Units
-- --------------------------------------------------

-- Create database
CREATE DATABASE IF NOT EXISTS recipe DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recipe;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE COMMENT 'Username for login',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'User email address',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed user password',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'User registration date'
);

-- Units table (for measurement units)
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Name of the unit (e.g., gram, liter, piece)',
    abbreviation VARCHAR(10) NOT NULL UNIQUE COMMENT 'Short form of the unit (e.g., g, l, pcs)'
);

-- Ingredients table
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Name of the ingredient',
    default_quantity DECIMAL(10,2) DEFAULT 0 COMMENT 'Default quantity for the ingredient',
    unit_id INT NOT NULL COMMENT 'Measurement unit reference',
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Name of the recipe category (e.g., Breakfast, Dessert)'
);

-- Recipes table
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Creator of the recipe',
    title VARCHAR(255) NOT NULL COMMENT 'Recipe title',
    description TEXT COMMENT 'Detailed description of the recipe',
    instructions TEXT COMMENT 'Cooking instructions',
    prep_time INT COMMENT 'Preparation time in minutes',
    cook_time INT COMMENT 'Cooking time in minutes',
    servings INT COMMENT 'Number of servings',
    category_id INT COMMENT 'Recipe category',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Recipe_Ingredients table (many-to-many between recipes and ingredients)
CREATE TABLE recipe_ingredients (
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10,2) COMMENT 'Amount of the ingredient used',
    PRIMARY KEY (recipe_id, ingredient_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);

-- Favorites table (users ↔ recipes)
CREATE TABLE favorites (
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Menus table (users ↔ days ↔ recipes)
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL COMMENT 'Planned day for the recipe',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Fridge Items table (users ↔ available ingredients)
CREATE TABLE fridge_items (
    user_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 0 COMMENT 'Amount of ingredient in user\'s fridge',
    PRIMARY KEY (user_id, ingredient_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);
