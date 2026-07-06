-- ============================================================
-- Migration: Chatbot CRM Link & Session Enhancements
-- Idempotent (safe to run multiple times)
-- ============================================================
USE `colon_colonbotdb`;

-- ─── Helper: add column if not exists ──────────────────────────────────────
SET @dbname = DATABASE();

-- Add session_count to chatbot_sessions
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'chatbot_sessions' 
                   AND COLUMN_NAME = 'session_count');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE chatbot_sessions ADD COLUMN session_count INT UNSIGNED NOT NULL DEFAULT 1 AFTER `state`',
              'SELECT "Column session_count already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add has_purchased to chatbot_sessions
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'chatbot_sessions' 
                   AND COLUMN_NAME = 'has_purchased');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE chatbot_sessions ADD COLUMN has_purchased TINYINT(1) NOT NULL DEFAULT 0 AFTER session_count',
              'SELECT "Column has_purchased already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add purchase_count to chatbot_sessions
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'chatbot_sessions' 
                   AND COLUMN_NAME = 'purchase_count');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE chatbot_sessions ADD COLUMN purchase_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER has_purchased',
              'SELECT "Column purchase_count already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add category to chatbot_sessions
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'chatbot_sessions' 
                   AND COLUMN_NAME = 'category');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE chatbot_sessions ADD COLUMN category VARCHAR(50) DEFAULT NULL AFTER purchase_count',
              'SELECT "Column category already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add chatbot_session_id to contacts
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = @dbname 
                   AND TABLE_NAME = 'contacts' 
                   AND COLUMN_NAME = 'chatbot_session_id');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE contacts ADD COLUMN chatbot_session_id BIGINT UNSIGNED DEFAULT NULL AFTER notes',
              'SELECT "Column chatbot_session_id already exists" AS status');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify contacts category ENUM (safe to run multiple times - ALTER MODIFY is idempotent)
ALTER TABLE contacts MODIFY COLUMN category ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark') NOT NULL DEFAULT 'prospecto_sin_historial';

-- Create promotion_views if not exists
CREATE TABLE IF NOT EXISTS promotion_views (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id INT UNSIGNED NOT NULL,
  contact_id INT UNSIGNED DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create promotion_inquiries if not exists
CREATE TABLE IF NOT EXISTS promotion_inquiries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id INT UNSIGNED NOT NULL,
  contact_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(191) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;