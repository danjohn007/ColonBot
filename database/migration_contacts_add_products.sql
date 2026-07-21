-- ============================================================
-- Migration: Add products column to contacts table
-- Se elimina la dependencia directa de contact_purchases
-- para la clasificación de clientes/lovemarks del CRM.
-- 
-- La tabla 'contacts' almacenará directamente:
--   - products: último producto/servicio registrado
--   - total_visits: número de compras realizadas
--   - total_purchases: monto total gastado
--   - category: clasificación (cliente, lovemark, etc.)
-- ============================================================
USE `colon_colonbotdb`;

-- Agregar columna products si no existe
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'contacts'
                   AND COLUMN_NAME = 'products');
SET @sql_add = IF(@col_exists = 0,
    'ALTER TABLE contacts ADD COLUMN products VARCHAR(255) DEFAULT NULL COMMENT "Último producto/servicio registrado"',
    'SELECT "OK: columna products ya existe" AS status');
PREPARE stmt FROM @sql_add;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar total_visits y total_purchases en contacts desde contact_purchases (sincronización única)
UPDATE contacts c
LEFT JOIN (
    SELECT
        contact_id,
        COUNT(*)            AS real_purchases,
        COALESCE(SUM(amount), 0) AS real_total
    FROM contact_purchases
    GROUP BY contact_id
) stats ON stats.contact_id = c.id
SET
    c.total_visits    = COALESCE(stats.real_purchases, c.total_visits),
    c.total_purchases = COALESCE(stats.real_total, c.total_purchases),
    c.products        = (SELECT cp.products FROM contact_purchases cp WHERE cp.contact_id = c.id ORDER BY cp.purchase_date DESC LIMIT 1);

-- Clasificar contactos según compras existentes
UPDATE contacts c
LEFT JOIN (
    SELECT contact_id, COUNT(*) AS purchase_count
    FROM contact_purchases
    GROUP BY contact_id
) pc ON pc.contact_id = c.id
SET c.category = CASE
    WHEN COALESCE(pc.purchase_count, 0) >= 3 THEN 'lovemark'
    WHEN COALESCE(pc.purchase_count, 0) >= 1 THEN 'cliente'
    ELSE c.category
END;

-- Mostrar resumen
SELECT '========================================' AS '';
SELECT 'Migración completada:' AS '';
SELECT '========================================' AS '';
SELECT category, COUNT(*) AS total_contactos
FROM contacts
GROUP BY category
ORDER BY category;