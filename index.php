<?php
require __DIR__ . '/includes/bootstrap.php';
$products = db()->query("SELECT p.*, COALESCE(AVG(r.rating),0) avg_rating, COUNT(r.id) rating_count
  FROM products p LEFT JOIN product_ratings r ON r.product_id = p.id
  WHERE p.is_active = 1
  GROUP BY p.id
  ORDER BY FIELD(p.name, 'Sando strawberry', 'Sando mangga', 'Sando anggur', 'Sando mix'), p.id
  LIMIT 4")->fetchAll();
$photos = [
    'Sando strawberry' => 'assets/images/sando-strawberry.png',
    'Sando mangga' => 'assets/images/sando-mango.png',
    'Sando anggur' => 'assets/images/sando-grape.png',
    'Sando mix' => 'assets/images/sando-mix.png',
];
$title = 'Tada Sweet Sando | Sando buah & krim segar';
include __DIR__ . '/includes/header.php';
?>
<main>
<section class="home-showcase" aria-label="Menu Tada Sweet Sando">
  <div class="home-copy">
    <span class="home-eyebrow">Fresh fruit sando</span>
    <h1>Manisnya buah,<br>selembut roti.</h1>
    <p>Sando buah dan krim segar untuk menemani harimu.</p>
    <a class="home-menu-link" href="menu.php">Jelajahi menu <span aria-hidden="true">→</span></a>
  </div>
  <div class="home-carousel" aria-label="Menu sando, geser ke samping untuk melihat pilihan lain">
    <div class="home-carousel-track">
      <?php foreach ($products as $p): ?>
        <article class="home-sando">
          <a class="home-sando-photo" href="product.php?id=<?= (int)$p['id'] ?>" aria-label="Lihat <?= h($p['name']) ?>">
            <?php if (isset($photos[$p['name']])): ?>
              <img src="<?= h($photos[$p['name']]) ?>" alt="<?= h($p['name']) ?>" draggable="false">
            <?php else: ?>
              <?= product_art($p, 180) ?>
            <?php endif; ?>
          </a>
          <div class="home-sando-info">
            <a class="home-sando-name" href="product.php?id=<?= (int)$p['id'] ?>"><?= h($p['name']) ?></a>
            <span class="home-sando-price"><?= rupiah((int)$p['price']) ?></span>
            <button class="home-add" type="button" data-add="<?= (int)$p['id'] ?>" aria-label="Tambah <?= h($p['name']) ?> ke keranjang">+</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
