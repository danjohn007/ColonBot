-- ============================================================
-- Migration: Sincronizar categoría de contacts desde contact_purchases
-- 
-- Propósito:
--   Asegura que la tabla 'contacts' refleje correctamente la
--   clasificación de clientes basada en las compras registradas
--   en 'contact_purchases'.
--
-- Lógica de clasificación (Customer Journey):
--   1. Sin compras → Se mantiene como prospecto (según sus visitas)
--   2. 1 o más compras  → 'cliente'
--   3. 3 o más compras en el mismo negocio → 'lovemark'
--      (mínimo de 3 compras para ser considerado lovemark)
--
-- NO altera ninguna otra funcionalidad del sistema.
-- Solo actualiza registros existentes en la tabla contacts.
-- ============================================================
USE `colon_colonbotdb`;

-- ============================================================
-- PASO 1: Sincronizar total_visits y total_purchases
--          para que coincidan con los datos reales
-- ============================================================
UPDATE contacts c
LEFT JOIN (
    SELECT
        contact_id,
        COUNT(*)       AS real_purchases,
        COALESCE(SUM(amount), 0) AS real_total
    FROM contact_purchases
    GROUP BY contact_id
) stats ON stats.contact_id = c.id
SET
    c.total_visits    = COALESCE(stats.real_purchases, 0),
    c.total_purchases = COALESCE(stats.real_total, 0);

-- ============================================================
-- PASO 2: Contactos SIN compras → reclasificar como prospecto
--          Según sus visitas se define si es recurrente o no
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
--          (Primera compra convierte al prospecto en cliente)
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
--          (Mínimo de 3 compras en el mismo negocio)
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
-- FIN: Verificar resultados (opcional, comentado)
-- ============================================================
-- SELECT category, COUNT(*) FROM contacts GROUP BY category;
-- SELECT c.id, c.name, c.category, pc.purchase_count
-- FROM contacts c
-- JOIN (SELECT contact_id, COUNT(*) AS purchase_count FROM contact_purchases GROUP BY contact_id) pc ON pc.contact_id = c.id
-- ORDER BY pc.purchase_count DESC;