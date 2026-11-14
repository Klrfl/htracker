<?php
require('koneksi.php');
require('helpers.php');
wajib_login();

$base = '.';
$title = 'Beranda';
$hari_ini = date('Y-m-d');

$stmt = mysqli_prepare($conn, "SELECT h.id, h.nama_habit, h.kategori, h.tanggal_mulai, l.tanggal FROM habits h LEFT JOIN habit_logs l ON l.habit_id = h.id WHERE h.user_id = ? AND h.is_active = 1 ORDER BY h.created_at ASC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$habits = [];
foreach ($rows as $r) {
  $id = (int)$r['id'];
  if (!isset($habits[$id])) {
    $habits[$id] = [
      'id' => $id,
      'nama' => $r['nama_habit'],
      'kategori' => $r['kategori'],
      'mulai' => $r['tanggal_mulai'],
      'log' => [],
    ];
  }
  if ($r['tanggal'] !== null) {
    $habits[$id]['log'][] = $r['tanggal'];
  }
}

$strip = [];
for ($i = 6; $i >= 0; $i--) {
  $strip[] = date('Y-m-d', strtotime("-{$i} days", strtotime($hari_ini)));
}

$total_aktif = count($habits);
$selesai_hari_ini = 0;
foreach ($habits as $h) {
  if (in_array($hari_ini, $h['log'], true)) {
    $selesai_hari_ini++;
  }
}
?>
<?php include 'partials/header.php'; ?>

<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Hari Ini</h1>
  <span id="total-hari-ini" class="badge" data-total="<?= $total_aktif ?>">
    <?= $selesai_hari_ini ?>/<?= $total_aktif ?> selesai
  </span>
</div>

<?php if (!$habits): ?>
  <div class="card bg-base-100 shadow-sm">
    <div class="card-body">
      <p>Belum ada habit aktif. <a href="habits/add.php" class="link">Tambah habit pertama</a>.</p>
    </div>
  </div>
<?php endif; ?>

<div class="grid gap-3">
  <?php foreach ($habits as $h):
    $done_today = in_array($hari_ini, $h['log'], true);
    $streak = hitung_streak($h['log'], $hari_ini, $h['mulai']);
  ?>
    <div class="card bg-base-100 shadow-sm">
      <div class="card-body p-4 gap-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <a href="habits/detail.php?id=<?= $h['id'] ?>" class="font-semibold link link-hover"><?= htmlspecialchars($h['nama']) ?></a>
            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($h['kategori']) ?></span>
          </div>
          <span class="badge badge-outline badge-sm" data-streak-badge="habit-<?= $h['id'] ?>">🔥 <?= $streak['current'] ?> hari</span>
        </div>

        <div class="flex items-center gap-1.5">
          <?php foreach ($strip as $d): ?>
            <div class="h-3 w-3 rounded-full <?= in_array($d, $h['log'], true) ? 'bg-success' : 'bg-base-300' ?>"
              data-dot data-hari="habit-<?= $h['id'] ?>" data-tanggal="<?= $d ?>"></div>
          <?php endforeach; ?>
        </div>

        <form method="post" action="habits/toggle.php" data-toggle-form>
          <input type="hidden" name="habit_id" value="<?= $h['id'] ?>">
          <input type="hidden" name="tanggal" value="<?= $hari_ini ?>">
          <input type="hidden" name="return" value="index.php">
          <button type="submit" class="btn btn-sm <?= $done_today ? 'btn-success' : 'btn-primary' ?>"
            data-toggle-btn="habit-<?= $h['id'] ?>">
            <?= $done_today ? 'Batalkan' : 'Tandai selesai' ?>
          </button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include 'partials/footer.php'; ?>
