<?php $pageTitle = 'Specializations'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Specializations</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <div class="row">
        <!-- Add form -->
        <div class="col-md-4">
          <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Add Specialization</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/?page=doctors&action=specializations">
              <?= CSRF::input() ?>
              <input type="hidden" name="action" value="create">
              <div class="card-body">
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger py-1">
                    <?= e($errors[0]) ?>
                  </div>
                <?php endif; ?>
                <div class="form-group mb-0">
                  <label>Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" required
                         placeholder="e.g. Cardiology">
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i> Add
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- List -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header"><h3 class="card-title">All Specializations</h3></div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead class="thead-light">
                  <tr><th>#</th><th>Name</th><th>Doctors</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($specs as $s): ?>
                  <?php $inUse = (int) ($s['doctor_count'] ?? 0) > 0; ?>
                  <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= e($s['name']) ?></td>
                    <td>
                      <span class="badge badge-<?= $inUse ? 'info' : 'secondary' ?>">
                        <?= (int) ($s['doctor_count'] ?? 0) ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($inUse): ?>
                        <button type="button" class="btn btn-xs btn-secondary" disabled
                                title="In use by <?= (int) $s['doctor_count'] ?> doctor(s) – cannot delete">
                          <i class="fas fa-lock"></i>
                        </button>
                      <?php else: ?>
                      <form method="POST"
                            action="<?= BASE_URL ?>/?page=doctors&action=specializations"
                            class="d-inline"
                            onsubmit="return confirm('Delete this specialization?')">
                        <?= CSRF::input() ?>
                        <input type="hidden" name="action"  value="delete">
                        <input type="hidden" name="spec_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-xs btn-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($specs)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No specializations.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
