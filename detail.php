<?php
include("./koneksi.php");
$id = 2;
$stmt = $connection->prepare("select * from film_barbie where id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
</head>

<body>

  <?= $row['judul'], $row['sinopsis']; ?>


</body>

</html>