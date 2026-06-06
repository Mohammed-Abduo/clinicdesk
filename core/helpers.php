<?php
// ============================================================
// core/helpers.php  –  Global utility functions
// ============================================================

// ---- Output / Security -----------------------------------------------

/**
 * Echo a value safely escaped for HTML context.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---- Routing / Redirects ---------------------------------------------

/**
 * Redirect to an internal URL and exit.
 */
function redirect(string $url): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
    exit;
}

/**
 * Redirect back with a flash message stored in session.
 */
function redirectWith(string $url, string $type, string $message): never
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    redirect($url);
}

// ---- Flash messages --------------------------------------------------

/**
 * Pop and return flash message array or null.
 */
function flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ---- File Uploads ----------------------------------------------------

/**
 * Upload an image (JPEG/PNG, max 1 MB) to $destDir.
 * Returns the new filename on success, throws RuntimeException on failure.
 */
function uploadImage(array $file, string $destDir): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error code: ' . $file['error']);
    }

    if ($file['size'] > UPLOAD_MAX_IMAGE) {
        throw new RuntimeException('Image exceeds 1 MB limit.');
    }

    // Validate it is a real image
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        throw new RuntimeException('Uploaded file is not a valid image.');
    }

    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    if (!in_array($info[2], $allowed, true)) {
        throw new RuntimeException('Only JPEG and PNG images are allowed.');
    }

    $ext      = ($info[2] === IMAGETYPE_PNG) ? 'png' : 'jpg';
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Failed to save uploaded image.');
    }

    return $filename;
}

/**
 * Upload a PDF (max 3 MB) to $destDir.
 * Returns the new filename on success, throws RuntimeException on failure.
 */
function uploadPdf(array $file, string $destDir): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error code: ' . $file['error']);
    }

    if ($file['size'] > UPLOAD_MAX_PDF) {
        throw new RuntimeException('PDF exceeds 3 MB limit.');
    }

    // Validate MIME using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if ($mime !== 'application/pdf') {
        throw new RuntimeException('Only PDF files are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.pdf';
    $dest     = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Failed to save uploaded PDF.');
    }

    return $filename;
}

// ---- Formatting helpers ----------------------------------------------

/**
 * Format a MySQL DATE or DATETIME string for display.
 */
function fmtDate(string $date, string $format = 'd M Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Format a MySQL TIME string (H:i:s) to h:i A.
 */
function fmtTime(string $time): string
{
    return date('h:i A', strtotime($time));
}

/**
 * Return a Bootstrap badge HTML string for appointment status.
 */
function statusBadge(string $status): string
{
    $map = [
        'pending'   => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge badge-' . $color . '">' . ucfirst(e($status)) . '</span>';
}

/**
 * Return a Bootstrap badge HTML for user role.
 */
function roleBadge(string $role): string
{
    $map = ['admin' => 'danger', 'doctor' => 'primary', 'patient' => 'success'];
    $color = $map[$role] ?? 'secondary';
    return '<span class="badge badge-' . $color . '">' . ucfirst(e($role)) . '</span>';
}

// ---- Misc ------------------------------------------------------------

/**
 * Sanitize a string for use as a filename.
 */
function safeFilename(string $name): string
{
    return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);
}

/**
 * Build a query string from current GET params merged with overrides.
 */
function buildQuery(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query($params);
}

// ---- Activity log ----------------------------------------------------

/**
 * Record an action in the audit trail.
 *
 * Resolves the acting user from the session automatically unless explicit
 * values are supplied (needed for logout, where the session is destroyed
 * first, and for failed logins, where there is no session user).
 *
 * Logging must never break the request, so all errors are swallowed and
 * written to the PHP error log instead.
 */
function logActivity(
    string $action,
    string $description = '',
    ?int $userId = null,
    ?string $userName = null
): void {
    try {
        if ($userId === null && class_exists('Auth') && Auth::check()) {
            $userId   = Auth::id();
            $userName = Auth::currentUser()['name'] ?? null;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        (new ActivityLogModel())->log($action, $description, $userId, $userName, $ip);
    } catch (Throwable $e) {
        error_log('[ClinicDesk] activity log failed: ' . $e->getMessage());
    }
}

/**
 * Human-friendly label + Bootstrap colour for an activity action key.
 * Returns ['label' => string, 'color' => string].
 */
function activityActionMeta(string $action): array
{
    $map = [
        'login'         => ['Login',              'success'],
        'logout'        => ['Logout',             'secondary'],
        'failed_login'  => ['Failed Login',       'danger'],
        'user_create'   => ['User Created',       'primary'],
        'user_update'   => ['User Updated',       'info'],
        'user_delete'   => ['User Deleted',       'danger'],
        'user_toggle'   => ['User Status Changed','warning'],
        'doctor_create' => ['Doctor Created',     'primary'],
        'doctor_update' => ['Doctor Updated',     'info'],
        'doctor_delete' => ['Doctor Deleted',     'danger'],
        'appt_create'   => ['Appointment Booked', 'primary'],
        'appt_update'   => ['Appointment Updated','info'],
        'appt_cancel'   => ['Appointment Cancelled','danger'],
        'rx_create'     => ['Prescription Added', 'success'],
    ];
    return [
        'label' => $map[$action][0] ?? ucwords(str_replace('_', ' ', $action)),
        'color' => $map[$action][1] ?? 'light',
    ];
}
