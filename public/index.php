<?php
/**
 * public/index.php — Front controller / redirect
 */
require_once __DIR__ . '/../src/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['pengguna_id'])) {
    header('Location: /spk-saw-kain/public/dashboard.php');
} else {
    header('Location: /spk-saw-kain/public/login.php');
}
exit;
