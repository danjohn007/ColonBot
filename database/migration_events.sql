-- Migration: Add events table
-- Run this on existing databases that don't yet have the events table.

CREATE TABLE IF NOT EXISTS `events` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `price`       DECIMAL(10,2) DEFAULT NULL,
  `date`        DATETIME     DEFAULT NULL,
  FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
