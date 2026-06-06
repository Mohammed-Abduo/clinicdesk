<?php $pageTitle = 'Admin Dashboard'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">

  <!-- Page Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Admin Dashboard</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <!-- Stat Boxes -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= $roleCounts['admin'] ?></h3>
              <p>Admins</p>
            </div>
            <div class="icon"><i class="fas fa-user-shield"></i></div>
            <a href="<?= BASE_URL ?>/?page=users&role=admin" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary">
            <div class="inner">
              <h3><?= $roleCounts['doctor'] ?></h3>
              <p>Doctors</p>
            </div>
            <div class="icon"><i class="fas fa-user-md"></i></div>
            <a href="<?= BASE_URL ?>/?page=doctors" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $roleCounts['patient'] ?></h3>
              <p>Patients</p>
            </div>
            <div class="icon"><i class="fas fa-procedures"></i></div>
            <a href="<?= BASE_URL ?>/?page=users&role=patient" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $todayCount ?></h3>
              <p>Appointments Today</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <a href="<?= BASE_URL ?>/?page=appointments" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Weekly appointments grouped by status (last 7 days) -->
      <div class="card card-outline card-secondary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-layer-group mr-1"></i> This Week by Status (last 7 days)</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-7">
              <div class="row text-center">
                <div class="col">
                  <span class="badge badge-warning">Pending</span>
                  <h4 class="mt-1"><?= $weeklyByStatus['pending'] ?></h4>
                </div>
                <div class="col">
                  <span class="badge badge-info">Confirmed</span>
                  <h4 class="mt-1"><?= $weeklyByStatus['confirmed'] ?></h4>
                </div>
                <div class="col">
                  <span class="badge badge-success">Completed</span>
                  <h4 class="mt-1"><?= $weeklyByStatus['completed'] ?></h4>
                </div>
                <div class="col">
                  <span class="badge badge-danger">Cancelled</span>
                  <h4 class="mt-1"><?= $weeklyByStatus['cancelled'] ?></h4>
                </div>
              </div>
            </div>
            <div class="col-md-5">
              <canvas id="statusChart" height="160"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Weekly Chart -->
        <div class="col-md-7">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Weekly Appointments</h3>
            </div>
            <div class="card-body">
              <canvas id="weeklyChart" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-5">
          <div class="card card-info">
            <div class="card-header"><h3 class="card-title">Quick Actions</h3></div>
            <div class="card-body">
              <a href="<?= BASE_URL ?>/?page=users&action=create" class="btn btn-outline-primary btn-block mb-2">
                <i class="fas fa-user-plus mr-2"></i>Add New User
              </a>
              <a href="<?= BASE_URL ?>/?page=doctors&action=create" class="btn btn-outline-info btn-block mb-2">
                <i class="fas fa-user-md mr-2"></i>Add New Doctor
              </a>
              <a href="<?= BASE_URL ?>/?page=reports" class="btn btn-outline-success btn-block mb-2">
                <i class="fas fa-chart-bar mr-2"></i>Generate Report
              </a>
              <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-outline-warning btn-block">
                <i class="fas fa-calendar-check mr-2"></i>View Appointments
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Appointments -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Recent Appointments</h3>
          <div class="card-tools">
            <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-sm btn-primary">View All</a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentAppts as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td>Dr. <?= e($a['doctor_name']) ?></td>
                <td><?= fmtDate($a['appt_date']) ?> <?= fmtTime($a['appt_time']) ?></td>
                <td><?= statusBadge($a['status']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recentAppts)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No appointments yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Activity</h3>
          <div class="card-tools">
            <a href="<?= BASE_URL ?>/?page=logs" class="btn btn-sm btn-info">View All</a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover table-sm mb-0">
            <thead class="thead-light">
              <tr><th>When</th><th>User</th><th>Action</th><th>Detail</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentLogs as $log): ?>
              <?php $meta = activityActionMeta($log['action']); ?>
              <tr>
                <td class="text-nowrap"><?= fmtDate($log['created_at'], 'd M H:i') ?></td>
                <td><?= $log['user_name'] ? e($log['user_name']) : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge badge-<?= e($meta['color']) ?>"><?= e($meta['label']) ?></span></td>
                <td><?= e($log['description'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recentLogs)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No activity recorded yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php
// Build chart data from weeklyStats
$labels = [];
$counts = [];
$map    = [];
foreach ($weeklyStats as $s) { $map[$s['day']] = $s['cnt']; }
for ($i = 6; $i >= 0; $i--) {
    $d       = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D d/m', strtotime($d));
    $counts[] = $map[$d] ?? 0;
}
$labelsJson = json_encode($labels);
$countsJson = json_encode($counts);
$statusJson = json_encode(array_values($weeklyByStatus));

$extraJs = <<<JS
<script>
var ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: $labelsJson,
    datasets: [{
      label: 'Appointments',
      data: $countsJson,
      backgroundColor: 'rgba(0,123,255,0.6)',
      borderColor: '#007bff',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});

// Status distribution (last 7 days) doughnut
var sctx = document.getElementById('statusChart');
if (sctx) {
  new Chart(sctx.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
      datasets: [{
        data: $statusJson,
        backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
}
</script>
JS;
?>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
