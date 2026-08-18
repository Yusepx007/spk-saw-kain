<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Desain — akses tabel `desain`
 */
class Desain
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua desain, urut terbaru dulu.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nama_desain, kategori, foto, created_at FROM desain ORDER BY created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil desain berdasarkan kategori (untuk filter rekomendasi).
     */
    public function getByKategori(string $kategori): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nama_desain, kategori, foto FROM desain WHERE kategori = ? ORDER BY nama_desain'
        );
        $stmt->execute([$kategori]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu desain by ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM desain WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Ambil daftar kategori unik yang tersedia.
     */
    public function getKategoriList(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT kategori FROM desain ORDER BY kategori');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Buat desain baru.
     */
    public function create(string $namaDesain, string $kategori, ?string $foto): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO desain (nama_desain, kategori, foto) VALUES (?, ?, ?)'
        );
        $stmt->execute([$namaDesain, $kategori, $foto]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update desain.
     */
    public function update(int $id, string $namaDesain, string $kategori, ?string $foto): bool
    {
        if ($foto !== null) {
            $stmt = $this->db->prepare(
                'UPDATE desain SET nama_desain = ?, kategori = ?, foto = ? WHERE id = ?'
            );
            return $stmt->execute([$namaDesain, $kategori, $foto, $id]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE desain SET nama_desain = ?, kategori = ? WHERE id = ?'
            );
            return $stmt->execute([$namaDesain, $kategori, $id]);
        }
    }

    /**
     * Hapus desain.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM desain WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Hitung jumlah desain.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM desain')->fetchColumn();
    }
}
