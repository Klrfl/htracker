<?php
require('../koneksi.php');
wajib_login();

$base = '..';
$title = 'Edit Habit';

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id, nama_habit, deskripsi, kategori, tanggal_mulai FROM habits WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$habit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$habit) {
  header('Location: ' . base_url() . '/habits/index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['hapus'])) {
    $stmt = mysqli_prepare($conn, "UPDATE habits SET is_active = 0 WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: ' . base_url() . '/habits/index.php');
    exit;
  }

  $nama = trim($_POST['nama'] ?? '');
  $deskripsi = trim($_POST['deskripsi'] ?? '');
  $kategori = $_POST['kategori'] ?? 'Kesehatan';
  $tanggal_mulai = $_POST['tanggal_mulai'] ?? $habit['tanggal_mulai'];
  $bt = DateTime::createFromFormat('Y-m-d', $tanggal_mulai);

  if ($nama === '') {
    $error = 'Nama habit wajib diisi.';
  } elseif (!in_array($kategori, KATEGORI, true)) {
    $error = 'Kategori tidak valid.';
  } elseif (!$bt || $bt->format('Y-m-d') !== $tanggal_mulai) {
    $error = 'Tanggal mulai tidak valid.';
  } else {
    $tanggal_mulai = $bt->format('Y-m-d');
    $deskripsi_simpan = $deskripsi !== '' ? $deskripsi : null;

    $stmt = mysqli_prepare($conn, "UPDATE habits SET nama_habit = ?, deskripsi = ?, kategori = ?, tanggal_mulai = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ssssii", $nama, $deskripsi_simpan, $kategori, $tanggal_mulai, $id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: ' . base_url() . "/habits/detail.php?id={$id}");
    exit;
  }
}
?>
<?php include '../partials/header.php'; ?>

<div class="card bg-base-100 shadow-sm max-w-md mx-auto">
  <div class="card-body">
    <h1 class="card-title text-xl mb-2">Edit Habit</h1>

    <?php if ($error): ?>
      <div class="alert alert-error text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="grid gap-4">
      <div>
        <label class="label" for="nama">Nama</label>
        <input type="text" id="nama" name="nama" class="input w-full" required value="<?= htmlspecialchars($_POST['nama'] ?? $habit['nama_habit']) ?>">
      </div>

      <div>
        <label class="label" for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" class="textarea w-full"><?= htmlspecialchars($_POST['deskripsi'] ?? $habit['deskripsi'] ?? '') ?></textarea>
      </div>

      <div>
        <label class="label" for="kategori">Kategori</label>
        <select id="kategori" name="kategori" class="select w-full">
          <?php foreach (KATEGORI as $k): ?>
            <option value="<?= $k ?>" <?= ($_POST['kategori'] ?? $habit['kategori']) === $k ? 'selected' : '' ?>><?= $k ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="label" for="tanggal_mulai">Tanggal mulai</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="input w-full" value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? $habit['tanggal_mulai']) ?>">
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary flex-1">Simpan</button>
        <button type="submit" name="hapus" value="1" class="btn btn-error flex-1" onclick="return confirm('Arsipkan habit ini? Riwayatnya tetap tersimpan.')">Arsipkan</button>
      </div>
    </form>
  </div>
</div>

<?php include '../partials/footer.php'; ?>
