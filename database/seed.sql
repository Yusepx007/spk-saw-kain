-- ============================================================
-- SPK SAW — SEED DATA (Data Awal)
-- Jalankan SETELAH schema.sql
-- ============================================================

USE `spk_saw_kain`;

-- --------------------------------------------------------
-- Akun Pengguna (Dataset User sesuai skripsi)
-- --------------------------------------------------------
-- Admin    → Admin/Admin123
-- Penjahit → masing-masing password sesuai dataset
INSERT INTO `pengguna` (`nama`, `username`, `password`, `role`) VALUES
('Administrator', 'Admin',       '$2y$10$m6KanqXxZzXM8Gp2r/IcTugfn.36GOrStpWKg.9eMLkd32yOegkqi', 'admin'),
('Dedi Suhendi',  'dedisuhendi', '$2y$10$KeorbwOeNUUC.TLaxrHdK.UW/TcxW3of9JCIVbCeBklA1KrrVhjn6', 'pengguna'),
('Nisa Amelia',   'nisaamelia',  '$2y$10$5cBEWCDGN6YIVWZOKKJWzuBBc39OqxeEs9/KNycHhAq6RkeWAJIRi', 'pengguna'),
('Karin Sri',     'karinsri',    '$2y$10$FpjX5oOT4szkkOoOSylvO.qGWxSoKMwasHf3EeGpUkTX9MWX1kMLi', 'pengguna');

-- --------------------------------------------------------
-- Bahan Kain (5 bahan + foto)
-- --------------------------------------------------------
INSERT INTO `bahan_kain` (`nama_bahan`, `foto`) VALUES
('Rayon',   'Kain/kain_rayon.jpg.jpeg'),
('Katun',   'Kain/kain_katun.jpg.jpeg'),
('Linen',   'Kain/kainLinen.jpg.jpeg'),
('Flannel', 'Kain/kain_flannel.jpg.jpeg'),
('Jersey',  'Kain/KainJersey.jpg.jpeg');

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
-- Desain Pakaian + foto (sesuai folder img/Desain/)
-- --------------------------------------------------------
INSERT INTO `desain` (`nama_desain`, `kategori`, `foto`) VALUES
('Kemeja Polos',    'Atasan',  'Desain/kemeja_polos.jpg.jpeg'),
('Blouse Casual',   'Atasan',  'Desain/kaos_polos.jpg.jpeg'),
('Kaos Polos',      'Atasan',  'Desain/kaos_polos.jpg.jpeg'),
('Celana Chino',    'Bawahan', 'Desain/celana_chino.jpg.jpeg'),
('Rok A-Line',      'Bawahan', 'Desain/rok a line.jpg.jpeg'),
('Celana Jeans',    'Bawahan', 'Desain/celana_jeans.jpg.jpeg'),
('Gamis Sederhana', 'Terusan', 'Desain/gamis_sederhana.jpg.jpeg'),
('Dress Casual',    'Terusan', 'Desain/dress_casual.jpg.jpeg'),
('Jumpsuit Santai', 'Terusan', 'Desain/jumpsuit_santai.jpg.jpeg');
