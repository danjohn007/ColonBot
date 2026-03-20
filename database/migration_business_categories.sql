-- ============================================================
-- Migración: Soporte para múltiples categorías por negocio
-- Ejecutar una sola vez en la base de datos existente
-- ============================================================

-- 1. Crear la tabla de relación negocio–categoría (many-to-many)
CREATE TABLE IF NOT EXISTS `business_categories` (
  `business_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`business_id`, `category_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar los datos existentes: cada negocio conserva su categoría actual
INSERT IGNORE INTO `business_categories` (`business_id`, `category_id`)
SELECT `id`, `category_id`
FROM `businesses`
WHERE `category_id` IS NOT NULL;
