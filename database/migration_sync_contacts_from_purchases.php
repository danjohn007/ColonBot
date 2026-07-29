<?php
/**
 * Migration: Sincronizar categoría de contacts desde contact_purchases
 *
 * Propósito:
 *   Asegura que la tabla 'contacts' refleje correctamente la
 *   clasificación de clientes basada en las compras registradas
 *   en 'contact_purchases'.
 *
 * Problema que resuelve:
 *   Había compras registradas en contact_purchases pero la tabla
 *   contacts no se actualizaba (datos históricos que no pasaron
 *   por refreshCategoryFromPurchases()).
 *
 * Lógica de clasificación (Customer Journey):
 *   1. Sin compras → prospecto (según total_visits)
 *   2. 1 a 2 compras → 'cliente'
 *   3. 3 o más compras en el mismo negocio → 'lovemark'
 *
 * NO altera ninguna otra funcionalidad del sistema.
 * Solo actualiza registros existentes en la tabla contacts.
 *
 * Ejecutar: php database/migration_sync_contacts_from_purchases.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

echo "=== Migration: Sync contacts from contact_purchases ===\n\n";

// ────────────────────────────────────────────────────────────
// PASO 1: Sincronizar total_visits y total_purchases
// ────────────────────────────────────────────────────────────
echo "1. Sincronizando total_visits y total_purchases en contacts...\n";
$affected = $db->exec("
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
        c.total_purchases = COALESCE(stats.real_total, 0)
");
echo "  -> $affected contactos actualizados\n";

// ────────────────────────────────────────────────────────────
// PASO 2: Sin compras → reclasificar como prospecto
// ────────────────────────────────────────────────────────────
echo "2. Reclasificando contactos SIN compras como prospectos...\n";
$affected = $db->exec("
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
    WHERE pc.purchase_count IS NULL
");
echo "  -> $affected contactos reclasificados como prospectos\n";

// ────────────────────────────────────────────────────────────
// PASO 3: 1 a 2 compras → 'cliente'
// ────────────────────────────────────────────────────────────
echo "3. Reclasificando contactos con 1-2 compras como 'cliente'...\n";
$affected = $db->exec("
    UPDATE contacts c
    JOIN (
        SELECT contact_id, COUNT(*) AS purchase_count
        FROM contact_purchases
        GROUP BY contact_id
        HAVING purchase_count BETWEEN 1 AND 2
    ) pc ON pc.contact_id = c.id
    SET c.category = 'cliente'
");
echo "  -> $affected contactos reclasificados como cliente\n";

// ────────────────────────────────────────────────────────────
// PASO 4: 3 o más compras → 'lovemark'
// ────────────────────────────────────────────────────────────
echo "4. Reclasificando contactos con 3+ compras como 'lovemark'...\n";
$affected = $db->exec("
    UPDATE contacts c
    JOIN (
        SELECT contact_id, COUNT(*) AS purchase_count
        FROM contact_purchases
        GROUP BY contact_id
        HAVING purchase_count >= 3
    ) pc ON pc.contact_id = c.id
    SET c.category = 'lovemark'
");
echo "  -> $affected contactos reclasificados como lovemark\n";

// ────────────────────────────────────────────────────────────
// MOSTRAR RESUMEN
// ────────────────────────────────────────────────────────────
echo "\n--- Resumen final de la tabla contacts ---\n";
$stmt = $db->query("SELECT category, COUNT(*) AS total FROM contacts GROUP BY category ORDER BY category");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    echo sprintf("  %-35s %d\n", $row['category'], $row['total']);
}

$total = $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
echo sprintf("  %-35s %d\n", 'TOTAL:', $total);

echo "\n=== Migración completada ===\n";