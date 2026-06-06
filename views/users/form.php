<?php
$isEdit     = isset($user);
$pageTitle  = $isEdit ? 'Edit User' : 'Add User';
$formAction = $isEdit
    ? BASE_URL . '/?page=users&action=edit&id=' . $user['id']
    : BASE_URL . '/?page=users&action=create';
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
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=users">Users</a></li>
            <li class="breadcrumb-item active"><?= $pageTitle ?></li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-7">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user mr-2"></i><?= $pageTitle ?>
              </h3>
            </div>

            <form method="POST" action="<?= $formAction ?>">
              <?= CSRF::input() ?>
              <div class="card-body">

                <!-- Validation errors -->
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                      <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= e($old['name'] ?? '') ?>">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= e($old['email'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Password <?= $isEdit ? '(leave blank to keep current)' : '<span class="text-danger">*</span>' ?></label>
                    <input type="password" name="password" class="form-control"
                           <?= $isEdit ? '' : 'required' ?> autocomplete="new-password"
                           placeholder="Min 8 characters">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= e($old['phone'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-control" required>
                      <?php foreach (['admin','doctor','patient'] as $r): ?>
                      <option value="<?= $r ?>" <?= ($old['role'] ?? '') === $r ? 'selected' : '' ?>>
                        <?= ucfirst($r) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group col-md-6 d-flex align-items-end pb-1">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" id="is_active"
                             name="is_active" value="1"
                             <?= !empty($old['is_active']) ? 'checked' : '' ?>>
                      <label class="custom-control-label" for="is_active">Active Account</label>
                    </div>
                  </div>
                </div>

              </div><!-- /.card-body -->

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?= $isEdit ? 'Update User' : 'Create User' ?>
                </button>
                <a href="<?= BASE_URL ?>/?page=users" class="btn btn-secondary ml-2">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require BASE_PATH . '/views/partials/footer.php'; ?>
