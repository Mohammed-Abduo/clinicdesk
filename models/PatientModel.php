<?php
declare(strict_types=1);
require_once __DIR__ . '/BaseModel.php';

class PatientModel extends BaseModel {
    public function getPatients(): array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role='patient'");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
