# CHANGELOG — ClinicDesk

All notable changes made during the finalization pass are documented here.
This release fixes the bugs that prevented the UI from rendering, completes
the analytics, and adds the previously-missing Activity Log subsystem.

---

## [1.1.0] — 2026-06-04

### Fixed (bugs that broke the running application)

- **Broken asset paths on standalone pages.** The login page
  (`views/auth/login.php`) and all three error pages
  (`views/errors/403.php`, `404.php`, `500.php`) loaded CSS/JS from
  `public/assets/adminlte/…`, a directory that **does not exist** in the
  project. As a result the login screen and error pages rendered with no
  styling and no JavaScript. All of these now load AdminLTE 3, Bootstrap,
  jQuery and Font Awesome from the same CDN already used by the main layout.

- **Dashboard charts never rendered.** `views/dashboard/admin.php` and
  `views/dashboard/doctor.php` injected Chart.js via a PHP *heredoc* that
  contained a literal `<?= BASE_URL ?>` (heredocs do not evaluate PHP short
  tags) pointing at a non-existent local `chart.min.js`. The broken
  `<script src>` line was removed; Chart.js is now loaded once, globally,
  in `views/partials/footer.php`.

- **`UserModel::update()` corrupted phone numbers.** The `mysqli` bind-type
  string was `'sssiii'`, which bound the `phone` column as an **integer**
  (dropping leading zeros and non-numeric characters). Corrected to
  `'ssssii'` to match the column order `name, email, role, phone,
  is_active, id`.

### Added

- **Activity Log subsystem (audit trail).**
  - New `activity_logs` table (`schema.sql`) plus a standalone migration
    (`migrations/2026_06_04_add_activity_logs.sql`) for existing databases.
    The `user_id` foreign key uses `ON DELETE SET NULL` and a `user_name`
    snapshot column so logs survive user deletion.
  - New `models/ActivityLogModel.php` — `log()`, `getRecent()`,
    `getFiltered()` (search + action + date filters, paginated) and
    `distinctActions()`.
  - New `logActivity()` and `activityActionMeta()` helpers in
    `core/helpers.php`. Logging failures are swallowed (written to the PHP
    error log) so the audit trail can never break a request.
  - Logging hooks wired into authentication and every write action:
    `login`, `failed_login`, `logout`, user create/update/delete/toggle,
    doctor create/update/delete, appointment book/status-change/cancel and
    prescription create.
  - New admin page `views/logs/index.php` with search, action filter, date
    range and pagination, reachable at `?page=logs`
    (`controllers/ActivityLogController.php`, admin-only).
  - New **Activity Logs** item in the admin sidebar and a **Recent
    Activity** widget on the admin dashboard.

- **Completed dashboard analytics (Chart.js).**
  - Admin dashboard: existing weekly bar chart now renders, plus a new
    **status-distribution doughnut** for the last 7 days.
  - Patient dashboard: new **"My Appointments" doughnut** (status
    breakdown) backed by a new `AppointmentModel::getStatusCountsForPatient()`.
  - Doctor dashboard: monthly trend line chart now renders.

- **PDF export for reports (dependency-free).** New
  `ReportController::printable()` and `views/reports/print.php` render a
  clean, print-optimised report (header, generated date, summary,
  paginated table, footer). The browser's *Print → Save as PDF* produces
  the PDF. An **Export PDF** button was added beside the existing CSV
  export on the Reports page; route `?page=reports&action=print`.

- **DataTables integration.** The footer already auto-initialised
  `.data-table` tables but the DataTables CSS/JS was never loaded — it is
  now included from CDN, so the doctors and prescriptions tables gain
  client-side search, sort and pagination.

### Changed

- `views/partials/footer.php` now loads jQuery, Bootstrap, AdminLTE,
  DataTables and Chart.js from CDN in one place (single source of truth for
  front-end dependencies).
- `schema.sql`: added `idx_appt_date` and `idx_appt_status` indexes on
  `appointments` to speed up the dashboard and report queries.

### Notes / known constraints

- **Front-end libraries are loaded via CDN, not vendored locally.** This
  makes the project run immediately on XAMPP (with internet) and avoids
  shipping large third-party files. To run fully offline, download the
  libraries into `public/assets/` and repoint the `<link>`/`<script>` tags
  (see `README_FINAL.md`).
- **PDF export is print-based rather than DomPDF/TCPDF.** The build
  environment had no network access to install a Composer PDF library. The
  printable view is structured so the same data can be handed to DomPDF
  later with no controller changes (see the note in
  `ReportController::printable()`).
- These changes were verified with a structural/static check (bracket,
  string, comment and heredoc balance across all 48 PHP files). They were
  **not** executed against a live PHP/MySQL stack in the build environment;
  run the smoke test in `README_FINAL.md` after importing the database.
