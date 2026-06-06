<?php
// ============================================================
// models/DoctorModel.php
// ============================================================

class DoctorModel extends BaseModel
{
    // ---- READ -------------------------------------------------------

    private string $baseSelect = "
        SELECT d.id, d.user_id, d.specialization_id, d.bio,
               d.consultation_fee, d.available_days, d.photo,
               u.name, u.email, u.phone, u.is_active,
               s.name AS specialization_name
        FROM doctors d
        JOIN users u ON u.id = d.user_id
        JOIN specializations s ON s.id = d.specialization_id
    ";

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $countRow = $this->query("SELECT COUNT(*) AS cnt FROM doctors");
        $total    = (int) ($countRow[0]['cnt'] ?? 0);

        $rows = $this->query(
            $this->baseSelect . " ORDER BY u.name LIMIT ? OFFSET ?",
            'ii', [$limit, $offset]
        );

        return ['total' => $total, 'rows' => $rows];
    }

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE d.id = ? LIMIT 1",
            'i', [$id]
        );
        return $rows[0] ?? null;
    }

    public function findByUserId(int $userId): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE d.user_id = ? LIMIT 1",
            'i', [$userId]
        );
        return $rows[0] ?? null;
    }

    public function getActiveList(): array
    {
        return $this->query(
            $this->baseSelect . " WHERE u.is_active = 1 ORDER BY u.name"
        );
    }

    // ---- WRITE ------------------------------------------------------

    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days, photo)
             VALUES (?, ?, ?, ?, ?, ?)",
            'iisdss',
            [
                $data['user_id'],
                $data['specialization_id'],
                $data['bio']              ?? null,
                $data['consultation_fee'] ?? 0,
                $data['available_days']   ?? 'Mon,Tue,Wed,Thu,Fri',
                $data['photo']            ?? null,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->query(
            "UPDATE doctors SET specialization_id = ?, bio = ?, consultation_fee = ?,
             available_days = ? WHERE id = ?",
            'isdsi',
            [
                $data['specialization_id'],
                $data['bio']              ?? null,
                $data['consultation_fee'] ?? 0,
                $data['available_days']   ?? 'Mon,Tue,Wed,Thu,Fri',
                $id,
            ]
        );
    }

    public function updatePhoto(int $id, string $filename): bool
    {
        return (bool) $this->query(
            "UPDATE doctors SET photo = ? WHERE id = ?",
            'si', [$filename, $id]
        );
    }

    public function delete(int $id): bool
    {
        return (bool) $this->query(
            "DELETE FROM doctors WHERE id = ?",
            'i', [$id]
        );
    }
}
