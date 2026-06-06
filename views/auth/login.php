<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ClinicDesk</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    body { background: #1e3248; }
    .login-box { margin-top: 8vh; }
    .card-header { background: #1a2a3a; }
  </style>
</head>
<body class="hold-transition">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="login-box">

        <!-- Logo -->
        <div class="text-center mb-4">
          <i class="fas fa-clinic-medical fa-4x text-info"></i>
          <h2 class="text-white mt-2 font-weight-bold">ClinicDesk</h2>
          <p class="text-light">Clinic Management System</p>
        </div>

        <div class="card elevation-4">
          <div class="card-header text-center">
            <h5 class="text-white mb-0"><i class="fas fa-lock mr-2"></i>Sign In</h5>
          </div>
          <div class="card-body p-4">

            <?php if ($error): ?>
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?= e($error) ?>
              </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/?page=login" autocomplete="off">
              <?= CSRF::input() ?>

              <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                  <input type="email" id="email" name="email" required autofocus
                         class="form-control"
                         placeholder="admin@clinicdesk.local"
                         value="<?= e($_POST['email'] ?? '') ?>">
                  <div class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-envelope text-muted"></i></div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                  <input type="password" id="password" name="password" required
                         class="form-control" placeholder="••••••••">
                  <div class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-lock text-muted"></i></div>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                <i class="fas fa-sign-in-alt mr-2"></i> Sign In
              </button>
            </form>

          </div><!-- /.card-body -->
        </div><!-- /.card -->

        <p class="text-center text-light mt-3 small">
          &copy; <?= date('Y') ?> ClinicDesk. All rights reserved.
        </p>

      </div><!-- /.login-box -->
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
