-- Kusina Namin database
-- Import this file in phpMyAdmin (Import tab). It creates the database for you.

CREATE DATABASE IF NOT EXISTS kusina_namin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kusina_namin;

-- Start fresh if you import again (children first, parents last)
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 1) users: the members of the site
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(20)  NOT NULL,
  email         VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,            -- never the real password
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),        -- no two members share a username
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- 2) categories: the recipe categories
CREATE TABLE categories (
  id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

-- 3) recipes: one row per recipe. updated_at stays NULL until someone edits it.
CREATE TABLE recipes (
  id                INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  user_id           INT UNSIGNED      NOT NULL,   -- who posted it
  category_id       INT UNSIGNED      NOT NULL,
  title             VARCHAR(100)      NOT NULL,
  description       VARCHAR(300)      NOT NULL,
  steps             TEXT              NOT NULL,   -- one step per line
  cook_time_minutes SMALLINT UNSIGNED NOT NULL,   -- our own extra feature
  servings          TINYINT UNSIGNED  NOT NULL,   -- our own extra feature
  created_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME          NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_recipes_created (created_at),
  CONSTRAINT fk_recipes_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_recipes_category
    FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4) ingredients: one row per ingredient of a recipe
CREATE TABLE ingredients (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipe_id INT UNSIGNED NOT NULL,
  name      VARCHAR(100) NOT NULL,                -- e.g. "2 cups rice"
  PRIMARY KEY (id),
  KEY idx_ingredients_recipe (recipe_id),
  CONSTRAINT fk_ingredients_recipe
    FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5) comments: what members say about a recipe
CREATE TABLE comments (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipe_id  INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  body       VARCHAR(500) NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_comments_recipe (recipe_id),
  CONSTRAINT fk_comments_recipe
    FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6) favorites: which member saved which recipe.
-- The two-column primary key means a member can save a recipe only once.
CREATE TABLE favorites (
  user_id    INT UNSIGNED NOT NULL,
  recipe_id  INT UNSIGNED NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, recipe_id),
  KEY idx_favorites_recipe (recipe_id),
  CONSTRAINT fk_favorites_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_favorites_recipe
    FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Starting categories
INSERT INTO categories (name) VALUES
  ('Almusal (Breakfast)'),
  ('Gulay (Vegetables)'),
  ('Kakanin (Rice Cakes)'),
  ('Meryenda (Snacks)'),
  ('Panghimagas (Desserts)'),
  ('Pancit (Noodles)'),
  ('Sabaw (Soups)'),
  ('Ulam (Main Dishes)');
