<?php $pageTitle = 'Activity Logs'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Activity Logs</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Activity Logs</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require BASE_PATH . '/views/partials/alerts.php'; ?>

      <!-- Filters -->
      <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filters</h3></div>
        <div class="card-body">
          <form method="GET" class="form-inline flex-wrap">
            <input type="hidden" name="page" value="logs">

            <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2"
                   placeholder="Search user / action / detail"
                   value="<?= e($_GET['search'] ?? '') ?>">

            <select name="action" class="form-control form-control-sm mr-2 mb-2">
              <option value="">All Actions</option>
              <?php foreach ($actions as $a): ?>
              <option value="<?= e($a) ?>" <?= ($_GET['action'] ?? '') === $a ? 'selected' : '' ?>>
                <?= e(activityActionMeta($a)['label']) ?>
              </option>
              <?php endforeach; ?>
            </select>

            <div class="form-group mr-2 mb-2">
              <label class="mr-1 small">From</label>
              <input type="date" name="start_date" class="form-control form-control-sm"
                     value="<?= e($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="form-group mr-2 mb-2">
              <label class="mr-1 small">To</label>
              <input type="date" name="end_date" class="form-control form-control-sm"
                     value="<?= e($_GET['end_date'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-sm btn-primary mb-2 mr-2">
              <i class="fas fa-search"></i> Apply
            </button>
            <a href="<?= BASE_URL ?>/?page=logs" class="btn btn-sm btn-outline-secondary mb-2">Reset</a>
          </form>
        </div>
      </div>

      <!-- Results -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Records <span class="badge badge-secondary ml-1"><?= $pager->total() ?></span>
          </h3>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>When</th>
                <th>User</th>
                <th>Action</th>
                <th>Detail</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
              <?php $meta = activityActionMeta($log['action']); ?>
              <tr>
                <td><?= $log['id'] ?></td>
                <td class="text-nowrap"><?= fmtDate($log['created_at'], 'd M Y H:i') ?></td>
                <td><?= $log['user_name'] ? e($log['user_name']) : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge badge-<?= e($meta['color']) ?>"><?= e($meta['label']) ?></span></td>
                <td><?= e($log['description'] ?? '') ?></td>
                <td><small class="text-muted"><?= e($log['ip_address'] ?? '') ?></small></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($logs)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="fas fa-history fa-2x mb-2 d-block"></i>
                  No activity recorded for the selected filters.
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
