-- ============================================================
-- Plataforma Turística Interactiva – Municipio de Colón
-- Schema SQL – MySQL 5.7 (Updated 2026)
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

USE `colon_colonbotdb`;

-- ─── Usuarios ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120)                         NOT NULL,
  `email`      VARCHAR(191)                         NOT NULL UNIQUE,
  `password`   VARCHAR(255)                         NOT NULL,
  `role`       ENUM('visitor','superadmin','prestador','colaborador_admin') NOT NULL DEFAULT 'visitor',
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
  `is_trusted`   TINYINT(1)       NOT NULL DEFAULT 0,
  `trusted_by`   INT UNSIGNED     DEFAULT NULL,
  `trusted_at`   DATETIME         DEFAULT NULL,
  `trusted_note` VARCHAR(255)     DEFAULT NULL,
  `is_open`      TINYINT(1)       NOT NULL DEFAULT 1,
  `open_for_messaging` ENUM('24hrs','schedule') NOT NULL DEFAULT 'schedule',
  `google_maps_link` VARCHAR(500) DEFAULT NULL,
  `waze_link`    VARCHAR(500)     DEFAULT NULL,
  `languages`    VARCHAR(255)     DEFAULT NULL COMMENT 'Idiomas separados por coma',
  `max_images`   INT UNSIGNED     NOT NULL DEFAULT 6,
  `capacity`     INT UNSIGNED     DEFAULT NULL COMMENT 'Aforo/Aforo de sede',
  `self_classification` TEXT      DEFAULT NULL COMMENT 'Autoclasificación del prestador',
  `visits`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `rating`       DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado`       ENUM('activo','inactivo') NOT NULL DEFAULT 'inactivo' COMMENT 'Sincronizado con is_open: activo = En línea en CristobalBot',
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
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `user_name`   VARCHAR(80)  NOT NULL,
  `rating`      TINYINT(1)   NOT NULL DEFAULT 5,
  `comment`     TEXT         DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_reviews_user_id` (`user_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `visitor_place_visits` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED NOT NULL,
  `visited_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visitor_visits_user` (`user_id`),
  INDEX `idx_visitor_visits_business` (`business_id`),
  INDEX `idx_visitor_visits_visited_at` (`visited_at`),
  INDEX `idx_visitor_visits_user_business` (`user_id`, `business_id`),
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
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

-- ─── Eventos ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL si es evento global/público',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de lista',
  `presale_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de preventa',
  `capacity` INT UNSIGNED DEFAULT NULL COMMENT 'Aforo de la sede',
  `location` VARCHAR(500) DEFAULT NULL COMMENT 'Ubicación del evento',
  `validity` VARCHAR(100) DEFAULT NULL COMMENT 'Vigencia del evento',
  `conditions` TEXT DEFAULT NULL COMMENT 'Condiciones generales',
  `public_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL pública generada automáticamente',
  `event_type` ENUM('publico','privado') NOT NULL DEFAULT 'publico',
  `bot_authorized` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Autorizado para publicar en chatbot',
  `bot_authorized_by` INT UNSIGNED DEFAULT NULL COMMENT 'Quién autorizó la publicación en chatbot',
  `bot_authorized_at` DATETIME DEFAULT NULL,
  `target_segment` SET('prospectos_sin_historial','prospectos_recurrentes','clientes','clientes_frecuentes','todos') NOT NULL DEFAULT 'todos',
  `status` ENUM('pending','approved','active','inactive','expired') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `presale_start` DATETIME DEFAULT NULL,
  `presale_end` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_business` (`business_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_bot_authorized` (`bot_authorized`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
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
  `event_id`    INT UNSIGNED DEFAULT NULL,
  `type`        ENUM('contact','review','status','system') NOT NULL DEFAULT 'system',
  `title`       VARCHAR(150) NOT NULL,
  `message`     TEXT         DEFAULT NULL,
  `read_at`     DATETIME     DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_read_at` (`read_at`),
  INDEX `idx_event` (`event_id`),
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`)    REFERENCES `events`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Contactos (CRM) ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `wa_id` VARCHAR(30) DEFAULT NULL COMMENT 'ID de WhatsApp del contacto',
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `category` ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark') NOT NULL DEFAULT 'prospecto_sin_historial',
  `source` ENUM('whatsapp','mapa','manual') NOT NULL DEFAULT 'manual',
  `notes` TEXT DEFAULT NULL,
  `products` VARCHAR(255) DEFAULT NULL COMMENT 'Último producto/servicio registrado',
  `total_visits` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_purchases` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `last_contact_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  INDEX `idx_wa_id` (`wa_id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_business_category` (`business_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Compras/visitas de contactos ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contact_purchases` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `contact_id` INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `products` TEXT DEFAULT NULL COMMENT 'JSON con productos/servicios comprados',
  `notes` TEXT DEFAULT NULL,
  `purchase_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  INDEX `idx_contact` (`contact_id`),
  INDEX `idx_business` (`business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Promociones ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL si es promoción global',
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Creador de la promoción',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de lista',
  `presale_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de preventa',
  `conditions` TEXT DEFAULT NULL COMMENT 'Condiciones generales',
  `public_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL pública generada automáticamente',
  `type` ENUM('promocion','evento') NOT NULL DEFAULT 'promocion',
  `target_segment` SET('prospectos_sin_historial','prospectos_recurrentes','clientes','clientes_frecuentes','todos') NOT NULL DEFAULT 'todos',
  `status` ENUM('pending','approved','active','inactive','expired') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL COMMENT 'ID del colaborador/superadmin que aprobó',
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `presale_start` DATETIME DEFAULT NULL COMMENT 'Inicio de preventa',
  `presale_end` DATETIME DEFAULT NULL COMMENT 'Fin de preventa',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_business` (`business_id`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Envíos de promociones ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promotion_sends` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED DEFAULT NULL,
  `sent_via` ENUM('whatsapp','chatbot','email') NOT NULL DEFAULT 'whatsapp',
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  INDEX `idx_promotion` (`promotion_id`),
  INDEX `idx_contact` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Vistas de promociones ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promotion_views` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
  INDEX `idx_promotion` (`promotion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Consultas de promociones ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promotion_inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(191) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
  INDEX `idx_promotion` (`promotion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
