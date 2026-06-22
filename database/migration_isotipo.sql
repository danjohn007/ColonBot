-- ============================================================
-- Migración: Agregar columna isotipo a la tabla businesses
-- ============================================================
ALTER TABLE `businesses`
  ADD COLUMN `isotipo` VARCHAR(30) DEFAULT NULL AFTER `schedule`;