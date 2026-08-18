<?php

namespace Services;

use Models\BahanKain;
use Models\Kriteria;
use Models\Desain;
use Models\Rekomendasi;
use Models\DetailRekomendasi;

/**
 * SAWService — SEMUA logika perhitungan SAW ada di sini.
 *
 * Rumus normalisasi (Architecture §4):
 *   benefit : rij = xij / max(kolom)
 *   cost    : rij = min(kolom) / xij
 *
 * Nilai preferensi:
 *   Vi = Σ (Wj × rij)
 *
 * JANGAN hardcode bobot/nilai — semua dibaca dari DB.
 */
class SAWService
{
    // Daftar aktivitas yang membuat kriteria Ketebalan bersifat COST
    // (nilai rendah = lebih baik → bahan tipis lebih cocok)
    // Sumber: Skripsi Tabel 3.1 — "bahan tebal kurang nyaman untuk aktivitas kerja/olahraga"
    private const AKTIVITAS_COST_KETEBALAN = ['Olahraga', 'Santai', 'Cuaca Panas', 'Kerja', 'Casual'];

    // Nama kriteria Ketebalan (harus persis sama dengan yang ada di DB)
    private const NAMA_KRITERIA_KETEBALAN = 'Ketebalan';

    private BahanKain        $modelBahan;
    private Kriteria         $modelKriteria;
    private Desain           $modelDesain;
    private Rekomendasi      $modelRekomendasi;
    private DetailRekomendasi $modelDetail;

    public function __construct()
    {
        $this->modelBahan        = new BahanKain();
        $this->modelKriteria     = new Kriteria();
        $this->modelDesain       = new Desain();
        $this->modelRekomendasi  = new Rekomendasi();
        $this->modelDetail       = new DetailRekomendasi();
    }

    /**
     * Jalankan kalkulasi SAW dan simpan hasilnya.
     *
     * @param array $input ['jenis_pakaian', 'tingkat_kenyamanan', 'aktivitas', 'pengguna_id']
     * @return array ['rekomendasi_id'=>int, 'hasil'=>array, 'normalisasi'=>array, 'kriteria'=>array]
     * @throws \RuntimeException jika total bobot tidak = 1.000
     */
    public function rekomendasikan(array $input): array
    {
        // 1. Ambil kriteria aktif dari DB
        $kriteriaList = $this->modelKriteria->getAllAktif();

        if (empty($kriteriaList)) {
            throw new \RuntimeException('Tidak ada data kriteria. Tambahkan kriteria terlebih dahulu.');
        }

        // Validasi total bobot
        $totalBobot = array_sum(array_column($kriteriaList, 'bobot'));
        if (abs($totalBobot - 1.0) > 0.001) {
            throw new \RuntimeException(
                sprintf('Total bobot kriteria = %.3f, harus = 1.000. Perbaiki data kriteria dulu.', $totalBobot)
            );
        }

        // 2. Ambil semua bahan kain + nilai dari DB
        $bahanList = $this->modelBahan->getAllWithNilai();

        if (empty($bahanList)) {
            throw new \RuntimeException('Tidak ada data bahan kain. Tambahkan bahan kain terlebih dahulu.');
        }

        // 3. Override atribut Ketebalan berdasarkan aktivitas (PRD §7)
        $aktivitas = $input['aktivitas'] ?? '';
        $kriteriaList = $this->applyKetebalanRule($kriteriaList, $aktivitas);

        // 4. Normalisasi matriks
        [$matriks, $normalisasi] = $this->normalisasi($bahanList, $kriteriaList);

        // 5. Hitung Vi = Σ (Wj × rij)
        $hasilVi = $this->hitungNilaiPreferensi($bahanList, $normalisasi, $kriteriaList);

        // 6. Urutkan DESC berdasarkan Vi
        usort($hasilVi, fn($a, $b) => $b['vi'] <=> $a['vi']);

        // 7. Filter/gabungkan dengan Desain yang cocok kategori
        $jenisPakaian = $input['jenis_pakaian'] ?? '';
        $desainCocok  = $this->modelDesain->getByKategori($jenisPakaian);
        $desainTop    = !empty($desainCocok) ? $desainCocok[0] : null; // ambil desain pertama yang cocok

        // 8. Simpan ke DB
        $rekomendasiId = $this->modelRekomendasi->create(
            (int)$input['pengguna_id'],
            $jenisPakaian,
            $input['tingkat_kenyamanan'] ?? '',
            $aktivitas
        );

        $detailBatch = [];
        foreach ($hasilVi as $peringkat => $item) {
            $detailBatch[] = [
                'bahan_kain_id'    => $item['id'],
                'desain_id'        => ($peringkat === 0 && $desainTop) ? $desainTop['id'] : null,
                'nilai_preferensi' => $item['vi'],
                'peringkat'        => $peringkat + 1,
            ];
        }
        $this->modelDetail->createBatch($rekomendasiId, $detailBatch);

        // 9. Return hasil lengkap
        return [
            'rekomendasi_id' => $rekomendasiId,
            'hasil'          => $hasilVi,
            'normalisasi'    => $normalisasi,
            'kriteria'       => $kriteriaList,
            'desain_cocok'   => $desainCocok,
        ];
    }

    /**
     * Override atribut Ketebalan menjadi 'cost' jika aktivitas termasuk kategori aktif/panas.
     */
    private function applyKetebalanRule(array $kriteriaList, string $aktivitas): array
    {
        foreach ($kriteriaList as &$k) {
            if ($k['nama_kriteria'] === self::NAMA_KRITERIA_KETEBALAN) {
                if (in_array($aktivitas, self::AKTIVITAS_COST_KETEBALAN, true)) {
                    $k['atribut'] = 'cost';
                } else {
                    // Untuk aktivitas Formal/Kerja/Cuaca Dingin → benefit (default)
                    $k['atribut'] = 'benefit';
                }
            }
        }
        return $kriteriaList;
    }

    /**
     * Normalisasi matriks keputusan.
     *
     * @return array [matriks_asli, matriks_normalisasi]
     *   matriks_normalisasi format: [ bahan_id => [ kriteria_id => nilai_normal ] ]
     */
    private function normalisasi(array $bahanList, array $kriteriaList): array
    {
        // Kumpulkan semua nilai per kolom (per kriteria)
        $kolomNilai = []; // [ kriteria_id => [nilai, ...] ]
        foreach ($kriteriaList as $k) {
            $kid = $k['id'];
            foreach ($bahanList as $b) {
                $kolomNilai[$kid][] = $b['nilai'][$kid] ?? 0;
            }
        }

        // Hitung max dan min per kolom
        $maxKolom = [];
        $minKolom = [];
        foreach ($kolomNilai as $kid => $vals) {
            $maxKolom[$kid] = max($vals);
            $minKolom[$kid] = min($vals);
        }

        // Matriks asli (untuk referensi)
        $matriks = [];
        foreach ($bahanList as $b) {
            $matriks[$b['id']] = $b['nilai'];
        }

        // Hitung normalisasi
        $normalisasi = [];
        foreach ($bahanList as $b) {
            $bid = $b['id'];
            foreach ($kriteriaList as $k) {
                $kid   = $k['id'];
                $xij   = $b['nilai'][$kid] ?? 0;
                $maxij = $maxKolom[$kid];
                $minij = $minKolom[$kid];

                if ($k['atribut'] === 'benefit') {
                    // rij = xij / max(kolom) — hindari div by zero
                    $normalisasi[$bid][$kid] = ($maxij > 0) ? round($xij / $maxij, 4) : 0;
                } else {
                    // rij = min(kolom) / xij — hindari div by zero
                    $normalisasi[$bid][$kid] = ($xij > 0) ? round($minij / $xij, 4) : 0;
                }
            }
        }

        return [$matriks, $normalisasi];
    }

    /**
     * Hitung Vi = Σ (Wj × rij) untuk setiap bahan kain.
     */
    private function hitungNilaiPreferensi(array $bahanList, array $normalisasi, array $kriteriaList): array
    {
        $hasil = [];

        foreach ($bahanList as $b) {
            $bid = $b['id'];
            $vi  = 0.0;

            foreach ($kriteriaList as $k) {
                $kid  = $k['id'];
                $wj   = (float)$k['bobot'];
                $rij  = $normalisasi[$bid][$kid] ?? 0;
                $vi  += $wj * $rij;
            }

            $hasil[] = [
                'id'         => $bid,
                'nama_bahan' => $b['nama_bahan'],
                'vi'         => round($vi, 4),
                'normalisasi'=> $normalisasi[$bid] ?? [],
                'nilai_asli' => $b['nilai'],
            ];
        }

        return $hasil;
    }
}
