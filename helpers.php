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
