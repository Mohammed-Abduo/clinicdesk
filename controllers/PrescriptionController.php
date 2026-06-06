<?php
// ============================================================
// controllers/PrescriptionController.php
// ============================================================

class PrescriptionController
{
    private PrescriptionModel $model;
    private AppointmentModel  $apptModel;
    private DoctorModel       $doctorModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model       = new PrescriptionModel();
        $this->apptModel   = new AppointmentModel();
        $this->doctorModel = new DoctorModel();
    }

    // ---- List (patient sees own, doctor sees own) --------------------

    public function index(): void
    {
        $role = Auth::role();

        if ($role === 'patient') {
            $prescriptions = $this->model->getForPatient(Auth::id());
        } elseif ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId(Auth::id());
            if (!$doctor) { redirect('?page=errors/403'); }
            $prescriptions = $this->model->getForDoctor($doctor['id']);
        } else {
            Auth::requireRole('admin');
            // Admin: show all prescriptions (no ownership filter)
            $prescriptions = $this->model->getAll();
        }

        require BASE_PATH . '/views/prescriptions/index.php';
    }

    // ---- Add (doctor only, after completed appointment) -------------

    public function add(): void
    {
        Auth::requireRole('doctor');

        $apptId = (int) ($_GET['appt_id'] ?? 0);
        $appt   = $this->apptModel->findById($apptId);

        if (!$appt) { redirect('?page=errors/404'); }

        // Verify the doctor owns this appointment
        $doctor = $this->doctorModel->findByUserId(Auth::id());
        if (!$doctor || $appt['doctor_id'] != $doctor['id']) {
            redirect('?page=errors/403');
        }

        // Must be completed
        if ($appt['status'] !== 'completed') {
            redirectWith('?page=appointments', 'warning', 'Prescriptions can only be added to completed appointments.');
        }

        // Already has one?
        if ($this->model->findByAppointment($apptId)) {
            redirectWith('?page=appointments&action=view&id=' . $apptId, 'info', 'Prescription already exists.');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old = $_POST;

            if (empty(trim($old['notes']))) {
                $errors[] = 'Prescription notes are required.';
            }

            $pdfFile = null;
            if (!empty($_FILES['pdf_file']['name'])) {
                try {
                    $pdfFile = uploadPdf($_FILES['pdf_file'], UPLOAD_RX);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (empty($errors)) {
                $this->model->create([
                    'appointment_id' => $apptId,
                    'doctor_id'      => $doctor['id'],
                    'patient_id'     => $appt['patient_id'],
                    'notes'          => trim($old['notes']),
                    'pdf_file'       => $pdfFile,
                ]);
                logActivity('rx_create', 'Added prescription for appointment #' . $apptId);
                redirectWith('?page=appointments&action=view&id=' . $apptId, 'success', 'Prescription added.');
            }
        }

        require BASE_PATH . '/views/prescriptions/form.php';
    }

    // ---- Download PDF (patient / doctor / admin) --------------------

    public function download(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $rx = $this->model->findById($id);

        if (!$rx || !$rx['pdf_file']) { redirect('?page=errors/404'); }

        // Ownership check
        $role = Auth::role();
        if ($role === 'patient' && $rx['patient_id'] != Auth::id()) {
            redirect('?page=errors/403');
        }
        if ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId(Auth::id());
            if (!$doctor || $rx['doctor_id'] != $doctor['id']) {
                redirect('?page=errors/403');
            }
        }

        $filepath = UPLOAD_RX . $rx['pdf_file'];

        if (!file_exists($filepath)) {
            redirect('?page=errors/404');
        }

        // Serve securely
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($filepath));
        header('X-Content-Type-Options: nosniff');
        readfile($filepath);
        exit;
    }
}
