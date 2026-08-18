<?php
/**
 * public/logout.php — Logout
 */
require_once __DIR__ . '/../src/autoload.php';

use Middleware\AuthGuard;

AuthGuard::logout();
header('Location: /spk-saw-kain/public/login.php');
exit;
