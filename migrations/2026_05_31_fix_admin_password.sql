-- ============================================================
-- Migration: 2026-05-31  Fix invalid seeded admin password hash
-- ============================================================
--
-- The original schema.sql seeded the admin account with a bcrypt
-- string whose cost field had been edited from 10 to 12 without
-- recomputing the checksum, making it verify against NO password.
-- This updates any existing admin row to a valid cost-12 hash of
-- "Admin@1234".
--
-- Run ONLY if you imported the original schema.sql before this fix.
-- A fresh import of the corrected schema.sql does not need this.
--
--   mysql -u root -p clinicdesk_db < migrations/2026_05_31_fix_admin_password.sql
--
-- IMPORTANT: change the admin password after logging in.
-- ============================================================

USE clinicdesk_db;

UPDATE users
SET password = '$2y$12$sKNm8UBYM3ulip/BLuLyY.WaTcBu9DEJYr7qUGxqmqZkCth0OwPkm'
WHERE email = 'admin@clinicdesk.local'
  AND role  = 'admin';
