-- ============================================================================
-- Email Settings Table
-- ============================================================================
-- DESCRIPTION:
-- This migration creates a table to store SMTP email configuration settings
-- for the DABS system. This allows administrators to configure email delivery
-- using SMTP instead of the PHP mail() function for better reliability.
--
-- CREATED: 20/11/2025 (UK Date Format)
-- ============================================================================

-- Create email_settings table
CREATE TABLE IF NOT EXISTS `email_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `smtp_enabled` tinyint(1) DEFAULT 0 COMMENT 'Whether SMTP is enabled (1) or use mail() (0)',
  `smtp_host` varchar(255) DEFAULT NULL COMMENT 'SMTP server hostname',
  `smtp_port` int DEFAULT 587 COMMENT 'SMTP server port (25, 465, 587)',
  `smtp_encryption` varchar(10) DEFAULT 'tls' COMMENT 'Encryption type: tls, ssl, or none',
  `smtp_auth` tinyint(1) DEFAULT 1 COMMENT 'Whether SMTP requires authentication',
  `smtp_username` varchar(255) DEFAULT NULL COMMENT 'SMTP authentication username',
  `smtp_password` varchar(255) DEFAULT NULL COMMENT 'SMTP authentication password (encrypted)',
  `from_email` varchar(255) DEFAULT 'noreply@example.com' COMMENT 'Default from email address',
  `from_name` varchar(255) DEFAULT 'DABS System' COMMENT 'Default from name',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  `updated_by` int DEFAULT NULL COMMENT 'User ID who last updated settings',
  PRIMARY KEY (`id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `email_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores SMTP email configuration settings';

-- Insert default settings
INSERT INTO `email_settings` 
  (`id`, `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_auth`, `smtp_username`, `smtp_password`, `from_email`, `from_name`) 
VALUES 
  (1, 0, '', 587, 'tls', 1, '', '', 'noreply@example.com', 'DABS System')
ON DUPLICATE KEY UPDATE id=id;
