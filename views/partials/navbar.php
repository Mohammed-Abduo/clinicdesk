<?php $user = Auth::currentUser(); ?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= BASE_URL ?>/?page=dashboard" class="nav-link">Home</a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <!-- User Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-user-circle fa-lg"></i>
        <span class="d-none d-md-inline ml-1"><?= e($user['name']) ?></span>
        <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'doctor' ? 'primary' : 'success') ?> ml-1">
          <?= e(ucfirst($user['role'])) ?>
        </span>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <span class="dropdown-item-text text-muted small"><?= e($user['email']) ?></span>
        <div class="dropdown-divider"></div>

        <!-- Logout via POST -->
        <form method="POST" action="<?= BASE_URL ?>/?page=logout" class="d-inline">
          <?= CSRF::input() ?>
          <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </li>
  </ul>
</nav>
<!-- /.navbar -->
