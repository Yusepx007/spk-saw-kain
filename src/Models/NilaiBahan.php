<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model NilaiBahan — akses tabel `nilai_bahan` (junction bahan_kain × kriteria)
 */
class NilaiBahan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua nilai untuk satu bahan kain.
     * Return: [ kriteria_id => nilai, ... ]
     */
    public function getByBahanId(int $bahanKainId): array
    {
        $stmt = $this->db->prepare(
            'SELECT kriteria_id, nilai FROM nilai_bahan WHERE bahan_kain_id = ?'
        );
        $stmt->execute([$bahanKainId]);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['kriteria_id']] = (float)$row['nilai'];
        }
        return $result;
    }

    /**
     * Insert atau update nilai (upsert) — pakai ON DUPLICATE KEY UPDATE.
     */
    public function upsert(int $bahanKainId, int $kriteriaId, float $nilai): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO nilai_bahan (bahan_kain_id, kriteria_id, nilai)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)'
        );
        return $stmt->execute([$bahanKainId, $kriteriaId, $nilai]);
    }

    /**
     * Batch upsert untuk semua kriteria sekaligus.
     * $nilaiArray format: [ kriteria_id => nilai, ... ]
     */
    public function upsertBatch(int $bahanKainId, array $nilaiArray): void
    {
        foreach ($nilaiArray as $kriteriaId => $nilai) {
            $this->upsert($bahanKainId, (int)$kriteriaId, (float)$nilai);
        }
    }

    /**
     * Hapus semua nilai untuk satu bahan kain (dipakai saat bahan dihapus).
     */
    public function deleteByBahanId(int $bahanKainId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM nilai_bahan WHERE bahan_kain_id = ?');
        return $stmt->execute([$bahanKainId]);
    }
}
