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
                                            <?php if ($d['foto'] && file_exists(__DIR__ . '/../assets/img/uploads/' . $d['foto'])): ?>
                                            <img src="/spk-saw-kain/public/assets/img/uploads/<?= htmlspecialchars($d['foto']) ?>"
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
