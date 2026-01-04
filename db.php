<?php
$config = require __DIR__ . '/config.php';

function getPdo() {
    global $config;
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};port={$config['db_port']};charset={$config['db_charset']}";
    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }
}