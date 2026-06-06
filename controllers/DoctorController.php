<?php
// ============================================================
// controllers/DoctorController.php  –  Admin doctor management
// ============================================================

class DoctorController
{
    private DoctorModel        $model;
    private SpecializationModel $specModel;
    private UserModel          $userModel;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->model     = new DoctorModel();
        $this->specModel = new SpecializationModel();
        $this->userModel = new UserModel();
    }

    // ---- List -------------------------------------------------------

    public function index(): void
    {
        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $result = $this->model->getAll(PER_PAGE, ($page - 1) * PER_PAGE);
        $pager  = new Paginator($result['total'], PER_PAGE, $page);
        $doctors = $result['rows'];

        require BASE_PATH . '/views/doctors/index.php';
    }

    // ---- Create -------------------------------------------------------

    public function create(): void
    {
        $specializations = $this->specModel->getAll();
        $doctorUsers    = $this->userModel->getAll(200, 0, 'doctor')['rows'];
        $errors          = [];
        $old             = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old    = $_POST;
            $errors = $this->validateDoctor($old);

            if (empty($errors)) {
                $photoFile = null;
                if (!empty($_FILES['photo']['name'])) {
                    try {
                        $photoFile = uploadImage($_FILES['photo'], UPLOAD_DOCTOR);
                    } catch (RuntimeException $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                if (empty($errors)) {
                    $this->model->create([
                        'user_id'           => (int) $old['user_id'],
                        'specialization_id' => (int) $old['specialization_id'],
                        'bio'               => trim($old['bio'] ?? ''),
                        'consultation_fee'  => (float) $old['consultation_fee'],
                        'available_days'    => implode(',', $old['available_days'] ?? ['Mon','Tue','Wed','Thu','Fri']),
                        'photo'             => $photoFile,
                    ]);
                    logActivity('doctor_create', 'Created doctor profile for user #' . (int) $old['user_id']);
                    redirectWith('?page=doctors', 'success', 'Doctor profile created.');
                }
            }
        }

        require BASE_PATH . '/views/doctors/form.php';
    }

    // ---- Edit -------------------------------------------------------

    public function edit(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $doctor = $this->model->findById($id);
        if (!$doctor) { redirect('?page=errors/404'); }

        $specializations = $this->specModel->getAll();
        $errors          = [];
        $old             = $doctor;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old    = $_POST;
            $errors = $this->validateDoctor($old);

            if (empty($errors)) {
                if (!empty($_FILES['photo']['name'])) {
                    try {
                        $photoFile = uploadImage($_FILES['photo'], UPLOAD_DOCTOR);
                        $this->model->updatePhoto($id, $photoFile);
                    } catch (RuntimeException $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                if (empty($errors)) {
                    $this->model->update($id, [
                        'specialization_id' => (int) $old['specialization_id'],
                        'bio'               => trim($old['bio'] ?? ''),
                        'consultation_fee'  => (float) $old['consultation_fee'],
                        'available_days'    => implode(',', $old['available_days'] ?? ['Mon','Tue','Wed','Thu','Fri']),
                    ]);
                    logActivity('doctor_update', 'Updated doctor profile #' . $id);
                    redirectWith('?page=doctors', 'success', 'Doctor profile updated.');
                }
            }
        }

        require BASE_PATH . '/views/doctors/form.php';
    }

    // ---- Delete -------------------------------------------------------

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?page=doctors'); }
        CSRF::validate();

        $id = (int) ($_POST['id'] ?? 0);
        $this->model->delete($id);
        logActivity('doctor_delete', 'Deleted doctor profile #' . $id);
        redirectWith('?page=doctors', 'success', 'Doctor deleted.');
    }

    // ---- Specializations sub-management (simple inline) --------------

    public function specializations(): void
    {
        $specs  = $this->specModel->getAllWithCounts();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                if ($name === '') { $errors[] = 'Name required.'; }
                else {
                    $this->specModel->create($name);
                    redirectWith('?page=doctors&action=specializations', 'success', 'Specialization added.');
                }
            } elseif ($action === 'delete') {
                $specId = (int) ($_POST['spec_id'] ?? 0);

                // Safe delete: block if any doctors are assigned to it.
                if (!$this->specModel->isSafeToDelete($specId)) {
                    redirectWith(
                        '?page=doctors&action=specializations',
                        'danger',
                        'Cannot delete: one or more doctors are assigned to this specialization. Reassign them first.'
                    );
                }

                $this->specModel->delete($specId);
                redirectWith('?page=doctors&action=specializations', 'success', 'Specialization deleted.');
            }

            $specs = $this->specModel->getAllWithCounts();
        }

        require BASE_PATH . '/views/doctors/specializations.php';
    }

    // ---- Validation -------------------------------------------------

    private function validateDoctor(array $data): array
    {
        $errors = [];
        if (empty($data['specialization_id'])) {
            $errors[] = 'Specialization is required.';
        }
        if (!is_numeric($data['consultation_fee'] ?? '')) {
            $errors[] = 'Consultation fee must be a number.';
        }
        if (empty($data['available_days'])) {
            $errors[] = 'At least one available day must be selected.';
        }
        return $errors;
    }
}
