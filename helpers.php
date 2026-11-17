<?php

function hari_berurutan(string $a, string $b): bool
{
  return (new DateTime($a))->diff(new DateTime($b))->days === 1;
}

// $tanggal_log: daftar tanggal Y-m-d. Streak dihitung mundur dari hari ini;
// jika hari ini belum ada log, tetap dihitung dari kemarin.
function hitung_streak(array $tanggal_log, string $hari_ini, string $tanggal_mulai): array
{
  $dates = array_values(array_filter(
    array_unique($tanggal_log),
    fn($d) => $d >= $tanggal_mulai && $d <= $hari_ini
  ));
  sort($dates);

  $longest = 0;
  $run = 0;
  $prev = null;
  foreach ($dates as $d) {
    if ($prev !== null && hari_berurutan($prev, $d)) {
      $run++;
    } else {
      $run = 1;
    }
    $longest = max($longest, $run);
    $prev = $d;
  }

  $kemarin = date('Y-m-d', strtotime('-1 day', strtotime($hari_ini)));
  $cursor = in_array($hari_ini, $dates, true) ? $hari_ini : $kemarin;

  $current = 0;
  while (in_array($cursor, $dates, true)) {
    $current++;

    $kemarin_cursor = date('Y-m-d', strtotime('-1 day', strtotime($cursor)));
    $cursor = $kemarin_cursor;
  }

  return ['current' => $current, 'longest' => $longest];
}

function nama_bulan(string $bulan): string
{
  $nama = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  $t = DateTime::createFromFormat('Y-m-d', $bulan . '-01');
  return $nama[(int)$t->format('n')] . ' ' . $t->format('Y');
}

function simpan_upload_gambar_habit(?array $file): array
{
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return ['filename' => null, 'error' => null];
  }

  if (($file['error'] ?? null) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    return ['filename' => null, 'error' => 'Upload gambar gagal. Silakan coba lagi.'];
  }

  if (($file['size'] ?? 0) <= 0 || $file['size'] > 5 * 1024 * 1024) {
    return ['filename' => null, 'error' => 'Ukuran gambar maksimal 5 MB.'];
  }

  if (!class_exists('finfo')) {
    return ['filename' => null, 'error' => 'Validasi tipe gambar tidak tersedia di server.'];
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $tipe_gambar = $finfo->file($file['tmp_name']);
  $ekstensi_gambar = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($ekstensi_gambar[$tipe_gambar])) {
    return ['filename' => null, 'error' => 'Format gambar harus JPG, PNG, atau WebP.'];
  }

  $folder_upload = __DIR__ . '/uploads/habits';
  if (!is_dir($folder_upload) && !mkdir($folder_upload, 0755, true) && !is_dir($folder_upload)) {
    return ['filename' => null, 'error' => 'Folder penyimpanan gambar tidak dapat dibuat.'];
  }

  $filename = bin2hex(random_bytes(16)) . '.' . $ekstensi_gambar[$tipe_gambar];
  if (!move_uploaded_file($file['tmp_name'], $folder_upload . '/' . $filename)) {
    return ['filename' => null, 'error' => 'Gambar tidak dapat disimpan. Silakan coba lagi.'];
  }

  return ['filename' => $filename, 'error' => null];
}
