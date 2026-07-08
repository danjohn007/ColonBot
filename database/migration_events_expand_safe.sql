-- ============================================================
-- Safe migration: expand legacy events table for the Events admin.
-- Run this once on production if events was created with the old
-- columns: id, business_id, name, description, price, date.
-- ============================================================

SET @db_name := DATABASE();

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` MODIFY COLUMN `business_id` INT UNSIGNED DEFAULT NULL',
    'SELECT "events.business_id already nullable" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'events'
    AND COLUMN_NAME = 'business_id'
    AND IS_NULLABLE = 'YES'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `user_id` INT UNSIGNED NULL AFTER `id`',
    'SELECT "events.user_id already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'user_id'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `title` VARCHAR(200) NULL AFTER `business_id`',
    'SELECT "events.title already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'title'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `image` VARCHAR(255) DEFAULT NULL AFTER `description`',
    'SELECT "events.image already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'image'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `presale_price` DECIMAL(12,2) DEFAULT NULL AFTER `price`',
    'SELECT "events.presale_price already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'presale_price'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `capacity` INT UNSIGNED DEFAULT NULL AFTER `presale_price`',
    'SELECT "events.capacity already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'capacity'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `location` VARCHAR(500) DEFAULT NULL AFTER `capacity`',
    'SELECT "events.location already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'location'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `validity` VARCHAR(100) DEFAULT NULL AFTER `location`',
    'SELECT "events.validity already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'validity'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `whatsapp` VARCHAR(30) DEFAULT NULL AFTER `location`',
    'SELECT "events.whatsapp already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'whatsapp'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `conditions` TEXT DEFAULT NULL AFTER `validity`',
    'SELECT "events.conditions already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'conditions'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `public_url` VARCHAR(500) DEFAULT NULL AFTER `conditions`',
    'SELECT "events.public_url already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'public_url'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `event_type` ENUM(''publico'',''privado'') NOT NULL DEFAULT ''publico'' AFTER `public_url`',
    'SELECT "events.event_type already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_type'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `bot_authorized` TINYINT(1) NOT NULL DEFAULT 0 AFTER `event_type`',
    'SELECT "events.bot_authorized already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'bot_authorized'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `bot_authorized_by` INT UNSIGNED DEFAULT NULL AFTER `bot_authorized`',
    'SELECT "events.bot_authorized_by already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'bot_authorized_by'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `bot_authorized_at` DATETIME DEFAULT NULL AFTER `bot_authorized_by`',
    'SELECT "events.bot_authorized_at already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'bot_authorized_at'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `target_segment` SET(''prospectos_sin_historial'',''prospectos_recurrentes'',''clientes'',''clientes_frecuentes'',''todos'') NOT NULL DEFAULT ''todos'' AFTER `bot_authorized_at`',
    'SELECT "events.target_segment already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'target_segment'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `status` ENUM(''pending'',''approved'',''active'',''inactive'',''expired'') NOT NULL DEFAULT ''pending'' AFTER `target_segment`',
    'SELECT "events.status already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'status'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `approved_by` INT UNSIGNED DEFAULT NULL AFTER `status`',
    'SELECT "events.approved_by already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'approved_by'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `start_date` DATETIME DEFAULT NULL AFTER `approved_by`',
    'SELECT "events.start_date already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'start_date'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `end_date` DATETIME DEFAULT NULL AFTER `start_date`',
    'SELECT "events.end_date already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'end_date'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `presale_start` DATETIME DEFAULT NULL AFTER `end_date`',
    'SELECT "events.presale_start already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'presale_start'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `presale_end` DATETIME DEFAULT NULL AFTER `presale_start`',
    'SELECT "events.presale_end already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'presale_end'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `presale_end`',
    'SELECT "events.created_at already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'created_at'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`',
    'SELECT "events.updated_at already exists" AS status')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'updated_at'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fallback_user_id := (
  SELECT id
  FROM users
  WHERE role IN ('superadmin', 'colaborador_admin', 'prestador', 'admin', 'colaborador', 'admin_colaborador')
  ORDER BY FIELD(role, 'superadmin', 'colaborador_admin', 'admin', 'admin_colaborador', 'colaborador', 'prestador'), id
  LIMIT 1
);

SET @has_legacy_name := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'name'
);

SET @has_legacy_date := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND COLUMN_NAME = 'date'
);

SET @sql := IF(@has_legacy_name > 0 AND @has_legacy_date > 0,
  'UPDATE `events`
   SET `user_id` = COALESCE(`user_id`, @fallback_user_id),
       `title` = COALESCE(`title`, `name`),
       `start_date` = COALESCE(`start_date`, `date`),
       `status` = IF(`status` = ''pending'' AND `date` IS NOT NULL, ''active'', `status`),
       `event_type` = COALESCE(`event_type`, ''privado'')
   WHERE `user_id` IS NULL
      OR `title` IS NULL
      OR `start_date` IS NULL',
  'UPDATE `events`
   SET `user_id` = COALESCE(`user_id`, @fallback_user_id),
       `title` = COALESCE(`title`, ''Evento''),
       `event_type` = COALESCE(`event_type`, ''privado'')
   WHERE `user_id` IS NULL
      OR `title` IS NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD INDEX `idx_events_status` (`status`)',
    'SELECT "idx_events_status already exists" AS status')
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_status'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD INDEX `idx_events_business` (`business_id`)',
    'SELECT "idx_events_business already exists" AS status')
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_business'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD INDEX `idx_events_user` (`user_id`)',
    'SELECT "idx_events_user already exists" AS status')
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_user'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `events` ADD INDEX `idx_events_bot_authorized` (`bot_authorized`)',
    'SELECT "idx_events_bot_authorized already exists" AS status')
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_bot_authorized'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
