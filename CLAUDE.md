# School Management System — CLAUDE.md

## Architecture
- PHP 8+ custom MVC, no framework
- Front controller: `public/index.php` with `?action=` routing
- Bootstrap 5.3.3 via CDN for CSS framework
- Vanilla JavaScript (no frontend framework)
- MySQL/MariaDB with PDO (singleton connection)
- Session-based authentication with role-based authorization

## Cameroon GCE Domain
- First Cycle: Forms 1-5, GCE O/L at Form 5
- Second Cycle: Lower Sixth, Upper Sixth, GCE A/L at Upper Sixth
- 3 terms per year, 2 sequences per term = 6 sequences
- Scoring: 0-20 scale, 10/20 pass threshold
- **Weighted averages**: Σ(mark × coefficient) / Σ(coefficients) — coefficients are MINESEC-standardized but should be configurable
- Report cards require: coefficient, seq1, seq2, term avg, class avg, ranking, remarks

## Important Conventions
- All POST routes are CSRF-validated globally in `index.php` (not per-controller)
- Session hardening configured via `Security::configureSession()` before `session_start()`
- Database credentials via `.env` file (parsed by `config/env.php`)
- Output escaping: use `htmlspecialchars()` — do NOT define global `e()` helper
- Views should use shared dashboard layout (`views/layouts/dashboard.php`) via `ob_start()`/`$content = ob_get_clean()`
- Model files MUST use PascalCase filenames matching class names

## Security Rules
- CSRF: `Security::csrfField()` in every form, `Security::requireValidCsrf()` in front controller
- Passwords: `Security::validatePasswordStrength()` — 8+ chars, uppercase, lowercase, number
- Rate limiting: `Security::checkLoginRateLimit()` — 5 attempts, 5-minute lockout (session-based)
- Never commit `.env` file (in `.gitignore`)

## Database Assumptions
- `academic_years.is_current` — exactly one row should be TRUE at a time
- `terms.is_current` — exactly one row TRUE at a time (within current academic year)
- `terms` — one row per (term name, within-term sequence 1|2); a "term" spans its two rows. Global sequence 1–6 is a display mapping only (`Term::globalSequence()`), not stored
- `enrollments` — one per student per academic year (UNIQUE constraint)
- `scores` — one per student/subject/term/sequence (UNIQUE constraint); `scores.sequence` is the within-term number (1|2)
- `subject_coefficients` — per-subject per-class weighting for GCE (UNIQUE on subject_id+class_id)
- `class_subjects` — which subjects a class offers (UNIQUE on class_id+subject_id); the authoritative subject list, independent of teacher assignment
- `student_term_reports` — per-student/term attendance, conduct, sanctions & remarks (UNIQUE on student_id+term_id) for the report card
- `activity_log` — admin dashboard "Recent Activity" feed (actor SET NULL on user delete)
- `events` — school events for the dashboard "Upcoming" panel
- Migrations live in `database/migration_00N_*.sql`; `database/schema.sql` mirrors the full schema for fresh installs

## Known Issues
- None currently tracked. (Prior issues — model-file casing, `Result`/`SubjectCoefficient` wiring, `Subject::getDashboardStatistics` dead code, and `User::updatePassword()` return type — are all resolved.)

### Caveats to keep in mind
- The report card subject list = a class's offered subjects (`class_subjects`) merged with any subject the student has marks in, so leftover marks for a no-longer-offered subject still appear until score data is reset. Only marked subjects contribute to the average.
- Dashboard pass-rate and class-ranking computations are N+1 (per class → per student); fine at school scale, revisit if data grows large.
- `Result::getClassSummary()` is currently unused (kept for a future class-summary screen).
- Model line endings are mixed per file; when editing, keep a file's existing convention to avoid noisy diffs.

## Testing Commands
```bash
# Run the unit test suite (pure core-maths: weighting, ranking, sequence mapping)
php tests/run.php

# Syntax check all PHP files
find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {}

# Check for require path issues (model casing)
grep -rn "require.*Models/" --include="*.php" src/Controllers/
```

## Current Roadmap
See PROJECT_PROGRESS.md for completed phases and next steps.
