<?php
// ============================================================
// models/SpecializationModel.php
// ============================================================

class SpecializationModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->query("SELECT * FROM specializations ORDER BY name");
    }

    /**
     * List specializations together with the number of doctors assigned
     * to each (for the management screen).
     */
    public function getAllWithCounts(): array
    {
        return $this->query(
            "SELECT s.id, s.name, s.created_at,
                    COUNT(d.id) AS doctor_count
             FROM specializations s
             LEFT JOIN doctors d ON d.specialization_id = s.id
             GROUP BY s.id, s.name, s.created_at
             ORDER BY s.name"
        );
    }

    /**
     * Number of doctors currently assigned to a specialization.
     */
    public function doctorCount(int $id): int
    {
        $rows = $this->query(
            "SELECT COUNT(*) AS cnt FROM doctors WHERE specialization_id = ?",
            'i', [$id]
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    /**
     * Safe to delete only when no doctors are assigned to it.
     */
    public function isSafeToDelete(int $id): bool
    {
        return $this->doctorCount($id) === 0;
    }

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            "SELECT * FROM specializations WHERE id = ? LIMIT 1",
            'i', [$id]
        );
        return $rows[0] ?? null;
    }

    public function create(string $name): int
    {
        $this->query(
            "INSERT INTO specializations (name) VALUES (?)",
            's', [$name]
        );
        return $this->lastId();
    }

    public function update(int $id, string $name): bool
    {
        return (bool) $this->query(
            "UPDATE specializations SET name = ? WHERE id = ?",
            'si', [$name, $id]
        );
    }

    public function delete(int $id): bool
    {
        return (bool) $this->query(
            "DELETE FROM specializations WHERE id = ?",
            'i', [$id]
        );
    }
}
