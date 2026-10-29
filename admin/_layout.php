<?php
$adminTitle = $adminTitle ?? 'Admin Tada Sweet Sando';
$activePage = $activePage ?? '';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($adminTitle) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
<aside class="side">
  <a class="brand" href="index.php">TADA! <small>Admin</small></a>
  <nav aria-label="Navigasi admin">
    <a class="<?= $activePage === 'dashboard' ? 'on' : '' ?>" href="index.php">Ringkasan</a>
    <a class="<?= $activePage === 'orders' ? 'on' : '' ?>" href="orders.php">Pesanan</a>
    <a href="../index.php">Lihat toko</a>
    <form class="logout-form" method="post" action="logout.php">
      <?= csrf_field() ?>
      <button type="submit">Keluar</button>
    </form>
  </nav>
</aside>
<main class="main">
  <?php if ($message = flash()): ?><p class="ok"><?= h($message) ?></p><?php endif; ?>
