-- ============================================================
--  inventaris_db  |  Tugas Rutin 8 - CRUD Inventaris
--  Cara pakai: import file ini via phpMyAdmin (tab Import)
--  atau CLI:  mysql -u root -p < database.sql
--  Aman dijalankan berulang kali (tabel lama akan di-reset).
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS kategori;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- TABEL 1 : kategori
-- ------------------------------------------------------------
CREATE TABLE kategori (
    id_kategori   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_kategori VARCHAR(60)  NOT NULL,
    deskripsi     VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_kategori),
    UNIQUE KEY uq_kategori_nama (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABEL 2 : supplier
-- ------------------------------------------------------------
CREATE TABLE supplier (
    id_supplier   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_supplier VARCHAR(100) NOT NULL,
    kontak        VARCHAR(80)  DEFAULT NULL,
    telepon       VARCHAR(25)  DEFAULT NULL,
    alamat        VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_supplier),
    UNIQUE KEY uq_supplier_nama (nama_supplier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABEL 3 : produk  (FK ke kategori & supplier)
-- ------------------------------------------------------------
CREATE TABLE produk (
    id_produk   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    kode_produk VARCHAR(20)   NOT NULL,
    nama_produk VARCHAR(100)  NOT NULL,
    id_kategori INT UNSIGNED  NOT NULL,
    id_supplier INT UNSIGNED  NOT NULL,
    harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok        INT UNSIGNED  NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_produk),
    UNIQUE KEY uq_produk_kode (kode_produk),
    KEY idx_produk_kategori (id_kategori),
    KEY idx_produk_supplier (id_supplier),
    CONSTRAINT fk_produk_kategori
        FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_produk_supplier
        FOREIGN KEY (id_supplier) REFERENCES supplier (id_supplier)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABEL BONUS : log_aktivitas
-- Sengaja TANPA foreign key agar riwayat tetap ada
-- meskipun produk yang bersangkutan sudah dihapus.
-- ------------------------------------------------------------
CREATE TABLE log_aktivitas (
    id_log         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    aksi           ENUM('TAMBAH','UBAH','HAPUS') NOT NULL,
    id_produk_ref  INT UNSIGNED DEFAULT NULL,
    kode_produk    VARCHAR(20)  NOT NULL,
    nama_produk    VARCHAR(100) NOT NULL,
    detail         TEXT         DEFAULT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    KEY idx_log_waktu (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DATA SEED  (minimal 5 baris per tabel)
-- ============================================================
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Elektronik',          'Perangkat elektronik kantor dan presentasi'),
('Alat Tulis',          'Perlengkapan tulis dan kertas'),
('Perabot Kantor',      'Meja, kursi, lemari, dan furnitur lainnya'),
('Perangkat Jaringan',  'Router, switch, dan kabel jaringan'),
('Kebersihan',          'Bahan dan alat kebersihan ruangan'),
('Aksesori Komputer',   'Mouse, keyboard, dan perlengkapan pendukung');

INSERT INTO supplier (nama_supplier, kontak, telepon, alamat) VALUES
('PT Medan Elektronik Jaya',      'Budi Santoso',   '061-4521001', 'Jl. Gatot Subroto No. 12, Medan'),
('CV Sumatera Stationery',        'Rina Lubis',     '061-4522002', 'Jl. Sisingamangaraja No. 45, Medan'),
('UD Karya Mebel Deli',           'Hendra Sitorus', '061-4523003', 'Jl. Yos Sudarso No. 8, Medan'),
('PT Nusantara Netindo',          'Dewi Pane',      '061-4524004', 'Jl. Ringroad No. 77, Medan'),
('CV Bersih Sejahtera',           'Ahmad Nasution', '061-4525005', 'Jl. Setia Budi No. 21, Medan'),
('Toko Aksesoris Digital Medan',  'Sari Harahap',   '061-4526006', 'Jl. Iskandar Muda No. 30, Medan');

INSERT INTO produk (kode_produk, nama_produk, id_kategori, id_supplier, harga, stok) VALUES
('ELK-001', 'Monitor LED 24 inci',            1, 1, 1850000, 12),
('ELK-002', 'Proyektor Portable HD',          1, 1, 3750000,  4),
('ATK-001', 'Kertas HVS A4 70gsm (rim)',      2, 2,   52000, 80),
('ATK-002', 'Pulpen Gel Hitam (lusin)',       2, 2,   36000, 45),
('ATK-003', 'Spidol Whiteboard (dus)',        2, 2,   68000,  3),
('PRB-001', 'Kursi Kerja Ergonomis',          3, 3,  875000,  9),
('PRB-002', 'Meja Kerja 120 cm',              3, 3, 1250000,  6),
('PRB-003', 'Lemari Arsip 4 Laci',            3, 3, 2100000,  0),
('JRG-001', 'Router WiFi Dual Band',          4, 4,  495000, 15),
('JRG-002', 'Kabel LAN Cat6 (roll 100 m)',    4, 4,  640000,  7),
('JRG-003', 'Switch 8 Port Gigabit',          4, 4,  320000, 11),
('KBR-001', 'Cairan Pembersih Lantai 5 L',    5, 5,   78000, 22),
('KBR-002', 'Tisu Gulung (pak)',              5, 5,   45000, 60),
('AKS-001', 'Mouse Wireless',                 6, 6,  125000, 30),
('AKS-002', 'Keyboard Mekanik',               6, 6,  450000,  5);
