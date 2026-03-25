-- Migration: Chatbot WhatsApp simplified settings
-- Replaces the Meta API credentials with a single WhatsApp number and active/inactive toggle.

INSERT INTO `settings` (`key`, `value`, `group`) VALUES
  ('chatbot_active',    '0', 'chatbot'),
  ('chatbot_wa_number', '',  'chatbot')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
