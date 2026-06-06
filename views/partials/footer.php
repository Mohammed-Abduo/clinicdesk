  </div><!-- /.content-wrapper -->

  <!-- Footer -->
  <footer class="main-footer">
    <strong>&copy; <?= date('Y') ?> <?= APP_NAME ?></strong>
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> <?= APP_VERSION ?>
    </div>
  </footer>

  <!-- Control Sidebar (closed) -->
  <aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- ./wrapper -->

<!-- Core JS: jQuery + Bootstrap + AdminLTE -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables (Bootstrap 4 integration) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<!-- Chart.js (used by dashboards / analytics) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// Auto-init DataTables on any table with .data-table class
$(function () {
  if ($.fn.DataTable) {
    $('.data-table').DataTable({
      responsive: true,
      pageLength: 15,
      order: [],
      language: { emptyTable: 'No data available' }
    });
  }
});

// Auto-dismiss flash alerts after a few seconds
setTimeout(function () {
  $('.alert-dismissible').fadeOut('slow');
}, 4000);
</script>

<?php if (isset($extraJs)) echo $extraJs; ?>

</body>
</html>
