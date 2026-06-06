<?php $pageTitle = 'Prescriptions'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Prescriptions</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Prescriptions</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-prescription-bottle-alt mr-2"></i>
            <?= Auth::role() === 'patient' ? 'My Prescriptions' : 'Issued Prescriptions' ?>
          </h3>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0 data-table">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <?php if (Auth::role() !== 'patient'): ?><th>Patient</th><?php endif; ?>
                <?php if (Auth::role() !== 'doctor'):  ?><th>Doctor</th><?php endif; ?>
                <th>Appt. Date</th>
                <th>Notes Preview</th>
                <th>PDF</th>
                <th>Issued</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($prescriptions as $rx): ?>
              <tr>
                <td><?= $rx['id'] ?></td>
                <?php if (Auth::role() !== 'patient'): ?>
                  <td><?= e($rx['patient_name']) ?></td>
                <?php endif; ?>
                <?php if (Auth::role() !== 'doctor'): ?>
                  <td>Dr. <?= e($rx['doctor_name']) ?></td>
                <?php endif; ?>
                <td><?= fmtDate($rx['appt_date']) ?></td>
                <td>
                  <span title="<?= e($rx['notes']) ?>">
                    <?= e(mb_substr($rx['notes'], 0, 50)) ?><?= mb_strlen($rx['notes']) > 50 ? '…' : '' ?>
                  </span>
                </td>
                <td>
                  <?php if ($rx['pdf_file']): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Available</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">None</span>
                  <?php endif; ?>
                </td>
                <td><?= fmtDate($rx['created_at']) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $rx['appointment_id'] ?>"
                     class="btn btn-xs btn-info" title="View Appointment">
                    <i class="fas fa-calendar-alt"></i>
                  </a>
                  <?php if ($rx['pdf_file']): ?>
                  <a href="<?= BASE_URL ?>/?page=prescriptions&action=download&id=<?= $rx['id'] ?>"
                     class="btn btn-xs btn-success" title="Download PDF">
                    <i class="fas fa-file-pdf"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($prescriptions)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="fas fa-prescription-bottle fa-2x mb-2 d-block"></i>
                  No prescriptions found.
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
