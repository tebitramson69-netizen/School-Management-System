-- ============================================
-- Migration 002: Configurable pass mark
-- ============================================
-- Adds a per-school pass threshold to school_settings.
-- The Result model reads this to decide PASS/FAIL.
-- Default 10.00 is the standard Cameroon GCE pass mark
-- on the 0-20 scale.
-- Safe to run multiple times (uses IF NOT EXISTS).
-- No existing data is modified or deleted.
-- ============================================

ALTER TABLE school_settings
    ADD COLUMN IF NOT EXISTS pass_mark DECIMAL(5,2) NOT NULL DEFAULT 10.00
    AFTER secondary_color;
