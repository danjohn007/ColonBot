-- ============================================================
-- Migración: Agregar columna wa_id a promotion_sends
-- ============================================================
-- Descripción: Agrega la columna wa_id a la tabla promotion_sends
--              para registrar el WhatsApp ID del destinatario
--              sin depender de la relación con contacts.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';

USE `colon_colonbotdb`;

ALTER TABLE `promotion_sends`
    ADD COLUMN `wa_id` VARCHAR(30) DEFAULT NULL COMMENT 'WhatsApp ID del destinatario (referencia de chatbot_sessions.wa_id)' AFTER `contact_id`,
    ADD INDEX `idx_wa_id` (`wa_id`);