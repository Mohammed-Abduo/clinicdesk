<?php
// ============================================================
// models/ActivityLogModel.php  –  Audit-trail data layer
// ============================================================

class ActivityLogModel extends BaseModel
{
    private string $baseSelect = "
        SELECT id, user_id, user_name, action, description, ip_address, created_at
        FROM activity_logs
    ";

    // ---- WRITE ------------------------------------------------------

    /**
     * Persist a single activity record.
     *
     * @param string      $action      Short machine action key (e.g. 'login').
     * @param string      $description Human-readable detail.
     * @param int|null    $userId      Acting user id (NULL for failed logins).
     * @param string|null $userName    Snapshot of the acting user's name.
     * @param string|null $ip          Request IP address.
     */
    public function log(
        string $action,
        string $description = '',
        ?int $userId = null,
        ?string $userName = null,
        ?string $ip = null
    ): int {
        $this->query(
            "INSERT INTO activity_logs (user_id, user_name, action, description, ip_address)
             VALUES (?, ?, ?, ?, ?)",
            'issss',
            [
                $userId,
                $userName !== null ? mb_substr($userName, 0, 120) : null,
                mb_substr($action, 0, 60),
                $description !== '' ? mb_substr($description, 0, 255) : null,
                $ip !== null ? mb_substr($ip, 0, 45) : null,
            ]
        );
        return $this->lastId();
    }

    // ---- READ -------------------------------------------------------

    /** Most recent activity records for the dashboard widget. */
    public function getRecent(int $limit = 8): array
    {
        return $this->query(
            $this->baseSelect . " ORDER BY created_at DESC, id DESC LIMIT ?",
            'i', [$limit]
        );
    }

    /**
     * Paginated, filterable log listing for the admin Activity Logs page.
     *
     * Supported filter keys: search, action, start_date, end_date.
     *
     * @return array{total:int, rows:array}
     */
    public function getFiltered(array $filters, int $limit, int $offset): array
    {
        [$where, $types, $params] = $this->buildFilters($filters);

        $countRow = $this->query(
            "SELECT COUNT(*) AS cnt FROM activity_logs $where",
            $types, $params
        );
        $total = (int) ($countRow[0]['cnt'] ?? 0);

        $types   .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $rows = $this->query(
            $this->baseSelect . " $where ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?",
            $types, $params
        );

        return ['total' => $total, 'rows' => $rows];
    }

    /** Distinct action keys present in the table (for the filter dropdown). */
    public function distinctActions(): array
    {
        $rows = $this->query(
            "SELECT DISTINCT action FROM activity_logs ORDER BY action"
        );
        return array_column($rows, 'action');
    }

    // ---- Helpers ----------------------------------------------------

    private function buildFilters(array $f): array
    {
        $where  = [];
        $types  = '';
        $params = [];

        if (!empty($f['search'])) {
            $where[]  = '(user_name LIKE ? OR description LIKE ? OR action LIKE ?)';
            $types   .= 'sss';
            $like     = '%' . $f['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($f['action'])) {
            $where[]  = 'action = ?';
            $types   .= 's';
            $params[] = $f['action'];
        }
        if (!empty($f['start_date'])) {
            $where[]  = 'created_at >= ?';
            $types   .= 's';
            $params[] = $f['start_date'] . ' 00:00:00';
        }
        if (!empty($f['end_date'])) {
            $where[]  = 'created_at <= ?';
            $types   .= 's';
            $params[] = $f['end_date'] . ' 23:59:59';
        }

        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$clause, $types, $params];
    }
}
