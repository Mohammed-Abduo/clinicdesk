<?php $pageTitle = 'Doctor Dashboard'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Welcome, Dr. <?= e(Auth::currentUser()['name']) ?></h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <!-- Stats -->
      <div class="row">
        <div class="col-md-4">
          <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-calendar-day"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Today's Appointments</span>
              <span class="info-box-number"><?= count($todayAppts) ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Upcoming Confirmed</span>
              <span class="info-box-number"><?= count($upcomingAppts) ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-stethoscope"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Specialization</span>
              <span class="info-box-number" style="font-size:1rem"><?= e($doctor['specialization_name']) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Spec stat counts -->
      <div class="row">
        <div class="col-md-4">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pending</span>
              <span class="info-box-number"><?= $pendingCount ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Completed</span>
              <span class="info-box-number"><?= $completedCount ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-secondary">
            <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">This Month (Total)</span>
              <span class="info-box-number"><?= $monthlyTotal ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Today's Schedule -->
        <div class="col-md-7">
          <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Today's Schedule</h3></div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead class="thead-light">
                  <tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  <?php foreach ($todayAppts as $a): ?>
                  <tr>
                    <td><strong><?= fmtTime($a['appt_time']) ?></strong></td>
                    <td><?= e($a['patient_name']) ?></td>
                    <td><?= e(substr($a['reason'] ?? '', 0, 40)) ?></td>
                    <td><?= statusBadge($a['status']) ?></td>
                    <td>
                      <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $a['id'] ?>"
                         class="btn btn-xs btn-info">View</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($todayAppts)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No appointments today.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Monthly chart -->
        <div class="col-md-5">
          <div class="card card-info">
            <div class="card-header"><h3 class="card-title">This Month</h3></div>
            <div class="card-body">
              <canvas id="monthlyChart" height="170"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming -->
      <?php if (!empty($upcomingAppts)): ?>
      <div class="card">
        <div class="card-header"><h3 class="card-title">Upcoming Confirmed Appointments</h3></div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr><th>Date</th><th>Time</th><th>Patient</th><th>Reason</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($upcomingAppts as $a): ?>
              <tr>
                <td><?= fmtDate($a['appt_date']) ?></td>
                <td><?= fmtTime($a['appt_time']) ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e(substr($a['reason'] ?? '', 0, 40)) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $a['id'] ?>"
                     class="btn btn-xs btn-primary">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php
$labels = [];
$counts = [];
$map    = [];
foreach ($monthlyStats as $s) { $map[$s['day']] = (int) $s['cnt']; }
$days = (int) date('t');
for ($i = 1; $i <= $days; $i++) {
    $d       = date('Y-m-') . str_pad($i, 2, '0', STR_PAD_LEFT);
    $labels[] = $i;
    $counts[] = $map[$d] ?? 0;
}
$lj = json_encode($labels);
$cj = json_encode($counts);

$extraJs = <<<JS
<script>
var ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: $lj,
    datasets: [{
      label: 'Appointments',
      data: $cj,
      borderColor: '#17a2b8',
      backgroundColor: 'rgba(23,162,184,0.15)',
      fill: true,
      tension: 0.3
    }]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
JS;
?>
<?php require BASE_PATH . '/views/partials/footer.php'; ?>
