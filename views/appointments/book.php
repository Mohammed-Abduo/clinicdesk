<?php $pageTitle = 'Book Appointment'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Book New Appointment</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=appointments">Appointments</a></li>
            <li class="breadcrumb-item active">Book</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-7">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>Appointment Details</h3>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/?page=appointments&action=book">
              <?= CSRF::input() ?>
              <div class="card-body">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>

                <!-- Doctor selection with info panel -->
                <div class="form-group">
                  <label>Select Doctor <span class="text-danger">*</span></label>
                  <select name="doctor_id" id="doctorSelect" class="form-control" required>
                    <option value="">-- Choose a Doctor --</option>
                    <?php foreach ($doctors as $d): ?>
                    <option value="<?= $d['id'] ?>"
                            data-days="<?= e($d['available_days']) ?>"
                            data-fee="<?= e(number_format($d['consultation_fee'], 2)) ?>"
                            data-spec="<?= e($d['specialization_name']) ?>"
                            <?= ($old['doctor_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                      Dr. <?= e($d['name']) ?> — <?= e($d['specialization_name']) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Doctor info box (dynamic) -->
                <div id="doctorInfo" class="callout callout-info d-none mb-3">
                  <strong>Specialization:</strong> <span id="infoSpec"></span><br>
                  <strong>Available Days:</strong> <span id="infoDays"></span><br>
                  <strong>Consultation Fee:</strong> $<span id="infoFee"></span>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Appointment Date <span class="text-danger">*</span></label>
                    <input type="date" name="appt_date" id="apptDate" class="form-control" required
                           min="<?= date('Y-m-d') ?>"
                           value="<?= e($old['appt_date'] ?? '') ?>">
                    <small id="dayWarning" class="text-danger d-none">
                      Doctor not available on this day.
                    </small>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Preferred Time <span class="text-danger">*</span></label>
                    <select name="appt_time" class="form-control" required>
                      <option value="">-- Select Time --</option>
                      <?php
                      $slots = ['08:00','08:30','09:00','09:30','10:00','10:30',
                                '11:00','11:30','12:00','12:30','13:00','13:30',
                                '14:00','14:30','15:00','15:30','16:00','16:30',
                                '17:00','17:30'];
                      foreach ($slots as $t):
                          $sel = ($old['appt_time'] ?? '') === $t ? 'selected' : '';
                      ?>
                      <option value="<?= $t ?>" <?= $sel ?>><?= date('h:i A', strtotime($t)) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label>Reason / Chief Complaint</label>
                  <textarea name="reason" class="form-control" rows="3"
                            placeholder="Briefly describe your symptoms or reason for visit..."><?= e($old['reason'] ?? '') ?></textarea>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-calendar-check mr-1"></i> Confirm Booking
                </button>
                <a href="<?= BASE_URL ?>/?page=appointments" class="btn btn-secondary ml-2">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php
$extraJs = <<<'JS'
<script>
const dayMap = { 0:'Sun',1:'Mon',2:'Tue',3:'Wed',4:'Thu',5:'Fri',6:'Sat' };

function updateDoctorInfo() {
    const sel  = document.getElementById('doctorSelect');
    const opt  = sel.options[sel.selectedIndex];
    const info = document.getElementById('doctorInfo');

    if (!sel.value) { info.classList.add('d-none'); return; }

    document.getElementById('infoSpec').textContent = opt.dataset.spec;
    document.getElementById('infoDays').textContent = opt.dataset.days;
    document.getElementById('infoFee').textContent  = opt.dataset.fee;
    info.classList.remove('d-none');
    checkDay();
}

function checkDay() {
    const sel  = document.getElementById('doctorSelect');
    const opt  = sel.options[sel.selectedIndex];
    const date = document.getElementById('apptDate').value;
    const warn = document.getElementById('dayWarning');

    if (!sel.value || !date) { warn.classList.add('d-none'); return; }

    const d       = new Date(date + 'T00:00:00');
    const dayAbbr = dayMap[d.getDay()];
    const avail   = (opt.dataset.days || '').split(',');

    if (!avail.includes(dayAbbr)) {
        warn.classList.remove('d-none');
    } else {
        warn.classList.add('d-none');
    }
}

document.getElementById('doctorSelect').addEventListener('change', updateDoctorInfo);
document.getElementById('apptDate').addEventListener('change', checkDay);

// Init on page load (if re-shown after error)
updateDoctorInfo();
</script>
JS;
?>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
