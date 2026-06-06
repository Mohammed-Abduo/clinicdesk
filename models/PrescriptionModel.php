<?php
// ============================================================
// models/PrescriptionModel.php
// ============================================================

class PrescriptionModel extends BaseModel
{
    private string $baseSelect = "
        SELECT p.id, p.appointment_id, p.doctor_id, p.patient_id,
               p.notes, p.pdf_file, p.created_at,
               u_d.name AS doctor_name,
               u_p.name AS patient_name,
               a.appt_date, a.appt_time
        FROM prescriptions p
        JOIN doctors      d   ON d.id   = p.doctor_id
        JOIN users        u_d ON u_d.id = d.user_id
        JOIN users        u_p ON u_p.id = p.patient_id
        JOIN appointments a   ON a.id   = p.appointment_id
    ";

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE p.id = ? LIMIT 1",
            'i', [$id]
        );
        return $rows[0] ?? null;
    }

    public function findByAppointment(int $appointmentId): ?array
    {
        $rows = $this->query(
            $this->baseSelect . " WHERE p.appointment_id = ? LIMIT 1",
            'i', [$appointmentId]
        );
        return $rows[0] ?? null;
    }

    public function getForPatient(int $patientId): array
    {
        return $this->query(
            $this->baseSelect . " WHERE p.patient_id = ? ORDER BY p.created_at DESC",
            'i', [$patientId]
        );
    }

    public function getForDoctor(int $doctorId): array
    {
        return $this->query(
            $this->baseSelect . " WHERE p.doctor_id = ? ORDER BY p.created_at DESC",
            'i', [$doctorId]
        );
    }

    /**
     * All prescriptions (admin view) – no ownership filter.
     */
    public function getAll(): array
    {
        return $this->query(
            $this->baseSelect . " ORDER BY p.created_at DESC"
        );
    }

    public function countForPatient(int $patientId): int
    {
        $rows = $this->query(
            "SELECT COUNT(*) AS cnt FROM prescriptions WHERE patient_id = ?",
            'i', [$patientId]
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, notes, pdf_file)
             VALUES (?, ?, ?, ?, ?)",
            'iiiss',
            [
                $data['appointment_id'],
                $data['doctor_id'],
                $data['patient_id'],
                $data['notes'],
                $data['pdf_file'] ?? null,
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->query(
            "UPDATE prescriptions SET notes = ?, pdf_file = ? WHERE id = ?",
            'ssi',
            [$data['notes'], $data['pdf_file'] ?? null, $id]
        );
    }
}
