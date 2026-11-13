<?php
require_once 'koneksi.php';
wajib_login();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <div style="padding: 16px; background: #ecfdf5; border-bottom: 1px solid #10b981;">
    Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>! 
    (<a href="logout.php">Klik di sini untuk Logout</a>)
</div>
</body>

</html>