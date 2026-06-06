# ClinicDesk — Update Notes (2026-05-31)

This document summarizes the fixes applied in **ClinicDesk_Final_Updated.zip**.
The original folder structure is preserved. No framework or dependency changes
were made; all edits are to existing native-PHP source plus a few new files.

---

## 1. Critical fixes (the app could not run correctly without these)

### 1.1 — Error pages no longer crash
**File:** `index.php`
The router referenced a non-existent `ErrorController` for `errors/403` and
`errors/404`, so every 403/404 redirect (auth failures, ownership checks,
missing records) triggered a fatal *"Class not found"* error instead of the
error page. The two phantom routes were removed; the dedicated fallback blocks
already present in `index.php` now render `views/errors/403.php` and
`views/errors/404.php` correctly.

### 1.2 — Default admin can now log in
**File:** `schema.sql` (+ new `migrations/2026_05_31_fix_admin_password.sql`)
The seeded admin hash was a cost-10 bcrypt hash with its cost field manually
edited to 12, which invalidated the checksum — it verified against **no**
password, so `admin@clinicdesk.local` could never log in. Replaced with a valid
cost-12 bcrypt hash of `Admin@1234` (verified). A migration is provided for
databases already imported from the old schema.

### 1.3 — Missing upload directories
**New:** `public/uploads/avatars/`, `public/uploads/doctor_photos/`
These directories were absent from the package, so `uploadImage()` (avatars and
doctor photos) failed at `move_uploaded_file()`. Both directories are now
included and hardened (see 2.3).

---

## 2. Security hardening

### 2.1 — Global exception handler
**File:** `index.php`
With strict `mysqli` reporting, any DB error (duplicate key, FK violation,
booking race against the `uq_no_double_book` constraint) threw an *uncaught*
exception, dumping a stack trace + SQL when `APP_DEBUG=true`. Added
`set_exception_handler()`: shows the trace only in debug mode, otherwise logs
the error and renders a clean 500 page.

### 2.2 — CSV formula-injection protection
**File:** `controllers/ReportController.php`
The report export wrote `reason`/`notes`/name fields to CSV raw. Values
beginning with `= + - @` (or tab/CR) are now prefixed with `'` so spreadsheet
apps treat them as text, not executable formulas.

### 2.3 — Upload directories cannot execute scripts
**New:** `.htaccess` in `public/uploads/avatars/` and `public/uploads/doctor_photos/`
Defense-in-depth: serves images but blocks PHP/CGI execution and directory
listing. (`prescriptions/` already had a deny-all `.htaccess`.)

---

## 3. Functional fix

### 3.1 — Admin prescriptions list was always empty
**Files:** `models/PrescriptionModel.php`, `controllers/PrescriptionController.php`
The admin branch called `getForDoctor(0)`, which filtered on a non-existent
doctor id 0 and returned nothing. Added `PrescriptionModel::getAll()` (no
ownership filter) and the admin view now uses it to show all prescriptions.

---

## 4. Minor / cosmetic

### 4.1 — Clearer variable name
**Files:** `controllers/DoctorController.php`, `views/doctors/form.php`
Renamed the misleadingly named `$patientUsers` (which actually holds
doctor-role users for the "link to user account" dropdown) to `$doctorUsers`.

---

## Modified & new files

**Modified**
- `index.php`
- `schema.sql`
- `controllers/ReportController.php`
- `controllers/PrescriptionController.php`
- `controllers/DoctorController.php`
- `models/PrescriptionModel.php`
- `views/doctors/form.php`

**New**
- `views/errors/500.php`
- `migrations/2026_05_31_fix_admin_password.sql`
- `public/uploads/avatars/.htaccess`
- `public/uploads/doctor_photos/.htaccess`

---

## Database changes to execute

- **Fresh install:** import the updated `schema.sql` as usual — no extra steps.
- **Existing database** (imported before this update): run the migration to
  repair the admin login:

  ```bash
  mysql -u root -p clinicdesk_db < migrations/2026_05_31_fix_admin_password.sql
  ```

After logging in with `admin@clinicdesk.local` / `Admin@1234`, change the
password immediately.

---

## Recommended (not implemented — would need design decisions)

These were identified during review but left out so the update stays
behavior-preserving. Consider them as next steps:

- **Login throttling / lockout** — the login form has no rate limiting, so the
  admin account is brute-forceable. A robust fix needs an attempts table or an
  external store (a session-only counter is trivially bypassed).
- **Stronger password policy** — user passwords currently require only 8+
  characters with no complexity rule.
- **`affected_rows`-based success** — `Database::query()` reports success for
  writes that matched zero rows, so edit/delete on a non-existent id still shows
  a success flash. Harmless, but slightly misleading.
- **Production config** — `config/config.php` ships `APP_DEBUG=true` and
  `config/database.php` ships `root` / empty password; flip these per the
  README production checklist before deploying.
