<?php
require __DIR__ . '/includes/bootstrap.php';
$pdo = db();
$site = $pdo->query('SELECT COALESCE(AVG(rating),0) a, COUNT(*) c FROM site_ratings')->fetch();
$dist = array_fill(1, 5, 0);
foreach ($pdo->query('SELECT rating, COUNT(*) c FROM site_ratings GROUP BY rating') as $d) $dist[(int)$d['rating']] = (int)$d['c'];
$reviews = $pdo->query('SELECT * FROM site_ratings ORDER BY id DESC LIMIT 6')->fetchAll();
$title = 'Tada Sweet Sando | Sando buah & krim segar';
include __DIR__ . '/includes/header.php';
?>
<main>
<section class="hero">
  <div class="hero-text">
    <h1>Roti lembut,<br>isian manis.<br>Tada!</h1>
    <p>Sando buah dan krim yang dibuat segar setiap pagi. Satu gigitan, langsung senyum.</p>
    <div class="hero-cta">
      <a class="btn" href="menu.php">Lihat menu</a>
      <a class="btn btn-ghost" href="rating.php"><?= stars((float)$site['a']) ?> <?= $site['c'] ? number_format((float)$site['a'], 1) : '' ?> dari <?= (int)$site['c'] ?> ulasan</a>
    </div>
  </div>
  <div class="hero-art">
    <div class="blob"></div>
    <?= sando_svg('#F0527E', 380) ?>
    <div class="sticker">TADA!!<br></div>
  </div>
</section>

<div class="marquee" aria-hidden="true"><div>
  <?php for ($i = 0; $i < 2; $i++): ?>
  <span>Strawberry</span><span>Mangga</span><span>Anggur</span><span>Mix</span><span>
  <?php endfor; ?>
</div></div>

<section class="section intro-cta">
  <div class="section-head">
    <h2>Temukan menu favoritmu</h2>
    <a class="btn" href="menu.php">Lihat semua menu</a>
  </div>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
