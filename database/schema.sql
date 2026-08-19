-- ============================================================
-- SPK SAW Pemilihan Desain & Bahan Kain — SCHEMA (DDL Only)
-- Engine: MySQL/MariaDB, charset utf8mb4
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `spk_saw_kain`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `spk_saw_kain`;

-- --------------------------------------------------------
-- 1. pengguna
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pengguna`;
CREATE TABLE `pengguna` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `username`   VARCHAR(50)  NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','pengguna') NOT NULL DEFAULT 'pengguna',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. desain
-- --------------------------------------------------------
DROP TABLE IF EXISTS `desain`;
CREATE TABLE `desain` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `nama_desain` VARCHAR(100) NOT NULL,
  `kategori`    VARCHAR(50)  NOT NULL COMMENT 'Atasan/Bawahan/Terusan/dll',
  `foto`        VARCHAR(255) DEFAULT NULL COMMENT 'path file gambar',
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. bahan_kain
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bahan_kain`;
CREATE TABLE `bahan_kain` (
  `id`          INT         NOT NULL AUTO_INCREMENT,
  `nama_bahan`  VARCHAR(50) NOT NULL,
  `foto`        VARCHAR(255) DEFAULT NULL,
  `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. kriteria
-- --------------------------------------------------------
DROP TABLE IF EXISTS `kriteria`;
CREATE TABLE `kriteria` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `nama_kriteria` VARCHAR(50)  NOT NULL,
  `bobot`         DECIMAL(5,3) NOT NULL COMMENT '0.000–1.000; total semua baris harus = 1.000',
  `atribut`       ENUM('benefit','cost') NOT NULL DEFAULT 'benefit',
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. nilai_bahan  (junction: bahan_kain × kriteria)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `nilai_bahan`;
CREATE TABLE `nilai_bahan` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `bahan_kain_id` INT         NOT NULL,
  `kriteria_id`  INT          NOT NULL,
  `nilai`        DECIMAL(4,2) NOT NULL COMMENT 'skala 1.00–5.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bahan_kriteria` (`bahan_kain_id`, `kriteria_id`),
  CONSTRAINT `fk_nb_bahan`    FOREIGN KEY (`bahan_kain_id`) REFERENCES `bahan_kain` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_nb_kriteria` FOREIGN KEY (`kriteria_id`)  REFERENCES `kriteria`   (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. rekomendasi  (header — satu submit form = satu baris)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rekomendasi`;
CREATE TABLE `rekomendasi` (
  `id`                 INT         NOT NULL AUTO_INCREMENT,
  `pengguna_id`        INT         NOT NULL,
  `jenis_pakaian`      VARCHAR(50) NOT NULL,
  `tingkat_kenyamanan` VARCHAR(20) NOT NULL COMMENT 'Rendah/Sedang/Tinggi',
  `aktivitas`          VARCHAR(50) NOT NULL COMMENT 'Kerja/Olahraga/Formal/Santai/dll',
  `created_at`         TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_rek_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. detail_rekomendasi  (hasil ranking per submit)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detail_rekomendasi`;
CREATE TABLE `detail_rekomendasi` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `rekomendasi_id`   INT          NOT NULL,
  `bahan_kain_id`    INT          NOT NULL,
  `desain_id`        INT          DEFAULT NULL COMMENT 'NULL jika tidak ada desain cocok',
  `nilai_preferensi` DECIMAL(6,4) NOT NULL COMMENT 'hasil Vi SAW',
  `peringkat`        INT          NOT NULL COMMENT '1 = terbaik',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_dr_rekomendasi` FOREIGN KEY (`rekomendasi_id`) REFERENCES `rekomendasi`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dr_bahan`       FOREIGN KEY (`bahan_kain_id`)  REFERENCES `bahan_kain`   (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dr_desain`      FOREIGN KEY (`desain_id`)      REFERENCES `desain`        (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
