<?php if (!isset($base)) $base = '.'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Habit Tracker') ?></title>

  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-slate-100 min-h-screen">
  <nav class="navbar bg-base-100 shadow-sm px-4">
    <a href="<?= $base ?>/index.php" class="btn btn-ghost text-lg font-bold">Habit Tracker</a>
    <div class="flex gap-1">
      <a href="<?= $base ?>/index.php" class="btn btn-ghost btn-sm">Beranda</a>
      <a href="<?= $base ?>/habits/index.php" class="btn btn-ghost btn-sm">Daftar Habit</a>
      <a href="<?= $base ?>/habits/add.php" class="btn btn-ghost btn-sm">Tambah</a>
    </div>
    <div class="ml-auto flex items-center gap-2">
      <span class="text-sm text-slate-600">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
      <a href="<?= $base ?>/logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
  </nav>

  <main class="max-w-3xl mx-auto p-4">
