<?php
// ============================================================
// controllers/UserController.php  –  Admin-only user management
// ============================================================

class UserController
{
    private UserModel $model;

    public function __construct()
    {
        Auth::requireRole('admin');
        $this->model = new UserModel();
    }

    // ---- List -------------------------------------------------------

    public function index(): void
    {
        $search  = trim($_GET['search'] ?? '');
        $role    = $_GET['role'] ?? '';
        $page    = max(1, (int) ($_GET['p'] ?? 1));

        $result  = $this->model->getAll(PER_PAGE, ($page - 1) * PER_PAGE, $role, $search);
        $pager   = new Paginator($result['total'], PER_PAGE, $page);
        $users   = $result['rows'];

        require BASE_PATH . '/views/users/index.php';
    }

    // ---- Create -------------------------------------------------------

    public function create(): void
    {
        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old = $_POST;

            $errors = $this->validateUser($old, isCreate: true);

            if (empty($errors)) {
                $this->model->create([
                    'name'      => trim($old['name']),
                    'email'     => trim($old['email']),
                    'password'  => $old['password'],
                    'role'      => $old['role'],
                    'phone'     => trim($old['phone'] ?? ''),
                    'is_active' => isset($old['is_active']) ? 1 : 0,
                ]);
                logActivity('user_create', 'Created user ' . trim($old['email']) . ' (' . $old['role'] . ')');
                redirectWith('?page=users', 'success', 'User created successfully.');
            }
        }

        require BASE_PATH . '/views/users/form.php';
    }

    // ---- Edit -------------------------------------------------------

    public function edit(): void
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $user = $this->model->findById($id);
        if (!$user) { redirect('?page=errors/404'); }

        $errors = [];
        $old    = $user;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            $old = $_POST;

            $errors = $this->validateUser($old, isCreate: false, userId: $id);

            if (empty($errors)) {
                $this->model->update($id, [
                    'name'      => trim($old['name']),
                    'email'     => trim($old['email']),
                    'role'      => $old['role'],
                    'phone'     => trim($old['phone'] ?? ''),
                    'is_active' => isset($old['is_active']) ? 1 : 0,
                ]);

                if (!empty($old['password'])) {
                    $this->model->updatePassword($id, $old['password']);
                }

                logActivity('user_update', 'Updated user #' . $id . ' (' . trim($old['email']) . ')');
                redirectWith('?page=users', 'success', 'User updated successfully.');
            }
        }

        require BASE_PATH . '/views/users/form.php';
    }

    // ---- Toggle active -----------------------------------------------

    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?page=users'); }
        CSRF::validate();

        $id = (int) ($_POST['id'] ?? 0);
        $this->model->toggleActive($id);

        logActivity('user_toggle', 'Toggled active status for user #' . $id);
        redirectWith('?page=users', 'success', 'User status updated.');
    }

    // ---- Delete -------------------------------------------------------

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?page=users'); }
        CSRF::validate();

        $id = (int) ($_POST['id'] ?? 0);

        // Prevent deleting yourself
        if ($id === Auth::id()) {
            redirectWith('?page=users', 'danger', 'You cannot delete your own account.');
        }

        $this->model->delete($id);
        logActivity('user_delete', 'Deleted user #' . $id);
        redirectWith('?page=users', 'success', 'User deleted.');
    }

    // ---- Validation -------------------------------------------------

    private function validateUser(array $data, bool $isCreate, int $userId = 0): array
    {
        $errors = [];

        if (empty(trim($data['name']))) {
            $errors[] = 'Name is required.';
        }

        if (empty(trim($data['email'])) || !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        } else {
            // Duplicate email check
            $existing = $this->model->findByEmail(trim($data['email']));
            if ($existing && (int) $existing['id'] !== $userId) {
                $errors[] = 'Email already in use.';
            }
        }

        if ($isCreate && empty($data['password'])) {
            $errors[] = 'Password is required for new users.';
        }

        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!in_array($data['role'] ?? '', ['admin', 'doctor', 'patient'], true)) {
            $errors[] = 'Invalid role selected.';
        }

        return $errors;
    }
}
