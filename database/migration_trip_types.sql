-- ============================================================
-- Migración: Tipos de viaje por negocio
-- Ejecutar una sola vez en la base de datos existente
-- ============================================================

-- 1. Crear la tabla de relación negocio–tipo de viaje (many-to-many)
CREATE TABLE IF NOT EXISTS `business_trip_types` (
  `business_id` INT UNSIGNED NOT NULL,
  `trip_type`   VARCHAR(30)  NOT NULL,
  PRIMARY KEY (`business_id`, `trip_type`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;