<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Rekomendasi — akses tabel `rekomendasi`
 */
class Rekomendasi
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buat header rekomendasi baru.
     * Return ID yang baru dibuat.
     */
    public function create(int $penggunaId, string $jenisPakaian, string $tingkatKenyamanan, string $aktivitas): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO rekomendasi (pengguna_id, jenis_pakaian, tingkat_kenyamanan, aktivitas)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$penggunaId, $jenisPakaian, $tingkatKenyamanan, $aktivitas]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Ambil semua rekomendasi (admin: semua; pengguna: filter by pengguna_id).
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT r.id, r.pengguna_id, r.jenis_pakaian, r.tingkat_kenyamanan, r.aktivitas, r.created_at,
                    p.nama AS nama_pengguna, p.username,
                    (SELECT bk.nama_bahan
                     FROM detail_rekomendasi dr
                     JOIN bahan_kain bk ON dr.bahan_kain_id = bk.id
                     WHERE dr.rekomendasi_id = r.id AND dr.peringkat = 1
                     LIMIT 1) AS top_bahan
             FROM rekomendasi r
             JOIN pengguna p ON r.pengguna_id = p.id
             ORDER BY r.created_at DESC
             LIMIT 50'
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil rekomendasi milik satu pengguna saja.
     */
    public function getByPenggunaId(int $penggunaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.pengguna_id, r.jenis_pakaian, r.tingkat_kenyamanan, r.aktivitas, r.created_at,
                    (SELECT bk.nama_bahan
                     FROM detail_rekomendasi dr
                     JOIN bahan_kain bk ON dr.bahan_kain_id = bk.id
                     WHERE dr.rekomendasi_id = r.id AND dr.peringkat = 1
                     LIMIT 1) AS top_bahan
             FROM rekomendasi r
             WHERE r.pengguna_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$penggunaId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu rekomendasi by ID (header saja).
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, p.nama AS nama_pengguna, p.username
             FROM rekomendasi r
             JOIN pengguna p ON r.pengguna_id = p.id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Ambil rekomendasi terbaru untuk dashboard (N baris).
     */
    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.jenis_pakaian, r.tingkat_kenyamanan, r.aktivitas, r.created_at,
                    p.nama AS nama_pengguna,
                    (SELECT bk.nama_bahan
                     FROM detail_rekomendasi dr
                     JOIN bahan_kain bk ON dr.bahan_kain_id = bk.id
                     WHERE dr.rekomendasi_id = r.id AND dr.peringkat = 1
                     LIMIT 1) AS top_bahan
             FROM rekomendasi r
             JOIN pengguna p ON r.pengguna_id = p.id
             ORDER BY r.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    /**
     * Hapus satu rekomendasi by ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM rekomendasi WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
