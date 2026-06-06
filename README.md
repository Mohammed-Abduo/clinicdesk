# ClinicDesk — Clinic Management Dashboard

A production-grade, secure, private clinic management system built with **PHP Native (OOP)**, **MySQL**, and **AdminLTE 3**.

---

## Tech Stack

| Layer        | Technology                  |
|--------------|-----------------------------|
| Backend      | PHP 8.1+ (OOP, no framework)|
| Database     | MySQL 8+ / MariaDB 10.6+   |
| DB Driver    | `mysqli` + Prepared Statements |
| Frontend     | AdminLTE 3, Bootstrap 4     |
| Auth         | Session-based, RBAC         |
| Security     | CSRF, XSS, bcrypt, finfo    |

---

## System Roles

| Role    | Capabilities |
|---------|-------------|
| Admin   | Manage users, doctors, specializations, view all appointments, reports, CSV export |
| Doctor  | View own appointments, confirm/complete/cancel, add prescriptions |
| Patient | Book appointments, view history, cancel pending, download prescriptions |

---

## Requirements

- PHP >= 8.1 with extensions: `mysqli`, `fileinfo`, `mbstring`, `session`
- Apache with `mod_rewrite` enabled
- MySQL 8+ or MariaDB 10.6+
- AdminLTE 3 local files (see below)

---

## Installation

### 1. Clone / Copy Project

```bash
cp -r clinicdesk/ /var/www/html/clinicdesk
```

### 2. Database Setup

```bash
mysql -u root -p < schema.sql
```

Or manually in phpMyAdmin: import `schema.sql`.

### 3. Configure Database Credentials

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinicdesk_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 4. Configure App URL

Edit `config/config.php`:

```php
define('BASE_URL', 'http://localhost/clinicdesk');
```

### 5. Download AdminLTE 3

Download from https://adminlte.io and place files at:

```
public/assets/adminlte/
├── css/
│   ├── adminlte.min.css
│   └── all.min.css          ← Font Awesome 5 (local)
├── js/
│   ├── jquery.min.js
│   ├── bootstrap.bundle.min.js
│   ├── adminlte.min.js
│   ├── datatables.min.js    ← DataTables plugin
│   └── chart.min.js         ← Chart.js
```

### 6. Set Upload Directory Permissions

```bash
chmod 775 public/uploads/avatars/
chmod 775 public/uploads/doctor_photos/
chmod 775 public/uploads/prescriptions/
chown www-data:www-data public/uploads/ -R
```

### 7. Enable Apache mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Make sure your Apache VirtualHost has:

```apache
<Directory /var/www/html/clinicdesk>
    AllowOverride All
</Directory>
```

---

## Default Login

| Field    | Value                   |
|----------|-------------------------|
| URL      | http://localhost/clinicdesk |
| Email    | admin@clinicdesk.local  |
| Password | Admin@1234              |

> **Important:** Change the admin password immediately after first login.

---

## Project Structure

```
clinicdesk/
├── index.php               ← Front controller / router
├── .htaccess               ← URL rewriting + security
├── schema.sql              ← Full database schema + seed
├── README.md
│
├── config/
│   ├── config.php          ← App constants (URL, paths, limits)
│   └── database.php        ← DB credentials
│
├── core/
│   ├── Database.php        ← Singleton mysqli wrapper
│   ├── Auth.php            ← Session auth + RBAC guards
│   ├── CSRF.php            ← CSRF token generation/validation
│   ├── Paginator.php       ← Pagination helper
│   └── helpers.php         ← Global utility functions
│
├── models/
│   ├── BaseModel.php
│   ├── UserModel.php
│   ├── DoctorModel.php
│   ├── AppointmentModel.php
│   ├── PrescriptionModel.php
│   └── SpecializationModel.php
│
├── controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UserController.php
│   ├── DoctorController.php
│   ├── AppointmentController.php
│   ├── PrescriptionController.php
│   └── ReportController.php
│
├── views/
│   ├── partials/           ← header, navbar, sidebar, footer, alerts
│   ├── auth/               ← login.php
│   ├── dashboard/          ← admin.php, doctor.php, patient.php
│   ├── users/              ← index.php, form.php
│   ├── doctors/            ← index.php, form.php, specializations.php
│   ├── appointments/       ← index.php, book.php, view.php
│   ├── prescriptions/      ← index.php, form.php
│   ├── reports/            ← index.php
│   └── errors/             ← 403.php, 404.php
│
└── public/
    ├── assets/adminlte/    ← AdminLTE CSS/JS (add manually)
    └── uploads/
        ├── avatars/
        ├── doctor_photos/
        └── prescriptions/  ← Protected by .htaccess
```

---

## Security Features

| Feature               | Implementation |
|-----------------------|----------------|
| Prepared Statements   | All queries via `Database::query()` with `bind_param` |
| CSRF Protection       | `CSRF::input()` in every form, `CSRF::validate()` on every POST |
| XSS Prevention        | `e()` helper (`htmlspecialchars`) on all output |
| Password Hashing      | `password_hash(PASSWORD_BCRYPT, cost=12)` |
| Session Hardening     | `session_regenerate_id(true)` on login, `httponly`, `samesite=Strict` |
| Role-based Access     | `Auth::requireRole()` guard at controller level |
| Ownership Checks      | Patient/doctor can only access their own records |
| Secure File Uploads   | `getimagesize()` for images, `finfo_file()` for PDFs |
| Protected Downloads   | Prescriptions served via PHP only, `.htaccess` blocks direct access |
| Logout via POST       | Prevents CSRF-based forced logout |

---

## Appointment Statuses

```
pending → confirmed → completed
         ↓                ↓
       cancelled       (can add prescription)
```

---

## CSV Export

Go to **Reports** → apply filters → click **Export CSV**.  
The export uses `fputcsv()` with UTF-8 BOM for Excel compatibility.

---

## Production Checklist

- [ ] Set `APP_DEBUG = false` in `config/config.php`
- [ ] Use HTTPS and set `session.cookie_secure = 1`
- [ ] Change default admin password
- [ ] Set strong DB credentials in `config/database.php`
- [ ] Keep `public/uploads/prescriptions/` blocked
- [ ] Set proper file permissions on uploads
- [ ] Enable error logging to file instead of display

---

## License

MIT — For educational and private use.
