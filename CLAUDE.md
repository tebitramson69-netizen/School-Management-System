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
- `enrollments` — one per student per academic year (UNIQUE constraint)
- `scores` — one per student/subject/term/sequence (UNIQUE constraint)
- `subject_coefficients` — per-subject per-class weighting for GCE

## Known Issues
- Model files have lowercase names but require statements use PascalCase (works on Windows, breaks Linux)
- `views/teacher/Mark_attendance.php` has mixed case (controller requires lowercase)
- Result model (rankings) exists but is not wired to any route
- SubjectCoefficient model exists but is not used in any calculation
- Subject::getDashboardStatistics() is dead code (duplicate of Student method)
- User::updatePassword() returns void but caller checks for false

## Testing Commands
```bash
# Syntax check all PHP files
find . -name "*.php" -not -path "./vendor/*" | xargs -I{} php -l {}

# Check for require path issues (model casing)
grep -rn "require.*Models/" --include="*.php" src/Controllers/
```

## Current Roadmap
See PROJECT_PROGRESS.md for completed phases and next steps.
