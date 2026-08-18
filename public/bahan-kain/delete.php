<?php
/**
 * public/bahan-kain/delete.php — Hapus Bahan Kain
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\BahanKain;
use Models\NilaiBahan;
use Helpers\Validator;

AuthGuard::requireAdmin();

$id         = Validator::sanitizeInt($_GET['id'] ?? 0);
$modelBahan = new BahanKain();
$bahan      = $modelBahan->getById($id);

if (!$bahan) {
    $_SESSION['flash_error'] = 'Data bahan kain tidak ditemukan.';
    header('Location: /spk-saw-kain/public/bahan-kain/index.php');
    exit;
}

try {
    // Hapus nilai bahan dulu (FK)
    $modelNilai = new NilaiBahan();
    $modelNilai->deleteByBahanId($id);

    $modelBahan->delete($id);
    $_SESSION['flash_success'] = "Bahan kain \"{$bahan['nama_bahan']}\" berhasil dihapus.";
} catch (\Exception $e) {
    error_log('[Delete Bahan Error] ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Gagal menghapus. Bahan kain mungkin masih digunakan di riwayat rekomendasi.';
}

header('Location: /spk-saw-kain/public/bahan-kain/index.php');
exit;
