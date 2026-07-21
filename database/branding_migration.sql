-- DABS branding migration for an existing installation.
-- Safe to run once in phpMyAdmin; existing records are retained.
CREATE TABLE IF NOT EXISTS `branding_settings` (
  `id` tinyint unsigned NOT NULL DEFAULT 1,
  `company_name` varchar(150) NOT NULL DEFAULT 'DABS',
  `tagline` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) NOT NULL DEFAULT 'images/logo.png',
  `company_logo_dark` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `show_login` tinyint(1) NOT NULL DEFAULT 1,
  `show_dashboard` tinyint(1) NOT NULL DEFAULT 1,
  `show_reports` tinyint(1) NOT NULL DEFAULT 1,
  `show_emails` tinyint(1) NOT NULL DEFAULT 1,
  `show_subcontractor_logos` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `branding_settings` (`id`) VALUES (1);

ALTER TABLE `dabs_subcontractors`
  ADD COLUMN `logo_path` varchar(255) DEFAULT NULL AFTER `email`,
  ADD COLUMN `logo_updated_at` datetime DEFAULT NULL AFTER `logo_path`;
