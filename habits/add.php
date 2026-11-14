<?php
require_once '../koneksi.php';
wajib_login();

$base = '..';
$title = 'Tambah Habit';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama'] ?? '');
  $deskripsi = trim($_POST['deskripsi'] ?? '');
  $kategori = $_POST['kategori'] ?? 'Kesehatan';
  $tanggal_mulai = $_POST['tanggal_mulai'] ?? date('Y-m-d');
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

    $stmt = mysqli_prepare($conn, "INSERT INTO habits (user_id, nama_habit, deskripsi, kategori, tanggal_mulai) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issss", $_SESSION['user_id'], $nama, $deskripsi_simpan, $kategori, $tanggal_mulai);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: ' . base_url() . '/habits/index.php');
    exit;
  }
}
?>
<?php include '../partials/header.php'; ?>

<div class="card bg-base-100 shadow-sm max-w-md mx-auto">
  <div class="card-body">
    <h1 class="card-title text-xl mb-2">Tambah Habit</h1>

    <?php if ($error): ?>
      <div class="alert alert-error text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="grid gap-4">
      <div>
        <label class="label" for="nama">Nama</label>
        <input type="text" id="nama" name="nama" class="input w-full" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
      </div>

      <div>
        <label class="label" for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" class="textarea w-full"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
      </div>

      <div>
        <label class="label" for="kategori">Kategori</label>
        <select id="kategori" name="kategori" class="select w-full">
          <?php foreach (KATEGORI as $k): ?>
            <option value="<?= $k ?>" <?= ($_POST['kategori'] ?? 'Kesehatan') === $k ? 'selected' : '' ?>><?= $k ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="label" for="tanggal_mulai">Tanggal mulai</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="input w-full" value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? date('Y-m-d')) ?>">
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

<?php include '../partials/footer.php'; ?>
