<?php
/**
 * Migration: Enlazar chatbot_sessions con contacts
 * Clasificar usuarios en niveles usando 'category'
 * 
 * Ejecutar: php database/migration_chatbot_crm_link.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

echo "=== Migration: chatbot_crm_link ===\n\n";

// 1. chatbot_sessions - Agregar columnas
$tables = [
    'chatbot_sessions' => [
        'session_count' => 'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `state`',
        'has_purchased' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `session_count`',
        'purchase_count' => 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `has_purchased`',
        'category' => "VARCHAR(50) DEFAULT NULL AFTER `purchase_count`",
    ],
    'contacts' => [
        'chatbot_session_id' => 'BIGINT UNSIGNED DEFAULT NULL AFTER `notes`',
    ],
];

foreach ($tables as $table => $columns) {
    echo "Procesando tabla: $table\n";
    foreach ($columns as $colName => $colDef) {
        $check = $db->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND COLUMN_NAME = ?",
            [$table, $colName]
        )->fetchColumn();

        if ((int)$check === 0) {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$colName` $colDef";
            $db->execute($sql);
            echo "  -> Columna '$colName' creada\n";
        } else {
            echo "  -> Columna '$colName' ya existe, saltando\n";
        }
    }
}

// 2. Modificar ENUM de category en contacts
echo "\n2. Modificando ENUM de category en contacts...\n";
try {
    $db->execute("ALTER TABLE `contacts` MODIFY COLUMN `category` ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark') NOT NULL DEFAULT 'prospecto_sin_historial'");
    echo "  -> ENUM actualizado\n";
} catch (Exception $e) {
    echo "  -> Error: " . $e->getMessage() . " (probablemente ya está actualizado)\n";
}

// 3. Crear tablas
echo "\n3. Creando tablas nuevas...\n";
$newTables = [
    "promotion_views" => "CREATE TABLE IF NOT EXISTS `promotion_views` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `promotion_id` INT UNSIGNED NOT NULL,
        `contact_id` INT UNSIGNED DEFAULT NULL,
        `ip` VARCHAR(45) DEFAULT NULL,
        `user_agent` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_promotion` (`promotion_id`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "promotion_inquiries" => "CREATE TABLE IF NOT EXISTS `promotion_inquiries` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `promotion_id` INT UNSIGNED NOT NULL,
        `contact_id` INT UNSIGNED DEFAULT NULL,
        `name` VARCHAR(120) DEFAULT NULL,
        `phone` VARCHAR(20) DEFAULT NULL,
        `email` VARCHAR(191) DEFAULT NULL,
        `message` TEXT DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_promotion` (`promotion_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($newTables as $name => $sql) {
    try {
        $db->execute($sql);
        echo "  -> Tabla '$name' lista\n";
    } catch (Exception $e) {
        echo "  -> Error en '$name': " . $e->getMessage() . "\n";
    }
}

echo "\n=== Migración completada ===\n";