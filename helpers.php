<?php
require_once __DIR__ . '/koneksi.php';
session_start();

function esc($s) {
    if ($s === null) return '';
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['user'];
}

function require_tu() {
    $u = require_login();
    if (($u['role'] ?? '') !== 'tu') {
        // jika bukan TU, kirim ke ms_ui (mahasiswa) atau login
        header('Location: ms_ui.php');
        exit;
    }
    return $u;
}
?>