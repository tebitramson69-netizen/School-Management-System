# School Management System

A school management platform built around the workflows of a Cameroonian
secondary school: students, teachers, classes, subjects, attendance and scores,
ending in a printed termly report card with the class ranked.

PHP 8 on MySQL, in a custom MVC structure. No framework, no Composer, no npm,
and no build step — the files you clone are the files that run.

---

## What it does

**Four roles, each with its own view of the same data.** Administrator,
teacher, student and parent. A parent is linked to their children through a
join table and sees those children only.

**Academic structure that matches the school year.** Academic years hold terms,
each term holds two sequences, and enrolments are recorded per year so a
student can be promoted without losing last year's results.

**Attendance per class assignment.** A teacher marks a whole class for a date
in one pass rather than one student at a time. One record per student, per
subject period, per day, enforced by a unique key rather than by remembering.

**Scores, averages and grades.** Raw per-sequence scores are stored and the
averages derived on demand. Each subject average feeds a coefficient-weighted
overall, a letter grade from the school's own editable grade scale, and a pass
or fail against a configurable pass mark — 10 out of 20 by default.

**Report cards assembled from stored data, not re-entered.** A term report
holds what the class master records — justified and unjustified absences, times
late, conduct, sanctions, distinction, and both the class master's and the
principal's remarks — alongside the derived marks and the class position.

---

## The ranking engine

This is the part worth reading before anything else, in `src/Models/Result.php`.

`Result::assignPositions()` is a pure static function: it takes an array of
results and returns it sorted and numbered, with no database access at all.
That is deliberate, and it is why `tests/core_maths_test.php` can assert
against it directly without a database.

Three rules it enforces:

- **Equal averages share a position.** Two students on 15.0 are both second.
- **A student with no results is not ranked last.** They sort to the end of the
  list but get a position of `null`, because "not ranked" and "ranked last" are
  different facts about a child.
- **The rank and the printed average come from one engine.** Both derive from
  `Score::overallAverageForStudentTerm()`, so a student's position can never
  disagree with the figure printed beside it.

Overall averages are coefficient-weighted per class. Where no coefficients have
been configured, every subject defaults to a coefficient of 1 — which is
exactly an unweighted mean, so a class that has not been set up yet still
produces results instead of an error.

`Term::globalSequence()` maps a term name and a sequence number onto a single
ordering, so "Term 2, sequence 1" sorts as the third assessment of the year
rather than the first.

---

## Requirements

| | |
|---|---|
| PHP | 8.0 or newer |
| Database | MySQL or MariaDB |
| Server | Apache (built and run under XAMPP) |
| Dependencies | none — no Composer, no npm |

---

## Setup

### 1. Clone into your web root

```bash
cd /path/to/htdocs
git clone https://github.com/tebitramson69-netizen/School-Management-System.git school-system
cd school-system
```

### 2. Create the database

```sql
CREATE DATABASE `school-system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Load the schema. `database/schema.sql` is the full current schema and is what a
fresh install should use:

```bash
mysql -u root school-system < database/schema.sql
```

It also seeds the Cameroon GCE grade scale (A–F with remarks) and one
`school_settings` row.

The files in `database/migration_00N_*.sql` are **forward-only migrations**, for
an existing installation to catch up. On a fresh database loaded from
`schema.sql` they are already applied — do not run them as well.

### 3. Configure the environment

```bash
cp .env.example .env
```

Then edit `.env`:

```
DB_HOST=localhost
DB_NAME=school-system
DB_USERNAME=root
DB_PASSWORD=
BASE_URL=/school-system/public
SCHOOL_EMAIL_DOMAIN=@school.com
```

`BASE_URL` must match the path the browser uses. `.env` is gitignored and must
never be committed.

`config/env.php` reads that file. It is committed, because it is code rather
than a secret — it defines `env(string $key, string $default = '')`, parses
`.env` into the environment, and lets a real environment variable override the
file, which is what you want on a host that sets its own. The credentials live
in `.env` alone, which stays gitignored.

> **Copying `.env.example` is not optional.** `config/database.php` requires
> `config/env.php`, which looks for `.env`. Skip this step and the application
> falls back to the defaults above — fine on a stock XAMPP install, wrong
> anywhere else.

### 4. Protect the files above `public/`

The repository root contains `.env`, `config/` and `src/`. With the layout in
step 1 the whole folder sits under the web root, so without a guard
`http://localhost/school-system/.env` serves your database credentials as plain
text.

Two files in the repository handle this, and you do not have to write them:

| File | Contents | Effect |
|---|---|---|
| `.htaccess` (root) | `Require all denied` | nothing above `public/` is served |
| `public/.htaccess` | `Require all granted` | grants that one directory back |

Apache applies the deepest `.htaccess`, so `public/` stays reachable while the
repository root does not.

**Verify it, do not assume it.** Both files are inert unless Apache is
configured with `AllowOverride` permitting them:

```
http://localhost/school-system/.env          → must be 403, never the file
http://localhost/school-system/public/       → must be the login page
```

A 200 on the first means `AllowOverride` is off and nothing is protected — move
the rules into the vhost configuration instead. Check the second as well: if
the root rule applies but `public/.htaccess` does not, the whole site returns
403.

### 5. Create the first administrator

There is no public registration, by design. The first account is inserted by
hand. Generate a hash:

```bash
php -r "echo password_hash('your-password-here', PASSWORD_DEFAULT), PHP_EOL;"
```

Then:

```sql
INSERT INTO users (email, password_hash, role, must_change_password)
VALUES ('admin@school.com', '<the hash from above>', 'admin', 1);

INSERT INTO admins (user_id, full_name)
VALUES (LAST_INSERT_ID(), 'Your Name');
```

`must_change_password` is `1`, so the first sign-in is forced through the
change-password screen.

### 6. Open it

```
http://localhost/school-system/public/
```

---

## Tests

```bash
php tests/run.php
```

A minimal runner, no PHPUnit. It covers the pure, database-free core
calculations — coefficient weighting, ranking and term-sequence mapping — and
exits non-zero if any assertion fails, so it can gate CI later.

---

## Layout

```
public/index.php      front controller and router; every request enters here
config/database.php   PDO connection, BASE_URL, SCHOOL_EMAIL_DOMAIN
src/Controllers/      one per role, plus auth and academic years
src/Models/           20 models, one per table or concern
src/Core/             Security (sessions, CSRF), School (shared helpers)
src/Middleware/       AuthMiddleware — the single role gate
views/                admin, teacher, student, parent, auth, components
database/             schema.sql plus eight forward-only migrations
tests/                run.php and the core-maths assertions
```

Routing is a `switch` on `?action=` in `public/index.php`. Flat, greppable, and
it means a route cannot exist without being visible in one file.

---

## Security

**One gate, before any work.** `AuthMiddleware::requireRole()` runs before a
controller does anything, so a route cannot be reached by the wrong role even if
a check inside the controller were forgotten.

**CSRF on every form.** `Security::requireValidCsrf()` runs in the front
controller for every POST, not per form, so a new form cannot ship unprotected.

**Hardened sessions.** `httponly` and `SameSite=Strict`, `use_strict_mode` and
`use_only_cookies`, with the `secure` flag added automatically when the request
is already HTTPS.

**Passwords.** Hashed with `password_hash()` using PHP's default algorithm.
Strength rules on entry: at least 8 characters, with an uppercase letter, a
lowercase letter and a digit. A change is forced on first sign-in.

**Credentials out of the code**, in a gitignored `.env`.

**Login throttling, with a caveat worth knowing.**
`Security::checkLoginRateLimit()` allows 5 attempts then locks out for 5
minutes — but the counter lives in `$_SESSION`, so it is tied to the attacker's
own cookie. Discarding the cookie resets it. It stops a careless attempt and a
human retrying by hand; it does not stop a script. Moving the counter to a table
keyed on the IP address and the email tried would.

---

## Status

Built as a project, not deployed to a school. There are no production users and
no results to report.

`PROJECT_PROGRESS.md` is the running build log, phase by phase, including what
was fixed and what is still open.

---

## Known limitations

- **No automated database migration runner.** Migrations are applied by hand
  with `mysql <` and nothing records which have run.
- **Layout fragmentation.** The admin dashboard uses the shared layout; several
  other views are still standalone HTML.
- **No test coverage above the core maths.** Controllers, models and the role
  gate are not under test.
- **Login throttling is per session, not per address** — see the note under
  Security. The counter is in `$_SESSION`, so clearing cookies clears it.

---

## Licence

No licence file is present, so default copyright applies: all rights reserved
by the author. Add a licence if you intend others to reuse it.
