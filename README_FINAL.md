# ClinicDesk — Clinic Management System

A role-based clinic management web application built in **native PHP 8.1**
using a hand-rolled **MVC** architecture, **MySQL** with `mysqli` prepared
statements, **AdminLTE 3** for the UI, **Chart.js** analytics, CSRF
protection and session authentication.

> **Version 1.1.0** — see `CHANGELOG.md` for what changed in this release and
> `SECURITY_AUDIT.md` for the security review.

---

## 1. Features

### Roles
- **Admin** — manage users, doctors and specializations; view all
  appointments; generate reports (CSV + PDF); review the activity log.
- **Doctor** — see today's schedule, change appointment status, issue
  prescriptions, view monthly trend analytics.
- **Patient** — book and cancel appointments, view prescriptions and a
  personal appointment-status chart.

### Functional modules
- **Authentication** — bcrypt login, session hardening, account
  enable/disable.
- **Users** — full CRUD, search & role filter, pagination, active toggle.
- **Doctors & Specializations** — CRUD, photo upload, available-days and
  consultation fee; specializations use *safe delete* (blocked while in use).
- **Appointments** — booking with day-availability and double-booking checks,
  status workflow (`pending → confirmed → completed / cancelled`),
  role-aware filtering and pagination.
- **Prescriptions** — issued against completed appointments, optional PDF
  attachment served through an authorization check.
- **Reports** — date/doctor/status filters, summary tiles, **CSV export**
  (Excel-safe) and **PDF export** (print-optimised).
- **Activity Log** — audit trail of logins, failed logins, logouts and all
  create/update/delete actions, with search, action and date filters.
- **Dashboards** — per-role stat tiles and Chart.js visualisations.

---

## 2. Requirements

- PHP **8.1+** (uses `declare(strict_types=1)`, union types, `match`)
- MySQL / MariaDB
- Apache with `mod_rewrite` (XAMPP is ideal)
- Internet access on the server (front-end libraries load from CDN — see
  §7 to run fully offline)

---

## 3. Installation (XAMPP)

1. **Copy the project** into your web root as a folder named `clinicdesk`:
   ```
   C:\xampp\htdocs\clinicdesk\
   ```
   > The folder name matters: the root `.htaccess` sets
   > `RewriteBase /clinicdesk/`. If you use a different folder name, update
   > that line (and `BASE_URL` is derived automatically from the script path).

2. **Start** Apache and MySQL from the XAMPP control panel.

3. **Create the database.** In phpMyAdmin (or the CLI) import `schema.sql`:
   ```bash
   mysql -u root < schema.sql
   ```
   This creates the `clinicdesk_db` database, all tables (including
   `activity_logs`), indexes and seed data.

   *Upgrading an existing pre-1.1 database instead of a fresh import?* run:
   ```bash
   mysql -u root clinicdesk_db < migrations/2026_06_04_add_activity_logs.sql
   ```

4. **Check DB credentials** in `config/database.php` (defaults match a
   stock XAMPP install: host `localhost`, db `clinicdesk_db`, user `root`,
   empty password).

5. **Open** the app:
   ```
   http://localhost/clinicdesk/
   ```

### Default credentials
| Role  | Email                     | Password     |
|-------|---------------------------|--------------|
| Admin | `admin@clinicdesk.local`  | `Admin@1234` |

> **Change this password immediately after first login.** Doctor and patient
> accounts are created from the admin panel (create a user with the `doctor`
> role, then add a doctor profile linked to it).

### 60-second smoke test
1. Log in as admin → the dashboard shows tiles, a weekly bar chart and a
   status doughnut (no broken images/blank charts).
2. Create a `doctor` user, then **Doctors → Add Doctor** linked to it.
3. Create a `patient` user, log in as them, **Book Appointment**.
4. Back as the doctor: confirm → complete the appointment, add a
   prescription.
5. **Reports** → Apply filters → **Export CSV** and **Export PDF**.
6. **Activity Logs** (admin) → confirm the login, booking and status events
   appear.

---

## 4. Folder structure

```
clinicdesk/
├── index.php                 # Front controller / router (whitelisted routes)
├── .htaccess                 # Rewrite rules + security headers
├── schema.sql                # Database schema + seed data
│
├── config/
│   ├── config.php            # App constants, paths, session name, upload limits
│   └── database.php          # DB credentials
│
├── core/
│   ├── Database.php          # Singleton mysqli wrapper (prepared statements)
│   ├── Auth.php              # Session auth + RBAC guards
│   ├── CSRF.php              # CSRF token generate / validate (rotating)
│   ├── Paginator.php         # Pagination helper
│   └── helpers.php           # e(), redirect, flash, uploads, logActivity(), …
│
├── models/
│   ├── BaseModel.php         # Shared query() proxy
│   ├── UserModel.php
│   ├── DoctorModel.php
│   ├── SpecializationModel.php
│   ├── AppointmentModel.php
│   ├── PrescriptionModel.php
│   └── ActivityLogModel.php  # (new) audit-trail data layer
│
├── controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UserController.php
│   ├── DoctorController.php
│   ├── AppointmentController.php
│   ├── PrescriptionController.php
│   ├── ReportController.php
│   └── ActivityLogController.php  # (new) admin audit log
│
├── views/
│   ├── partials/             # header, navbar, sidebar, footer, alerts (layout)
│   ├── auth/                 # login
│   ├── dashboard/            # admin, doctor, patient
│   ├── users/                # index, form
│   ├── doctors/              # index, form, specializations
│   ├── appointments/         # index, book, view
│   ├── prescriptions/        # index, form
│   ├── reports/              # index, print (printable PDF)
│   ├── logs/                 # index (new — activity log)
│   └── errors/               # 403, 404, 500
│
├── migrations/
│   ├── 2026_05_31_fix_admin_password.sql
│   └── 2026_06_04_add_activity_logs.sql   # (new)
│
├── public/uploads/           # avatars / doctor_photos / prescriptions
│                             # (each has a .htaccess blocking script execution)
│
├── README_FINAL.md           # this file
├── CHANGELOG.md
└── SECURITY_AUDIT.md
```

### Routing
Requests are dispatched through `index.php` using `?page=<slug>&action=<action>`.
Routes are an explicit whitelist; unknown pages fall through to a 404.
Examples: `?page=dashboard`, `?page=users&action=create`,
`?page=appointments&action=view&id=5`, `?page=reports&action=print`,
`?page=logs`.

---

## 5. Security features

A summary (full review in `SECURITY_AUDIT.md`):

- **SQL injection:** all queries are `mysqli` prepared statements with bound
  parameters — no user input is concatenated into SQL.
- **XSS:** output escaped via the `e()` helper
  (`htmlspecialchars`, `ENT_QUOTES`).
- **CSRF:** rotating token on every state-changing form, validated with
  `hash_equals` on every POST.
- **Passwords:** bcrypt, cost 12.
- **Sessions:** HttpOnly, `SameSite=Strict`, strict mode, ID regenerated on
  login, fully destroyed on logout; disabled accounts cannot log in.
- **File uploads:** real type validation (`getimagesize` for images, `finfo`
  MIME for PDFs), size limits, random filenames, and per-directory
  `.htaccess` that disables script execution.
- **CSV injection:** export cells beginning with `= + - @` are neutralised.
- **Headers / server:** `.htaccess` sets `X-Frame-Options`,
  `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`, disables
  indexes, and blocks direct access to source directories.
- **Audit trail:** logins, failed logins and all writes are recorded with
  source IP (admin-reviewable).

---

## 6. Export & reporting

- **CSV export** (`Reports → Export CSV`): UTF-8 with BOM (Excel-friendly),
  formula-injection-safe, respects the active filters.
- **PDF export** (`Reports → Export PDF`): opens a clean, print-optimised
  report (`views/reports/print.php`) with a branded header, generated
  timestamp, summary, paginated table and footer. Use the browser's
  **Print → Save as PDF**. This needs no server-side library and works
  immediately.
- **Reporting filters:** date range, doctor and status, with live summary
  tiles for total / pending / confirmed / completed / cancelled.
- **Dashboards:** admin (weekly bar + status doughnut + recent activity),
  doctor (monthly trend line), patient (status doughnut).

---

## 7. Notes on third-party libraries (CDN vs offline)

Front-end libraries (AdminLTE 3.2, Bootstrap 4.6.2, jQuery 3.7.1, Font
Awesome 6.5, Chart.js 4.4.1, DataTables 1.13.7) are loaded from CDN. This
keeps the repository small and the app runs immediately on a machine with
internet.

**To run fully offline**, download those libraries into
`public/assets/` (e.g. `public/assets/adminlte/css|js`, `…/chartjs`, etc.)
and repoint the `<link>`/`<script>` tags in `views/partials/header.php`,
`views/partials/footer.php`, `views/auth/login.php` and the three
`views/errors/*.php` pages to the local paths. The directory layout the code
originally expected was `public/assets/adminlte/{css,js}/…`.

**Server-side PDF (optional).** PDF export is print-based by default. To
generate PDFs on the server, install DomPDF:
```bash
composer require dompdf/dompdf
```
then in `ReportController::printable()` render the same `$appointments` /
`$summary` data into a `Dompdf` instance instead of requiring the view
(`$dompdf->loadHtml(...); $dompdf->render(); $dompdf->stream(...)`). No other
controller changes are required.

---

## 8. Verification status

The 1.1.0 changes were validated with a static structural check (bracket,
string, comment and heredoc balance) across all PHP files. The build
environment had no PHP/MySQL runtime, so run the smoke test in §3 after
importing the database to confirm end-to-end behaviour in your environment.
