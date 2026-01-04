<?php
//koneksi mysqli
$config = require __DIR__ . '/config.php';

$DB_HOST = $config['db_host'] ?? '127.0.0.1';
$DB_USER = $config['db_user'] ?? 'root';
$DB_PASS = $config['db_pass'] ?? '';
$DB_NAME = $config['db_name'] ?? 'sp_demo';
$DB_PORT = $config['db_port'] ?? 3306;

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if (!$koneksi) {
    die('Gagal koneksi DB: ' . mysqli_connect_error());
}
mysqli_set_charset($koneksi, $config['db_charset'] ?? 'utf8mb4');
?>