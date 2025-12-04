<?php
// db.php - Versi PDO (Untuk Auth.php yang pakai $pdo)

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

// Fallback jika variable env belum ke-load (misal di local)
$host = $host ? $host : 'localhost';
$port = $port ? $port : '3306';
$db   = $db   ? $db   : 'railway';
$user = $user ? $user : 'root';
$pass = $pass ? $pass : '';

try {
    // String koneksi (DSN)
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    // Buat variable $pdo (Sesuai permintaan auth.php)
    $pdo = new PDO($dsn, $user, $pass);
    
    // Setting error mode agar kalau error muncul pesannya
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}
?>