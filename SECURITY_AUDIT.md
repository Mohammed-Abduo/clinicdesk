# SECURITY_AUDIT — ClinicDesk

**Audit date:** 2026-06-04
**Scope:** full codebase (router, core, models, controllers, views, config,
SQL, `.htaccess`).
**Method:** manual source review against the OWASP Top 10, plus a static
structural pass over all PHP files.

This project was already built to a high security standard. The audit
confirmed the existing controls, fixed the issues found, and added an audit
trail. No critical or high-severity vulnerabilities were found in the
reviewed code.

---

## 1. Findings and resolutions

| # | Severity | Finding | Status |
|---|----------|---------|--------|
| F-1 | Low (data integrity) | `UserModel::update()` bound `phone` with type `i` (`'sssiii'`), corrupting phone numbers with leading zeros or symbols. | **Fixed** → `'ssssii'`. |
| F-2 | Low (availability) | Login and error pages referenced a non-existent local asset directory, so those pages rendered unstyled. Not exploitable, but a defect. | **Fixed** → CDN assets. |
| F-3 | Informational | No audit trail existed, so security-relevant events (logins, failed logins, deletions) could not be reviewed after the fact. | **Resolved** → Activity Log subsystem added, including `failed_login` events with the attempted identifier and source IP. |

The Activity Log records the source IP for every event and is admin-only,
giving basic detection capability for brute-force or misuse (OWASP A09 –
Security Logging & Monitoring Failures).

---

## 2. Controls verified (already present, confirmed correct)

### A01 — Broken Access Control
- Central RBAC via `Auth::requireLogin()` / `Auth::requireRole()` invoked in
  every controller constructor; admin-only controllers
  (`User`, `Doctor`, `Report`, `ActivityLog`) gate in `__construct()`.
- Per-record ownership checks: `AppointmentController::verifyOwnership()`,
  prescription download ownership checks, and per-role status-transition
  whitelists in `updateStatus()`.
- Patients can only cancel their own *pending* appointments; doctors can
  only act on their own appointments.
- Users cannot delete their own account (`UserController::delete()`).

### A02 — Cryptographic Failures
- Passwords hashed with `password_hash(PASSWORD_BCRYPT, cost 12)`; verified
  with `password_verify()`. No plaintext or reversible storage.
- The seeded admin hash was confirmed to verify against its documented
  password.

### A03 — Injection
- **SQL:** 100% parameterised. All database access goes through
  `Database::query()` / `BaseModel::query()` using `mysqli` prepared
  statements with bound parameters. No string concatenation of user input
  into SQL was found. `WHERE`/filter builders bind every dynamic value.
- **XSS:** output is escaped with the `e()` helper
  (`htmlspecialchars(ENT_QUOTES|ENT_SUBSTITUTE, UTF-8)`); user content in
  views is consistently passed through `e()` / `nl2br(e())`.
- **CSV injection:** `ReportController::csvSafe()` neutralises cells
  starting with `= + - @` (and tab/CR) on export.

### A04 — Insecure Design
- Server-side validation in every controller (email format, password length
  ≥ 8, role whitelist, fee numeric, available-days, date in the future,
  double-booking conflict). A `UNIQUE` constraint
  (`uq_no_double_book`) enforces no double-booking at the database layer too.

### A05 — Security Misconfiguration
- `APP_DEBUG = false` in production; a global exception handler renders a
  clean 500 page and logs the detail instead of leaking stack traces/SQL.
- `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)` so DB errors
  throw rather than fail silently.
- Root `.htaccess`: `Options -Indexes`, `ServerSignature Off`, denies
  dot-files, blocks direct access to `config/core/models/controllers/views`
  `.php` files, and sets `X-Frame-Options`, `X-Content-Type-Options`,
  `X-XSS-Protection`, `Referrer-Policy`.

### A06 — Vulnerable Components
- The application carries no bundled third-party PHP dependencies (no
  Composer tree to audit). Front-end libraries are pinned to specific CDN
  versions (AdminLTE 3.2, Bootstrap 4.6.2, jQuery 3.7.1, Chart.js 4.4.1,
  DataTables 1.13.7).

### A07 — Identification & Authentication Failures
- Session hardening in `index.php`: `session.use_strict_mode`,
  `cookie_httponly`, `cookie_samesite=Strict` (and a commented
  `cookie_secure` for HTTPS), custom session name.
- `session_regenerate_id(true)` on successful login (prevents fixation).
- Full session teardown on logout (clears `$_SESSION`, expires the cookie,
  `session_destroy()`).
- Disabled accounts (`is_active = 0`) cannot authenticate.

### A08 — Software & Data Integrity Failures
- **CSRF:** `CSRF::generateToken()` + `CSRF::input()` on every state-changing
  form; `CSRF::validate()` (constant-time `hash_equals`, token rotated after
  use) on every POST handler, including logout.
- **File uploads:** images validated by real type (`getimagesize` +
  `IMAGETYPE_*` whitelist, ≤ 1 MB); PDFs validated by MIME
  (`finfo`, `application/pdf`, ≤ 3 MB). Uploads are renamed to
  `random_bytes(16)` hex, and each upload directory ships a `.htaccess`
  that disables script execution and directory listing. Prescription PDFs
  are streamed through a PHP authorization check, never linked directly.

### A09 — Logging & Monitoring
- Addressed by the new Activity Log subsystem (see §1, F-3).

### A10 — SSRF
- The application makes no outbound server-side HTTP requests from user
  input. Not applicable.

---

## 3. Recommendations (hardening beyond the assignment scope)

1. **Enable HTTPS** in production and uncomment
   `ini_set('session.cookie_secure', '1')` in `index.php`.
2. **Login rate limiting / lockout.** The `failed_login` events are now
   recorded; add throttling (e.g. lock for N minutes after M failures) on
   top of them.
3. **Add a `Content-Security-Policy`** header. Note that the inline
   `<script>` chart blocks and the CDN sources would need to be reflected in
   the policy (a strong reason to vendor assets locally — see README).
4. **Restrict default DB credentials.** `config/database.php` ships with
   `root` / empty password for XAMPP convenience; create a least-privilege
   MySQL user before any real deployment.
5. **Change the seeded admin password** immediately after first login.
