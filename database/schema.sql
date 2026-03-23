-- ============================================================
-- Plataforma Turística Interactiva – Municipio de Colón
-- Schema SQL – MySQL 5.7
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- ─── Base de datos ────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `colonbot`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `colonbot`;

-- ─── Usuarios ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120)                         NOT NULL,
  `email`      VARCHAR(191)                         NOT NULL UNIQUE,
  `password`   VARCHAR(255)                         NOT NULL,
  `role`       ENUM('visitor','admin','superadmin')  NOT NULL DEFAULT 'visitor',
  `phone`      VARCHAR(20)                          DEFAULT NULL,
  `avatar`     VARCHAR(255)                         DEFAULT NULL,
  `active`     TINYINT(1)                           NOT NULL DEFAULT 1,
  `created_at` DATETIME                             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME                             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Categorías ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(80)  NOT NULL,
  `slug`       VARCHAR(100) NOT NULL UNIQUE,
  `icon`       VARCHAR(60)  NOT NULL DEFAULT 'map-pin',
  `color`      VARCHAR(10)  NOT NULL DEFAULT '#3B82F6',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `active`     TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Amenidades globales ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `amenities` (
  `id`     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`   VARCHAR(80) NOT NULL,
  `icon`   VARCHAR(60) NOT NULL DEFAULT 'check',
  `active` TINYINT(1)  NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Negocios / Lugares turísticos ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `businesses` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED     NOT NULL,
  `category_id`  INT UNSIGNED     NOT NULL,
  `name`         VARCHAR(150)     NOT NULL,
  `slug`         VARCHAR(180)     NOT NULL UNIQUE,
  `description`  TEXT             DEFAULT NULL,
  `address`      VARCHAR(255)     DEFAULT NULL,
  `lat`          DECIMAL(10,7)    DEFAULT NULL,
  `lng`          DECIMAL(10,7)    DEFAULT NULL,
  `phone`        VARCHAR(20)      DEFAULT NULL,
  `whatsapp`     VARCHAR(20)      DEFAULT NULL,
  `email`        VARCHAR(191)     DEFAULT NULL,
  `website`      VARCHAR(255)     DEFAULT NULL,
  `facebook`     VARCHAR(255)     DEFAULT NULL,
  `instagram`    VARCHAR(255)     DEFAULT NULL,
  `schedule`     TEXT             DEFAULT NULL,
  `cover_image`  VARCHAR(255)     DEFAULT NULL,
  `status`       ENUM('draft','pending','published','rejected') NOT NULL DEFAULT 'draft',
  `featured`     TINYINT(1)       NOT NULL DEFAULT 0,
  `visits`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `rating`       DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Galería de imágenes ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `business_images` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `path`        VARCHAR(255) NOT NULL,
  `caption`     VARCHAR(150) DEFAULT NULL,
  `sort_order`  INT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Categorías por negocio (múltiples) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `business_categories` (
  `business_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`business_id`, `category_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Amenidades por negocio ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `business_amenities` (
  `business_id` INT UNSIGNED NOT NULL,
  `amenity_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`business_id`, `amenity_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`amenity_id`)  REFERENCES `amenities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Servicios del negocio ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `price`       DECIMAL(10,2) DEFAULT NULL,
  `active`      TINYINT(1)   NOT NULL DEFAULT 1,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Productos del negocio ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `price`       DECIMAL(10,2) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `available`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Calificaciones ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `user_name`   VARCHAR(80)  NOT NULL,
  `rating`      TINYINT(1)   NOT NULL DEFAULT 5,
  `comment`     TEXT         DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Analítica ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `analytics` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED DEFAULT NULL,
  `event`       VARCHAR(60)  NOT NULL,
  `ip`          VARCHAR(45)  DEFAULT NULL,
  `user_agent`  VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_event` (`event`),
  INDEX `idx_business` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Configuraciones globales ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`   VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT         DEFAULT NULL,
  `group` VARCHAR(60)  NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Bitácora de acciones ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `action_log` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `action`     VARCHAR(120) NOT NULL,
  `model`      VARCHAR(60)  DEFAULT NULL,
  `model_id`   INT UNSIGNED DEFAULT NULL,
  `detail`     TEXT         DEFAULT NULL,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Registro de errores ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `error_log` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level`      ENUM('debug','info','warning','error','critical') NOT NULL DEFAULT 'error',
  `message`    TEXT         NOT NULL,
  `file`       VARCHAR(255) DEFAULT NULL,
  `line`       INT UNSIGNED DEFAULT NULL,
  `trace`      TEXT         DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Dispositivos IoT – HikVision ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `iot_hikvision` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(120) NOT NULL,
  `ip`          VARCHAR(45)  NOT NULL,
  `port`        SMALLINT UNSIGNED NOT NULL DEFAULT 80,
  `username`    VARCHAR(80)  NOT NULL DEFAULT 'admin',
  `password`    VARCHAR(255) NOT NULL,
  `stream_url`  VARCHAR(255) DEFAULT NULL,
  `type`        ENUM('camera','nvr','dvr') NOT NULL DEFAULT 'camera',
  `location`    VARCHAR(150) DEFAULT NULL,
  `active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Dispositivos IoT – Shelly Cloud ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `iot_shelly` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120) NOT NULL,
  `device_id`  VARCHAR(80)  NOT NULL UNIQUE,
  `auth_key`   VARCHAR(255) NOT NULL,
  `server_uri` VARCHAR(255) NOT NULL DEFAULT 'https://shelly-41-eu.shelly.cloud',
  `type`       VARCHAR(60)  NOT NULL DEFAULT 'relay',
  `location`   VARCHAR(150) DEFAULT NULL,
  `active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── GPS Trackers ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `gps_trackers` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120) NOT NULL,
  `imei`       VARCHAR(20)  NOT NULL UNIQUE,
  `api_key`    VARCHAR(255) DEFAULT NULL,
  `provider`   VARCHAR(80)  DEFAULT NULL,
  `last_lat`   DECIMAL(10,7) DEFAULT NULL,
  `last_lng`   DECIMAL(10,7) DEFAULT NULL,
  `last_seen`  DATETIME     DEFAULT NULL,
  `active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Conversaciones chatbot ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `chatbot_sessions` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wa_id`       VARCHAR(30)  NOT NULL,
  `state`       VARCHAR(80)  NOT NULL DEFAULT 'menu',
  `last_message` TEXT        DEFAULT NULL,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_wa_id` (`wa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Notificaciones ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED DEFAULT NULL,
  `type`        ENUM('contact','review','status','system') NOT NULL DEFAULT 'system',
  `title`       VARCHAR(150) NOT NULL,
  `message`     TEXT         DEFAULT NULL,
  `read_at`     DATETIME     DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_read_at` (`read_at`),
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
