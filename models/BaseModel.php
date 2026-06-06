<?php
// ============================================================
// models/BaseModel.php  –  Shared database access
// ============================================================

abstract class BaseModel
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Thin proxy so child models can call $this->query(...)
     */
    protected function query(string $sql, string $types = '', array $params = []): array|bool
    {
        return $this->db->query($sql, $types, $params);
    }

    protected function lastId(): int
    {
        return $this->db->lastInsertId();
    }
}
