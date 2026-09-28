-- ============================================
-- Migration 008: events
-- ============================================
--
-- School events (tests, report cards, GCE registration deadlines,
-- staff meetings...) shown in the dashboard "Upcoming" panel and
-- managed by the admin.
-- ============================================

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_events_date ON events (event_date);
