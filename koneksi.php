<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$env_file = __DIR__ . '/.env';
$env = [];
if (is_file($env_file)) {
  $env = parse_ini_file($env_file);
  if ($env === false) {
    throw new RuntimeException('File .env tidak dapat dibaca atau formatnya tidak valid.');
  }
}

$host = $env['DB_HOST'] ?? "localhost";
$user = $env['DB_USERNAME'] ?? "root";
$pass = $env['DB_PASSWORD'] ?? "";
$db   = $env['DB_NAME'] ?? "db_habit_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}

// Menyesuaikan charset dengan utf8mb4 di database
mysqli_set_charset($conn, "utf8mb4");

const KATEGORI = ['Kesehatan', 'Olahraga', 'Produktivitas', 'Mental', 'Lainnya'];

function base_url(): string
{
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  if (str_ends_with($dir, '/habits')) {
    $dir = rtrim(dirname($dir), '/');
  }
  return $dir === '/' ? '' : $dir;
}

// Fungsi helper untuk memproteksi halaman (dipakai di index.php, detail.php, dll)
function wajib_login()
{
  if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url() . "/login.php");
    exit;
  }
}
