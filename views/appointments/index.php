<?php $pageTitle = 'Appointments'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Appointments</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Appointments</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <!-- Filters -->
      <div class="card card-outline card-secondary mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filters</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="page" value="appointments">

            <select name="status" class="form-control form-control-sm mr-2 mb-2">
              <option value="">All Statuses</option>
              <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= ucfirst($s) ?>
              </option>
              <?php endforeach; ?>
            </select>

            <?php if (Auth::role() === 'admin'): ?>
            <select name="doctor_id" class="form-control form-control-sm mr-2 mb-2">
              <option value="">All Doctors</option>
              <?php foreach ($doctors as $d): ?>
              <option value="<?= $d['id'] ?>"
                <?= ($_GET['doctor_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                Dr. <?= e($d['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="patient_name" class="form-control form-control-sm mr-2 mb-2"
                   value="<?= e($_GET['patient_name'] ?? '') ?>" placeholder="Patient name">
            <?php endif; ?>

            <input type="date" name="start_date" class="form-control form-control-sm mr-2 mb-2"
                   value="<?= e($_GET['start_date'] ?? '') ?>" placeholder="From">
            <input type="date" name="end_date" class="form-control form-control-sm mr-2 mb-2"
                   value="<?= e($_GET['end_date'] ?? '') ?>" placeholder="To">

            <button type="submit" class="btn btn-sm btn-primary mb-2 mr-2">
              <i class="fas fa-search"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-sm btn-outline-secondary mb-2">
              Reset
            </a>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <div class="card-tools">
            <?php if (Auth::role() === 'patient'): ?>
            <a href="<?= BASE_URL ?>/?page=appointments&action=book" class="btn btn-sm btn-success">
              <i class="fas fa-plus mr-1"></i> Book Appointment
            </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="card-body p-0">
          <table class="table table-hover table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <?php if (Auth::role() !== 'patient'): ?><th>Patient</th><?php endif; ?>
                <?php if (Auth::role() !== 'doctor'):  ?><th>Doctor</th><?php endif; ?>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <?php if (Auth::role() !== 'patient'): ?>
                  <td><?= e($a['patient_name']) ?></td>
                <?php endif; ?>
                <?php if (Auth::role() !== 'doctor'): ?>
                  <td>Dr. <?= e($a['doctor_name']) ?></td>
                <?php endif; ?>
                <td><?= e($a['specialization']) ?></td>
                <td><?= fmtDate($a['appt_date']) ?></td>
                <td><?= fmtTime($a['appt_time']) ?></td>
                <td><?= statusBadge($a['status']) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $a['id'] ?>"
                     class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>

                  <?php if (Auth::role() === 'patient' && $a['status'] === 'pending'): ?>
                  <form method="POST" action="<?= BASE_URL ?>/?page=appointments&action=cancel"
                        class="d-inline" onsubmit="return confirm('Cancel this appointment?')">
                    <?= CSRF::input() ?>
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-xs btn-danger">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($appointments)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                  No appointments found.
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($pager->totalPages() > 1): ?>
        <div class="card-footer clearfix">
          <ul class="pagination pagination-sm m-0 float-right">
            <?php if ($pager->hasPrev()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildQuery(['p' => $pager->currentPage() - 1]) ?>">«</a>
              </li>
            <?php endif; ?>
            <?php foreach ($pager->pages() as $pg): ?>
              <li class="page-item <?= $pg === $pager->currentPage() ? 'active' : '' ?>">
                <a class="page-link" href="<?= buildQuery(['p' => $pg]) ?>"><?= $pg ?></a>
              </li>
            <?php endforeach; ?>
            <?php if ($pager->hasNext()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildQuery(['p' => $pager->currentPage() + 1]) ?>">»</a>
              </li>
            <?php endif; ?>
          </ul>
          <p class="text-muted small mt-2 mb-0">
            <?= count($appointments) ?> of <?= $pager->total() ?> appointments
          </p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
