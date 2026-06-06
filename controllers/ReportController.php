<?php
// ============================================================
// controllers/ReportController.php  –  Admin reports + CSV
// ============================================================

class ReportController
{
    private AppointmentModel $model;
    private DoctorModel      $doctorModel;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->model       = new AppointmentModel();
        $this->doctorModel = new DoctorModel();
    }

    // ---- Report page ------------------------------------------------

    public function index(): void
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
            'doctor_id'  => $_GET['doctor_id']  ?? '',
            'status'     => $_GET['status']     ?? '',
        ];

        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $result = $this->model->getFiltered($filters, PER_PAGE, ($page - 1) * PER_PAGE);
        $pager  = new Paginator($result['total'], PER_PAGE, $page);

        $appointments = $result['rows'];
        $doctors      = $this->doctorModel->getActiveList();

        // Summary counts
        $summary = $this->buildSummary($filters);

        require BASE_PATH . '/views/reports/index.php';
    }

    // ---- Printable PDF report --------------------------------------

    /**
     * Render a clean, print-optimised report page. The browser's native
     * "Print → Save as PDF" produces a professional PDF with header, footer,
     * generated date and pagination — with zero third-party dependencies.
     *
     * To switch to server-side PDF generation, install DomPDF
     * (`composer require dompdf/dompdf`) and feed the same $appointments /
     * $summary data into a Dompdf instance instead of requiring the view.
     */
    public function printable(): void
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
            'doctor_id'  => $_GET['doctor_id']  ?? '',
            'status'     => $_GET['status']     ?? '',
        ];

        $result       = $this->model->getFiltered($filters, 10000, 0);
        $appointments = $result['rows'];
        $summary      = $this->buildSummary($filters);
        $generatedAt  = date('d M Y H:i');

        $doctorName = 'All Doctors';
        if (!empty($filters['doctor_id'])) {
            $doc = $this->doctorModel->findById((int) $filters['doctor_id']);
            if ($doc) { $doctorName = 'Dr. ' . $doc['name']; }
        }

        require BASE_PATH . '/views/reports/print.php';
    }

    // ---- CSV Export ------------------------------------------------

    public function export(): void
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
            'doctor_id'  => $_GET['doctor_id']  ?? '',
            'status'     => $_GET['status']     ?? '',
        ];

        // Fetch all rows (no pagination)
        $result = $this->model->getFiltered($filters, 10000, 0);
        $rows   = $result['rows'];

        $filename = 'clinicdesk_report_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fputs($out, "\xEF\xBB\xBF");

        // Header row
        fputcsv($out, [
            'ID', 'Patient', 'Doctor', 'Specialization',
            'Date', 'Time', 'Status', 'Reason', 'Notes', 'Created At'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                self::csvSafe($row['patient_name']),
                self::csvSafe($row['doctor_name']),
                self::csvSafe($row['specialization']),
                $row['appt_date'],
                $row['appt_time'],
                $row['status'],
                self::csvSafe($row['reason']),
                self::csvSafe($row['notes']),
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Neutralise CSV formula injection. Spreadsheet apps execute a cell
     * that begins with = + - @ (or tab/CR), so prefix such values with
     * a single quote to force literal text.
     */
    private static function csvSafe(?string $value): string
    {
        $value = (string) $value;
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }
        return $value;
    }

    // ---- Helpers ----------------------------------------------------

    private function buildSummary(array $filters): array
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $summary  = [];

        foreach ($statuses as $s) {
            $f = array_merge($filters, ['status' => $s]);
            $r = $this->model->getFiltered($f, 1, 0);
            $summary[$s] = $r['total'];
        }
        $summary['total'] = array_sum($summary);

        return $summary;
    }
}
