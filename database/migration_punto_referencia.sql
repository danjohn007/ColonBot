-- ============================================================
-- Migración: Agregar categoría 'Punto de referencia'
-- ============================================================
INSERT IGNORE INTO `categories` (`name`, `slug`, `icon`, `color`, `sort_order`) VALUES
('Punto de referencia', 'punto-de-referencia', 'map-pin', '#6B21A8', 8);