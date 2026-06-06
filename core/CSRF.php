<?php
// ============================================================
// core/CSRF.php  –  CSRF token helpers
// ============================================================

class CSRF
{
    private const TOKEN_KEY = '__cd_csrf';

    /**
     * Generate (or return existing) CSRF token for this session.
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Return an HTML hidden input containing the CSRF token.
     */
    public static function input(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate the submitted token against the session token.
     * Rotates the token after successful validation.
     *
     * @throws RuntimeException on failure (or call validateSafe() for bool)
     */
    public static function validate(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $stored    = $_SESSION[self::TOKEN_KEY] ?? '';

        if (!hash_equals($stored, $submitted)) {
            http_response_code(403);
            die('Invalid CSRF token. Please go back and try again.');
        }

        // Rotate token after use
        unset($_SESSION[self::TOKEN_KEY]);
    }

    /**
     * Same as validate() but returns bool instead of dying.
     */
    public static function validateSafe(): bool
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $stored    = $_SESSION[self::TOKEN_KEY] ?? '';

        if (!hash_equals($stored, $submitted)) {
            return false;
        }

        unset($_SESSION[self::TOKEN_KEY]);
        return true;
    }
}
