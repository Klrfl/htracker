<?php
require('../koneksi.php');
wajib_login();

$base = '..';
$title = 'Daftar Habit';

$stmt = mysqli_prepare($conn, "SELECT h.id, h.nama_habit, h.kategori, h.is_active, COUNT(l.id) AS total_selesai FROM habits h LEFT JOIN habit_logs l ON l.habit_id = h.id WHERE h.user_id = ? GROUP BY h.id ORDER BY h.is_active DESC, h.nama_habit ASC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$aktif = array_filter($rows, fn($r) => (int)$r['is_active'] === 1);
$arsip = array_filter($rows, fn($r) => (int)$r['is_active'] === 0);
?>
<?php include '../partials/header.php'; ?>

<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Daftar Habit</h1>
  <a href="add.php" class="btn btn-primary btn-sm">Tambah Habit</a>
</div>

<section class="mb-6">
  <h2 class="text-lg font-semibold mb-2">Aktif</h2>
  <?php if (!$aktif): ?>
    <p class="text-slate-500">Belum ada habit aktif. <a href="add.php" class="link">Tambah habit pertama</a>.</p>
  <?php endif; ?>
  <div class="grid gap-2">
    <?php foreach ($aktif as $h): ?>
      <a href="detail.php?id=<?= (int)$h['id'] ?>" class="card bg-base-100 shadow-sm hover:bg-base-200 transition-colors">
        <div class="card-body py-3 px-4 flex-row items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="font-semibold"><?= htmlspecialchars($h['nama_habit']) ?></span>
            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($h['kategori']) ?></span>
          </div>
          <span class="text-sm text-slate-500"><?= (int)$h['total_selesai'] ?> hari selesai</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <h2 class="text-lg font-semibold mb-2">Arsip</h2>
  <?php if (!$arsip): ?>
    <p class="text-slate-500">Belum ada habit yang diarsipkan.</p>
  <?php endif; ?>
  <div class="grid gap-2">
    <?php foreach ($arsip as $h): ?>
      <a href="detail.php?id=<?= (int)$h['id'] ?>" class="card bg-base-100 shadow-sm opacity-75">
        <div class="card-body py-3 px-4 flex-row items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="font-semibold"><?= htmlspecialchars($h['nama_habit']) ?></span>
            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($h['kategori']) ?></span>
          </div>
          <span class="text-sm text-slate-500"><?= (int)$h['total_selesai'] ?> hari selesai</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
