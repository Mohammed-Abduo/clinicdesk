<?php $pageTitle = 'Reports'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Appointment Reports</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Reports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <!-- Filter Card -->
      <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filters</h3></div>
        <div class="card-body">
          <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="page" value="reports">

            <div class="form-group mr-3 mb-2">
              <label class="mr-2">From:</label>
              <input type="date" name="start_date" class="form-control form-control-sm"
                     value="<?= e($filters['start_date']) ?>">
            </div>
            <div class="form-group mr-3 mb-2">
              <label class="mr-2">To:</label>
              <input type="date" name="end_date" class="form-control form-control-sm"
                     value="<?= e($filters['end_date']) ?>">
            </div>
            <div class="form-group mr-3 mb-2">
              <label class="mr-2">Doctor:</label>
              <select name="doctor_id" class="form-control form-control-sm">
                <option value="">All Doctors</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>"
                  <?= $filters['doctor_id'] == $d['id'] ? 'selected' : '' ?>>
                  Dr. <?= e($d['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mr-3 mb-2">
              <label class="mr-2">Status:</label>
              <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                  <?= ucfirst($s) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" class="btn btn-sm btn-primary mb-2 mr-2">
              <i class="fas fa-search"></i> Apply
            </button>
            <a href="<?= BASE_URL ?>/?page=reports" class="btn btn-sm btn-outline-secondary mb-2 mr-2">
              Reset
            </a>

            <!-- CSV Export -->
            <a href="<?= BASE_URL ?>/?page=reports&action=export&<?= http_build_query(array_filter($filters)) ?>"
               class="btn btn-sm btn-success mb-2 mr-2">
              <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>

            <!-- PDF Export (print-optimised; opens print dialog) -->
            <a href="<?= BASE_URL ?>/?page=reports&action=print&auto=1&<?= http_build_query(array_filter($filters)) ?>"
               target="_blank" rel="noopener"
               class="btn btn-sm btn-danger mb-2">
              <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
          </form>
        </div>
      </div>

      <!-- Summary Boxes -->
      <div class="row">
        <div class="col-6 col-md-2">
          <div class="small-box bg-secondary">
            <div class="inner"><h3><?= $summary['total'] ?></h3><p>Total</p></div>
            <div class="icon"><i class="fas fa-list"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="small-box bg-warning">
            <div class="inner"><h3><?= $summary['pending'] ?></h3><p>Pending</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="small-box bg-info">
            <div class="inner"><h3><?= $summary['confirmed'] ?></h3><p>Confirmed</p></div>
            <div class="icon"><i class="fas fa-check"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="small-box bg-success">
            <div class="inner"><h3><?= $summary['completed'] ?></h3><p>Completed</p></div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="small-box bg-danger">
            <div class="inner"><h3><?= $summary['cancelled'] ?></h3><p>Cancelled</p></div>
            <div class="icon"><i class="fas fa-times"></i></div>
          </div>
        </div>
      </div>

      <!-- Results Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Results
            <span class="badge badge-secondary ml-1"><?= $pager->total() ?></span>
          </h3>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td>Dr. <?= e($a['doctor_name']) ?></td>
                <td><?= e($a['specialization']) ?></td>
                <td><?= fmtDate($a['appt_date']) ?></td>
                <td><?= fmtTime($a['appt_time']) ?></td>
                <td><?= statusBadge($a['status']) ?></td>
                <td><?= e(mb_substr($a['reason'] ?? '', 0, 40)) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($appointments)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  No records match the selected filters.
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
            Page <?= $pager->currentPage() ?> of <?= $pager->totalPages() ?>
          </p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
