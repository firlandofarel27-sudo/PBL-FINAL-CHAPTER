-- ============================================================
-- TITIPIN — Skema penuh database PostgreSQL
--
-- Dibuat dari struktur tabel yang sudah dipakai backend lama,
-- ditambah kolom baru untuk integrasi frontend PHP.
--
-- Jalankan pada database kosong:
--   psql -U postgres -c "CREATE DATABASE titipin"
--   psql -U postgres -d titipin -f database/schema.sql
-- ============================================================

BEGIN;

-- ---------- Tipe enum -----------------------------------------------
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role') THEN
        CREATE TYPE user_role AS ENUM ('Mahasiswa', 'Admin', 'Super Admin');
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'jenis_lapor') THEN
        CREATE TYPE jenis_lapor AS ENUM ('Lost', 'Found');
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_barang') THEN
        CREATE TYPE status_barang AS ENUM ('Menunggu Verifikasi', 'Tersedia', 'Selesai');
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_perbaikan') THEN
        CREATE TYPE status_perbaikan AS ENUM ('Dilaporkan', 'Proses Perbaikan', 'Selesai');
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_klaim') THEN
        CREATE TYPE status_klaim AS ENUM ('Diajukan', 'Disetujui', 'Ditolak');
    END IF;
END $$;

-- ---------- Master data ---------------------------------------------
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori   serial PRIMARY KEY,
    nama_kategori varchar(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS lokasi (
    id_lokasi   serial PRIMARY KEY,
    nama_lokasi varchar(100) NOT NULL,
    gedung      varchar(50),
    aktif       boolean NOT NULL DEFAULT true
);

-- ---------- Pengguna ------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    nim          varchar(50) PRIMARY KEY,
    username     varchar(100) NOT NULL,
    nama_lengkap varchar(255) NOT NULL,
    email        varchar(255) NOT NULL UNIQUE,
    password     varchar(255) NOT NULL,
    foto_profil  text,
    role         user_role NOT NULL DEFAULT 'Mahasiswa',
    jurusan      varchar(100),
    kelas        varchar(50),
    no_telepon   varchar(20),
    status_akun  varchar(20) NOT NULL DEFAULT 'Aktif'
                 CHECK (status_akun IN ('Aktif', 'Ditangguhkan'))
);

-- ---------- Laporan barang ------------------------------------------
CREATE TABLE IF NOT EXISTS barang (
    id_barang       serial PRIMARY KEY,
    nama_barang     varchar(255) NOT NULL,
    kategori_id     integer REFERENCES kategori(id_kategori) ON DELETE SET NULL,
    lokasi_id       integer REFERENCES lokasi(id_lokasi) ON DELETE SET NULL,
    lokasi_detail   text,
    waktu_kejadian  varchar(40),
    jenis           jenis_lapor NOT NULL,
    deskripsi       text,
    tanggal         date,
    foto            text,
    status          status_barang DEFAULT 'Menunggu Verifikasi',
    prioritas       boolean NOT NULL DEFAULT false,
    pelapor_id      varchar(50) REFERENCES users(nim) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS barang_status_idx ON barang (status);
CREATE INDEX IF NOT EXISTS barang_pelapor_idx ON barang (pelapor_id);

-- ---------- Laporan kerusakan fasilitas -----------------------------
CREATE TABLE IF NOT EXISTS kerusakan_fasilitas (
    id_kerusakan        serial PRIMARY KEY,
    nama_fasilitas      varchar(255) NOT NULL,
    kategori_id         integer REFERENCES kategori(id_kategori) ON DELETE SET NULL,
    lokasi_id           integer REFERENCES lokasi(id_lokasi) ON DELETE SET NULL,
    lokasi_detail       text,
    waktu_kejadian      varchar(40),
    deskripsi_kerusakan text,
    foto                text,
    tanggal_lapor       date NOT NULL,
    status              status_perbaikan DEFAULT 'Dilaporkan',
    pelapor_id          varchar(50) REFERENCES users(nim) ON DELETE CASCADE,
    catatan_teknisi     text
);

CREATE INDEX IF NOT EXISTS fasilitas_status_idx ON kerusakan_fasilitas (status);

-- ---------- Klaim ---------------------------------------------------
CREATE TABLE IF NOT EXISTS klaim (
    id_klaim            serial PRIMARY KEY,
    kode_tiket          varchar(20) UNIQUE,
    item_id             integer REFERENCES barang(id_barang) ON DELETE CASCADE,
    claimer_id          varchar(50) REFERENCES users(nim) ON DELETE CASCADE,
    admin_id            varchar(50) REFERENCES users(nim) ON DELETE SET NULL,
    bukti_kepemilikan   text NOT NULL,
    bukti_serah_terima  text,
    ciri_khusus         text,
    status_klaim        status_klaim DEFAULT 'Diajukan',
    redeem_code_hash    text,
    redeem_expires_at   timestamptz,
    redeem_used_at      timestamptz,
    alasan_penolakan    text
);

CREATE INDEX IF NOT EXISTS klaim_status_expires_idx ON klaim (status_klaim, redeem_expires_at);

-- Satu pengguna hanya boleh punya satu klaim pending per barang.
CREATE UNIQUE INDEX IF NOT EXISTS klaim_satu_pending_per_user_barang
    ON klaim (item_id, claimer_id)
    WHERE status_klaim = 'Diajukan';

-- Satu barang hanya boleh punya satu klaim disetujui.
CREATE UNIQUE INDEX IF NOT EXISTS klaim_satu_disetujui_per_barang
    ON klaim (item_id)
    WHERE status_klaim = 'Disetujui';

-- ---------- Pengaturan sistem ---------------------------------------
CREATE TABLE IF NOT EXISTS pengaturan (
    kunci text PRIMARY KEY,
    nilai text NOT NULL
);

COMMIT;
