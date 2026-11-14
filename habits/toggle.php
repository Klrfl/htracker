<?php
require('../koneksi.php');
require('../helpers.php');

wajib_login();

$hari_ini = date('Y-m-d');
$return = $_POST['return'] ?? 'index.php';
if (str_contains($return, '://') || str_starts_with($return, '/')) {
  $return = 'index.php';
}

$habit_id = (int)($_POST['habit_id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? $hari_ini;
$bt = DateTime::createFromFormat('Y-m-d', $tanggal);
$gagal = $habit_id <= 0 || !$bt || $bt->format('Y-m-d') !== $tanggal;

$habit = null;
if (!$gagal) {
  $stmt = mysqli_prepare($conn, "SELECT tanggal_mulai, is_active FROM habits WHERE id = ? AND user_id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $habit_id, $_SESSION['user_id']);
  mysqli_stmt_execute($stmt);
  $habit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);

  $gagal = !$habit
    || (int)$habit['is_active'] !== 1
    || $tanggal < $habit['tanggal_mulai']
    || $tanggal > $hari_ini;
}

$done = false;
$streak = null;
if (!$gagal) {
  $stmt = mysqli_prepare($conn, "SELECT id FROM habit_logs WHERE habit_id = ? AND tanggal = ?");
  mysqli_stmt_bind_param($stmt, "is", $habit_id, $tanggal);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $sudah_ada = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_close($stmt);

  if ($sudah_ada) {
    $stmt = mysqli_prepare($conn, "DELETE FROM habit_logs WHERE habit_id = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt, "is", $habit_id, $tanggal);
    mysqli_stmt_execute($stmt);
  } else {
    $stmt = mysqli_prepare($conn, "INSERT INTO habit_logs (habit_id, tanggal, status) VALUES (?, ?, 'selesai')");
    mysqli_stmt_bind_param($stmt, "is", $habit_id, $tanggal);
    mysqli_stmt_execute($stmt);
    $done = true;
  }
  mysqli_stmt_close($stmt);

  $stmt = mysqli_prepare($conn, "SELECT tanggal FROM habit_logs WHERE habit_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $habit_id);
  mysqli_stmt_execute($stmt);
  $log = array_column(mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC), 'tanggal');
  mysqli_stmt_close($stmt);
  $streak = hitung_streak($log, $hari_ini, $habit['tanggal_mulai'])['current'];
}

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
  header('Content-Type: application/json');
  $total_hari_ini = 0;
  if (!$gagal) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(DISTINCT l.habit_id) AS total FROM habit_logs l JOIN habits h ON h.id = l.habit_id WHERE h.user_id = ? AND l.tanggal = ?");
    mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $hari_ini);
    mysqli_stmt_execute($stmt);
    $total_hari_ini = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);
  }
  echo json_encode([
    'done' => $done,
    'streak' => $streak,
    'total_today' => $total_hari_ini,
    'error' => $gagal ? 'Permintaan tidak valid.' : null,
  ]);
  exit;
}

header('Location: ' . base_url() . '/' . $return);
exit;
