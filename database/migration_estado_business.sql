-- ─── Migración: Agregar columna `estado` a la tabla `businesses` ───────────────
-- Descripción: Añade una columna ENUM('activo','inactivo') que refleja
--              si el negocio está "En línea en CristobalBot" (is_open = 1).
--              Si is_open = 1 → estado = 'activo'
--              Si is_open = 0 → estado = 'inactivo'
-- ────────────────────────────────────────────────────────────────────────────────

ALTER TABLE `businesses`
  ADD COLUMN `estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'inactivo'
  COMMENT 'Sincronizado con is_open: activo = En línea en CristobalBot';

-- Actualizar registros existentes: los que tienen is_open=1 pasan a 'activo'
UPDATE `businesses` SET `estado` = 'activo' WHERE `is_open` = 1;