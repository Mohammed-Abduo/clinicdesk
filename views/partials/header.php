<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'ClinicDesk') ?> | <?= APP_NAME ?></title>

  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- Font Awesome (local fallback or inline SVG icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    /* ClinicDesk brand tweaks */
    .brand-link { background: #1a2a3a; }
    .main-sidebar { background: #1e3248; }
    .nav-sidebar .nav-item > .nav-link.active,
    .nav-sidebar .nav-item > .nav-link:hover { background: rgba(255,255,255,.12); }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active { background: #007bff; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
