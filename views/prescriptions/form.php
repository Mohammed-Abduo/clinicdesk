<?php $pageTitle = 'Add Prescription'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Add Prescription</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $appt['id'] ?>">
                Appointment #<?= $appt['id'] ?>
              </a>
            </li>
            <li class="breadcrumb-item active">Add Prescription</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">

          <!-- Appointment info -->
          <div class="callout callout-info mb-3">
            <strong>Patient:</strong> <?= e($appt['patient_name']) ?> &nbsp;|&nbsp;
            <strong>Date:</strong> <?= fmtDate($appt['appt_date']) ?> <?= fmtTime($appt['appt_time']) ?>
          </div>

          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-file-prescription mr-2"></i>Prescription</h3>
            </div>

            <form method="POST"
                  action="<?= BASE_URL ?>/?page=prescriptions&action=add&appt_id=<?= $appt['id'] ?>"
                  enctype="multipart/form-data">
              <?= CSRF::input() ?>
              <div class="card-body">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>

                <div class="form-group">
                  <label>Prescription Notes / Medications <span class="text-danger">*</span></label>
                  <textarea name="notes" class="form-control" rows="6" required
                            placeholder="e.g.&#10;1. Amoxicillin 500mg – 3x daily for 7 days&#10;2. Paracetamol 500mg – as needed for pain&#10;&#10;Rest advised. Follow up in 1 week."><?= e($old['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label>Upload Prescription PDF (optional, max 3 MB)</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="pdf_file"
                             name="pdf_file" accept="application/pdf">
                      <label class="custom-file-label" for="pdf_file">
                        Choose PDF file...
                      </label>
                    </div>
                  </div>
                  <small class="text-muted">Only PDF files are accepted. Max size: 3 MB.</small>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> Save Prescription
                </button>
                <a href="<?= BASE_URL ?>/?page=appointments&action=view&id=<?= $appt['id'] ?>"
                   class="btn btn-secondary ml-2">Cancel</a>
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
document.getElementById('pdf_file').addEventListener('change', function(){
    var name = this.files[0]?.name || 'Choose PDF file...';
    this.nextElementSibling.innerText = name;
});
</script>
JS;
?>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
