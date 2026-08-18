<?php
/**
 * public/rekomendasi/form.php — Form Rekomendasi SAW
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Desain;
use Models\Kriteria;
use Services\SAWService;
use Helpers\Validator;

AuthGuard::check();

$pageTitle     = 'Form Rekomendasi';
$modelDesain   = new Desain();
$modelKriteria = new Kriteria();
$csrfToken     = AuthGuard::generateCsrfToken();

$kategoriList  = $modelDesain->getKategoriList();
$totalBobot    = $modelKriteria->getTotalBobot();
$bobotValid    = abs($totalBobot - 1.0) <= 0.001;

$aktivitasList = ['Kerja', 'Formal', 'Olahraga', 'Santai', 'Casual', 'Cuaca Panas', 'Cuaca Dingin'];
$kenyamananList = ['Rendah', 'Sedang', 'Tinggi'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthGuard::validateCsrf();

    $jenisPakaian     = Validator::sanitizeString($_POST['jenis_pakaian'] ?? '');
    $tingkatKenyamanan = Validator::sanitizeString($_POST['tingkat_kenyamanan'] ?? '');
    $aktivitas        = Validator::sanitizeString($_POST['aktivitas'] ?? '');

    $v = new Validator();
    $v->required('jenis_pakaian', $jenisPakaian, 'Jenis Pakaian')
      ->required('tingkat_kenyamanan', $tingkatKenyamanan, 'Tingkat Kenyamanan')
      ->required('aktivitas', $aktivitas, 'Aktivitas');
    $errors = $v->getErrors();

    if (empty($errors)) {
        if (!$bobotValid) {
            $error = 'Total bobot kriteria tidak valid. Hubungi admin untuk memperbaikinya.';
        } else {
            try {
                $sawService = new SAWService();
                $hasil = $sawService->rekomendasikan([
                    'pengguna_id'        => $_SESSION['pengguna_id'],
                    'jenis_pakaian'      => $jenisPakaian,
                    'tingkat_kenyamanan' => $tingkatKenyamanan,
                    'aktivitas'          => $aktivitas,
                ]);

                // Simpan hasil ke session untuk ditampilkan di hasil.php
                $_SESSION['hasil_rekomendasi'] = $hasil;

                header('Location: /spk-saw-kain/public/rekomendasi/hasil.php?id=' . $hasil['rekomendasi_id']);
                exit;
            } catch (\Exception $e) {
                error_log('[SAW Error] ' . $e->getMessage());
                $error = $e->getMessage();
            }
        }
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
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-magic me-2 text-primary"></i>Form Rekomendasi Bahan Kain
                </h2>
            </div>

            <?php if (!$bobotValid): ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Sistem tidak bisa dijalankan.</strong>
                Total bobot kriteria = <?= number_format($totalBobot, 3) ?> (harus = 1.000).
                Hubungi admin untuk memperbaiki data kriteria.
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <i class="bi bi-x-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (empty($kategoriList)): ?>
            <div class="alert alert-warning mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Belum ada data desain. Tambahkan desain terlebih dahulu agar jenis pakaian tersedia.
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Form -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="bi bi-ui-checks me-2 text-primary"></i>Isi Form Rekomendasi
                        </div>
                        <div class="card-body">
                            <form method="POST" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                                <!-- Jenis Pakaian -->
                                <div class="mb-4">
                                    <label for="jenis_pakaian" class="form-label">
                                        <i class="bi bi-tag me-1"></i>Jenis Pakaian <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_pakaian" name="jenis_pakaian" required
                                            <?= !$bobotValid || empty($kategoriList) ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Jenis Pakaian --</option>
                                        <?php foreach ($kategoriList as $kat): ?>
                                        <option value="<?= htmlspecialchars($kat) ?>"
                                            <?= ($_POST['jenis_pakaian'] ?? '') === $kat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Sistem akan menyarankan desain yang cocok dengan kategori ini.</div>
                                </div>

                                <!-- Tingkat Kenyamanan -->
                                <div class="mb-4">
                                    <label for="tingkat_kenyamanan" class="form-label">
                                        <i class="bi bi-heart me-1"></i>Tingkat Kenyamanan yang Diinginkan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="tingkat_kenyamanan" name="tingkat_kenyamanan" required
                                            <?= !$bobotValid ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Tingkat Kenyamanan --</option>
                                        <?php foreach ($kenyamananList as $k): ?>
                                        <option value="<?= htmlspecialchars($k) ?>"
                                            <?= ($_POST['tingkat_kenyamanan'] ?? '') === $k ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Aktivitas -->
                                <div class="mb-4">
                                    <label for="aktivitas" class="form-label">
                                        <i class="bi bi-lightning me-1"></i>Jenis Aktivitas <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="aktivitas" name="aktivitas" required
                                            <?= !$bobotValid ? 'disabled' : '' ?>>
                                        <option value="">-- Pilih Aktivitas --</option>
                                        <?php foreach ($aktivitasList as $a): ?>
                                        <option value="<?= htmlspecialchars($a) ?>"
                                            <?= ($_POST['aktivitas'] ?? '') === $a ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($a) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Olahraga/Santai/Cuaca Panas → kriteria Ketebalan diperlakukan sebagai Cost.
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2"
                                        <?= !$bobotValid || empty($kategoriList) ? 'disabled' : '' ?>
                                        id="btn-rekomendasikan">
                                    <i class="bi bi-magic me-2"></i>LIHAT REKOMENDASI
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-info-circle me-2 text-primary"></i>Cara Kerja Sistem SAW
                        </div>
                        <div class="card-body">
                            <ol class="mb-0" style="font-size:13.5px;line-height:1.8;">
                                <li>Pilih jenis pakaian, kenyamanan, dan aktivitas.</li>
                                <li>Sistem membaca data bahan kain dan bobot kriteria dari database.</li>
                                <li>Matriks keputusan dinormalisasi:<br>
                                    <code>Benefit: r = x / max(kolom)</code><br>
                                    <code>Cost: r = min(kolom) / x</code>
                                </li>
                                <li>Nilai preferensi dihitung: <code>V<sub>i</sub> = Σ(W<sub>j</sub> × r<sub>ij</sub>)</code></li>
                                <li>Bahan kain diurutkan dari nilai V<sub>i</sub> tertinggi.</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-table me-2 text-primary"></i>Kriteria Aktif
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $kriteriaList = $modelKriteria->getAll();
                            ?>
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Kriteria</th><th class="text-center">Bobot</th><th class="text-center">Atribut</th></tr></thead>
                                <tbody>
                                    <?php foreach ($kriteriaList as $k): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($k['nama_kriteria']) ?></td>
                                        <td class="text-center"><?= number_format($k['bobot'], 3) ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $k['atribut'] === 'benefit' ? 'bg-success' : 'bg-danger' ?> badge-sm">
                                                <?= $k['atribut'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
