<?php
require __DIR__ . '/includes/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT p.*, COALESCE(AVG(r.rating),0) avg_rating, COUNT(r.id) rating_count
  FROM products p LEFT JOIN product_ratings r ON r.product_id = p.id WHERE p.id = ? AND p.is_active = 1 GROUP BY p.id");
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { http_response_code(404); $title = 'Menu tidak ditemukan'; include __DIR__ . '/includes/header.php';
  echo '<main class="section"><h2>Menu tidak ditemukan</h2><p><a class="btn" href="index.php#menu">Kembali ke menu</a></p></main>';
  include __DIR__ . '/includes/footer.php'; exit; }
$rv = db()->prepare('SELECT * FROM product_ratings WHERE product_id = ? ORDER BY id DESC LIMIT 30');
$rv->execute([$id]);
$reviews = $rv->fetchAll();
$title = $p['name'] . ' | Tada Sweet Sando';
include __DIR__ . '/includes/header.php';
$wa = 'https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode('Halo, saya mau pesan ' . $p['name']);
?>
<main>
<section class="detail" style="--card:<?= h($p['bg_color']) ?>">
  <div class="detail-art"><?= product_art($p, 340) ?></div>
  <div class="detail-info">
    <a class="back" href="index.php#menu">Kembali ke menu</a>
    <h1><?= h($p['name']) ?></h1>
    <?= rate_summary('product-' . $p['id'], (float)$p['avg_rating'], (int)$p['rating_count']) ?>
    <p class="lead"><?= h($p['description']) ?></p>
    <b class="price big"><?= rupiah((int)$p['price']) ?></b>
    <div class="hero-cta">
      <button class="btn" data-add="<?= (int)$p['id'] ?>">+ Tambah ke keranjang</button>
      <button class="btn btn-ghost" data-rate data-type="product" data-id="<?= (int)$p['id'] ?>" data-title="<?= h($p['name']) ?>">Beri rating</button>
    </div>
  </div>
</section>
<section class="section">
  <h2>Ulasan pembeli</h2>
  <div class="reviews" id="productReviews">
    <?php foreach ($reviews as $r) echo review_html($r); ?>
    <?php if (!$reviews): ?><p class="empty">Belum ada ulasan untuk menu ini.</p><?php endif; ?>
  </div>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
