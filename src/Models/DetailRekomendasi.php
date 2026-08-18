<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model DetailRekomendasi — akses tabel `detail_rekomendasi`
 */
class DetailRekomendasi
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Insert batch detail rekomendasi (hasil ranking SAW).
     *
     * @param int   $rekomendasiId
     * @param array $details  Format: [
     *   ['bahan_kain_id'=>1, 'desain_id'=>null|int, 'nilai_preferensi'=>0.9123, 'peringkat'=>1],
     *   ...
     * ]
     */
    public function createBatch(int $rekomendasiId, array $details): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO detail_rekomendasi
             (rekomendasi_id, bahan_kain_id, desain_id, nilai_preferensi, peringkat)
             VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($details as $d) {
            $stmt->execute([
                $rekomendasiId,
                $d['bahan_kain_id'],
                $d['desain_id'] ?? null,
                $d['nilai_preferensi'],
                $d['peringkat'],
            ]);
        }
    }

    /**
     * Ambil semua detail untuk satu rekomendasi, join dengan nama bahan & desain.
     * Diurut berdasarkan peringkat ASC.
     */
    public function getByRekomendasiId(int $rekomendasiId): array
    {
        $stmt = $this->db->prepare(
            'SELECT dr.peringkat, dr.nilai_preferensi,
                    bk.nama_bahan,
                    d.nama_desain, d.kategori, d.foto
             FROM detail_rekomendasi dr
             JOIN bahan_kain bk ON dr.bahan_kain_id = bk.id
             LEFT JOIN desain d  ON dr.desain_id    = d.id
             WHERE dr.rekomendasi_id = ?
             ORDER BY dr.peringkat ASC'
        );
        $stmt->execute([$rekomendasiId]);
        return $stmt->fetchAll();
    }
}
