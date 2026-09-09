-- migration_003_terms_unique_constraint.sql
--
-- Purpose:
--   Prevent duplicate term/sequence records within an academic year.
--   Duplicate rows in `terms` would split scores across different term_ids
--   and silently corrupt subject averages, overall averages and rankings.
--
-- Precondition (verified before writing this migration):
--   SELECT academic_year_id, name, sequence_number, COUNT(*) AS cnt
--   FROM terms
--   GROUP BY academic_year_id, name, sequence_number
--   HAVING cnt > 1;
--   -> returned zero rows (no existing duplicates), so the constraint applies cleanly.
--
-- Apply:
ALTER TABLE `terms`
    ADD UNIQUE KEY `uq_terms_year_name_sequence`
    (`academic_year_id`, `name`, `sequence_number`);

-- Rollback:
-- ALTER TABLE `terms` DROP INDEX `uq_terms_year_name_sequence`;