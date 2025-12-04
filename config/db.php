<?php
// db.php - Koneksi Database Khusus Railway
// Script ini otomatis mendeteksi konfigurasi environment variable

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// --- FALLBACK (JAGA-JAGA) ---
// Jika MYSQLHOST kosong (seperti di screenshot "7 variables" kamu), 
// kita ambil data dari MYSQL_URL yang pasti ada.
if (!$host && getenv('MYSQL_URL')) {
    $url = parse_url(getenv('MYSQL_URL'));
    $host = $url['host'];
    $user = $url['user'];
    $pass = $url['pass'];
    $db   = ltrim($url['path'], '/'); // Menghapus tanda slash di depan nama db
    $port = $url['port'];
}

// Fallback password: Jika MYSQLPASSWORD kosong, coba pakai MYSQL_ROOT_PASSWORD
if (!$pass) {
    $pass = getenv('MYSQL_ROOT_PASSWORD');
}

// Default ke localhost jika dijalankan di laptop (XAMPP) tanpa ENV
$host = $host ? $host : 'localhost';
$user = $user ? $user : 'root';
$pass = $pass ? $pass : '';
$db   = $db   ? $db   : 'todolist_db'; // Ganti dengan nama DB lokalmu
$port = $port ? $port : 3307;

// Koneksi ke Database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// Opsional: Cek koneksi berhasil (matikan baris ini saat production)
// echo "Berhasil konek ke host: $host";
?>