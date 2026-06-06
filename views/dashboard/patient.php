<?php $pageTitle = 'My Dashboard'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Hello, <?= e(Auth::currentUser()['name']) ?></h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <div class="row">
        <div class="col-md-4">
          <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Active Appointments</span>
              <span class="info-box-number"><?= count($activeAppts) ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-prescription-bottle-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Prescriptions</span>
              <span class="info-box-number"><?= $rxCount ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-plus-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Book New Appointment</span>
              <a href="<?= BASE_URL ?>/?page=appointments&action=book"
                 class="btn btn-light btn-sm mt-1">Book Now</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Completed + Next appointment -->
      <div class="row">
        <div class="col-md-4">
          <div class="info-box bg-secondary">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Completed Appointments</span>
              <span class="info-box-number"><?= $completedCount ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="info-box bg-light">
            <span class="info-box-icon bg-info"><i class="fas fa-calendar-day"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Next Appointment</span>
              <?php if ($nextAppt): ?>
              <span class="info-box-number" style="font-size:1rem">
                <?= fmtDate($nextAppt['appt_date']) ?> at <?= fmtTime($nextAppt['appt_time']) ?>
                — Dr. <?= e($nextAppt['doctor_name']) ?>
                <?= statusBadge($nextAppt['status']) ?>
              </span>
              <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $nextAppt['id'] ?>"
                 class="btn btn-xs btn-info mt-1">View</a>
              <?php else: ?>
              <span class="info-box-number" style="font-size:1rem">No upcoming appointments</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- My appointments overview chart -->
      <div class="row">
        <div class="col-md-5">
          <div class="card card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i>My Appointments</h3></div>
            <div class="card-body">
              <?php if (array_sum($statusCounts) > 0): ?>
                <canvas id="patientStatusChart" height="180"></canvas>
              <?php else: ?>
                <p class="text-muted text-center mb-0 py-4">No appointment data to chart yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="card card-secondary">
            <div class="card-header"><h3 class="card-title">Status Breakdown</h3></div>
            <div class="card-body">
              <div class="row text-center">
                <div class="col"><span class="badge badge-warning">Pending</span><h4 class="mt-1"><?= $statusCounts['pending'] ?></h4></div>
                <div class="col"><span class="badge badge-info">Confirmed</span><h4 class="mt-1"><?= $statusCounts['confirmed'] ?></h4></div>
                <div class="col"><span class="badge badge-success">Completed</span><h4 class="mt-1"><?= $statusCounts['completed'] ?></h4></div>
                <div class="col"><span class="badge badge-danger">Cancelled</span><h4 class="mt-1"><?= $statusCounts['cancelled'] ?></h4></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming confirmed -->
      <?php if (!empty($activeAppts)): ?>
      <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Upcoming Confirmed Appointments</h3></div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <thead class="thead-light">
              <tr><th>Date</th><th>Time</th><th>Doctor</th><th>Specialization</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($activeAppts as $a): ?>
              <tr>
                <td><?= fmtDate($a['appt_date']) ?></td>
                <td><?= fmtTime($a['appt_time']) ?></td>
                <td>Dr. <?= e($a['doctor_name']) ?></td>
                <td><?= e($a['specialization']) ?></td>
                <td><?= statusBadge($a['status']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Recent -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Recent Appointments</h3>
          <div class="card-tools">
            <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-sm btn-primary">View All</a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr><th>Date</th><th>Doctor</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentAppts as $a): ?>
              <tr>
                <td><?= fmtDate($a['appt_date']) ?></td>
                <td>Dr. <?= e($a['doctor_name']) ?></td>
                <td><?= statusBadge($a['status']) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $a['id'] ?>"
                     class="btn btn-xs btn-info">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recentAppts)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No appointments yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php
$patientStatusJson = json_encode(array_values($statusCounts));
$extraJs = <<<JS
<script>
var pctx = document.getElementById('patientStatusChart');
if (pctx) {
  new Chart(pctx.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
      datasets: [{
        data: $patientStatusJson,
        backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
      }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
}
</script>
JS;
?>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
