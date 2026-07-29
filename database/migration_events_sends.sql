-- ============================================================
-- Migración: Crear tabla events_sends
-- ============================================================
-- Descripción: Crea la tabla events_sends con estructura
--              idéntica a promotion_sends, pero asociada
--              a la tabla events en lugar de promotions.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';

USE `colon_colonbotdb`;

CREATE TABLE IF NOT EXISTS `events_sends` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL COMMENT 'ID del evento enviado',
  `wa_id` VARCHAR(30) DEFAULT NULL COMMENT 'WhatsApp ID del destinatario (referencia de chatbot_sessions.wa_id)',
  `sent_via` ENUM('whatsapp','chatbot','email') NOT NULL DEFAULT 'whatsapp',
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmado` TINYINT(1) DEFAULT NULL COMMENT 'NULL=sin respuesta, 1=confirmado, 0=rechazado',
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  INDEX `idx_event` (`event_id`),
  INDEX `idx_wa_id` (`wa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;