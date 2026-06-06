<?php
// ============================================================
// core/Auth.php  –  Session-based authentication & RBAC
// ============================================================

class Auth
{
    // Keys stored in $_SESSION
    private const USER_KEY = '__cd_user';

    // --------------------------------------------------------
    // Login
    // --------------------------------------------------------

    /**
     * Attempt to log in a user.
     * Returns true on success, false on failure.
     */
    public static function login(string $email, string $password): bool
    {
        $db   = Database::getInstance();
        $rows = $db->query(
            "SELECT id, name, email, password, role, is_active FROM users WHERE email = ? LIMIT 1",
            's',
            [$email]
        );

        if (empty($rows)) {
            return false;
        }

        $user = $rows[0];

        if (!$user['is_active']) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Harden session
        session_regenerate_id(true);

        $_SESSION[self::USER_KEY] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        return true;
    }

    // --------------------------------------------------------
    // Logout
    // --------------------------------------------------------

    /**
     * Destroy the session completely.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }

        session_destroy();
    }

    // --------------------------------------------------------
    // Checks
    // --------------------------------------------------------

    /**
     * Return true if a user is logged in.
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::USER_KEY]);
    }

    /**
     * Return current user array or null.
     */
    public static function currentUser(): ?array
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }

    /**
     * Return current user's role or empty string.
     */
    public static function role(): string
    {
        return $_SESSION[self::USER_KEY]['role'] ?? '';
    }

    /**
     * Return current user's ID or null.
     */
    public static function id(): ?int
    {
        return isset($_SESSION[self::USER_KEY])
            ? (int) $_SESSION[self::USER_KEY]['id']
            : null;
    }

    // --------------------------------------------------------
    // Guard helpers (call at top of controllers)
    // --------------------------------------------------------

    /**
     * Redirect to login if not authenticated.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('?page=login');
        }
    }

    /**
     * Require one of the given roles, redirect to 403 otherwise.
     *
     * @param string|array $roles  e.g. 'admin' or ['admin','doctor']
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();

        $roles = (array) $roles;

        if (!in_array(self::role(), $roles, true)) {
            redirect('?page=errors/403');
        }
    }
}
