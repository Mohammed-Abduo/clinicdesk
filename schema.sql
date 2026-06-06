-- ============================================================
-- ClinicDesk Database Schema
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS clinicdesk_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clinicdesk_db;

-- ------------------------------------------------------------
-- 1. users
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(180) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone       VARCHAR(20)  DEFAULT NULL,
    avatar      VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. specializations
-- ------------------------------------------------------------
CREATE TABLE specializations (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_spec_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. doctors
-- ------------------------------------------------------------
CREATE TABLE doctors (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED NOT NULL,
    specialization_id   INT UNSIGNED NOT NULL,
    bio                 TEXT         DEFAULT NULL,
    consultation_fee    DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    available_days      SET('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL DEFAULT 'Mon,Tue,Wed,Thu,Fri',
    photo               VARCHAR(255) DEFAULT NULL,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doctors_user (user_id),
    CONSTRAINT fk_doctors_user   FOREIGN KEY (user_id)           REFERENCES users(id)           ON DELETE CASCADE,
    CONSTRAINT fk_doctors_spec   FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. appointments
-- ------------------------------------------------------------
CREATE TABLE appointments (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_id  INT UNSIGNED NOT NULL,
    doctor_id   INT UNSIGNED NOT NULL,
    appt_date   DATE         NOT NULL,
    appt_time   TIME         NOT NULL,
    status      ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    reason      TEXT         DEFAULT NULL,
    notes       TEXT         DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_no_double_book (doctor_id, appt_date, appt_time),
    KEY idx_appt_date   (appt_date),
    KEY idx_appt_status (status),
    CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id) REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_appt_doctor  FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. prescriptions
-- ------------------------------------------------------------
CREATE TABLE prescriptions (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id  INT UNSIGNED NOT NULL,
    doctor_id       INT UNSIGNED NOT NULL,
    patient_id      INT UNSIGNED NOT NULL,
    notes           TEXT         NOT NULL,
    pdf_file        VARCHAR(255) DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rx_appointment (appointment_id),
    CONSTRAINT fk_rx_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_rx_doctor      FOREIGN KEY (doctor_id)      REFERENCES doctors(id)      ON DELETE CASCADE,
    CONSTRAINT fk_rx_patient     FOREIGN KEY (patient_id)     REFERENCES users(id)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. activity_logs  (audit trail)
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED DEFAULT NULL,          -- NULL for failed logins / deleted users
    user_name   VARCHAR(120) DEFAULT NULL,          -- snapshot so logs survive user deletion
    action      VARCHAR(60)  NOT NULL,              -- e.g. login, user_create, appt_update
    description VARCHAR(255) DEFAULT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,          -- IPv4/IPv6
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_user    (user_id),
    KEY idx_logs_action  (action),
    KEY idx_logs_created (created_at),
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Seed data
-- ------------------------------------------------------------

-- Default admin  (password: Admin@1234)
INSERT INTO users (name, email, password, role, is_active)
VALUES (
    'System Admin',
    'admin@clinicdesk.local',
    '$2y$12$sKNm8UBYM3ulip/BLuLyY.WaTcBu9DEJYr7qUGxqmqZkCth0OwPkm', -- bcrypt (cost 12) of "Admin@1234"
    'admin',
    1
);

-- Default specializations
INSERT INTO specializations (name) VALUES
    ('General Medicine'),
    ('Cardiology'),
    ('Dermatology'),
    ('Orthopedics'),
    ('Pediatrics'),
    ('Neurology'),
    ('Ophthalmology'),
    ('ENT');
