<?php $pageTitle = 'Manage Doctors'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Doctors</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Doctors</li>
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
          <div class="card-tools">
            <a href="<?= BASE_URL ?>/?page=doctors&action=create" class="btn btn-sm btn-primary">
              <i class="fas fa-user-md mr-1"></i> Add Doctor
            </a>
            <a href="<?= BASE_URL ?>/?page=doctors&action=specializations" class="btn btn-sm btn-info ml-1">
              <i class="fas fa-list mr-1"></i> Specializations
            </a>
          </div>
        </div>

        <div class="card-body p-0">
          <table class="table table-hover mb-0 data-table">
            <thead class="thead-light">
              <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Specialization</th>
                <th>Fee</th>
                <th>Available Days</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($doctors as $d): ?>
              <tr>
                <td>
                  <?php if ($d['photo']): ?>
                    <img src="<?= BASE_URL ?>/public/uploads/doctor_photos/<?= e($d['photo']) ?>"
                         class="img-circle elevation-1" width="38" height="38" style="object-fit:cover"
                         alt="photo">
                  <?php else: ?>
                    <span class="text-muted"><i class="fas fa-user-circle fa-2x"></i></span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong>Dr. <?= e($d['name']) ?></strong>
                  <br><small class="text-muted"><?= e($d['email']) ?></small>
                </td>
                <td><?= e($d['specialization_name']) ?></td>
                <td>$<?= number_format($d['consultation_fee'], 2) ?></td>
                <td>
                  <?php
                  $days = explode(',', $d['available_days']);
                  foreach ($days as $day): ?>
                    <span class="badge badge-light border"><?= e($day) ?></span>
                  <?php endforeach; ?>
                </td>
                <td>
                  <?php if ($d['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=doctors&action=edit&id=<?= $d['id'] ?>"
                     class="btn btn-xs btn-info"><i class="fas fa-edit"></i> Edit</a>

                  <form method="POST" action="<?= BASE_URL ?>/?page=doctors&action=delete"
                        class="d-inline" onsubmit="return confirm('Delete this doctor profile?')">
                    <?= CSRF::input() ?>
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <button type="submit" class="btn btn-xs btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($doctors)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No doctors found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($pager->totalPages() > 1): ?>
        <div class="card-footer clearfix">
          <ul class="pagination pagination-sm m-0 float-right">
            <?php if ($pager->hasPrev()): ?>
              <li class="page-item"><a class="page-link" href="<?= buildQuery(['p' => $pager->currentPage() - 1]) ?>">«</a></li>
            <?php endif; ?>
            <?php foreach ($pager->pages() as $pg): ?>
              <li class="page-item <?= $pg === $pager->currentPage() ? 'active' : '' ?>">
                <a class="page-link" href="<?= buildQuery(['p' => $pg]) ?>"><?= $pg ?></a>
              </li>
            <?php endforeach; ?>
            <?php if ($pager->hasNext()): ?>
              <li class="page-item"><a class="page-link" href="<?= buildQuery(['p' => $pager->currentPage() + 1]) ?>">»</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
