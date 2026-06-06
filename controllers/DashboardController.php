<?php
// ============================================================
// controllers/DashboardController.php
// ============================================================

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $role = Auth::role();

        if ($role === 'admin') {
            $this->adminDashboard();
        } elseif ($role === 'doctor') {
            $this->doctorDashboard();
        } else {
            $this->patientDashboard();
        }
    }

    // ---- Admin -------------------------------------------------------

    private function adminDashboard(): void
    {
        $userModel  = new UserModel();
        $apptModel  = new AppointmentModel();
        $logModel   = new ActivityLogModel();

        $roleCounts     = $userModel->countByRole();
        $todayCount     = $apptModel->getTodayCount();
        $weeklyStats    = $apptModel->getWeeklyStats();
        $weeklyByStatus = $apptModel->getWeeklyByStatus();
        $recentAppts    = $apptModel->getRecent(5);
        $recentLogs     = $logModel->getRecent(8);

        require BASE_PATH . '/views/dashboard/admin.php';
    }

    // ---- Doctor ------------------------------------------------------

    private function doctorDashboard(): void
    {
        $doctorModel = new DoctorModel();
        $apptModel   = new AppointmentModel();

        $doctor = $doctorModel->findByUserId(Auth::id());
        if (!$doctor) {
            redirect('?page=errors/403');
        }

        $todayAppts   = $apptModel->getTodayForDoctor($doctor['id']);
        $monthlyStats = $apptModel->getMonthlyForDoctor($doctor['id']);

        // Spec stat counts
        $pendingCount   = $apptModel->countForDoctor($doctor['id'], 'pending');
        $completedCount = $apptModel->countForDoctor($doctor['id'], 'completed');
        $monthlyTotal   = $apptModel->getMonthlyCountForDoctor($doctor['id']);

        // Upcoming (confirmed future)
        $upcomingData = $apptModel->getFiltered(
            ['doctor_id' => $doctor['id'], 'status' => 'confirmed', 'start_date' => date('Y-m-d')],
            5, 0
        );
        $upcomingAppts = $upcomingData['rows'];

        require BASE_PATH . '/views/dashboard/doctor.php';
    }

    // ---- Patient -----------------------------------------------------

    private function patientDashboard(): void
    {
        $apptModel = new AppointmentModel();
        $rxModel   = new PrescriptionModel();

        $patientId   = Auth::id();
        $rxCount     = $rxModel->countForPatient($patientId);

        // Spec stats
        $completedCount = $apptModel->countForPatient($patientId, 'completed');
        $nextAppt       = $apptModel->getNextForPatient($patientId);
        $statusCounts   = $apptModel->getStatusCountsForPatient($patientId);

        $activeData  = $apptModel->getFiltered(
            ['patient_id' => $patientId, 'status' => 'confirmed', 'start_date' => date('Y-m-d')],
            10, 0
        );
        $activeAppts = $activeData['rows'];

        $allData     = $apptModel->getForPatient($patientId, 5, 0);
        $recentAppts = $allData['rows'];

        require BASE_PATH . '/views/dashboard/patient.php';
    }
}
