<?php
// ============================================================
// models/UserModel.php
// ============================================================

class UserModel extends BaseModel
{
    // ---- READ -------------------------------------------------------

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            "SELECT id, name, email, role, phone, avatar, is_active, created_at
             FROM users WHERE id = ? LIMIT 1",
            'i', [$id]
        );
        return $rows[0] ?? null;
    }

    public function findByEmail(string $email): ?array
    {
        $rows = $this->query(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            's', [$email]
        );
        return $rows[0] ?? null;
    }

    public function getAll(int $limit, int $offset, string $role = '', string $search = ''): array
    {
        $where  = [];
        $types  = '';
        $params = [];

        if ($role !== '') {
            $where[]  = 'role = ?';
            $types   .= 's';
            $params[] = $role;
        }
        if ($search !== '') {
            $where[]  = '(name LIKE ? OR email LIKE ?)';
            $types   .= 'ss';
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count
        $countRows = $this->query(
            "SELECT COUNT(*) AS cnt FROM users $whereClause",
            $types, $params
        );
        $total = (int) ($countRows[0]['cnt'] ?? 0);

        // Data
        $types   .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $rows = $this->query(
            "SELECT id, name, email, role, phone, is_active, created_at
             FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $types, $params
        );

        return ['total' => $total, 'rows' => $rows];
    }

    public function countByRole(): array
    {
        $rows = $this->query(
            "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role"
        );
        $result = ['admin' => 0, 'doctor' => 0, 'patient' => 0];
        foreach ($rows as $row) {
            $result[$row['role']] = (int) $row['cnt'];
        }
        return $result;
    }

    // ---- WRITE ------------------------------------------------------

    public function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $this->query(
            "INSERT INTO users (name, email, password, role, phone, is_active)
             VALUES (?, ?, ?, ?, ?, ?)",
            'sssssi',
            [
                $data['name'],
                $data['email'],
                $hash,
                $data['role'],
                $data['phone'] ?? null,
                $data['is_active'] ?? 1,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->query(
            "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, is_active = ?
             WHERE id = ?",
            'ssssii',
            [
                $data['name'],
                $data['email'],
                $data['role'],
                $data['phone'] ?? null,
                $data['is_active'] ?? 1,
                $id,
            ]
        );
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return (bool) $this->query(
            "UPDATE users SET password = ? WHERE id = ?",
            'si', [$hash, $id]
        );
    }

    public function updateAvatar(int $id, string $filename): bool
    {
        return (bool) $this->query(
            "UPDATE users SET avatar = ? WHERE id = ?",
            'si', [$filename, $id]
        );
    }

    public function toggleActive(int $id): bool
    {
        return (bool) $this->query(
            "UPDATE users SET is_active = NOT is_active WHERE id = ?",
            'i', [$id]
        );
    }

    public function delete(int $id): bool
    {
        return (bool) $this->query(
            "DELETE FROM users WHERE id = ?",
            'i', [$id]
        );
    }
}
