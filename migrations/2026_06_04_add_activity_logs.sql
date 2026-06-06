-- ============================================================
-- Migration: 2026-06-04  Add activity_logs table + appt indexes
-- ============================================================
--
-- Adds the audit-trail table used by the Activity Log feature and
-- two helper indexes on `appointments` that speed up the dashboard
-- and report queries. Safe to run once on an existing database that
-- was created from the pre-Activity-Log schema.
--
--   mysql -u root -p clinicdesk_db < migrations/2026_06_04_add_activity_logs.sql
-- ============================================================

USE clinicdesk_db;

CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED DEFAULT NULL,
    user_name   VARCHAR(120) DEFAULT NULL,
    action      VARCHAR(60)  NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_user    (user_id),
    KEY idx_logs_action  (action),
    KEY idx_logs_created (created_at),
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Helper indexes for appointments (ignore errors if they already exist).
ALTER TABLE appointments ADD INDEX idx_appt_date   (appt_date);
ALTER TABLE appointments ADD INDEX idx_appt_status (status);
