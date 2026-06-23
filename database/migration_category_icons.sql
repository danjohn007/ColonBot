-- ============================================================
-- Migración: Actualizar íconos de categorías
-- ============================================================

-- Turismo religioso → cruz (cross/✝️)
UPDATE `categories` SET `icon` = 'cross' WHERE `name` = 'Turismo religioso' OR `slug` = 'turismo-religioso';

-- Ecoturismo y Aventura → estrella (star/⭐)
UPDATE `categories` SET `icon` = 'ecoturismo' WHERE `name` = 'Ecoturismo y Aventura' OR `slug` = 'ecoturismo-y-aventura';