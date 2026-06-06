<?php
$flash = flash();
if ($flash):
    $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
    $msg  = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
?>
<div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
  <i class="fas fa-<?= $type === 'success' ? 'check-circle' : ($type === 'danger' ? 'times-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?> mr-2"></i>
  <?= $msg ?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<?php endif; ?>
