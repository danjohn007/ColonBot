-- Funcionalidades de visitante: historial, reseñas asociadas y compatibilidad.

SET @db_name = DATABASE();

SET @add_reviews_user_id = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE reviews ADD COLUMN user_id INT UNSIGNED NULL AFTER business_id',
    'SELECT 1'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'reviews' AND COLUMN_NAME = 'user_id'
);
PREPARE stmt FROM @add_reviews_user_id;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_reviews_user_idx = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE reviews ADD INDEX idx_reviews_user_id (user_id)',
    'SELECT 1'
  )
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'reviews' AND INDEX_NAME = 'idx_reviews_user_id'
);
PREPARE stmt FROM @add_reviews_user_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_reviews_user_fk = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT 1'
  )
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'reviews' AND CONSTRAINT_NAME = 'fk_reviews_user'
);
PREPARE stmt FROM @add_reviews_user_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS visitor_place_visits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_visitor_visits_user (user_id),
  INDEX idx_visitor_visits_business (business_id),
  INDEX idx_visitor_visits_visited_at (visited_at),
  INDEX idx_visitor_visits_user_business (user_id, business_id),
  CONSTRAINT fk_visitor_visits_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_visitor_visits_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
