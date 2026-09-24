-- ============================================
-- Migration 007: activity_log
-- ============================================
--
-- Lightweight audit/activity feed for the admin dashboard's
-- "Recent Activity" panel. An entry is written whenever an admin
-- performs a notable action (registering or removing people,
-- assigning teachers, posting announcements, managing subjects).
--
-- actor_user_id is SET NULL on user delete so the log survives.
-- ============================================

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT DEFAULT NULL,
    type VARCHAR(40) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_activity_log_created ON activity_log (created_at);
