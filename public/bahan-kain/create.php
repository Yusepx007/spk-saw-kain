<?php
/**
 * public/bahan-kain/create.php — Tambah Bahan Kain Baru
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\BahanKain;
use Models\Kriteria;
use Models\NilaiBahan;
use Helpers\Validator;

AuthGuard::requireAdmin();

$pageTitle     = 'Tambah Bahan Kain';
$errors        = [];
$csrfToken     = AuthGuard::generateCsrfToken();
$modelKriteria = new Kriteria();
$kriteriaList  = $modelKriteria->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthGuard::validateCsrf();

    $namaBahan = Validator::sanitizeString($_POST['nama_bahan'] ?? '');

    $v = new Validator();
    $v->required('nama_bahan', $namaBahan, 'Nama Bahan')->maxLength('nama_bahan', $namaBahan, 50, 'Nama Bahan');

    // Validasi nilai per kriteria
    $nilaiInput = $_POST['nilai'] ?? [];
    foreach ($kriteriaList as $k) {
        $kid   = $k['id'];
        $field = "nilai_$kid";
        $val   = $nilaiInput[$kid] ?? '';
        $v->required($field, $val, "Nilai " . $k['nama_kriteria'])
          ->numericRange($field, $val, 1, 5, "Nilai " . $k['nama_kriteria']);
    }

    $errors = $v->getErrors();

    if (empty($errors)) {
        $modelBahan = new BahanKain();
        $newId      = $modelBahan->create($namaBahan);

        $modelNilai = new NilaiBahan();
        $nilaiSave  = [];
        foreach ($kriteriaList as $k) {
            $nilaiSave[$k['id']] = Validator::sanitizeFloat($nilaiInput[$k['id']] ?? 0);
        }
        $modelNilai->upsertBatch($newId, $nilaiSave);

        $_SESSION['flash_success'] = "Bahan kain \"$namaBahan\" berhasil ditambahkan.";
        header('Location: /spk-saw-kain/public/bahan-kain/index.php');
        exit;
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
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Bahan Kain
                </h2>
                <a href="/spk-saw-kain/public/bahan-kain/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card" style="max-width:640px;">
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Nama Bahan -->
                        <div class="mb-4">
                            <label for="nama_bahan" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control <?= isset($errors['nama_bahan']) ? 'is-invalid' : '' ?>"
                                   id="nama_bahan" name="nama_bahan"
                                   value="<?= htmlspecialchars($_POST['nama_bahan'] ?? '') ?>"
                                   placeholder="cth. Rayon, Katun, Linen"
                                   required>
                            <?php if (isset($errors['nama_bahan'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['nama_bahan']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Nilai Per Kriteria -->
                        <?php if (!empty($kriteriaList)): ?>
                        <div class="mb-4">
                            <label class="form-label fw-600 d-block mb-3">
                                Nilai Per Kriteria <span class="text-danger">*</span>
                                <span class="text-muted fw-normal ms-1">(skala 1.00 – 5.00)</span>
                            </label>

                            <div class="row g-3">
                                <?php foreach ($kriteriaList as $k): ?>
                                <?php $field = "nilai_{$k['id']}"; ?>
                                <div class="col-sm-6">
                                    <label for="<?= $field ?>" class="form-label">
                                        <?= htmlspecialchars($k['nama_kriteria']) ?>
                                        <span class="badge bg-secondary ms-1" style="font-size:10px;">
                                            W=<?= $k['bobot'] ?> | <?= $k['atribut'] ?>
                                        </span>
                                    </label>
                                    <input type="number"
                                           class="form-control nilai-bahan-input <?= isset($errors[$field]) ? 'is-invalid' : '' ?>"
                                           id="<?= $field ?>"
                                           name="nilai[<?= $k['id'] ?>]"
                                           value="<?= htmlspecialchars($_POST['nilai'][$k['id']] ?? '') ?>"
                                           step="0.25" min="1" max="5"
                                           placeholder="1.00 – 5.00"
                                           required>
                                    <?php if (isset($errors[$field])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors[$field]) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tambahkan data kriteria terlebih dahulu sebelum menambah bahan kain.
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" <?= empty($kriteriaList) ? 'disabled' : '' ?>>
                                <i class="bi bi-check-lg me-1"></i>Simpan
                            </button>
                            <a href="/spk-saw-kain/public/bahan-kain/index.php" class="btn btn-outline-secondary">Batal</a>
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
