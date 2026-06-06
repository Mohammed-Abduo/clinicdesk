<?php
// Standalone printable report — no AdminLTE chrome, print-optimised.
// Browser "Save as PDF" turns this into a professional PDF document.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ClinicDesk Appointment Report</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; color: #222; margin: 0; padding: 32px; font-size: 12px; }
    .report-header { display: flex; justify-content: space-between; align-items: flex-start;
                     border-bottom: 3px solid #1e3248; padding-bottom: 12px; margin-bottom: 18px; }
    .brand { font-size: 22px; font-weight: 700; color: #1e3248; }
    .brand .sub { display: block; font-size: 11px; font-weight: 400; color: #6c757d; margin-top: 2px; }
    .meta { text-align: right; font-size: 11px; color: #555; line-height: 1.6; }
    .meta strong { color: #222; }
    h2 { font-size: 15px; margin: 0 0 10px; color: #1e3248; }
    .summary { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .summary td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: center; }
    .summary .num { font-size: 18px; font-weight: 700; }
    .summary .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #1e3248; color: #fff; text-align: left; padding: 7px 8px; font-size: 11px; }
    table.data td { border-bottom: 1px solid #e9ecef; padding: 6px 8px; vertical-align: top; }
    table.data tr:nth-child(even) td { background: #f8f9fa; }
    .status { padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; color: #fff; }
    .s-pending { background: #ffc107; color: #222; }
    .s-confirmed { background: #17a2b8; }
    .s-completed { background: #28a745; }
    .s-cancelled { background: #dc3545; }
    .report-footer { margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px;
                     font-size: 10px; color: #6c757d; display: flex; justify-content: space-between; }
    .empty { text-align: center; color: #888; padding: 30px; }
    .toolbar { margin-bottom: 16px; }
    .toolbar button, .toolbar a { font-size: 12px; padding: 6px 14px; border-radius: 4px; cursor: pointer;
                                  border: 1px solid #1e3248; text-decoration: none; }
    .toolbar button { background: #1e3248; color: #fff; }
    .toolbar a { background: #fff; color: #1e3248; }
    @media print {
      body { padding: 0; }
      .toolbar { display: none; }
      table.data { page-break-inside: auto; }
      table.data tr { page-break-inside: avoid; }
      thead { display: table-header-group; }
    }
  </style>
</head>
<body>

  <div class="toolbar">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <a href="<?= BASE_URL ?>/?page=reports">← Back to Reports</a>
  </div>

  <div class="report-header">
    <div>
      <span class="brand"><?= APP_NAME ?>
        <span class="sub">Clinic Management System — Appointment Report</span>
      </span>
    </div>
    <div class="meta">
      <div><strong>Generated:</strong> <?= e($generatedAt) ?></div>
      <div><strong>Period:</strong> <?= e($filters['start_date']) ?> &rarr; <?= e($filters['end_date']) ?></div>
      <div><strong>Doctor:</strong> <?= e($doctorName) ?></div>
      <div><strong>Status:</strong> <?= e($filters['status'] !== '' ? ucfirst($filters['status']) : 'All') ?></div>
    </div>
  </div>

  <h2>Summary</h2>
  <table class="summary">
    <tr>
      <td><div class="num"><?= $summary['total'] ?></div><div class="lbl">Total</div></td>
      <td><div class="num"><?= $summary['pending'] ?></div><div class="lbl">Pending</div></td>
      <td><div class="num"><?= $summary['confirmed'] ?></div><div class="lbl">Confirmed</div></td>
      <td><div class="num"><?= $summary['completed'] ?></div><div class="lbl">Completed</div></td>
      <td><div class="num"><?= $summary['cancelled'] ?></div><div class="lbl">Cancelled</div></td>
    </tr>
  </table>

  <h2>Appointments (<?= count($appointments) ?>)</h2>
  <table class="data">
    <thead>
      <tr>
        <th>#</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Specialization</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $a): ?>
      <tr>
        <td><?= $a['id'] ?></td>
        <td><?= e($a['patient_name']) ?></td>
        <td>Dr. <?= e($a['doctor_name']) ?></td>
        <td><?= e($a['specialization']) ?></td>
        <td><?= fmtDate($a['appt_date']) ?></td>
        <td><?= fmtTime($a['appt_time']) ?></td>
        <td><span class="status s-<?= e($a['status']) ?>"><?= ucfirst(e($a['status'])) ?></span></td>
        <td><?= e(mb_substr($a['reason'] ?? '', 0, 60)) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($appointments)): ?>
      <tr><td colspan="8" class="empty">No appointments match the selected filters.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="report-footer">
    <span><?= APP_NAME ?> v<?= APP_VERSION ?> — Confidential</span>
    <span>Generated <?= e($generatedAt) ?></span>
  </div>

  <script>
    // Auto-open the print dialog when arriving with ?print=1
    <?php if (($_GET['auto'] ?? '') === '1'): ?>
    window.addEventListener('load', function () { window.print(); });
    <?php endif; ?>
  </script>
</body>
</html>
