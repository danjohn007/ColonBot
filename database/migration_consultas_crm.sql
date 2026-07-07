-- ============================================================
-- Migration: Consultas CRM - Tabla y correcciones
-- ============================================================
-- A) Eliminar el tipo 'prospecto' de la columna category en contacts
--    Reemplazar 'prospecto' por 'prospecto_sin_historial'
-- ============================================================
UPDATE contacts SET category = 'prospecto_sin_historial' WHERE category = 'prospecto';

-- ============================================================
-- B) Crear nueva tabla 'consultas' para almacenar acciones
--    de usuarios del chatbot (solicitar_info / compra_reservacion)
-- ============================================================
CREATE TABLE IF NOT EXISTS `consultas` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wa_id`       VARCHAR(30)  NOT NULL COMMENT 'ID de WhatsApp del usuario que realizó la acción',
  `tipo_accion` ENUM('solicitar_informacion','compra_reservacion') NOT NULL COMMENT 'Tipo de acción realizada',
  `business_id` INT UNSIGNED DEFAULT NULL COMMENT 'Negocio relacionado con la acción',
  `detalle`     TEXT         DEFAULT NULL COMMENT 'Detalle adicional de la acción (mensaje, producto, etc.)',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_wa_id` (`wa_id`),
  INDEX `idx_tipo_accion` (`tipo_accion`),
  INDEX `idx_business` (`business_id`),
  INDEX `idx_wa_business` (`wa_id`, `business_id`),
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;