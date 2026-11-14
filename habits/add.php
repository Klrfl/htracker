<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  echo $_POST['nama'];
  echo $_POST['deskripsi'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Habits</title>

  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
  <main class=" w-full max-w-md">
    <h1 class="text-xl font-bold text-slate-800 mb-6 text-center">Tambah habit baru</h1>

    <form method="post" class="grid gap-4 bg-white rounded-xl px-8 py-4">
      <label for="nama" class="label">Nama</label>
      <input type="text" id="nama" name="nama" class="input w-full" required>

      <label for="deskripsi" class="label">Deskripsi</label>
      <input type="text" id="deskripsi" name="deskripsi" class="input w-full">

      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </main>
</body>

</html>
