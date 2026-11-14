<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_habit_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}

// Menyesuaikan charset dengan utf8mb4 di database
mysqli_set_charset($conn, "utf8mb4");

// Fungsi helper untuk memproteksi halaman (dipakai di index.php & detail.php)
function wajib_login()
{
  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
  }
}
