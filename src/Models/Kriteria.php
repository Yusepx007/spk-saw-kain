<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Kriteria — akses tabel `kriteria`
 */
class Kriteria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua kriteria.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nama_kriteria, bobot, atribut, created_at FROM kriteria ORDER BY id'
        );
        return $stmt->fetchAll();
    }

    /**
     * Alias getAll — untuk konsistensi pemanggilan di SAWService.
     */
    public function getAllAktif(): array
    {
        return $this->getAll();
    }

    /**
     * Ambil satu kriteria by ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM kriteria WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Hitung total bobot semua kriteria.
     */
    public function getTotalBobot(): float
    {
        $result = $this->db->query('SELECT SUM(bobot) FROM kriteria')->fetchColumn();
        return $result !== null ? round((float)$result, 3) : 0.0;
    }

    /**
     * Buat kriteria baru.
     */
    public function create(string $namaKriteria, float $bobot, string $atribut): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO kriteria (nama_kriteria, bobot, atribut) VALUES (?, ?, ?)'
        );
        $stmt->execute([$namaKriteria, $bobot, $atribut]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update kriteria.
     */
    public function update(int $id, string $namaKriteria, float $bobot, string $atribut): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE kriteria SET nama_kriteria = ?, bobot = ?, atribut = ? WHERE id = ?'
        );
        return $stmt->execute([$namaKriteria, $bobot, $atribut, $id]);
    }

    /**
     * Hapus kriteria (akan gagal kalau masih ada FK di nilai_bahan).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM kriteria WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Hitung jumlah kriteria.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM kriteria')->fetchColumn();
    }

    /**
     * Hitung total bobot KECUALI satu ID (untuk live preview saat edit).
     */
    public function getTotalBobotExclude(int $excludeId): float
    {
        $stmt = $this->db->prepare('SELECT SUM(bobot) FROM kriteria WHERE id != ?');
        $stmt->execute([$excludeId]);
        $result = $stmt->fetchColumn();
        return $result !== null ? round((float)$result, 3) : 0.0;
    }
}
