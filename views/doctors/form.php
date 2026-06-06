<?php
$isEdit     = isset($doctor['id']);
$pageTitle  = $isEdit ? 'Edit Doctor' : 'Add Doctor';
$formAction = $isEdit
    ? BASE_URL . '/?page=doctors&action=edit&id=' . $doctor['id']
    : BASE_URL . '/?page=doctors&action=create';

$allDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$selDays = $isEdit ? explode(',', $old['available_days'] ?? $doctor['available_days'] ?? '') : ['Mon','Tue','Wed','Thu','Fri'];
?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><?= $pageTitle ?></h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=doctors">Doctors</a></li>
            <li class="breadcrumb-item active"><?= $pageTitle ?></li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><?= $pageTitle ?></h3></div>

            <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
              <?= CSRF::input() ?>
              <div class="card-body">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>

                <?php if (!$isEdit): ?>
                <!-- User selection (only on create) -->
                <div class="form-group">
                  <label>Link to User Account (Doctor role) <span class="text-danger">*</span></label>
                  <select name="user_id" class="form-control" required>
                    <option value="">-- Select User --</option>
                    <?php foreach ($doctorUsers as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($old['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                      <?= e($u['name']) ?> (<?= e($u['email']) ?>)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php endif; ?>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Specialization <span class="text-danger">*</span></label>
                    <select name="specialization_id" class="form-control" required>
                      <option value="">-- Select --</option>
                      <?php foreach ($specializations as $s): ?>
                      <option value="<?= $s['id'] ?>"
                        <?= ($old['specialization_id'] ?? $doctor['specialization_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['name']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Consultation Fee ($)</label>
                    <input type="number" name="consultation_fee" class="form-control"
                           min="0" step="0.01"
                           value="<?= e($old['consultation_fee'] ?? $doctor['consultation_fee'] ?? '0') ?>">
                  </div>
                </div>

                <!-- Available Days -->
                <div class="form-group">
                  <label>Available Days <span class="text-danger">*</span></label>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($allDays as $day): ?>
                    <div class="custom-control custom-checkbox custom-control-inline mr-3">
                      <input type="checkbox" class="custom-control-input"
                             id="day_<?= $day ?>" name="available_days[]" value="<?= $day ?>"
                             <?= in_array($day, $selDays) ? 'checked' : '' ?>>
                      <label class="custom-control-label" for="day_<?= $day ?>"><?= $day ?></label>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="form-group">
                  <label>Bio</label>
                  <textarea name="bio" class="form-control" rows="3"
                            placeholder="Brief professional bio..."><?= e($old['bio'] ?? $doctor['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label>Profile Photo (JPEG/PNG, max 1MB)</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="photo" name="photo"
                             accept="image/jpeg,image/png">
                      <label class="custom-file-label" for="photo">Choose photo...</label>
                    </div>
                  </div>
                  <?php if (!empty($doctor['photo'])): ?>
                    <small class="text-muted">Current:
                      <img src="<?= BASE_URL ?>/public/uploads/doctor_photos/<?= e($doctor['photo']) ?>"
                           width="40" height="40" class="img-circle ml-1" style="object-fit:cover">
                    </small>
                  <?php endif; ?>
                </div>

              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Update' : 'Create' ?>
                </button>
                <a href="<?= BASE_URL ?>/?page=doctors" class="btn btn-secondary ml-2">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
// Show filename in file input label
document.getElementById('photo').addEventListener('change', function(){
    var name = this.files[0]?.name || 'Choose photo...';
    this.nextElementSibling.innerText = name;
});
</script>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
