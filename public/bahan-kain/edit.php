<?php
/**
 * public/bahan-kain/edit.php — Edit Bahan Kain
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\BahanKain;
use Models\Kriteria;
use Models\NilaiBahan;
use Helpers\Validator;

AuthGuard::requireAdmin();

$id           = Validator::sanitizeInt($_GET['id'] ?? 0);
$modelBahan   = new BahanKain();
$bahan        = $modelBahan->getById($id);

if (!$bahan) {
    $_SESSION['flash_error'] = 'Data bahan kain tidak ditemukan.';
    header('Location: /spk-saw-kain/public/bahan-kain/index.php');
    exit;
}

$pageTitle     = 'Ubah Bahan Kain';
$errors        = [];
$csrfToken     = AuthGuard::generateCsrfToken();
$modelKriteria = new Kriteria();
$modelNilai    = new NilaiBahan();
$kriteriaList  = $modelKriteria->getAll();
$existingNilai = $modelNilai->getByBahanId($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthGuard::validateCsrf();

    $namaBahan  = Validator::sanitizeString($_POST['nama_bahan'] ?? '');
    $nilaiInput = $_POST['nilai'] ?? [];

    $v = new Validator();
    $v->required('nama_bahan', $namaBahan, 'Nama Bahan')->maxLength('nama_bahan', $namaBahan, 50, 'Nama Bahan');

    foreach ($kriteriaList as $k) {
        $kid   = $k['id'];
        $field = "nilai_$kid";
        $val   = $nilaiInput[$kid] ?? '';
        $v->required($field, $val, "Nilai " . $k['nama_kriteria'])
          ->numericRange($field, $val, 1, 5, "Nilai " . $k['nama_kriteria']);
    }

    $errors = $v->getErrors();

    if (empty($errors)) {
        $modelBahan->update($id, $namaBahan);

        $nilaiSave = [];
        foreach ($kriteriaList as $k) {
            $nilaiSave[$k['id']] = Validator::sanitizeFloat($nilaiInput[$k['id']] ?? 0);
        }
        $modelNilai->upsertBatch($id, $nilaiSave);

        $_SESSION['flash_success'] = "Bahan kain \"$namaBahan\" berhasil diperbarui.";
        header('Location: /spk-saw-kain/public/bahan-kain/index.php');
        exit;
    }

    $existingNilai = [];
    foreach ($nilaiInput as $kid => $val) {
        $existingNilai[$kid] = (float)$val;
    }
    $bahan['nama_bahan'] = $namaBahan;
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
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Ubah Bahan Kain
                </h2>
                <a href="/spk-saw-kain/public/bahan-kain/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card" style="max-width:640px;">
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-4">
                            <label for="nama_bahan" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control <?= isset($errors['nama_bahan']) ? 'is-invalid' : '' ?>"
                                   id="nama_bahan" name="nama_bahan"
                                   value="<?= htmlspecialchars($bahan['nama_bahan']) ?>"
                                   required>
                            <?php if (isset($errors['nama_bahan'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['nama_bahan']) ?></div>
                            <?php endif; ?>
                        </div>

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
                                           value="<?= htmlspecialchars((string)($existingNilai[$k['id']] ?? '')) ?>"
                                           step="0.25" min="1" max="5"
                                           required>
                                    <?php if (isset($errors[$field])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors[$field]) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Perbarui
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
