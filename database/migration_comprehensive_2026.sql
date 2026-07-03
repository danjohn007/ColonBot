-- ============================================================
-- Migration Comprehensive 2026 – All Changes in One Script
-- Platform: Colón Turístico
-- ============================================================

USE `colon_colonbotdb`;

-- ============================================================
-- 1. DELETE TABLES: iot_hikvision, gps_trackers, iot_shelly
-- ============================================================
DROP TABLE IF EXISTS `iot_hikvision`;
DROP TABLE IF EXISTS `gps_trackers`;
DROP TABLE IF EXISTS `iot_shelly`;

-- ============================================================
-- 2. DELETE TABLE: emergency_numbers
-- ============================================================
DROP TABLE IF EXISTS `emergency_numbers`;

-- ============================================================
-- 3. DELETE TABLE: tourist_profiles
-- ============================================================
DROP TABLE IF EXISTS `tourist_profiles`;

-- ============================================================
-- 4. FUSIONAR roles: 'admin' + 'colaborador' => 'colaborador_admin'
--    También fusionar 'turista' => 'visitor'
-- ============================================================

-- First, update all 'admin' users to 'colaborador_admin'
UPDATE `users` SET `role` = 'colaborador_admin' WHERE `role` = 'admin';

-- Update all 'colaborador' users to 'colaborador_admin'
UPDATE `users` SET `role` = 'colaborador_admin' WHERE `role` = 'colaborador';

-- Update all 'turista' users to 'visitor'
UPDATE `users` SET `role` = 'visitor' WHERE `role` = 'turista';

-- Now modify the ENUM column to remove old roles and add new ones
ALTER TABLE `users` 
  MODIFY COLUMN `role` ENUM('visitor','superadmin','prestador','colaborador_admin') NOT NULL DEFAULT 'visitor';

-- ============================================================
-- 5. EXPAND events table with all required fields
-- ============================================================

-- Drop old events table if exists and create new one
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL si es evento global/público',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de lista',
  `presale_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Precio de preventa',
  `capacity` INT UNSIGNED DEFAULT NULL COMMENT 'Aforo de la sede',
  `location` VARCHAR(500) DEFAULT NULL COMMENT 'Ubicación del evento',
  `validity` VARCHAR(100) DEFAULT NULL COMMENT 'Vigencia del evento',
  `conditions` TEXT DEFAULT NULL COMMENT 'Condiciones generales',
  `public_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL pública generada automáticamente',
  `event_type` ENUM('publico','privado') NOT NULL DEFAULT 'publico',
  `bot_authorized` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Autorizado para publicar en chatbot',
  `bot_authorized_by` INT UNSIGNED DEFAULT NULL COMMENT 'Quién autorizó la publicación en chatbot',
  `bot_authorized_at` DATETIME DEFAULT NULL,
  `target_segment` SET('prospectos_sin_historial','prospectos_recurrentes','clientes','clientes_frecuentes','todos') NOT NULL DEFAULT 'todos',
  `status` ENUM('pending','approved','active','inactive','expired') NOT NULL DEFAULT 'pending',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `presale_start` DATETIME DEFAULT NULL,
  `presale_end` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_business` (`business_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_bot_authorized` (`bot_authorized`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. Create notifications table for event authorization requests
-- (If not already exists with proper fields)
-- MySQL 5.7 compatible (no IF NOT EXISTS for columns)
-- ============================================================
-- Add event_id column to notifications if it doesn't exist
SET @dbname = DATABASE();
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'notifications' 
                   AND COLUMN_NAME = 'event_id');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE `notifications` ADD COLUMN `event_id` INT UNSIGNED DEFAULT NULL AFTER `business_id`',
              'SELECT "Column event_id already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index on event_id if it doesn't exist
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'notifications' 
                   AND INDEX_NAME = 'idx_event');
SET @sql2 = IF(@idx_exists = 0, 
               'ALTER TABLE `notifications` ADD INDEX `idx_event` (`event_id`)',
               'SELECT "Index idx_event already exists" AS status');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- ============================================================
-- 7. Drop promotions-related tables that are being replaced
-- NOTE: promotions table stays as-is for actual promotions
-- but we need promotion_views and promotion_inquiries
-- ============================================================
-- Keep promotion_sends, promotion_views, promotion_inquiries - they're still useful

-- ============================================================
-- 8. Update business table - add capacity field if not exists
-- MySQL 5.7 compatible
-- ============================================================
SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = @dbname 
                    AND TABLE_NAME = 'businesses' 
                    AND COLUMN_NAME = 'capacity');
SET @sql3 = IF(@col_exists2 = 0, 
               'ALTER TABLE `businesses` ADD COLUMN `capacity` INT UNSIGNED DEFAULT NULL COMMENT \'Aforo/Aforo de sede\' AFTER `max_images`',
               'SELECT "Column capacity already exists" AS status');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
