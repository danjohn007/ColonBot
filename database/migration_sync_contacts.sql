-- ============================================================
-- Migration COMPLETA: Sincronizar contacts desde contact_purchases
-- MySQL 5.7+ compatible
--
-- ¿Qué hace?
--   1. Garantiza que las FOREIGN KEY necesarias existan.
--   2. Agrega un TRIGGER AFTER INSERT en contact_purchases
--      que automáticamente actualiza contacts (total_visits,
--      total_purchases, category) cada vez que se inserta
--      una compra.
--   3. Sincroniza los datos históricos de contact_purchases
--      hacia la tabla contacts.
--
-- Lógica de clasificación en contacts.category:
--   Sin compras       → 'prospecto_sin_historial' o 'prospecto_recurrente'
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
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contacts'
                  AND CONSTRAINT_NAME = 'contacts_ibfk_1');
SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE contacts ADD CONSTRAINT contacts_ibfk_1 FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE',
    'SELECT "OK: FK contacts.business_id ya existe" AS status');
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 0b) FK: contact_purchases.contact_id → contacts.id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contact_purchases'
                  AND CONSTRAINT_NAME = 'contact_purchases_ibfk_1');
SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE contact_purchases ADD CONSTRAINT contact_purchases_ibfk_1 FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE',
    'SELECT "OK: FK contact_purchases.contact_id ya existe" AS status');
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 0c) FK: contact_purchases.business_id → businesses.id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'contact_purchases'
                  AND CONSTRAINT_NAME = 'contact_purchases_ibfk_2');
SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE contact_purchases ADD CONSTRAINT contact_purchases_ibfk_2 FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE',
    'SELECT "OK: FK contact_purchases.business_id ya existe" AS status');
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 0b: Crear/Reemplazar TRIGGER para sincronización automática
--          Cada vez que se INSERTA una compra en contact_purchases,
--          el trigger actualiza automáticamente:
--            - total_visits (+1)
--            - total_purchases (+ amount)
--            - category  según el número de compras
-- ============================================================

-- Eliminar trigger si ya existe (para recrearlo)
DROP TRIGGER IF EXISTS after_contact_purchase_insert;

-- Crear el trigger
DELIMITER //
CREATE TRIGGER after_contact_purchase_insert
AFTER INSERT ON contact_purchases
FOR EACH ROW
BEGIN
    DECLARE purchase_count INT;
    
    -- Actualizar total_visits y total_purchases en contacts
    UPDATE contacts
    SET total_visits    = total_visits + 1,
        total_purchases = total_purchases + NEW.amount
    WHERE id = NEW.contact_id;
    
    -- Obtener el número total de compras del contacto
    SELECT COUNT(*) INTO purchase_count
    FROM contact_purchases
    WHERE contact_id = NEW.contact_id;
    
    -- Clasificar según el número de compras
    IF purchase_count >= 3 THEN
        UPDATE contacts SET category = 'lovemark' WHERE id = NEW.contact_id;
    ELSEIF purchase_count >= 1 THEN
        UPDATE contacts SET category = 'cliente' WHERE id = NEW.contact_id;
    END IF;
END //
DELIMITER ;

-- ============================================================
-- PASO 1: Sincronizar datos históricos (total_visits y total_purchases)
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
SELECT '========================================' AS '';
SELECT 'RESUMEN FINAL - Clasificación de contactos:' AS '';
SELECT '========================================' AS '';
SELECT category, COUNT(*) AS total_contactos
FROM contacts
GROUP BY category
ORDER BY category;

SELECT '========================================' AS '';
SELECT 'Trigger creado: after_contact_purchase_insert' AS '';
SELECT 'Se activa automáticamente al insertar en contact_purchases' AS '';

-- Fin de la migración