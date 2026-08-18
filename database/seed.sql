-- ============================================================
-- SPK SAW — SEED DATA (Data Awal)
-- Jalankan SETELAH schema.sql
-- ============================================================

USE `spk_saw_kain`;

-- --------------------------------------------------------
-- Akun Admin Default
-- password: admin123 (bcrypt hash)
-- WAJIB DIGANTI SETELAH INSTALASI!
-- --------------------------------------------------------
-- Password: admin  → admin123
-- Password: user   → user123
INSERT INTO `pengguna` (`nama`, `username`, `password`, `role`) VALUES
('Administrator', 'admin', '$2y$12$CgdD.uqLi8O2ZpdHdMJfwuo/9O4RW2LHjC/ZfLEwNQGmDnvWlYnHW', 'admin'),
('Pengguna Demo',  'user',  '$2y$12$Rmx7u.jiIvhhIKTmxSrBYuKYFIIwLnmS9/Aud5itAHlcH3dc5gJNS', 'pengguna');

-- --------------------------------------------------------
-- Bahan Kain (5 bahan sesuai PRD §5)
-- --------------------------------------------------------
INSERT INTO `bahan_kain` (`nama_bahan`) VALUES
('Rayon'),
('Katun'),
('Linen'),
('Flannel'),
('Jersey');

-- --------------------------------------------------------
-- Kriteria (5 kriteria, bobot sesuai PRD §5)
-- --------------------------------------------------------
INSERT INTO `kriteria` (`nama_kriteria`, `bobot`, `atribut`) VALUES
('Kenyamanan',          0.234, 'benefit'),
('Ketebalan',           0.174, 'benefit'),
('Tekstur',             0.209, 'benefit'),
('Kemudahan Perawatan', 0.174, 'benefit'),
('Kecocokan Aktivitas', 0.209, 'benefit');

-- --------------------------------------------------------
-- Nilai Bahan (25 baris = 5 bahan × 5 kriteria)
-- Sumber: PRD §5 — rata-rata hasil wawancara
--
-- Urutan kriteria_id: 1=Kenyamanan, 2=Ketebalan, 3=Tekstur,
--                     4=Kemudahan Perawatan, 5=Kecocokan Aktivitas
-- Urutan bahan_kain_id: 1=Rayon, 2=Katun, 3=Linen, 4=Flannel, 5=Jersey
-- --------------------------------------------------------
INSERT INTO `nilai_bahan` (`bahan_kain_id`, `kriteria_id`, `nilai`) VALUES
-- Rayon
(1, 1, 5.00), (1, 2, 3.00), (1, 3, 4.25), (1, 4, 2.25), (1, 5, 4.25),
-- Katun
(2, 1, 4.25), (2, 2, 3.25), (2, 3, 3.50), (2, 4, 3.25), (2, 5, 3.25),
-- Linen
(3, 1, 4.25), (3, 2, 3.50), (3, 3, 3.75), (3, 4, 3.25), (3, 5, 3.00),
-- Flannel
(4, 1, 3.75), (4, 2, 3.00), (4, 3, 3.00), (4, 4, 3.50), (4, 5, 3.50),
-- Jersey
(5, 1, 4.25), (5, 2, 3.00), (5, 3, 3.25), (5, 4, 3.75), (5, 5, 3.25);

-- --------------------------------------------------------
-- Contoh Data Desain Awal
-- --------------------------------------------------------
INSERT INTO `desain` (`nama_desain`, `kategori`) VALUES
('Kemeja Polos',       'Atasan'),
('Blouse Casual',      'Atasan'),
('Kaos Polos',         'Atasan'),
('Celana Chino',       'Bawahan'),
('Rok A-Line',         'Bawahan'),
('Celana Jeans',       'Bawahan'),
('Gamis Sederhana',    'Terusan'),
('Dress Casual',       'Terusan'),
('Jumpsuit Santai',    'Terusan');
