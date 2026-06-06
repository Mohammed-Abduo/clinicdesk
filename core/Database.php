<?php
// ============================================================
// core/Database.php  –  Singleton mysqli wrapper
// ============================================================

class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    // Private constructor – use Database::getInstance()
    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->conn->set_charset(DB_CHARSET);
    }

    // Prevent cloning
    private function __clone() {}

    /**
     * Return the single Database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return raw mysqli connection for prepared statements.
     */
    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    /**
     * Convenience wrapper – prepare, bind, execute, return result.
     *
     * Usage:
     *   $rows = Database::getInstance()->query(
     *       "SELECT * FROM users WHERE role = ? AND is_active = ?",
     *       "si", [$role, 1]
     *   );
     *
     * @param string $sql        Parameterized SQL
     * @param string $types      mysqli bind types string  (e.g. "ssi")
     * @param array  $params     Values matching the type string
     * @return array|bool        Array of assoc rows for SELECT; true/false for others
     */
    public function query(string $sql, string $types = '', array $params = []): array|bool
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->conn->error);
        }

        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        // SELECT / SHOW / DESCRIBE etc.
        $result = $stmt->get_result();
        if ($result instanceof mysqli_result) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows;
        }

        // INSERT / UPDATE / DELETE
        $ok = ($stmt->affected_rows >= 0);
        $stmt->close();
        return $ok;
    }

    /**
     * Return the auto-increment ID of the last INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->conn->insert_id;
    }

    /**
     * Return affected rows from last write query.
     */
    public function affectedRows(): int
    {
        return (int) $this->conn->affected_rows;
    }
}
