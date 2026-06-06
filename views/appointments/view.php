<?php $pageTitle = 'Appointment #' . $appt['id']; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Appointment #<?= $appt['id'] ?></h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=appointments">Appointments</a></li>
            <li class="breadcrumb-item active">#<?= $appt['id'] ?></li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <div class="row">
        <!-- Appointment Details -->
        <div class="col-md-7">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Appointment Details</h3>
              <div class="card-tools"><?= statusBadge($appt['status']) ?></div>
            </div>
            <div class="card-body">
              <table class="table table-borderless table-sm">
                <tr>
                  <th width="35%">Patient</th>
                  <td><?= e($appt['patient_name']) ?></td>
                </tr>
                <tr>
                  <th>Doctor</th>
                  <td>Dr. <?= e($appt['doctor_name']) ?></td>
                </tr>
                <tr>
                  <th>Specialization</th>
                  <td><?= e($appt['specialization']) ?></td>
                </tr>
                <tr>
                  <th>Date</th>
                  <td><?= fmtDate($appt['appt_date'], 'l, d F Y') ?></td>
                </tr>
                <tr>
                  <th>Time</th>
                  <td><?= fmtTime($appt['appt_time']) ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><?= statusBadge($appt['status']) ?></td>
                </tr>
                <tr>
                  <th>Reason</th>
                  <td><?= e($appt['reason'] ?? '—') ?></td>
                </tr>
                <?php if ($appt['notes']): ?>
                <tr>
                  <th>Doctor Notes</th>
                  <td><?= nl2br(e($appt['notes'])) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <th>Booked On</th>
                  <td><?= fmtDate($appt['created_at'], 'd M Y H:i') ?></td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Prescription card -->
          <?php if ($prescription): ?>
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-prescription mr-2"></i>Prescription</h3>
            </div>
            <div class="card-body">
              <p><?= nl2br(e($prescription['notes'])) ?></p>
              <?php if ($prescription['pdf_file']): ?>
              <a href="<?= BASE_URL ?>/?page=prescriptions&action=download&id=<?= $prescription['id'] ?>"
                 class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Actions Panel -->
        <div class="col-md-5">
          <!-- Doctor: status update -->
          <?php if (Auth::role() === 'doctor' && in_array($appt['status'], ['pending','confirmed','completed'])): ?>
          <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">Update Status</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/?page=appointments&action=update_status">
              <?= CSRF::input() ?>
              <input type="hidden" name="id" value="<?= $appt['id'] ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>New Status</label>
                  <select name="status" class="form-control" required>
                    <?php
                    $transitions = [
                        'pending'   => ['confirmed','cancelled'],
                        'confirmed' => ['completed','cancelled'],
                        'completed' => [],
                    ];
                    foreach ($transitions[$appt['status']] ?? [] as $s): ?>
                    <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Notes / Instructions</label>
                  <textarea name="notes" class="form-control" rows="3"
                            placeholder="Optional notes for patient..."><?= e($appt['notes'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-warning btn-sm">
                  <i class="fas fa-sync-alt mr-1"></i> Update Status
                </button>
              </div>
            </form>
          </div>

          <!-- Doctor: Add prescription after completing -->
          <?php if ($appt['status'] === 'completed' && !$prescription): ?>
          <div class="card card-success">
            <div class="card-body text-center">
              <a href="<?= BASE_URL ?>/?page=prescriptions&action=add&appt_id=<?= $appt['id'] ?>"
                 class="btn btn-success">
                <i class="fas fa-file-prescription mr-1"></i> Add Prescription
              </a>
            </div>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <!-- Admin: status update -->
          <?php if (Auth::role() === 'admin'): ?>
          <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Admin: Change Status</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/?page=appointments&action=update_status">
              <?= CSRF::input() ?>
              <input type="hidden" name="id" value="<?= $appt['id'] ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>Status</label>
                  <select name="status" class="form-control">
                    <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $appt['status'] === $s ? 'selected' : '' ?>>
                      <?= ucfirst($s) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Notes</label>
                  <textarea name="notes" class="form-control" rows="2"><?= e($appt['notes'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fas fa-save mr-1"></i> Save
                </button>
              </div>
            </form>
          </div>
          <?php endif; ?>

          <!-- Patient: cancel -->
          <?php if (Auth::role() === 'patient' && $appt['status'] === 'pending'): ?>
          <div class="card card-danger">
            <div class="card-body text-center">
              <form method="POST" action="<?= BASE_URL ?>/?page=appointments&action=cancel"
                    onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                <?= CSRF::input() ?>
                <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                <button type="submit" class="btn btn-danger">
                  <i class="fas fa-times-circle mr-1"></i> Cancel Appointment
                </button>
              </form>
            </div>
          </div>
          <?php endif; ?>

          <!-- Back -->
          <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-secondary btn-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
          </a>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
