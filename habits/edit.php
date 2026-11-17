<?php
require('../koneksi.php');
require('../helpers.php');
wajib_login();

$base = '..';
$title = 'Edit Habit';

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id, nama_habit, deskripsi, kategori, tanggal_mulai, gambar, is_active FROM habits WHERE id = ? AND user_id = ?");
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
  if (isset($_POST['arsipkan']) || isset($_POST['aktifkan'])) {
    $is_active = isset($_POST['aktifkan']) ? 1 : 0;
    $stmt = mysqli_prepare($conn, "UPDATE habits SET is_active = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "iii", $is_active, $id, $_SESSION['user_id']);
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
    $hasil_upload = simpan_upload_gambar_habit($_FILES['gambar'] ?? null);
    $gambar_baru = $hasil_upload['filename'];
    $error = $hasil_upload['error'] ?? '';

    if ($error === '') {
      $hapus_gambar = isset($_POST['hapus_gambar']) && !$gambar_baru;
      $gambar_simpan = $gambar_baru ?: ($hapus_gambar ? null : $habit['gambar']);
      $stmt = mysqli_prepare($conn, "UPDATE habits SET nama_habit = ?, deskripsi = ?, kategori = ?, tanggal_mulai = ?, gambar = ? WHERE id = ? AND user_id = ?");
      mysqli_stmt_bind_param($stmt, "sssssii", $nama, $deskripsi_simpan, $kategori, $tanggal_mulai, $gambar_simpan, $id, $_SESSION['user_id']);
      $berhasil = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      if (!$berhasil) {
        if ($gambar_baru) {
          unlink(__DIR__ . '/../uploads/habits/' . $gambar_baru);
        }
        $error = 'Perubahan habit gagal disimpan. Silakan coba lagi.';
      } else {
        if ($gambar_baru || $hapus_gambar) {
          $gambar_lama = $habit['gambar'] ? __DIR__ . '/../uploads/habits/' . basename($habit['gambar']) : null;
          if ($gambar_lama && is_file($gambar_lama)) {
            unlink($gambar_lama);
          }
        }

        header('Location: ' . base_url() . "/habits/detail.php?id={$id}");
        exit;
      }
    }
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

    <form method="post" enctype="multipart/form-data" class="grid gap-4">
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

      <div>
        <label class="label" for="gambar">Gambar habit</label>
        <input type="file" id="gambar" name="gambar" class="file-input w-full" accept="image/jpeg,image/png,image/webp">
        <p class="text-xs text-slate-500 mt-1">JPG, PNG, atau WebP; maksimal 5 MB.</p>
        <?php if ($habit['gambar']): ?>
          <label class="label cursor-pointer justify-start gap-2 mt-2">
            <input type="checkbox" name="hapus_gambar" value="1" class="checkbox checkbox-sm">
            <span>Hapus gambar saat ini</span>
          </label>
        <?php endif; ?>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary flex-1">Simpan</button>
        <?php if ((int)$habit['is_active'] === 1): ?>
          <button type="submit" name="arsipkan" value="1" class="btn btn-error flex-1" onclick="return confirm('Arsipkan habit ini? Riwayatnya tetap tersimpan.')">Arsipkan</button>
        <?php else: ?>
          <button type="submit" name="aktifkan" value="1" class="btn btn-success flex-1" onclick="return confirm('Aktifkan kembali habit ini?')">Aktifkan</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php include '../partials/footer.php'; ?>
