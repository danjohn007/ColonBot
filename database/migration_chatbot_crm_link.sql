ALTER TABLE chatbot_sessions ADD COLUMN session_count INT UNSIGNED NOT NULL DEFAULT 1 AFTER `state`;

ALTER TABLE chatbot_sessions ADD COLUMN has_purchased TINYINT(1) NOT NULL DEFAULT 0 AFTER session_count;

ALTER TABLE chatbot_sessions ADD COLUMN purchase_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER has_purchased;

ALTER TABLE chatbot_sessions ADD COLUMN category VARCHAR(50) DEFAULT NULL AFTER purchase_count;

ALTER TABLE contacts ADD COLUMN chatbot_session_id BIGINT UNSIGNED DEFAULT NULL AFTER notes;

ALTER TABLE contacts MODIFY COLUMN category ENUM('prospecto','prospecto_sin_historial','prospecto_recurrente','cliente','lovemark') NOT NULL DEFAULT 'prospecto_sin_historial';

CREATE TABLE IF NOT EXISTS promotion_views (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id INT UNSIGNED NOT NULL,
  contact_id INT UNSIGNED DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_promotion (promotion_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_inquiries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  promotion_id INT UNSIGNED NOT NULL,
  contact_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(191) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_promotion (promotion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;