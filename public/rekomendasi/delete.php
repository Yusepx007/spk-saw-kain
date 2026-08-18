<?php
/**
 * public/rekomendasi/delete.php — Hapus Riwayat Rekomendasi
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Rekomendasi;
use Models\DetailRekomendasi;
use Helpers\Validator;

AuthGuard::check();

$id       = Validator::sanitizeInt($_GET['id']       ?? 0);
$redirect = Validator::sanitizeString($_GET['redirect'] ?? '');

$modelRek = new Rekomendasi();
$rek      = $modelRek->getById($id);

if (!$rek) {
    $_SESSION['flash_error'] = 'Data rekomendasi tidak ditemukan.';
    header('Location: /spk-saw-kain/public/rekomendasi/hasil.php');
    exit;
}

// Cek otorisasi: admin bisa hapus semua, pengguna hanya miliknya
$bolehHapus = ($_SESSION['role'] === 'admin')
           || ((int)$rek['pengguna_id'] === (int)$_SESSION['pengguna_id']);

if (!$bolehHapus) {
    $_SESSION['flash_error'] = 'Anda tidak memiliki akses untuk menghapus data ini.';
    header('Location: /spk-saw-kain/public/rekomendasi/hasil.php');
    exit;
}

try {
    // Hapus detail dulu (kalau belum ON DELETE CASCADE)
    $modelDetail = new DetailRekomendasi();
    $modelDetail->deleteByRekomendasiId($id);

    // Hapus rekomendasi utama
    $modelRek->delete($id);

    $_SESSION['flash_success'] = "Riwayat rekomendasi #{$id} berhasil dihapus.";
} catch (\Exception $e) {
    error_log('[Delete Rekomendasi Error] ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Gagal menghapus riwayat rekomendasi.';
}

header('Location: /spk-saw-kain/public/rekomendasi/hasil.php');
exit;
