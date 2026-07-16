-- ─── Migración: Agregar columna `business_type` a la tabla `businesses` ─────────
-- Descripción: Añade una columna ENUM('lugar_de_paso','lugar_turistico') que
--              permite clasificar el negocio como lugar de paso o lugar turístico.
-- ────────────────────────────────────────────────────────────────────────────────

ALTER TABLE `businesses`
  ADD COLUMN `business_type` ENUM('lugar_de_paso','lugar_turistico') DEFAULT NULL
  COMMENT 'Tipo de negocio: lugar_de_paso = Lugar de paso, lugar_turistico = Lugar turístico';