<?php
/**
 * insert_dataset.php — Masukkan 10 data rekomendasi ke database
 * Hapus file ini setelah dijalankan!
 */
require_once __DIR__ . '/src/autoload.php';

use Config\Database;
use Services\SAWService;
use Models\Rekomendasi;
use Models\DetailRekomendasi;

$db          = Database::getInstance();
$sawService  = new SAWService();
$modelRek    = new Rekomendasi();
$modelDetail = new DetailRekomendasi();

// 10 Dataset rekomendasi (sesuai skripsi)
$dataset = [
    // [pengguna_id, jenis_pakaian, kenyamanan, aktivitas, created_at]
    [3, 'Atasan',  'Sedang', 'Kerja',       '2026-08-10 08:15:00'],
    [4, 'Bawahan', 'Tinggi', 'Formal',      '2026-08-10 09:30:00'],
    [5, 'Terusan', 'Sedang', 'Santai',      '2026-08-11 10:00:00'],
    [1, 'Atasan',  'Tinggi', 'Cuaca Dingin','2026-08-11 13:45:00'],
    [3, 'Bawahan', 'Rendah', 'Olahraga',   '2026-08-12 07:00:00'],
    [4, 'Terusan', 'Tinggi', 'Casual',     '2026-08-12 14:20:00'],
    [5, 'Atasan',  'Sedang', 'Cuaca Panas','2026-08-13 09:10:00'],
    [1, 'Bawahan', 'Tinggi', 'Formal',     '2026-08-13 11:30:00'],
    [3, 'Terusan', 'Sedang', 'Santai',     '2026-08-14 15:00:00'],
    [4, 'Atasan',  'Rendah', 'Kerja',      '2026-08-14 16:45:00'],
];

$inserted = 0;

foreach ($dataset as [$pid, $jenis, $kenyamanan, $aktivitas, $createdAt]) {
    // Hitung SAW
    $hasil = $sawService->hitung($jenis, $kenyamanan, $aktivitas);
    if (empty($hasil['hasil'])) {
        echo "❌ Gagal hitung SAW untuk: $jenis / $aktivitas\n";
        continue;
    }

    // Insert header rekomendasi dengan created_at custom
    $stmt = $db->prepare(
        'INSERT INTO rekomendasi (pengguna_id, jenis_pakaian, tingkat_kenyamanan, aktivitas, created_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$pid, $jenis, $kenyamanan, $aktivitas, $createdAt]);
    $rekId = (int) $db->lastInsertId();

    // Insert detail ranking
    $details = [];
    foreach ($hasil['hasil'] as $rank => $h) {
        $details[] = [
            'bahan_kain_id'    => $h['bahan_kain_id'],
            'desain_id'        => $h['desain_id'] ?? null,
            'nilai_preferensi' => $h['vi'],
            'peringkat'        => $rank + 1,
        ];
    }
    $modelDetail->createBatch($rekId, $details);

    $top = $hasil['hasil'][0];
    echo "✅ #{$rekId} | {$jenis} / {$aktivitas} | 🏆 {$top['nama_bahan']} (Vi={$top['vi']})\n";
    $inserted++;
}

echo "\n=== Selesai: {$inserted}/10 data berhasil dimasukkan ===\n";
