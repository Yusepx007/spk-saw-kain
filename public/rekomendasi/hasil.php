<?php
/**
 * public/rekomendasi/hasil.php — Hasil Rekomendasi SAW
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Rekomendasi;
use Models\DetailRekomendasi;
use Models\Kriteria;
use Helpers\Validator;

AuthGuard::check();

$pageTitle = 'Hasil Rekomendasi';

// Cek apakah ada ID rekomendasi spesifik
$rekId = Validator::sanitizeInt($_GET['id'] ?? 0);

$modelRek    = new Rekomendasi();
$modelDetail = new DetailRekomendasi();
$modelKriteria = new Kriteria();
$kriteriaList  = $modelKriteria->getAll();

// Jika ada ID spesifik → tampilkan detail satu rekomendasi
if ($rekId > 0) {
    $rekomendasi  = $modelRek->getById($rekId);
    $detailList   = $modelDetail->getByRekomendasiId($rekId);
    $hasilSession = $_SESSION['hasil_rekomendasi'] ?? null;
    unset($_SESSION['hasil_rekomendasi']);

    $showList = false; // tampilkan detail, bukan list
} else {
    // Tampilkan semua riwayat
    $showList = true;
    if ($_SESSION['role'] === 'admin') {
        $riwayatList = $modelRek->getAll();
    } else {
        $riwayatList = $modelRek->getByPenggunaId((int)$_SESSION['pengguna_id']);
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="layout-wrapper">
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

        <div class="content-wrapper">

            <?php if ($showList): ?>
            <!-- ================================================
                 TAMPILAN: Daftar Riwayat Rekomendasi
                 ================================================ -->
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-trophy me-2 text-primary"></i>Riwayat Hasil Rekomendasi
                </h2>
                <a href="/spk-saw-kain/public/rekomendasi/form.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Buat Rekomendasi Baru
                </a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($riwayatList)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada riwayat rekomendasi.</p>
                        <a href="/spk-saw-kain/public/rekomendasi/form.php" class="btn btn-primary btn-sm">
                            Buat Rekomendasi Sekarang
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <th>Pengguna</th>
                                    <?php endif; ?>
                                    <th>Jenis Pakaian</th>
                                    <th>Kenyamanan</th>
                                    <th>Aktivitas</th>
                                    <th>Rekomendasi Terbaik</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayatList as $i => $r): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="text-muted" style="font-size:12px;">
                                        <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td class="fw-600"><?= htmlspecialchars($r['nama_pengguna']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($r['jenis_pakaian']) ?></td>
                                    <td><?= htmlspecialchars($r['tingkat_kenyamanan']) ?></td>
                                    <td><?= htmlspecialchars($r['aktivitas']) ?></td>
                                    <td>
                                        <?php if ($r['top_bahan']): ?>
                                        <span class="badge-terbaik">
                                            <i class="bi bi-award-fill"></i>
                                            <?= htmlspecialchars($r['top_bahan']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="/spk-saw-kain/public/rekomendasi/hasil.php?id=<?= $r['id'] ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Lihat
                                            </a>
                                            <?php
                                            $bolehHapus = ($_SESSION['role'] === 'admin')
                                                       || (int)$r['pengguna_id'] === (int)$_SESSION['pengguna_id'];
                                            ?>
                                            <?php if ($bolehHapus): ?>
                                            <a href="/spk-saw-kain/public/rekomendasi/delete.php?id=<?= $r['id'] ?>"
                                               class="btn btn-sm btn-outline-danger confirm-delete"
                                               data-nama="riwayat rekomendasi #<?= $r['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- ================================================
                 TAMPILAN: Detail Hasil Satu Rekomendasi
                 ================================================ -->
            <?php if (!$rekomendasi): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>Data rekomendasi tidak ditemukan.
            </div>
            <?php else: ?>

            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-trophy me-2 text-primary"></i>Hasil Rekomendasi Bahan Kain
                </h2>
                <a href="/spk-saw-kain/public/rekomendasi/hasil.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
                </a>
            </div>

            <!-- Info Rekomendasi -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;font-weight:600;">Pengguna</div>
                            <div class="fw-600"><?= htmlspecialchars($rekomendasi['nama_pengguna']) ?></div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;font-weight:600;">Jenis Pakaian</div>
                            <div class="fw-600"><?= htmlspecialchars($rekomendasi['jenis_pakaian']) ?></div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;font-weight:600;">Kenyamanan</div>
                            <div class="fw-600"><?= htmlspecialchars($rekomendasi['tingkat_kenyamanan']) ?></div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;font-weight:600;">Aktivitas</div>
                            <div class="fw-600"><?= htmlspecialchars($rekomendasi['aktivitas']) ?></div>
                        </div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size:12px;">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('d MMMM Y H:i', strtotime($rekomendasi['created_at'])) ?>
                        <?= date('d/m/Y H:i', strtotime($rekomendasi['created_at'])) ?>
                    </div>
                </div>
            </div>

            <!-- Tabel Ranking Hasil SAW -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-bar-chart-steps me-2 text-primary"></i>
                    Ranking Rekomendasi Bahan Kain (Metode SAW)
                </div>
                <div class="card-body p-0">
                    <?php if (empty($detailList)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Tidak ada detail hasil untuk rekomendasi ini.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover rank-table mb-0">
                            <thead>
                                <tr>
                                    <th width="80" class="text-center">Peringkat</th>
                                    <th>Bahan Kain</th>
                                    <th>Desain Cocok</th>
                                    <th class="text-center">Nilai Preferensi (V<sub>i</sub>)</th>
                                    <th class="text-center">Detail Normalisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailList as $d): ?>
                                <?php
                                $rank   = (int)$d['peringkat'];
                                $rankClass = match(true) {
                                    $rank === 1 => 'rank-1',
                                    $rank === 2 => 'rank-2',
                                    $rank === 3 => 'rank-3',
                                    default     => 'rank-n',
                                };
                                $detailId = 'normalisasi-' . $rekId . '-' . $rank;
                                ?>
                                <tr class="<?= $rank === 1 ? 'rank-1' : '' ?>">
                                    <td class="text-center">
                                        <div class="rank-badge <?= $rankClass ?>">
                                            <?= $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank)) ?>
                                        </div>
                                        <?php if ($rank === 1): ?>
                                        <div class="mt-1">
                                            <span class="badge-terbaik"><i class="bi bi-award-fill"></i> Terbaik</span>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-700" style="font-size:15px;">
                                        <?= htmlspecialchars($d['nama_bahan']) ?>
                                    </td>
                                    <td>
                                        <?php if ($d['nama_desain']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php
                                        // Support 2 format: path relatif ke img/ ATAU nama file di uploads/
                                        $fotoSrc = '';
                                        if ($d['foto']) {
                                            if (str_contains($d['foto'], '/')) {
                                                // Path relatif: Desain/xxx.jpg atau Kain/xxx.jpg
                                                $fotoSrc = '/spk-saw-kain/public/assets/img/' . $d['foto'];
                                            } else {
                                                // Nama file saja → di uploads/
                                                $fotoSrc = '/spk-saw-kain/public/assets/img/uploads/' . $d['foto'];
                                            }
                                        }
                                        ?>
                                        <?php if ($fotoSrc): ?>
                                        <img src="<?= htmlspecialchars($fotoSrc) ?>"
                                                 class="desain-foto" style="width:36px;height:36px;"
                                                 alt="<?= htmlspecialchars($d['nama_desain']) ?>">
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-600" style="font-size:13px;"><?= htmlspecialchars($d['nama_desain']) ?></div>
                                                <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($d['kategori'] ?? '') ?></div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-700 fs-5 <?= $rank === 1 ? 'text-warning' : 'text-primary' ?>">
                                            <?= number_format($d['nilai_preferensi'], 4) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- Hanya tampilkan detail normalisasi jika tersedia di session -->
                                        <?php if ($hasilSession): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-toggle-detail"
                                                data-target="<?= $detailId ?>">
                                            <i class="bi bi-chevron-down"></i> Detail
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Detail Normalisasi (expandable) -->
                                <?php if ($hasilSession): ?>
                                <?php
                                // Cari data normalisasi untuk bahan ini dari session
                                $bahanNama    = $d['nama_bahan'];
                                $hasilItem    = null;
                                foreach ($hasilSession['hasil'] as $h) {
                                    if ($h['nama_bahan'] === $bahanNama) {
                                        $hasilItem = $h;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($hasilItem): ?>
                                <tr id="<?= $detailId ?>" style="display:none;">
                                    <td colspan="5" class="p-2">
                                        <div class="normalisasi-detail">
                                            <div class="fw-600 mb-2">Detail Perhitungan — <?= htmlspecialchars($bahanNama) ?></div>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-2">
                                                    <thead>
                                                        <tr>
                                                            <th>Kriteria</th>
                                                            <th class="text-center">Bobot (W)</th>
                                                            <th class="text-center">Atribut</th>
                                                            <th class="text-center">Nilai Asli (x)</th>
                                                            <th class="text-center">Nilai Normal (r)</th>
                                                            <th class="text-center">W × r</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($hasilSession['kriteria'] as $k): ?>
                                                        <?php
                                                        $kid   = $k['id'];
                                                        $rij   = $hasilItem['normalisasi'][$kid] ?? 0;
                                                        $xij   = $hasilItem['nilai_asli'][$kid]  ?? 0;
                                                        $wj    = (float)$k['bobot'];
                                                        $wxr   = $wj * $rij;
                                                        ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($k['nama_kriteria']) ?></td>
                                                            <td class="text-center"><?= number_format($wj, 3) ?></td>
                                                            <td class="text-center">
                                                                <span class="badge <?= $k['atribut'] === 'benefit' ? 'bg-success' : 'bg-danger' ?>" style="font-size:10px;">
                                                                    <?= $k['atribut'] ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center"><?= number_format($xij, 2) ?></td>
                                                            <td class="text-center fw-600"><?= number_format($rij, 4) ?></td>
                                                            <td class="text-center text-primary fw-600"><?= number_format($wxr, 4) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-active">
                                                            <td colspan="5" class="text-end fw-700">V<sub>i</sub> = Σ(W × r) =</td>
                                                            <td class="text-center fw-700 text-warning"><?= number_format($hasilItem['vi'], 4) ?></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($detailList)):
                $top = $detailList[0]; // bahan terbaik

                // Deskripsi per bahan kain (sesuai Skripsi §2.1.3)
                $deskripsi = [
                    'Rayon'   => [
                        'icon'    => 'bi-droplet-half',
                        'color'   => 'primary',
                        'singkat' => 'Lembut & Sejuk',
                        'panjang' => 'Kain Rayon terbuat dari serat semi sintetis yang memiliki tekstur lembut dan halus. Daya serapnya yang tinggi membuat bahan ini terasa sejuk saat dikenakan, sangat cocok untuk iklim tropis seperti Indonesia.',
                        'cocok'   => 'Pakaian kasual, blouse, dress, dan pakaian sehari-hari di cuaca panas.',
                        'hindari' => 'Pakaian yang membutuhkan kekakuan struktur atau kehangatan.',
                    ],
                    'Katun'   => [
                        'icon'    => 'bi-cloud-sun',
                        'color'   => 'success',
                        'singkat' => 'Nyaman & Mudah Dirawat',
                        'panjang' => 'Kain Katun berasal dari serat kapas alami yang dikenal lembut di kulit, memiliki sirkulasi udara yang baik, dan mudah menyerap keringat. Perawatannya relatif mudah dan tahan lama.',
                        'cocok'   => 'Kemeja, kaos, pakaian kerja casual, baju anak, dan pakaian olahraga ringan.',
                        'hindari' => 'Pakaian formal yang perlu tampilan licin karena katun mudah kusut.',
                    ],
                    'Linen'   => [
                        'icon'    => 'bi-wind',
                        'color'   => 'info',
                        'singkat' => 'Kuat & Berkesan Elegan',
                        'panjang' => 'Kain Linen terbuat dari serat rami yang kuat dan tahan lama. Teksturnya sedikit lebih kaku namun memberikan kesan elegan dan profesional. Sifatnya yang ringan membuatnya terasa sejuk meski di cuaca panas.',
                        'cocok'   => 'Pakaian formal, blazer kasual, celana, dan busana musim panas.',
                        'hindari' => 'Pakaian olahraga karena sifatnya yang mudah kusut.',
                    ],
                    'Flannel' => [
                        'icon'    => 'bi-thermometer-half',
                        'color'   => 'warning',
                        'singkat' => 'Hangat & Tebal',
                        'panjang' => 'Kain Flannel memiliki tenunan yang tebal sehingga memberikan kehangatan bagi pemakainya. Teksturnya lembut dan nyaman disentuh, menjadikannya pilihan ideal untuk pakaian musim dingin atau cuaca sejuk.',
                        'cocok'   => 'Jaket, kemeja flannel, pakaian outdoor, dan busana cuaca dingin.',
                        'hindari' => 'Cuaca panas atau aktivitas olahraga intensitas tinggi.',
                    ],
                    'Jersey'  => [
                        'icon'    => 'bi-person-arms-up',
                        'color'   => 'danger',
                        'singkat' => 'Elastis & Fleksibel',
                        'panjang' => 'Kain Jersey merupakan kain rajut dengan elastisitas tinggi yang memungkinkan gerak bebas pemakainya. Bahan ini ringan, lembut, dan kembali ke bentuk semula setelah diregangkan.',
                        'cocok'   => 'Pakaian olahraga, kaos, dress kasual, dan busana yang membutuhkan kelenturan gerak.',
                        'hindari' => 'Pakaian formal yang memerlukan struktur kaku.',
                    ],
                ];

                $topDesc = $deskripsi[$top['nama_bahan']] ?? null;
            ?>

            <!-- ===== ANALISIS & KESIMPULAN ===== -->

            <?php if ($topDesc): ?>
            <!-- Highlight Terbaik -->
            <div class="card mb-4" style="border:2px solid var(--color-primary);background:linear-gradient(135deg,#f0f7ff 0%,#e8f4fd 100%);">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:52px;height:52px;border-radius:14px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-award-fill text-white fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="mb-0 fw-700" style="color:var(--color-primary);">
                                    🏆 Rekomendasi Terbaik: <?= htmlspecialchars($top['nama_bahan']) ?>
                                </h5>
                                <span class="badge bg-warning text-dark">Vi = <?= number_format($top['nilai_preferensi'], 4) ?></span>
                            </div>
                            <p class="mb-2" style="font-size:13.5px;color:#374151;">
                                <?= htmlspecialchars($topDesc['panjang']) ?>
                            </p>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div style="background:#d1fae5;border-radius:8px;padding:8px 12px;font-size:12.5px;">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        <strong>Cocok untuk:</strong> <?= htmlspecialchars($topDesc['cocok']) ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div style="background:#fee2e2;border-radius:8px;padding:8px 12px;font-size:12.5px;">
                                        <i class="bi bi-exclamation-circle-fill text-danger me-1"></i>
                                        <strong>Kurang cocok untuk:</strong> <?= htmlspecialchars($topDesc['hindari']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Karakteristik Semua Bahan -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    Karakteristik Bahan Kain yang Direkomendasikan
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($detailList as $d):
                            $nama = $d['nama_bahan'];
                            $desc = $deskripsi[$nama] ?? null;
                            $rank = (int)$d['peringkat'];
                            if (!$desc) continue;
                        ?>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 p-3 rounded-3 h-100"
                                 style="background:<?= $rank === 1 ? 'linear-gradient(135deg,#fefce8,#fef9c3)' : '#f8fafc' ?>;
                                        border:1px solid <?= $rank === 1 ? '#fde68a' : '#e2e8f0' ?>;">
                                <div style="width:40px;height:40px;border-radius:10px;background:var(--color-<?= $desc['color'] === 'primary' ? 'primary' : ($desc['color'] === 'success' ? 'success' : ($desc['color'] === 'info' ? 'info' : ($desc['color'] === 'warning' ? 'warning' : 'danger'))) ?>);display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85;">
                                    <i class="bi <?= $desc['icon'] ?> text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-700" style="font-size:14px;">
                                        <?php if ($rank === 1): ?>🏆 <?php elseif ($rank === 2): ?>🥈 <?php elseif ($rank === 3): ?>🥉 <?php else: ?>#<?= $rank ?> <?php endif; ?>
                                        <?= htmlspecialchars($nama) ?>
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size:10px;">
                                            <?= htmlspecialchars($desc['singkat']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-0 mt-1" style="font-size:12px;color:#4b5563;line-height:1.6;">
                                        <?= htmlspecialchars($desc['panjang']) ?>
                                    </p>
                                    <div class="mt-2" style="font-size:11.5px;">
                                        <span class="text-success"><i class="bi bi-check2 me-1"></i><?= htmlspecialchars($desc['cocok']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Catatan Metodologi -->
            <div class="card mb-4" style="border-left:4px solid var(--color-primary);">
                <div class="card-body py-3">
                    <div class="d-flex gap-2">
                        <i class="bi bi-lightbulb-fill text-warning fs-5 flex-shrink-0 mt-1"></i>
                        <div style="font-size:13px;">
                            <div class="fw-700 mb-1">Catatan Metode SAW (Simple Additive Weighting)</div>
                            <p class="mb-1 text-muted">
                                Nilai preferensi (V<sub>i</sub>) dihitung menggunakan rumus
                                <strong>V<sub>i</sub> = Σ(W<sub>j</sub> × r<sub>ij</sub>)</strong>,
                                di mana bobot kriteria yang digunakan adalah:
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach ($kriteriaList as $k): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:11px;">
                                    <?= htmlspecialchars($k['nama_kriteria']) ?> = <?= number_format($k['bobot'], 3) ?>
                                    <span class="ms-1 badge <?= $k['atribut'] === 'benefit' ? 'bg-success' : 'bg-danger' ?>" style="font-size:9px;">
                                        <?= $k['atribut'] === 'benefit' ? '↑ Benefit' : '↓ Cost' ?>
                                    </span>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <p class="mb-0 mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Kriteria <strong>Ketebalan</strong> bersifat <em>Cost</em> untuk aktivitas
                                <em>Olahraga, Santai, Casual, Kerja, dan Cuaca Panas</em>
                                (bahan tipis lebih diutamakan). Untuk aktivitas <em>Formal dan Cuaca Dingin</em>
                                bersifat <em>Benefit</em>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; // end if !empty($detailList) ?>

            <!-- Aksi -->

            <div class="d-flex gap-2 flex-wrap">
                <a href="/spk-saw-kain/public/rekomendasi/form.php" class="btn btn-primary">
                    <i class="bi bi-magic me-1"></i>Buat Rekomendasi Baru
                </a>
                <a href="/spk-saw-kain/public/rekomendasi/hasil.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul me-1"></i>Lihat Semua Riwayat
                </a>
                <?php
                $bolehHapusDetail = ($_SESSION['role'] === 'admin')
                               || (int)$rekomendasi['pengguna_id'] === (int)$_SESSION['pengguna_id'];
                ?>
                <?php if ($bolehHapusDetail): ?>
                <a href="/spk-saw-kain/public/rekomendasi/delete.php?id=<?= $rekomendasi['id'] ?>&redirect=list"
                   class="btn btn-outline-danger confirm-delete"
                   data-nama="riwayat rekomendasi #<?= $rekomendasi['id'] ?>">
                    <i class="bi bi-trash me-1"></i>Hapus Riwayat Ini
                </a>
                <?php endif; ?>
            </div>

            <?php endif; // end if $rekomendasi ?>
            <?php endif; // end if $showList ?>

        </div><!-- /content-wrapper -->
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
