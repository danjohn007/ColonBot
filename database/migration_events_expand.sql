-- ============================================================
-- Migration: Expand events table with all required fields
-- for the new Events module (separated from promotions)
-- ============================================================

-- Expand the events table with all needed fields
ALTER TABLE `events`
  ADD COLUMN IF NOT EXISTS `user_id` INT UNSIGNED NOT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `title` VARCHAR(200) NOT NULL AFTER `name`,
  ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) DEFAULT NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `presale_price` DECIMAL(12,2) DEFAULT NULL AFTER `price`,
  ADD COLUMN IF NOT EXISTS `conditions` TEXT DEFAULT NULL AFTER `presale_price`,
  ADD COLUMN IF NOT EXISTS `public_url` VARCHAR(500) DEFAULT NULL AFTER `conditions`,
  ADD COLUMN IF NOT EXISTS `target_segment` SET('prospectos_sin_historial','prospectos_recurrentes','clientes','clientes_frecuentes','todos') NOT NULL DEFAULT 'todos' AFTER `public_url`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','approved','active','inactive','expired') NOT NULL DEFAULT 'pending' AFTER `target_segment`,
  ADD COLUMN IF NOT EXISTS `approved_by` INT UNSIGNED DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `end_date` DATETIME DEFAULT NULL AFTER `date`,
  ADD COLUMN IF NOT EXISTS `presale_start` DATETIME DEFAULT NULL AFTER `end_date`,
  ADD COLUMN IF NOT EXISTS `presale_end` DATETIME DEFAULT NULL AFTER `presale_start`,
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `presale_end`,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD INDEX IF NOT EXISTS `idx_status` (`status`),
  ADD INDEX IF NOT EXISTS `idx_business` (`business_id`),
  ADD INDEX IF NOT EXISTS `idx_user` (`user_id`);

-- Copy existing events data from promotions table
INSERT IGNORE INTO `events` (business_id, name, title, description, price, start_date, end_date, status, created_at, updated_at)
SELECT business_id, title, title, description, price, start_date, end_date, status, created_at, updated_at
FROM promotions WHERE type = 'evento';