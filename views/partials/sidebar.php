<?php
$role       = Auth::role();
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- Brand Logo -->
  <a href="<?= BASE_URL ?>/?page=dashboard" class="brand-link text-center">
    <span class="brand-text font-weight-bold" style="font-size:1.3rem;">
      <i class="fas fa-clinic-medical mr-1 text-info"></i> ClinicDesk
    </span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- User Panel -->
    <?php $u = Auth::currentUser(); ?>
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <i class="fas fa-user-md fa-2x text-light ml-2 mt-1"></i>
      </div>
      <div class="info">
        <a href="#" class="d-block text-white"><?= e($u['name']) ?></a>
        <small class="text-light opacity-75"><?= ucfirst(e($role)) ?></small>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <!-- Dashboard -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=dashboard"
             class="nav-link <?= str_starts_with($currentPage, 'dashboard') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <?php if ($role === 'admin'): ?>

        <!-- Users -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=users"
             class="nav-link <?= str_starts_with($currentPage, 'users') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Users</p>
          </a>
        </li>

        <!-- Doctors -->
        <li class="nav-item <?= str_starts_with($currentPage, 'doctors') ? 'menu-open' : '' ?>">
          <a href="<?= BASE_URL ?>/?page=doctors"
             class="nav-link <?= str_starts_with($currentPage, 'doctors') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>
              Doctors
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/?page=doctors" class="nav-link">
                <i class="far fa-circle nav-icon"></i><p>All Doctors</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/?page=doctors&action=create" class="nav-link">
                <i class="far fa-plus-square nav-icon"></i><p>Add Doctor</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/?page=doctors&action=specializations" class="nav-link">
                <i class="far fa-list-alt nav-icon"></i><p>Specializations</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Appointments (Admin) -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=appointments"
             class="nav-link <?= str_starts_with($currentPage, 'appointments') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>Appointments</p>
          </a>
        </li>

        <!-- Reports -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=reports"
             class="nav-link <?= str_starts_with($currentPage, 'reports') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
          </a>
        </li>

        <!-- Activity Logs -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=logs"
             class="nav-link <?= str_starts_with($currentPage, 'logs') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-history"></i>
            <p>Activity Logs</p>
          </a>
        </li>

        <?php elseif ($role === 'doctor'): ?>

        <!-- Doctor nav -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=appointments"
             class="nav-link <?= str_starts_with($currentPage, 'appointments') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>My Appointments</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=prescriptions"
             class="nav-link <?= str_starts_with($currentPage, 'prescriptions') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-file-prescription"></i>
            <p>Prescriptions</p>
          </a>
        </li>

        <?php else: ?>

        <!-- Patient nav -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=appointments&action=book"
             class="nav-link <?= ($currentPage === 'appointments' && ($_GET['action'] ?? '') === 'book') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-plus-circle"></i>
            <p>Book Appointment</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=appointments"
             class="nav-link <?= ($currentPage === 'appointments' && ($_GET['action'] ?? '') !== 'book') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>My Appointments</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/?page=prescriptions"
             class="nav-link <?= str_starts_with($currentPage, 'prescriptions') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-prescription-bottle-alt"></i>
            <p>My Prescriptions</p>
          </a>
        </li>

        <?php endif; ?>

      </ul>
    </nav>
  </div><!-- /.sidebar -->
</aside>
