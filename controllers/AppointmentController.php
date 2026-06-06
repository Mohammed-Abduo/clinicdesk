<?php
// ============================================================
// controllers/AppointmentController.php
// ============================================================

class AppointmentController
{
    private AppointmentModel $model;
    private DoctorModel      $doctorModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model       = new AppointmentModel();
        $this->doctorModel = new DoctorModel();
    }

    // ---- List (role-aware) ------------------------------------------

    public function index(): void
    {
        $role   = Auth::role();
        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $offset = ($page - 1) * PER_PAGE;

        // Build filter set
        $filters = [];
        if ($role === 'patient') {
            $filters['patient_id'] = Auth::id();
            // Patient may filter own list by status + date range.
            if (!empty($_GET['start_date'])) $filters['start_date'] = $_GET['start_date'];
            if (!empty($_GET['end_date']))   $filters['end_date']   = $_GET['end_date'];
        } elseif ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId(Auth::id());
            if (!$doctor) { redirect('?page=errors/403'); }
            $filters['doctor_id'] = $doctor['id'];
            // Doctor may filter own list by status + date range.
            if (!empty($_GET['start_date'])) $filters['start_date'] = $_GET['start_date'];
            if (!empty($_GET['end_date']))   $filters['end_date']   = $_GET['end_date'];
        } else {
            // admin: full filter set from GET (doctor, patient name, dates).
            if (!empty($_GET['doctor_id']))    $filters['doctor_id']    = (int) $_GET['doctor_id'];
            if (!empty($_GET['patient_id']))   $filters['patient_id']   = (int) $_GET['patient_id'];
            if (!empty($_GET['patient_name'])) $filters['patient_name'] = trim($_GET['patient_name']);
            if (!empty($_GET['start_date']))   $filters['start_date']   = $_GET['start_date'];
            if (!empty($_GET['end_date']))     $filters['end_date']     = $_GET['end_date'];
        }

        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

        $result = $this->model->getFiltered($filters, PER_PAGE, $offset);
        $pager  = new Paginator($result['total'], PER_PAGE, $page);
        $appointments = $result['rows'];
        $doctors      = $this->doctorModel->getActiveList();

        require BASE_PATH . '/views/appointments/index.php';
    }

    // ---- Book (patient only) ----------------------------------------

    public function book(): void
    {
        Auth::requireRole('patient');

        $doctors = $this->doctorModel->getActiveList();
        $errors  = [];
        $old     = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old    = $_POST;
            $errors = $this->validateBooking($old);

            if (empty($errors)) {
                $this->model->create([
                    'patient_id' => Auth::id(),
                    'doctor_id'  => (int) $old['doctor_id'],
                    'appt_date'  => $old['appt_date'],
                    'appt_time'  => $old['appt_time'],
                    'reason'     => trim($old['reason'] ?? ''),
                ]);
                logActivity('appt_create', 'Booked appointment with doctor #' . (int) $old['doctor_id'] . ' on ' . $old['appt_date']);
                redirectWith('?page=appointments', 'success', 'Appointment booked successfully. Awaiting confirmation.');
            }
        }

        require BASE_PATH . '/views/appointments/book.php';
    }

    // ---- View single ------------------------------------------------

    public function view(): void
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $appt = $this->model->findById($id);

        if (!$appt) { redirect('?page=errors/404'); }
        $this->verifyOwnership($appt);

        $rxModel     = new PrescriptionModel();
        $prescription = $rxModel->findByAppointment($id);

        require BASE_PATH . '/views/appointments/view.php';
    }

    // ---- Status change (doctor / admin) -----------------------------

    public function updateStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?page=appointments'); }
        CSRF::validate();

        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = trim($_POST['notes'] ?? '');
        $appt   = $this->model->findById($id);

        if (!$appt) { redirect('?page=errors/404'); }

        $role = Auth::role();

        // Role-based permission check
        if ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId(Auth::id());
            if (!$doctor || $appt['doctor_id'] != $doctor['id']) {
                redirect('?page=errors/403');
            }
            $allowed = ['confirmed', 'completed', 'cancelled'];
        } elseif ($role === 'admin') {
            $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
        } else {
            redirect('?page=errors/403');
        }

        if (!in_array($status, $allowed, true)) { redirect('?page=appointments'); }

        $this->model->updateStatus($id, $status, $notes ?: null);
        logActivity('appt_update', 'Appointment #' . $id . ' status set to ' . $status);
        redirectWith('?page=appointments&action=view&id=' . $id, 'success', 'Status updated to ' . $status . '.');
    }

    // ---- Cancel (patient only) --------------------------------------

    public function cancel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?page=appointments'); }
        Auth::requireRole('patient');
        CSRF::validate();

        $id   = (int) ($_POST['id'] ?? 0);
        $appt = $this->model->findById($id);

        if (!$appt || $appt['patient_id'] != Auth::id()) { redirect('?page=errors/403'); }
        if ($appt['status'] !== 'pending') {
            redirectWith('?page=appointments', 'warning', 'Only pending appointments can be cancelled.');
        }

        $this->model->updateStatus($id, 'cancelled');
        logActivity('appt_cancel', 'Cancelled appointment #' . $id);
        redirectWith('?page=appointments', 'success', 'Appointment cancelled.');
    }

    // ---- Helpers -----------------------------------------------------

    private function validateBooking(array $data): array
    {
        $errors = [];

        $doctorId = (int) ($data['doctor_id'] ?? 0);
        $date     = $data['appt_date'] ?? '';
        $time     = $data['appt_time'] ?? '';

        if (!$doctorId) {
            $errors[] = 'Please select a doctor.';
            return $errors;
        }

        if (empty($date) || strtotime($date) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Please select a future date.';
        }

        if (empty($time)) {
            $errors[] = 'Please select a time.';
        }

        if (empty($errors)) {
            // Available-days check
            $doctor = $this->doctorModel->findById($doctorId);
            if ($doctor) {
                $dayAbbr    = date('D', strtotime($date)); // Mon, Tue ...
                $availDays  = explode(',', $doctor['available_days']);
                if (!in_array($dayAbbr, $availDays, true)) {
                    $errors[] = 'Dr. ' . e($doctor['name']) . ' is not available on ' . $dayAbbr . '.';
                }
            }

            // Conflict check
            if (empty($errors) && $this->model->isSlotTaken($doctorId, $date, $time)) {
                $errors[] = 'That time slot is already booked. Please choose another time.';
            }
        }

        return $errors;
    }

    private function verifyOwnership(array $appt): void
    {
        $role = Auth::role();
        if ($role === 'admin') return;

        if ($role === 'patient' && $appt['patient_id'] == Auth::id()) return;

        if ($role === 'doctor') {
            $doctor = $this->doctorModel->findByUserId(Auth::id());
            if ($doctor && $appt['doctor_id'] == $doctor['id']) return;
        }

        redirect('?page=errors/403');
    }
}
