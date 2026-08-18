<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model BahanKain — akses tabel `bahan_kain` (+ join `nilai_bahan`)
 */
class BahanKain
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua bahan kain (tanpa nilai).
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nama_bahan, created_at FROM bahan_kain ORDER BY nama_bahan'
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil semua bahan kain beserta nilai per kriteria (untuk SAWService).
     * Return format: [ ['id'=>1, 'nama_bahan'=>'Rayon', 'nilai'=>[kriteria_id=>nilai, ...]], ... ]
     */
    public function getAllWithNilai(): array
    {
        // Ambil semua bahan
        $bahan = $this->getAll();

        // Ambil semua nilai sekaligus (lebih efisien dari N+1 query)
        $stmt = $this->db->query('SELECT bahan_kain_id, kriteria_id, nilai FROM nilai_bahan');
        $nilaiRows = $stmt->fetchAll();

        // Indeks nilai by bahan_kain_id
        $nilaiByBahan = [];
        foreach ($nilaiRows as $row) {
            $nilaiByBahan[$row['bahan_kain_id']][$row['kriteria_id']] = (float)$row['nilai'];
        }

        // Gabungkan
        foreach ($bahan as &$b) {
            $b['nilai'] = $nilaiByBahan[$b['id']] ?? [];
        }

        return $bahan;
    }

    /**
     * Ambil satu bahan kain by ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM bahan_kain WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Buat bahan kain baru (nama saja; nilai diinsert lewat NilaiBahan).
     */
    public function create(string $namaBahan): int
    {
        $stmt = $this->db->prepare('INSERT INTO bahan_kain (nama_bahan) VALUES (?)');
        $stmt->execute([$namaBahan]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update nama bahan kain.
     */
    public function update(int $id, string $namaBahan): bool
    {
        $stmt = $this->db->prepare('UPDATE bahan_kain SET nama_bahan = ? WHERE id = ?');
        return $stmt->execute([$namaBahan, $id]);
    }

    /**
     * Hapus bahan kain (akan gagal kalau masih ada FK di nilai_bahan atau detail_rekomendasi).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM bahan_kain WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Hitung jumlah bahan kain.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM bahan_kain')->fetchColumn();
    }
}
