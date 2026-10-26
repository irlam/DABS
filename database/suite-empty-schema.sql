-- Dedicated DABS schema only: no users, passwords, tenant records, logs or sample data.
-- Reviewed from database/schema.sql; portable utf8mb4 collation and required attendee email.

CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `briefing_id` int NOT NULL COMMENT 'Briefing this activity belongs to',
  `date` date NOT NULL,
  `time` time NOT NULL COMMENT 'Scheduled time for the activity',
  `title` varchar(255) NOT NULL COMMENT 'Short title describing the activity',
  `description` text COMMENT 'Detailed description of the activity',
  `area` varchar(255) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT NULL COMMENT 'Priority level of the activity',
  `labor_count` int DEFAULT '0',
  `contractors` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_to` varchar(100) DEFAULT NULL COMMENT 'Person or team assigned to this activity',
  `completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores scheduled activities for daily briefings';

CREATE TABLE `activity_log` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL COMMENT 'User who performed the action',
  `action` varchar(100) NOT NULL COMMENT 'Type of action performed',
  `details` text COMMENT 'Additional details about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address action was performed from',
  `timestamp` datetime NOT NULL COMMENT 'When the action occurred'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System-wide audit trail of user actions';

CREATE TABLE `briefings` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` int NOT NULL COMMENT 'Project this briefing belongs to',
  `date` date NOT NULL COMMENT 'Date for this briefing',
  `overview` text COMMENT 'General overview of the day''s activities',
  `safety_info` text COMMENT 'Safety information and hazard warnings',
  `notes` text COMMENT 'Additional notes and information',
  `created_by` int DEFAULT NULL COMMENT 'User who created this briefing',
  `updated_by` int DEFAULT NULL COMMENT 'User who last updated this briefing',
  `last_updated` datetime DEFAULT NULL COMMENT 'When briefing was last updated',
  `status` enum('draft','published','archived') DEFAULT 'draft' COMMENT 'Current status of the briefing',
  UNIQUE KEY `project_date` (`project_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores daily briefing information for construction sites';

CREATE TABLE `dabs_attendees` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` int NOT NULL,
  `briefing_date` date NOT NULL,
  `attendee_name` varchar(255) NOT NULL,
  `subcontractor_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `added_by` varchar(255) DEFAULT NULL,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dabs_notes` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` int NOT NULL,
  `note_date` date NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `branding_settings` (
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

CREATE TABLE `dabs_subcontractors` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `trade` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `logo_updated_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dabs_subcontractor_tasks` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `subcontractor_id` int NOT NULL,
  `task_date` date NOT NULL,
  `task_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL COMMENT 'User requesting password reset',
  `token` varchar(64) NOT NULL COMMENT 'Secure token sent via email for verification',
  `expires_at` datetime NOT NULL COMMENT 'Token expiration date and time (usually 1 hour)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When reset request was made'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores password reset requests and tokens';

CREATE TABLE `projects` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL COMMENT 'Project name',
  `description` text COMMENT 'Detailed project description',
  `location` varchar(255) DEFAULT NULL COMMENT 'Physical location of the project',
  `manager` varchar(100) DEFAULT NULL COMMENT 'Name of project manager',
  `start_date` date NOT NULL COMMENT 'Project start date',
  `end_date` date DEFAULT NULL COMMENT 'Estimated completion date',
  `status` enum('planning','active','paused','completed') DEFAULT 'planning' COMMENT 'Current project status',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores construction projects information';

CREATE TABLE `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL COMMENT 'User associated with this token',
  `token` varchar(64) NOT NULL COMMENT 'Secure random token for cookie authentication',
  `expires_at` datetime NOT NULL COMMENT 'Token expiration date and time',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When token was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores "remember me" authentication tokens';

CREATE TABLE `resources` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `briefing_id` int NOT NULL COMMENT 'Briefing this resource allocation belongs to',
  `name` varchar(100) NOT NULL COMMENT 'Name of the resource',
  `type` enum('personnel','equipment','material','other') NOT NULL COMMENT 'Type of resource',
  `location` varchar(100) DEFAULT NULL COMMENT 'Location where the resource will be used',
  `assigned_to` varchar(100) DEFAULT NULL COMMENT 'Person or team the resource is assigned to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores resource allocations for daily briefings';

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE COMMENT 'Unique login username',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `name` varchar(100) NOT NULL COMMENT 'User full name',
  `email` varchar(100) NOT NULL COMMENT 'User email address',
  `role` enum('admin','manager','user') DEFAULT 'user' COMMENT 'User permission level',
  `last_login` datetime DEFAULT NULL COMMENT 'Last successful login timestamp',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last profile update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores user accounts and authentication details';

ALTER TABLE `briefings` ADD FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`);
ALTER TABLE `activities` ADD FOREIGN KEY (`briefing_id`) REFERENCES `briefings`(`id`);
ALTER TABLE `resources` ADD FOREIGN KEY (`briefing_id`) REFERENCES `briefings`(`id`);
ALTER TABLE `dabs_attendees` ADD FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`);
ALTER TABLE `dabs_subcontractors` ADD FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`);
ALTER TABLE `dabs_notes` ADD UNIQUE KEY `project_note_date` (`project_id`,`note_date`), ADD FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`);

