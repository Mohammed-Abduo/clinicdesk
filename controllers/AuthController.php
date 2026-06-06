<?php
// ============================================================
// controllers/AuthController.php
// ============================================================

class AuthController
{
    public function login(): void
    {
        // Already logged in?
        if (Auth::check()) {
            $this->redirectByRole();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();

            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (Auth::login($email, $password)) {
                session_regenerate_id(true);
                logActivity('login', 'Signed in');
                $this->redirectByRole();
            } else {
                logActivity('failed_login', 'Failed login attempt for ' . $email);
                $error = 'Invalid email or password, or account is disabled.';
            }
        }

        require BASE_PATH . '/views/auth/login.php';
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=login');
        }
        CSRF::validate();

        // Capture identity before the session is destroyed.
        $userId   = Auth::id();
        $userName = Auth::currentUser()['name'] ?? null;

        Auth::logout();
        logActivity('logout', 'Signed out', $userId, $userName);
        redirect('?page=login');
    }

    private function redirectByRole(): never
    {
        match (Auth::role()) {
            'admin'   => redirect('?page=dashboard'),
            'doctor'  => redirect('?page=dashboard'),
            'patient' => redirect('?page=dashboard'),
            default   => redirect('?page=login'),
        };
    }
}
