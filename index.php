<?php
// ============================================================
// index.php  –  ClinicDesk Front Controller / Router
// ============================================================

declare(strict_types=1);

// ---- Bootstrap --------------------------------------------------

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/helpers.php';

// Secure session
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly',  '1');
ini_set('session.cookie_samesite', 'Strict');
// Uncomment in production with HTTPS:
// ini_set('session.cookie_secure', '1');

session_name(SESSION_NAME);
session_start();

// Error display (disable in production)
if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ---- Global exception handler -----------------------------------
// Prevents uncaught exceptions (e.g. DB constraint violations from the
// strict mysqli reporting mode) from leaking stack traces / SQL to the
// client. In debug mode the trace is shown; otherwise a clean 500 page.
set_exception_handler(function (Throwable $e): void {
    http_response_code(500);

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<h1>Application Error</h1><pre>'
            . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8')
            . '</pre>';
        return;
    }

    error_log('[ClinicDesk] ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());

    $errorView = __DIR__ . '/views/errors/500.php';
    if (is_file($errorView)) {
        require $errorView;
    } else {
        echo 'An unexpected error occurred. Please try again later.';
    }
});

// ---- Autoload core, models, controllers -------------------------

$autoloadDirs = [
    __DIR__ . '/core/',
    __DIR__ . '/models/',
    __DIR__ . '/controllers/',
];

spl_autoload_register(function (string $class) use ($autoloadDirs): void {
    foreach ($autoloadDirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ---- Route ------------------------------------------------------

$page   = trim($_GET['page']   ?? 'dashboard');
$action = trim($_GET['action'] ?? 'index');

// Whitelist of allowed page slugs → [controller, method]
$routes = [
    // Auth
    'login'   => ['AuthController', 'login'],
    'logout'  => ['AuthController', 'logout'],

    // Dashboard
    'dashboard' => ['DashboardController', 'index'],

    // Users (admin)
    'users' => [
        'index'  => ['UserController', 'index'],
        'create' => ['UserController', 'create'],
        'edit'   => ['UserController', 'edit'],
        'toggle' => ['UserController', 'toggle'],
        'delete' => ['UserController', 'delete'],
    ],

    // Doctors (admin)
    'doctors' => [
        'index'           => ['DoctorController', 'index'],
        'create'          => ['DoctorController', 'create'],
        'edit'            => ['DoctorController', 'edit'],
        'delete'          => ['DoctorController', 'delete'],
        'specializations' => ['DoctorController', 'specializations'],
    ],

    // Appointments
    'appointments' => [
        'index'         => ['AppointmentController', 'index'],
        'book'          => ['AppointmentController', 'book'],
        'view'          => ['AppointmentController', 'view'],
        'cancel'        => ['AppointmentController', 'cancel'],
        'update_status' => ['AppointmentController', 'updateStatus'],
    ],

    // Prescriptions
    'prescriptions' => [
        'index'    => ['PrescriptionController', 'index'],
        'add'      => ['PrescriptionController', 'add'],
        'download' => ['PrescriptionController', 'download'],
    ],

    // Reports
    'reports' => [
        'index'  => ['ReportController', 'index'],
        'export' => ['ReportController', 'export'],
        'print'  => ['ReportController', 'printable'],
    ],

    // Activity logs (admin)
    'logs' => [
        'index' => ['ActivityLogController', 'index'],
    ],

    // NOTE: errors/403 and errors/404 are handled by the dedicated
    // fallback blocks below (they render views/errors/*.php directly).
];

// ---- Dispatch ---------------------------------------------------

// Single-method pages (login, logout, dashboard)
if (isset($routes[$page]) && isset($routes[$page][0])) {
    [$ctrlClass, $method] = $routes[$page];
    $ctrl = new $ctrlClass();
    $ctrl->$method();
    exit;
}

// Multi-action pages
if (isset($routes[$page]) && is_array($routes[$page])) {
    $actionMap = $routes[$page];
    $resolvedAction = $action !== '' && isset($actionMap[$action])
        ? $action
        : 'index';

    [$ctrlClass, $method] = $actionMap[$resolvedAction];
    $ctrl = new $ctrlClass();
    $ctrl->$method();
    exit;
}

// Error pages (errors/403, errors/404)
if ($page === 'errors/403') {
    http_response_code(403);
    require __DIR__ . '/views/errors/403.php';
    exit;
}

if ($page === 'errors/404') {
    http_response_code(404);
    require __DIR__ . '/views/errors/404.php';
    exit;
}

// Fallback → 404
http_response_code(404);
require __DIR__ . '/views/errors/404.php';
