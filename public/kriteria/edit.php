<?php
/**
 * public/kriteria/edit.php — Edit Kriteria
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Kriteria;
use Helpers\Validator;

AuthGuard::requireAdmin();

$id            = Validator::sanitizeInt($_GET['id'] ?? 0);
$modelKriteria = new Kriteria();
$kriteria      = $modelKriteria->getById($id);

if (!$kriteria) {
    $_SESSION['flash_error'] = 'Data kriteria tidak ditemukan.';
    header('Location: /spk-saw-kain/public/kriteria/index.php');
    exit;
}

$pageTitle    = 'Ubah Kriteria';
$errors       = [];
$csrfToken    = AuthGuard::generateCsrfToken();
// Bobot semua kriteria KECUALI yang sedang diedit
$bobotLain    = $modelKriteria->getTotalBobotExclude($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthGuard::validateCsrf();

    $namaKriteria = Validator::sanitizeString($_POST['nama_kriteria'] ?? '');
    $bobot        = $_POST['bobot']   ?? '';
    $atribut      = $_POST['atribut'] ?? '';

    $v = new Validator();
    $v->required('nama_kriteria', $namaKriteria, 'Nama Kriteria')
      ->maxLength('nama_kriteria', $namaKriteria, 50, 'Nama Kriteria')
      ->required('bobot', $bobot, 'Bobot')
      ->numericRange('bobot', $bobot, 0.001, 1.0, 'Bobot')
      ->required('atribut', $atribut, 'Atribut')
      ->inList('atribut', $atribut, ['benefit', 'cost'], 'Atribut');

    $errors = $v->getErrors();

    if (empty($errors)) {
        $totalBaru = round($bobotLain + (float)$bobot, 3);
        if ($totalBaru > 1.001) {
            $errors['bobot'] = "Bobot terlalu besar. Bobot kriteria lain = {$bobotLain}; total = {$totalBaru} (> 1.000).";
        }
    }

    if (empty($errors)) {
        $modelKriteria->update($id, $namaKriteria, Validator::sanitizeFloat($bobot), $atribut);
        $_SESSION['flash_success'] = "Kriteria \"$namaKriteria\" berhasil diperbarui.";
        header('Location: /spk-saw-kain/public/kriteria/index.php');
        exit;
    }

    $kriteria['nama_kriteria'] = $namaKriteria;
    $kriteria['bobot']         = $bobot;
    $kriteria['atribut']       = $atribut;
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
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Ubah Kriteria
                </h2>
                <a href="/spk-saw-kain/public/kriteria/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card" style="max-width:520px;">
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Bobot kriteria lain (selain ini): <strong><?= number_format($bobotLain, 3) ?></strong>.
                        Bobot kriteria ini harus ≤ <strong><?= number_format(max(0, 1.0 - $bobotLain), 3) ?></strong>.
                    </div>

                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" id="bobot-lain" value="<?= $bobotLain ?>">

                        <div class="mb-4">
                            <label for="nama_kriteria" class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control <?= isset($errors['nama_kriteria']) ? 'is-invalid' : '' ?>"
                                   id="nama_kriteria" name="nama_kriteria"
                                   value="<?= htmlspecialchars($kriteria['nama_kriteria']) ?>"
                                   required>
                            <?php if (isset($errors['nama_kriteria'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['nama_kriteria']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="bobot" class="form-label">Bobot (W) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control <?= isset($errors['bobot']) ? 'is-invalid' : '' ?>"
                                   id="bobot" name="bobot"
                                   value="<?= htmlspecialchars((string)$kriteria['bobot']) ?>"
                                   step="0.001" min="0.001" max="1.0"
                                   required>
                            <?php if (isset($errors['bobot'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['bobot']) ?></div>
                            <?php else: ?>
                            <div class="mt-2">
                                <span id="total-bobot-display"></span>
                                <span id="sisa-bobot-display" class="ms-2"></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="atribut" class="form-label">Atribut <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['atribut']) ? 'is-invalid' : '' ?>"
                                    id="atribut" name="atribut" required>
                                <option value="">-- Pilih Atribut --</option>
                                <option value="benefit" <?= $kriteria['atribut'] === 'benefit' ? 'selected' : '' ?>>
                                    Benefit (nilai lebih tinggi = lebih baik)
                                </option>
                                <option value="cost" <?= $kriteria['atribut'] === 'cost' ? 'selected' : '' ?>>
                                    Cost (nilai lebih rendah = lebih baik)
                                </option>
                            </select>
                            <?php if (isset($errors['atribut'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['atribut']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Perbarui
                            </button>
                            <a href="/spk-saw-kain/public/kriteria/index.php" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
