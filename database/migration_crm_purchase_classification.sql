-- ============================================================
-- CRM purchase-based classification
-- Clientes solo nacen de compras capturadas por el prestador.
-- MySQL 5.7 compatible.
-- ============================================================
USE `colon_colonbotdb`;

ALTER TABLE contacts
  MODIFY COLUMN category ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark')
  NOT NULL DEFAULT 'prospecto_sin_historial';

-- Sin compras registradas no puede ser cliente.
UPDATE contacts c
LEFT JOIN (
  SELECT contact_id, COUNT(*) AS purchase_count
  FROM contact_purchases
  GROUP BY contact_id
) pc ON pc.contact_id = c.id
SET c.category = CASE
  WHEN c.total_visits > 1 THEN 'prospecto_recurrente'
  ELSE 'prospecto_sin_historial'
END
WHERE COALESCE(pc.purchase_count, 0) = 0
  AND c.category IN ('prospecto', 'cliente', 'lovemark');

-- Una a dos compras: cliente.
UPDATE contacts c
JOIN (
  SELECT contact_id, COUNT(*) AS purchase_count
  FROM contact_purchases
  GROUP BY contact_id
) pc ON pc.contact_id = c.id
SET c.category = 'cliente'
WHERE pc.purchase_count BETWEEN 1 AND 2;

-- Tres o mas compras: cliente recurrente / Lovemark.
UPDATE contacts c
JOIN (
  SELECT contact_id, COUNT(*) AS purchase_count
  FROM contact_purchases
  GROUP BY contact_id
) pc ON pc.contact_id = c.id
SET c.category = 'lovemark'
WHERE pc.purchase_count >= 3;