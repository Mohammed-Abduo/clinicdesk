<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>500 Server Error | ClinicDesk</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="hold-transition" style="background:#1e3248">
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
      <div class="error-page">
        <h2 class="headline text-danger display-1 font-weight-bold">500</h2>
        <div class="error-content">
          <h3 class="text-white"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Server Error</h3>
          <p class="text-light">Something went wrong on our end. The issue has been logged. Please try again later.</p>
          <a href="<?= BASE_URL ?>/?page=dashboard" class="btn btn-danger">
            <i class="fas fa-home mr-1"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
