<?php
// ============================================================
// models/AppointmentModel.php
// ============================================================

class AppointmentModel extends BaseModel
{
    private string $baseSelect = "
        SELECT a.id, a.patient_id, a.doctor_id, a.appt_date, a.appt_time,
               a.status, a.reason, a.notes, a.created_at,
               u_p.name  AS patient_name,
               u_d.name  AS doctor_name,
               d.id      AS doctor_profile_id,
               s.name    AS specialization
        FROM appointments a
        JOIN users   u_p ON u_p.id = a.patient_id
        JOIN doctors d   ON d.id   = a.doctor_id
        JOIN users   u_d ON u_d.id = d.user_id
        JOIN specializations s ON s.id = d.specialization_id
    ";

    // ---- READ -------------------------------------------------------

    public function getFiltered(array $filters, int $limit, int $offset): array
    {
        [$where, $types, $params] = $this->buildFilters($filters);

        $countRow = $this->query(
            "SELECT COUNT(*) AS cnt FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users   u_p ON u_p.id = a.patient_id $where",
            $types, $params
        );
        $total = (int) ($countRow[0]['cnt'] ?? 0);

        $types   .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $rows = $this->query(
            $this->baseSelect . " $where ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            $types, $params
        );

        return ['total' => $total, 'rows' => $rows];
    }

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE a.id = ? LIMIT 1",
            'i', [$id]
        );
        return $rows[0] ?? null;
    }

    public function getForPatient(int $patientId, int $limit, int $offset): array
    {
        $countRow = $this->query(
            "SELECT COUNT(*) AS cnt FROM appointments WHERE patient_id = ?",
            'i', [$patientId]
        );
        $total = (int) ($countRow[0]['cnt'] ?? 0);

        $rows = $this->query(
            $this->baseSelect . " WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            'iii', [$patientId, $limit, $offset]
        );

        return ['total' => $total, 'rows' => $rows];
    }

    public function getForDoctor(int $doctorId, int $limit, int $offset): array
    {
        $countRow = $this->query(
            "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ?",
            'i', [$doctorId]
        );
        $total = (int) ($countRow[0]['cnt'] ?? 0);

        $rows = $this->query(
            $this->baseSelect . " WHERE a.doctor_id = ?
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            'iii', [$doctorId, $limit, $offset]
        );

        return ['total' => $total, 'rows' => $rows];
    }

    public function getTodayForDoctor(int $doctorId): array
    {
        return $this->query(
            $this->baseSelect . " WHERE a.doctor_id = ? AND a.appt_date = CURDATE()
             ORDER BY a.appt_time",
            'i', [$doctorId]
        );
    }

    public function getTodayCount(): int
    {
        $rows = $this->query(
            "SELECT COUNT(*) AS cnt FROM appointments WHERE appt_date = CURDATE()"
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    public function getWeeklyStats(): array
    {
        return $this->query(
            "SELECT DATE(appt_date) AS day, COUNT(*) AS cnt
             FROM appointments
             WHERE appt_date BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()
             GROUP BY DATE(appt_date)
             ORDER BY day"
        );
    }

    public function getMonthlyForDoctor(int $doctorId): array
    {
        return $this->query(
            "SELECT DATE(appt_date) AS day, COUNT(*) AS cnt
             FROM appointments
             WHERE doctor_id = ?
               AND MONTH(appt_date)  = MONTH(CURDATE())
               AND YEAR(appt_date)   = YEAR(CURDATE())
             GROUP BY DATE(appt_date)
             ORDER BY day",
            'i', [$doctorId]
        );
    }

    public function getRecent(int $limit = 10): array
    {
        return $this->query(
            $this->baseSelect . " ORDER BY a.created_at DESC LIMIT ?",
            'i', [$limit]
        );
    }

    /**
     * Last-7-days appointment counts grouped by status (admin dashboard).
     * Always returns all four statuses, defaulting to 0.
     */
    public function getWeeklyByStatus(): array
    {
        $rows = $this->query(
            "SELECT status, COUNT(*) AS cnt
             FROM appointments
             WHERE appt_date BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()
             GROUP BY status"
        );
        $out = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $r) {
            if (isset($out[$r['status']])) { $out[$r['status']] = (int) $r['cnt']; }
        }
        return $out;
    }

    /** Count a doctor's appointments, optionally restricted to one status. */
    public function countForDoctor(int $doctorId, ?string $status = null): int
    {
        if ($status !== null) {
            $rows = $this->query(
                "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ? AND status = ?",
                'is', [$doctorId, $status]
            );
        } else {
            $rows = $this->query(
                "SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ?",
                'i', [$doctorId]
            );
        }
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    /** Total appointments for a doctor in the current calendar month. */
    public function getMonthlyCountForDoctor(int $doctorId): int
    {
        $rows = $this->query(
            "SELECT COUNT(*) AS cnt FROM appointments
             WHERE doctor_id = ?
               AND MONTH(appt_date) = MONTH(CURDATE())
               AND YEAR(appt_date)  = YEAR(CURDATE())",
            'i', [$doctorId]
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    /** Count a patient's appointments, optionally restricted to one status. */
    public function countForPatient(int $patientId, ?string $status = null): int
    {
        if ($status !== null) {
            $rows = $this->query(
                "SELECT COUNT(*) AS cnt FROM appointments WHERE patient_id = ? AND status = ?",
                'is', [$patientId, $status]
            );
        } else {
            $rows = $this->query(
                "SELECT COUNT(*) AS cnt FROM appointments WHERE patient_id = ?",
                'i', [$patientId]
            );
        }
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    /** Appointment counts grouped by status for one patient (all four keys). */
    public function getStatusCountsForPatient(int $patientId): array
    {
        $rows = $this->query(
            "SELECT status, COUNT(*) AS cnt FROM appointments
             WHERE patient_id = ? GROUP BY status",
            'i', [$patientId]
        );
        $out = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $r) {
            if (isset($out[$r['status']])) { $out[$r['status']] = (int) $r['cnt']; }
        }
        return $out;
    }

    /**
     * The patient's next upcoming appointment (pending/confirmed, today or
     * later), soonest first, or null if none.
     */
    public function getNextForPatient(int $patientId): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE a.patient_id = ?
               AND a.appt_date >= CURDATE()
               AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC
             LIMIT 1",
            'i', [$patientId]
        );
        return $rows[0] ?? null;
    }

    // ---- Conflict check ----------------------------------------------

    public function isSlotTaken(int $doctorId, string $date, string $time, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = $this->query(
                "SELECT id FROM appointments
                 WHERE doctor_id = ? AND appt_date = ? AND appt_time = ? AND id != ?",
                'issi', [$doctorId, $date, $time, $excludeId]
            );
        } else {
            $rows = $this->query(
                "SELECT id FROM appointments
                 WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?",
                'iss', [$doctorId, $date, $time]
            );
        }
        return !empty($rows);
    }

    // ---- WRITE -------------------------------------------------------

    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason, status)
             VALUES (?, ?, ?, ?, ?, 'pending')",
            'iisss',
            [
                $data['patient_id'],
                $data['doctor_id'],
                $data['appt_date'],
                $data['appt_time'],
                $data['reason'] ?? null,
            ]
        );
        return $this->lastId();
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        return (bool) $this->query(
            "UPDATE appointments SET status = ?, notes = ? WHERE id = ?",
            'ssi', [$status, $notes, $id]
        );
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->query(
            "UPDATE appointments SET appt_date = ?, appt_time = ?, reason = ? WHERE id = ?",
            'sssi',
            [$data['appt_date'], $data['appt_time'], $data['reason'] ?? null, $id]
        );
    }

    // ---- Helpers for filter building ---------------------------------

    private function buildFilters(array $f): array
    {
        $where  = [];
        $types  = '';
        $params = [];

        if (!empty($f['doctor_id'])) {
            $where[]  = 'a.doctor_id = ?';
            $types   .= 'i';
            $params[] = (int) $f['doctor_id'];
        }
        if (!empty($f['patient_id'])) {
            $where[]  = 'a.patient_id = ?';
            $types   .= 'i';
            $params[] = (int) $f['patient_id'];
        }
        if (!empty($f['patient_name'])) {
            $where[]  = 'u_p.name LIKE ?';
            $types   .= 's';
            $params[] = '%' . $f['patient_name'] . '%';
        }
        if (!empty($f['status'])) {
            $where[]  = 'a.status = ?';
            $types   .= 's';
            $params[] = $f['status'];
        }
        if (!empty($f['start_date'])) {
            $where[]  = 'a.appt_date >= ?';
            $types   .= 's';
            $params[] = $f['start_date'];
        }
        if (!empty($f['end_date'])) {
            $where[]  = 'a.appt_date <= ?';
            $types   .= 's';
            $params[] = $f['end_date'];
        }

        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$clause, $types, $params];
    }
}
