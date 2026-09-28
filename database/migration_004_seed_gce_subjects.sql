-- ============================================
-- Migration 004: Seed the standard Cameroon GCE subject menu
-- ============================================
--
-- The subjects table shipped with only 14 rows, short of the
-- official GCE Board menu (21 O/L subjects, 20 A/L subjects).
-- This adds the commonly-offered subjects that were missing so
-- classes can be configured realistically.
--
-- INSERT IGNORE keys on the UNIQUE(code) constraint: existing
-- rows are left untouched, only genuinely new subjects are added.
-- Re-running this migration is safe (idempotent).
--
-- School-specific subjects that are not on the official GCE Board
-- list (Law, Government) are included because they are commonly
-- offered; admins can add or remove any subject from the Subjects
-- screen.
-- ============================================

INSERT IGNORE INTO subjects (name, code) VALUES
    ('Additional Mathematics',              'ADMATH'),
    ('Human Biology',                       'HBIO'),
    ('Religious Studies',                   'RS'),
    ('Food Science and Nutrition',          'FN'),
    ('Geology',                             'GEOL'),
    ('Logic',                               'LOGIC'),
    ('Philosophy',                          'PHIL'),
    ('Information and Communication Technology', 'ICT'),
    ('Pure Mathematics with Mechanics',     'PMM'),
    ('Pure Mathematics with Statistics',    'PMS'),
    ('Further Mathematics',                 'FMATH'),
    ('Special Bilingual Education French',   'SBEF'),
    ('Law',                                 'LAW'),
    ('Government',                          'GOVT');
