-- ============================================================
-- Migración: Tabla de Notificaciones
-- Plataforma Turística Interactiva – Municipio de Colón
-- MySQL 5.7
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

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
