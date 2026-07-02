-- ============================================================
-- Migration: Enlazar chatbot_sessions con contacts usando 'category'
-- Clasificar usuarios en niveles: Prospecto, Cliente, Lovemark
-- ============================================================

-- 1. Agregar columna 'session_count' a chatbot_sessions para contar interacciones
ALTER TABLE `chatbot_sessions` 
  ADD COLUMN `session_count` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `state`,
  ADD COLUMN `has_purchased` TINYINT(1) NOT NULL DEFAULT 0 AFTER `session_count`,
  ADD COLUMN `purchase_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `has_purchased`,
  ADD COLUMN `category` VARCHAR(50) DEFAULT NULL COMMENT 'Prospecto sin historial, Prospecto recurrente, Cliente, Lovemark' AFTER `purchase_count`;

-- 2. Agregar columna 'chatbot_session_id' a contacts para enlazar con chatbot_sessions
ALTER TABLE `contacts`
  ADD COLUMN `chatbot_session_id` BIGINT UNSIGNED DEFAULT NULL AFTER `notes`,
  ADD INDEX `idx_chatbot_session` (`chatbot_session_id`);

-- 3. Modificar ENUM de category para incluir los nuevos niveles
ALTER TABLE `contacts` 
  MODIFY COLUMN `category` ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark') NOT NULL DEFAULT 'prospecto_sin_historial';

-- 4. Tabla para tracking de vistas de promociones
CREATE TABLE IF NOT EXISTS `promotion_views` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  INDEX `idx_promotion` (`promotion_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla para solicitudes de informes de promociones
CREATE TABLE IF NOT EXISTS `promotion_inquiries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `contact_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(120) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(191) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`promotion_id`) REFERENCES `promotions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  INDEX `idx_promotion` (`promotion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;