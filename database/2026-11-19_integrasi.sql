-- ============================================================
-- TITIPIN — Migrasi database yang SUDAH ADA ke struktur baru.
--
-- Aman dijalankan berulang kali (idempotent) dan tidak menghapus
-- data yang sudah ada.
--
--   psql -U postgres -d titipin -f database/2026-11-19_integrasi.sql
-- ============================================================

-- ---------- 1. Peran baru: Super Admin ------------------------------
-- ALTER TYPE ... ADD VALUE tidak boleh dipakai dalam transaksi yang
-- sama dengan pemakaian nilainya, jadi dijalankan terpisah.
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_enum e
        JOIN pg_type t ON t.oid = e.enumtypid
        WHERE t.typname = 'user_role' AND e.enumlabel = 'Super Admin'
    ) THEN
        ALTER TYPE user_role ADD VALUE 'Super Admin';
    END IF;
END $$;

BEGIN;

-- ---------- 2. Kolom tambahan laporan barang ------------------------
ALTER TABLE barang ADD COLUMN IF NOT EXISTS lokasi_detail  text;
ALTER TABLE barang ADD COLUMN IF NOT EXISTS waktu_kejadian varchar(40);
ALTER TABLE barang ADD COLUMN IF NOT EXISTS prioritas      boolean NOT NULL DEFAULT false;

-- ---------- 3. Kolom tambahan laporan fasilitas ---------------------
ALTER TABLE kerusakan_fasilitas ADD COLUMN IF NOT EXISTS lokasi_detail  text;
ALTER TABLE kerusakan_fasilitas ADD COLUMN IF NOT EXISTS waktu_kejadian varchar(40);

-- ---------- 4. Status akun pengguna --------------------------------
ALTER TABLE users ADD COLUMN IF NOT EXISTS status_akun varchar(20) NOT NULL DEFAULT 'Aktif';

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'users_status_akun_check'
    ) THEN
        ALTER TABLE users
            ADD CONSTRAINT users_status_akun_check
            CHECK (status_akun IN ('Aktif', 'Ditangguhkan'));
    END IF;
END $$;

-- ---------- 5. Tabel pengaturan sistem -----------------------------
CREATE TABLE IF NOT EXISTS pengaturan (
    kunci text PRIMARY KEY,
    nilai text NOT NULL
);

-- ---------- 5b. Keterangan ciri khusus pada klaim -------------------
-- Formulir klaim meminta ciri khusus barang sebagai bahan verifikasi
-- petugas; tanpa kolom ini keterangan itu hilang setelah dikirim.
ALTER TABLE klaim ADD COLUMN IF NOT EXISTS ciri_khusus text;

INSERT INTO pengaturan (kunci, nilai) VALUES
    ('nama_aplikasi',    'TITIPIN Campus Care'),
    ('retensi_hari',     '90'),
    ('target_selesai',   '85'),
    ('notifikasi_email', 'Aktif')
ON CONFLICT (kunci) DO NOTHING;

-- ---------- 6. Lokasi lantai sesuai formulir terbaru ----------------
-- Sequence pada database lama tertinggal karena id diisi manual
-- (lokasi 1–6, kategori 1–5). Tanpa sinkronisasi, INSERT berikutnya
-- langsung menabrak primary key.
SELECT setval(pg_get_serial_sequence('lokasi', 'id_lokasi'),
              GREATEST((SELECT COALESCE(max(id_lokasi), 0) FROM lokasi), 1));

SELECT setval(pg_get_serial_sequence('kategori', 'id_kategori'),
              GREATEST((SELECT COALESCE(max(id_kategori), 0) FROM kategori), 1));

-- Dropdown frontend memakai Lantai 1–8 dan Halaman Parkir, sementara
-- tabel lokasi hanya berisi nama ruangan. Baris berikut melengkapi
-- pilihan tersebut tanpa mengubah data ruangan yang sudah ada.
INSERT INTO lokasi (nama_lokasi, gedung)
SELECT v.nama, v.gedung
FROM (VALUES
    ('Lantai 1', 'Gedung Utama'),
    ('Lantai 2', 'Gedung Utama'),
    ('Lantai 3', 'Gedung Utama'),
    ('Lantai 4', 'Gedung Utama'),
    ('Lantai 5', 'Gedung Utama'),
    ('Lantai 6', 'Gedung Utama'),
    ('Lantai 7', 'Gedung Utama'),
    ('Lantai 8', 'Gedung Utama'),
    ('Halaman Parkir', 'Area Luar')
) AS v(nama, gedung)
WHERE NOT EXISTS (
    SELECT 1 FROM lokasi l WHERE l.nama_lokasi = v.nama AND l.gedung = v.gedung
);

-- Ruangan yang sudah ada diberi gedung agar tidak menumpuk di "Lainnya".
UPDATE lokasi SET gedung = 'Gedung Utama'
WHERE gedung IS NULL AND nama_lokasi IN ('Kelas', 'Lobby', 'Musholah', 'Ruang Baca', 'Kamar Mandi');

UPDATE lokasi SET gedung = 'Gedung Utama'
WHERE gedung IS NULL AND nama_lokasi = 'Kantin lt 8';

-- ---------- 7. Indeks untuk filter dan laporan ---------------------
CREATE INDEX IF NOT EXISTS barang_status_idx   ON barang (status);
CREATE INDEX IF NOT EXISTS barang_pelapor_idx  ON barang (pelapor_id);
CREATE INDEX IF NOT EXISTS fasilitas_status_idx ON kerusakan_fasilitas (status);

-- ---------- 8. Master lokasi: lantai + ruangan ---------------------
-- Formulir laporan memakai dua pilihan: lantai (mis. "Lantai 5 Barat")
-- lalu ruangan pada lantai tersebut. Baris lama dinonaktifkan agar tidak
-- muncul di dropdown, tetapi riwayat laporan tetap utuh karena barisnya
-- tidak dihapus.
ALTER TABLE lokasi ADD COLUMN IF NOT EXISTS aktif boolean NOT NULL DEFAULT true;

UPDATE lokasi SET aktif = false
WHERE gedung IS NULL OR gedung IN ('Gedung Utama', 'Area Luar');

INSERT INTO lokasi (nama_lokasi, gedung, aktif)
SELECT v.ruangan, v.lantai, true
FROM (VALUES
    ('R.501',  'Lantai 5 Barat'),
    ('R.502',  'Lantai 5 Barat'),
    ('R.503',  'Lantai 5 Barat'),
    ('Lab 5A', 'Lantai 5 Barat'),
    ('R.601',  'Lantai 6 Timur'),
    ('R.602',  'Lantai 6 Timur'),
    ('R.603',  'Lantai 6 Timur'),
    ('Lab 6A', 'Lantai 6 Timur'),
    ('R.611',  'Lantai 6 Barat'),
    ('R.612',  'Lantai 6 Barat'),
    ('R.613',  'Lantai 6 Barat'),
    ('R.701',  'Lantai 7 Timur'),
    ('R.702',  'Lantai 7 Timur'),
    ('R.703',  'Lantai 7 Timur'),
    ('R.711',  'Lantai 7 Barat'),
    ('R.712',  'Lantai 7 Barat'),
    ('R.713',  'Lantai 7 Barat'),
    ('R.801',  'Lantai 8 Timur'),
    ('R.802',  'Lantai 8 Timur'),
    ('R.803',  'Lantai 8 Timur'),
    ('Aula 8', 'Lantai 8 Timur'),
    ('R.811',  'Lantai 8 Barat'),
    ('R.812',  'Lantai 8 Barat'),
    ('R.813',  'Lantai 8 Barat'),
    ('R.814',  'Lantai 8 Barat')
) AS v(ruangan, lantai)
WHERE NOT EXISTS (
    SELECT 1 FROM lokasi l
    WHERE l.nama_lokasi = v.ruangan AND l.gedung = v.lantai
);

-- ---------- 9. Ruangan bernomor 1–8 pada setiap lantai ---------------
-- Nama ruangan asli belum tersedia, jadi dipakai penomoran sementara.
-- Baris dummy sebelumnya dinonaktifkan (bukan dihapus) agar laporan lama
-- yang mungkin sudah memakainya tetap menunjuk lokasi yang benar.
UPDATE lokasi
SET aktif = false
WHERE gedung LIKE 'Lantai %'
  AND nama_lokasi NOT LIKE 'Ruangan %';

INSERT INTO lokasi (nama_lokasi, gedung, aktif)
SELECT 'Ruangan ' || n, lantai, true
FROM (VALUES
    ('Lantai 5 Barat'),
    ('Lantai 6 Timur'),
    ('Lantai 6 Barat'),
    ('Lantai 7 Timur'),
    ('Lantai 7 Barat'),
    ('Lantai 8 Timur'),
    ('Lantai 8 Barat')
) AS f(lantai)
CROSS JOIN generate_series(1, 8) AS n
WHERE NOT EXISTS (
    SELECT 1 FROM lokasi l
    WHERE l.nama_lokasi = 'Ruangan ' || n AND l.gedung = f.lantai
);

COMMIT;
