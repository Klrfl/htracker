<?php
require('../koneksi.php');
require('../helpers.php');
wajib_login();

$base = '..';
$hari_ini = date('Y-m-d');

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id, nama_habit, deskripsi, kategori, tanggal_mulai, is_active, gambar FROM habits WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$habit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$habit) {
  http_response_code(404);
  $title = 'Habit tidak ditemukan';
  include '../partials/header.php';
  echo '<div class="alert alert-error">Habit tidak ditemukan.</div>';
  include '../partials/footer.php';
  exit;
}

$title = $habit['nama_habit'];

// Bulan yang ditampilkan via ?bulan=Y-m, default bulan berjalan
$bulan = $_GET['bulan'] ?? date('Y-m');
$bt = DateTime::createFromFormat('Y-m-d', $bulan . '-01');
if (!$bt || $bt->format('Y-m') !== $bulan) {
  $bt = new DateTime($hari_ini);
  $bulan = $bt->format('Y-m');
}
$awal_bulan = $bulan . '-01';
$akhir_bulan = $bt->format('Y-m-t');

$stmt = mysqli_prepare($conn, "SELECT tanggal FROM habit_logs WHERE habit_id = ? AND tanggal BETWEEN ? AND ?");
mysqli_stmt_bind_param($stmt, "iss", $id, $awal_bulan, $akhir_bulan);
mysqli_stmt_execute($stmt);
$log_bulan = array_column(mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC), 'tanggal');
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT tanggal FROM habit_logs WHERE habit_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$semua_log = array_column(mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC), 'tanggal');
mysqli_stmt_close($stmt);

$streak = hitung_streak($semua_log, $hari_ini, $habit['tanggal_mulai']);
$total_selesai = count($semua_log);

$jml_hari = (int)$bt->format('t');
$offset = (int)$bt->format('N') - 1;
$prev = (clone $bt)->modify('-1 month')->format('Y-m');
$next = (clone $bt)->modify('+1 month')->format('Y-m');
?>
<?php include '../partials/header.php'; ?>

<div class="flex items-center justify-between mb-4">
  <div class="flex items-center gap-2">
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($habit['nama_habit']) ?></h1>
    <span class="badge badge-ghost"><?= htmlspecialchars($habit['kategori']) ?></span>
    <?php if ((int)$habit['is_active'] === 0): ?>
      <span class="badge badge-warning">Arsip</span>
    <?php endif; ?>
  </div>
  <a href="edit.php?id=<?= (int)$habit['id'] ?>" class="btn btn-sm">Edit</a>
</div>

<?php if ($habit['gambar']): ?>
  <img src="../uploads/habits/<?= rawurlencode(basename($habit['gambar'])) ?>" alt="Gambar <?= htmlspecialchars($habit['nama_habit']) ?>" class="w-full max-h-80 object-cover rounded-xl mb-4">
<?php endif; ?>

<?php if ($habit['deskripsi']): ?>
  <p class="text-slate-600 mb-4"><?= htmlspecialchars($habit['deskripsi']) ?></p>
<?php endif; ?>

<div class="grid grid-cols-3 gap-2 mb-4">
  <div class="card bg-base-100 shadow-sm">
    <div class="card-body p-3 grid grid-cols-2 grid-cols-[auto_1fr]">
      <div class="row-span-2 text-4xl grid place-items-center">
        🔥
      </div>
      <p class="text-xs text-slate-500">Streak saat ini</p>
      <p class="text-xl font-bold" data-streak-badge="habit-<?= (int)$habit['id'] ?>"><?= $streak['current'] ?> hari</p>
    </div>
  </div>
  <div class="card bg-base-100 shadow-sm">
    <div class="card-body p-3 grid grid-cols-2 grid-cols-[auto_1fr]">
      <div class="row-span-2 text-4xl grid place-items-center">
        🏆
      </div>
      <p class="text-xs text-slate-500">Streak terpanjang</p>
      <p class="text-xl font-bold"><?= $streak['longest'] ?> hari</p>
    </div>
  </div>
  <div class="card bg-base-100 shadow-sm">
    <div class="card-body p-3">
      <p class="text-xs text-slate-500">Total selesai</p>
      <p class="text-xl font-bold"><?= $total_selesai ?> hari</p>
    </div>
  </div>
</div>

<div class="card bg-base-100 shadow-sm">
  <div class="card-body p-4">
    <div class="flex items-center justify-between mb-2">
      <a href="?id=<?= (int)$habit['id'] ?>&bulan=<?= $prev ?>" class="btn btn-ghost btn-sm">&larr;</a>
      <span class="font-semibold"><?= nama_bulan($bulan) ?></span>
      <a href="?id=<?= (int)$habit['id'] ?>&bulan=<?= $next ?>" class="btn btn-ghost btn-sm">&rarr;</a>
    </div>

    <form method="post" action="toggle.php" data-toggle-form>
      <input type="hidden" name="habit_id" value="<?= (int)$habit['id'] ?>">
      <input type="hidden" name="return" value="habits/detail.php?id=<?= (int)$habit['id'] ?>&bulan=<?= $bulan ?>">
      <div class="grid grid-cols-7 gap-1 text-center text-sm">
        <?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $hari): ?>
          <div class="text-xs text-slate-500"><?= $hari ?></div>
        <?php endforeach; ?>

        <?php for ($i = 0; $i < $offset; $i++): ?><div></div><?php endfor; ?>

        <?php for ($i = 1; $i <= $jml_hari; $i++):
          $d = $bulan . '-' . sprintf('%02d', $i);
          $done = in_array($d, $log_bulan, true);
          $masa_depan = $d > $hari_ini;
          $bisa_klik = !$masa_depan && $d >= $habit['tanggal_mulai'] && (int)$habit['is_active'] === 1;
        ?>
          <?php if ($bisa_klik): ?>
            <button type="submit" name="tanggal" value="<?= $d ?>"
              class="aspect-square rounded-lg <?= $done ? 'bg-success text-white' : 'bg-base-200' ?>"
              data-hari="habit-<?= (int)$habit['id'] ?>" data-tanggal="<?= $d ?>">
              <?= $i ?>
            </button>
          <?php else: ?>
            <div class="aspect-square rounded-lg flex items-center justify-center bg-base-200 <?= $masa_depan ? 'opacity-40' : '' ?>"><?= $i ?></div>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </form>

    <?php if ((int)$habit['is_active'] === 1): ?>
      <p class="text-xs text-slate-500 mt-2">Klik tanggal untuk mencatat atau membatalkan.</p>
    <?php endif; ?>
  </div>
</div>

<?php include '../partials/footer.php'; ?>
