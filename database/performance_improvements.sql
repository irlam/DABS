-- ============================================================================
-- Performance Improvements for DABS Database
-- ============================================================================
-- DESCRIPTION:
-- Additional indexes and optimizations for better query performance
-- 
-- AUTHOR: System Optimization
-- DATE: 19/11/2025
-- ============================================================================

-- Add composite index for activities by date and area (frequently filtered together)
ALTER TABLE `activities` 
ADD INDEX `idx_date_area_priority` (`date`, `area`, `priority`);

-- Add index for briefings by date for faster lookups
ALTER TABLE `briefings`
ADD INDEX `idx_date_status` (`date`, `status`);

-- Add index for attendees by date for faster briefing attendee queries
ALTER TABLE `dabs_attendees`
ADD INDEX `idx_date_project` (`briefing_date`, `project_id`);

-- Add index for notes by date for history queries
ALTER TABLE `dabs_notes`
ADD INDEX `idx_note_date` (`note_date` DESC);

-- Add index for subcontractors by status for active filtering
ALTER TABLE `dabs_subcontractors`
ADD INDEX `idx_status_project` (`status`, `project_id`);

-- Add index for activity log timestamp for recent activity queries
ALTER TABLE `activity_log`
ADD INDEX `idx_timestamp` (`timestamp` DESC);

-- Optimize table structures for InnoDB
OPTIMIZE TABLE `activities`;
OPTIMIZE TABLE `briefings`;
OPTIMIZE TABLE `dabs_attendees`;
OPTIMIZE TABLE `dabs_notes`;
OPTIMIZE TABLE `dabs_subcontractors`;
OPTIMIZE TABLE `resources`;

-- ============================================================================
-- Copy and paste the above SQL into phpMyAdmin SQL tab to apply optimizations
-- ============================================================================
