-- ============================================================
-- TITIPIN — Akun awal untuk pengujian alur verifikasi.
--
-- Tanpa akun Admin, klaim tidak bisa diverifikasi karena
-- persetujuan hanya lewat halaman Verifikasi.
--
--   psql -U postgres -d titipin -f database/seed_akun.sql
--
-- GANTI PASSWORD sebelum dipakai di luar lingkungan latihan.
--   admin@titipin.ac.id / admin123   (Admin)
--   super@titipin.ac.id / super123   (Super Admin)
--   mahasiswa@titipin.ac.id / mhs12345 (Mahasiswa)
-- ============================================================

BEGIN;

INSERT INTO users (nim, username, nama_lengkap, email, password, role, status_akun)
VALUES (
    '000000001',
    'admin_titipin',
    'Bayu Santoso',
    'admin@titipin.ac.id',
    '$2y$12$FYzTM81PVUo5QTUqMLOgGeLh.aPgYBDYyLegSy7xwxFilEY2StRqm',
    'Admin',
    'Aktif'
)
ON CONFLICT (nim) DO NOTHING;

INSERT INTO users (nim, username, nama_lengkap, email, password, role, status_akun)
VALUES (
    '000000002',
    'super_titipin',
    'Super Admin TITIPIN',
    'super@titipin.ac.id',
    '$2y$12$N.Wb5St5fN2ZwKxf6G.wru8Bzpv/yqVg6LEKtNzASG4MljiPRH.cC',
    'Super Admin',
    'Aktif'
)
ON CONFLICT (nim) DO NOTHING;

INSERT INTO users (nim, username, nama_lengkap, email, password, role, jurusan, kelas, status_akun)
VALUES (
    '000000003',
    'nadia_putri',
    'Nadia Putri',
    'mahasiswa@titipin.ac.id',
    '$2y$12$TvW3mAAcCNnpe0Gh6pK3HeWFJTrSoGsjfrxic9YIUWr6wQsIVtunG',
    'Mahasiswa',
    'Teknik Informatika',
    'TI-2A',
    'Aktif'
)
ON CONFLICT (nim) DO NOTHING;

COMMIT;
