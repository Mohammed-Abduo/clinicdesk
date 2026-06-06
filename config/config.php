<?php
define('APP_NAME','ClinicDesk');
define('APP_VERSION','1.0.0');

$scriptDir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));

define('BASE_PATH', dirname(__DIR__));

define('UPLOAD_MAX_IMAGE', 1 * 1024 * 1024);
define('UPLOAD_MAX_PDF', 3 * 1024 * 1024);

define('UPLOAD_AVATAR', BASE_PATH . '/public/uploads/avatars/');
define('UPLOAD_DOCTOR', BASE_PATH . '/public/uploads/doctor_photos/');
define('UPLOAD_RX', BASE_PATH . '/public/uploads/prescriptions/');

define('SESSION_NAME', 'CLINICDESK_SID');
define('PER_PAGE', 15);

date_default_timezone_set('Asia/Jerusalem');

define('APP_DEBUG', false);
