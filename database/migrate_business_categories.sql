-- ─── Migración: tabla de categorías múltiples por negocio ────────────────
-- Ejecutar en bases de datos existentes para habilitar múltiples categorías
-- por negocio. La columna category_id en businesses se mantiene como
-- categoría primaria para compatibilidad.

CREATE TABLE IF NOT EXISTS `business_categories` (
  `business_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`business_id`, `category_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Poblar la tabla con las categorías primarias ya existentes
INSERT IGNORE INTO `business_categories` (`business_id`, `category_id`)
SELECT `id`, `category_id` FROM `businesses`;
