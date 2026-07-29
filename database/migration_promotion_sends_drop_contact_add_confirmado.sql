-- ============================================================
-- Migración: Eliminar contact_id y agregar confirmado
-- ============================================================
-- Descripción:
--   1. Elimina la columna contact_id de promotion_sends
--      (los datos de wa_id ya reemplazan esa relación).
--   2. Agrega la columna confirmado (TINYINT(1) DEFAULT NULL)
--      para registrar si el destinatario confirmó recepción.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';

USE `colon_colonbotdb`;

-- ============================================================
-- 1. Eliminar la columna contact_id
-- ============================================================
-- NOTA: Se elimina la FK primero (si existe) y luego la columna.
--       La columna wa_id ya cumple la función de identificar
--       al destinatario sin depender de la tabla contacts.
-- ============================================================
ALTER TABLE `promotion_sends`
    DROP FOREIGN KEY `promotion_sends_ibfk_2`;

ALTER TABLE `promotion_sends`
    DROP COLUMN `contact_id`;

-- ============================================================
-- 2. Agregar columna confirmado (NULL por defecto)
-- ============================================================
-- Esta columna se mantiene como NULL cuando se envía la promoción.
-- Puede actualizarse a 1 cuando el destinatario confirme,
-- o a 0 si se rechaza explícitamente.
-- ============================================================
ALTER TABLE `promotion_sends`
    ADD COLUMN `confirmado` TINYINT(1) DEFAULT NULL COMMENT 'NULL=sin respuesta, 1=confirmado, 0=rechazado' AFTER `wa_id`;