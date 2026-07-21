-- ============================================================
-- Migration COMPLETA: Sincronizar contacts desde contact_purchases
-- MySQL 5.7+ compatible
--
-- ¿Qué hace?
--   1. Garantiza que las FOREIGN KEY entre contacts y
--      contact_purchases existan correctamente.
--   2. Sincroniza los datos históricos de contact_purchases
--      hacia la tabla contacts (total_visits, total_purchases,
--      category).
--
-- Lógica de clasificación en contacts.category:
--   Sin compras       → 'prospecto_sin_historial' (o 'prospecto_recurrente' si tiene visitas)
--   1 a 2 compras     → 'cliente'
--   3 o más compras   → 'lovemark'
--
-- NO altera el diseño del sistema ni modifica archivos PHP.
-- ============================================================
USE `colon_colonbotdb`;

-- ============================================================
-- PASO 0: Verificar/Agregar FOREIGN KEY necesarias
-- ============================================================

-- 0a) FK: contacts.business_id → businesses.id
--     (si no existe, se agrega)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contacts'
                  AND CONSTRAINT_NAME = 'contacts_ibfk_1');
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contacts ADD CONSTRAINT contacts_ibfk_1 FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE',
    'SELECT "FK contacts.business_id ya existe" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 0b) FK: contact_purchases.contact_id → contacts.id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contact_purchases'
                  AND CONSTRAINT_NAME = 'contact_purchases_ibfk_1');
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contact_purchases ADD CONSTRAINT contact_purchases_ibfk_1 FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE',
    'SELECT "FK contact_purchases.contact_id ya existe" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 0c) FK: contact_purchases.business_id → businesses.id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contact_purchases'
                  AND CONSTRAINT_NAME = 'contact_purchases_ibfk_2');
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contact_purchases ADD CONSTRAINT contact_purchases_ibfk_2 FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE',
    'SELECT "FK contact_purchases.business_id ya existe" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 0d) Índices para optimizar consultas entre ambas tablas
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'contacts'
                   AND INDEX_NAME = 'idx_business_category');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_business_category ON contacts(business_id, category)',
    'SELECT "Índice idx_business_category ya existe" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 1: Sincronizar total_visits y total_purchases
--          Actualiza los acumulados en contacts con los datos
--          reales registrados en contact_purchases
-- ============================================================
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
    c.total_visits    = COALESCE(stats.real_purchases, 0),
    c.total_purchases = COALESCE(stats.real_total, 0);

-- ============================================================
-- PASO 2: Contactos SIN compras → reclasificar como prospecto
--          Si tiene visitas registradas (>0) → 'prospecto_recurrente'
--          Si no tiene visitas → 'prospecto_sin_historial'
-- ============================================================
UPDATE contacts c
LEFT JOIN (
    SELECT contact_id, COUNT(*) AS purchase_count
    FROM contact_purchases
    GROUP BY contact_id
) pc ON pc.contact_id = c.id
SET c.category = CASE
    WHEN c.total_visits > 0 THEN 'prospecto_recurrente'
    ELSE 'prospecto_sin_historial'
END
WHERE pc.purchase_count IS NULL;

-- ============================================================
-- PASO 3: Contactos con 1 a 2 compras → 'cliente'
--          Un prospecto recurrente se convierte en cliente al
--          registrar su primera compra (Customer Journey Etapa A→B)
-- ============================================================
UPDATE contacts c
JOIN (
    SELECT contact_id, COUNT(*) AS purchase_count
    FROM contact_purchases
    GROUP BY contact_id
    HAVING purchase_count BETWEEN 1 AND 2
) pc ON pc.contact_id = c.id
SET c.category = 'cliente';

-- ============================================================
-- PASO 4: Contactos con 3 o MÁS compras → 'lovemark'
--          Al alcanzar 3 compras en el mismo negocio, el cliente
--          se convierte en lovemark (cliente frecuente/fiel)
-- ============================================================
UPDATE contacts c
JOIN (
    SELECT contact_id, COUNT(*) AS purchase_count
    FROM contact_purchases
    GROUP BY contact_id
    HAVING purchase_count >= 3
) pc ON pc.contact_id = c.id
SET c.category = 'lovemark';

-- ============================================================
-- RESULTADO: Mostrar la clasificación final
-- ============================================================
SELECT 'RESUMEN FINAL - Clasificación de contactos:' AS '';
SELECT category, COUNT(*) AS total_contactos
FROM contacts
GROUP BY category
ORDER BY category;

SELECT 'Contactos con sus compras:' AS '';
SELECT c.id, c.name, c.category AS clasificacion, 
       COALESCE(pc.purchase_count, 0) AS num_compras, 
       COALESCE(pc.total_amount, 0) AS monto_total
FROM contacts c
LEFT JOIN (
    SELECT contact_id, 
           COUNT(*) AS purchase_count, 
           COALESCE(SUM(amount), 0) AS total_amount
    FROM contact_purchases
    GROUP BY contact_id
) pc ON pc.contact_id = c.id
ORDER BY pc.purchase_count DESC;

-- Fin de la migración