<?php $pageTitle = 'Manage Users'; ?>
<?php require BASE_PATH . '/views/partials/header.php'; ?>
<?php require BASE_PATH . '/views/partials/navbar.php'; ?>
<?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Users</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=dashboard">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
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
          <!-- Filters -->
          <form method="GET" class="form-inline">
            <input type="hidden" name="page" value="users">
            <input type="text" name="search" class="form-control form-control-sm mr-2"
                   placeholder="Search name or email" value="<?= e($_GET['search'] ?? '') ?>">
            <select name="role" class="form-control form-control-sm mr-2">
              <option value="">All Roles</option>
              <?php foreach (['admin','doctor','patient'] as $r): ?>
              <option value="<?= $r ?>" <?= ($_GET['role'] ?? '') === $r ? 'selected' : '' ?>>
                <?= ucfirst($r) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary mr-2">
              <i class="fas fa-search"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/?page=users" class="btn btn-sm btn-outline-secondary">Reset</a>
          </form>

          <div class="card-tools">
            <a href="<?= BASE_URL ?>/?page=users&action=create" class="btn btn-sm btn-primary">
              <i class="fas fa-user-plus mr-1"></i> Add User
            </a>
          </div>
        </div>

        <div class="card-body p-0">
          <table class="table table-hover table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td><?= $u['id'] ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= roleBadge($u['role']) ?></td>
                <td><?= e($u['phone'] ?? '–') ?></td>
                <td>
                  <?php if ($u['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td><?= fmtDate($u['created_at']) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/?page=users&action=edit&id=<?= $u['id'] ?>"
                     class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>

                  <!-- Toggle active -->
                  <form method="POST" action="<?= BASE_URL ?>/?page=users&action=toggle" class="d-inline">
                    <?= CSRF::input() ?>
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-xs <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?>"
                            title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                      <i class="fas <?= $u['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                    </button>
                  </form>

                  <!-- Delete -->
                  <?php if ($u['id'] !== Auth::id()): ?>
                  <form method="POST" action="<?= BASE_URL ?>/?page=users&action=delete" class="d-inline"
                        onsubmit="return confirm('Delete this user permanently?')">
                    <?= CSRF::input() ?>
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-xs btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($users)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">No users found.</td></tr>
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
            Showing <?= count($users) ?> of <?= $pager->total() ?> users
          </p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
