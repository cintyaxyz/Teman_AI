-- ============================================================
-- Schema Database: teman_warga_ai
-- Teman Warga AI - Platform Pelayanan Publik
-- ============================================================

CREATE DATABASE IF NOT EXISTS teman_warga_ai
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE teman_warga_ai;

-- ─── Tabel Users ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id           INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150)    NOT NULL,
    email        VARCHAR(150)    UNIQUE NOT NULL,
    no_hp        VARCHAR(20)     DEFAULT NULL,
    nik          VARCHAR(16)     DEFAULT NULL,
    password     VARCHAR(255)    NOT NULL,
    role         ENUM('warga','petugas','admin') DEFAULT 'warga',
    wilayah      VARCHAR(100)    DEFAULT 'Sukamaju',
    avatar       VARCHAR(255)    DEFAULT NULL,
    is_verified  TINYINT(1)      DEFAULT 0,
    created_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Tabel Laporan ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS laporan (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    judul       VARCHAR(255)    NOT NULL,
    kategori    ENUM('jalan_rusak','sampah','banjir','lampu_jalan','fasilitas_umum','lainnya') NOT NULL,
    deskripsi   TEXT            NOT NULL,
    foto        VARCHAR(255)    DEFAULT NULL,
    lat         DECIMAL(10,7)   DEFAULT NULL,
    lng         DECIMAL(10,7)   DEFAULT NULL,
    alamat      VARCHAR(255)    DEFAULT NULL,
    status      ENUM('menunggu','diproses','selesai','ditolak') DEFAULT 'menunggu',
    is_anonim   TINYINT(1)      DEFAULT 0,
    prioritas   ENUM('rendah','sedang','tinggi','darurat') DEFAULT 'sedang',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Tabel Komentar Laporan ───────────────────────────────────
CREATE TABLE IF NOT EXISTS laporan_komentar (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    laporan_id  INT UNSIGNED    NOT NULL,
    user_id     INT UNSIGNED    NOT NULL,
    komentar    TEXT            NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Tabel Forum Warga ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS forum_posts (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    judul       VARCHAR(255)    NOT NULL,
    konten      TEXT            NOT NULL,
    kategori    VARCHAR(80)     DEFAULT 'Umum',
    likes       INT UNSIGNED    DEFAULT 0,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Tabel Komentar Forum ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS forum_komentar (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED    NOT NULL,
    user_id     INT UNSIGNED    NOT NULL,
    komentar    TEXT            NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id)  REFERENCES forum_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Tabel Notifikasi ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifikasi (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    judul       VARCHAR(150)    NOT NULL,
    pesan       TEXT            NOT NULL,
    tipe        ENUM('laporan','forum','sistem','darurat') DEFAULT 'sistem',
    dibaca      TINYINT(1)      DEFAULT 0,
    link        VARCHAR(255)    DEFAULT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── Tabel Password Reset ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(150)    NOT NULL,
    token       VARCHAR(64)     NOT NULL,
    expires_at  TIMESTAMP       NOT NULL,
    used        TINYINT(1)      DEFAULT 0,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ─── Data Demo (Seed) ─────────────────────────────────────────
-- Password untuk semua akun demo: "password123"
-- Hash: $2y$12$... (bcrypt)

INSERT IGNORE INTO users (nama_lengkap, email, no_hp, nik, password, role, wilayah) VALUES
('Rasya Pratama',   'rasya@temanwarga.id',   '081234567890', '3201010101010001', '$2y$12$yZ9GJpE9.GQr2DGvWB34buXuRX/dZLdDOqeUjI5lRi.JMSGbXWm9e', 'warga',   'Sukamaju'),
('Siti Petugas',    'petugas@temanwarga.id', '081234567891', '3201010101010002', '$2y$12$yZ9GJpE9.GQr2DGvWB34buXuRX/dZLdDOqeUjI5lRi.JMSGbXWm9e', 'petugas', 'Sukamaju'),
('Admin Sistem',    'admin@temanwarga.id',   '081234567892', '3201010101010003', '$2y$12$yZ9GJpE9.GQr2DGvWB34buXuRX/dZLdDOqeUjI5lRi.JMSGbXWm9e', 'admin',   'Sukamaju');

INSERT IGNORE INTO laporan (user_id, judul, kategori, deskripsi, lat, lng, alamat, status, prioritas) VALUES
(1, 'Jalan berlubang di depan SMP 2',          'jalan_rusak',    'Jalan berlubang cukup dalam, berbahaya untuk pengendara motor',     -6.2146, 106.8451, 'Jl. Melati, Kec. Sukamaju',    'diproses', 'tinggi'),
(1, 'Tumpukan sampah di pinggir sungai',       'sampah',         'Sampah menumpuk selama 3 hari di pinggir sungai kecil RT 04',      -6.2150, 106.8455, 'Jl. Cempaka, Kec. Sukamaju',   'menunggu', 'sedang'),
(1, 'Lampu PJU mati di gang Mawar',            'lampu_jalan',    'Lampu jalan sudah mati 2 minggu, gelap total di malam hari',       -6.2155, 106.8460, 'Gang Mawar, Kec. Sukamaju',    'selesai',  'sedang'),
(1, 'Genangan air di persimpangan Jl. Pitara', 'banjir',         'Setiap hujan deras ada genangan setinggi 30cm, macet parah',       -6.2160, 106.8465, 'Jl. Pitara, Kec. Sukamaju',    'menunggu', 'tinggi');

INSERT IGNORE INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES
(1, 'Laporan Diproses',   'Laporan Anda tentang "Jalan berlubang di depan SMP 2" sedang diproses oleh Dinas PU.', 'laporan', 'detail_Laporan.php?id=1'),
(1, 'Selamat Datang!',    'Selamat bergabung di Teman Warga AI. Bersama kita wujudkan lingkungan yang lebih baik!', 'sistem', 'beranda.php'),
(1, 'Forum Baru',         'Ada pengumuman baru dari Pak RT 04 di Forum Warga. Cek sekarang!', 'forum', 'forum_warga.php');
