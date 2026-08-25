-- ============================================================
-- Migration: Campañas de mensajes WhatsApp (CRM "Mensajes")
-- Crea: mensajes_campanas, mensajes_envios (cola)
-- Agrega configuración de template de marketing en settings
-- Idempotente (seguro ejecutar varias veces)
-- ============================================================

-- ─── Campañas de mensajes ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mensajes_campanas` (
  `id_campana`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre`             VARCHAR(200) NOT NULL COMMENT 'Nombre de la campaña',
  `mensaje`            TEXT         NOT NULL COMMENT 'Texto del mensaje',
  `segmento`           VARCHAR(50)  NOT NULL DEFAULT 'todos' COMMENT 'todos | prospecto_sin_historial | prospecto_recurrente | cliente | cliente_frecuente | individual',
  `total_destinatarios` INT UNSIGNED NOT NULL DEFAULT 0,
  `creado_por`         INT UNSIGNED DEFAULT NULL COMMENT 'ID del usuario que creó la campaña',
  `creado_en`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`creado_por`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_creado_por` (`creado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Cola de envíos de mensajes ───────────────────────────────
CREATE TABLE IF NOT EXISTS `mensajes_envios` (
  `id_envio`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_campana`             INT UNSIGNED NOT NULL,
  `id_contacto`            INT UNSIGNED DEFAULT NULL,
  `whatsapp`               VARCHAR(20)  DEFAULT NULL COMMENT 'Teléfono/WhatsApp del destinatario',
  `mensaje`                TEXT         DEFAULT NULL,
  `estado`                 ENUM('pendiente','procesando','aceptado_meta','enviado','entregado','leido','error') NOT NULL DEFAULT 'pendiente',
  `tipo_envio`             ENUM('masivo','segmento','individual') NOT NULL DEFAULT 'masivo',
  `ventana_24h_abierta`    TINYINT(1)   DEFAULT NULL COMMENT '1 si el usuario escribió en las últimas 24h',
  `ultimo_mensaje_usuario_en` DATETIME  DEFAULT NULL COMMENT 'Timestamp del último mensaje del usuario en chatbot_sessions',
  `template_nombre`        VARCHAR(100) DEFAULT NULL COMMENT 'Plantilla de marketing de WhatsApp',
  `intentos`               INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_message_id`        VARCHAR(100) DEFAULT NULL COMMENT 'ID del mensaje devuelto por Meta',
  `detalle_error`          TEXT         DEFAULT NULL,
  `programado_en`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `procesado_en`           DATETIME     DEFAULT NULL,
  `actualizado_en`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_campana`) REFERENCES `mensajes_campanas`(`id_campana`) ON DELETE CASCADE,
  FOREIGN KEY (`id_contacto`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  INDEX `idx_campana` (`id_campana`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_id_contacto` (`id_contacto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Configuración: template de marketing ─────────────────────
INSERT INTO `settings` (`key`, `value`, `group`) VALUES
  ('whatsapp_template_marketing', 'marketing_colonbot_texto', 'chatbot'),
  ('whatsapp_template_language',  'es_MX',                    'chatbot')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);