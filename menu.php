<?php
require __DIR__ . '/includes/bootstrap.php';
$pdo = db();
$products = $pdo->query("SELECT p.*, COALESCE(AVG(r.rating),0) avg_rating, COUNT(r.id) rating_count
  FROM products p LEFT JOIN product_ratings r ON r.product_id = p.id
  WHERE p.is_active = 1 GROUP BY p.id ORDER BY p.is_best DESC, p.id")->fetchAll();
$photos = [
    'Sando strawberry' => 'assets/images/sando-strawberry.png',
    'Sando mangga' => 'assets/images/sando-mango.png',
    'Sando anggur' => 'assets/images/sando-grape.png',
    'Sando mix' => 'assets/images/sando-mix.png',
];
$cats = array_values(array_unique(array_column($products, 'category')));
$title = 'Menu | Tada Sweet Sando';
include __DIR__ . '/includes/header.php';
?>
<main>
  <section class="section" id="menu">
    <div class="section-head">
      <h2>Pilih sando favoritmu</h2>
      <div class="chips" role="group" aria-label="Filter kategori">
        <button class="chip active" data-filter="all">Semua</button>
        <?php foreach ($cats as $c): ?><button class="chip" data-filter="<?= h($c) ?>"><?= h($c) ?></button><?php endforeach; ?>
      </div>
    </div>
    <div class="grid">
      <?php foreach ($products as $p): ?>
        <article class="card" data-cat="<?= h($p['category']) ?>" style="--card:<?= h($p['bg_color']) ?>">
          <?php if ($p['is_best']): ?><span class="badge">Terlaris</span><?php endif; ?>
          <a class="card-art" href="product.php?id=<?= (int)$p['id'] ?>" aria-label="Detail <?= h($p['name']) ?>">
            <?php if (isset($photos[$p['name']])): ?>
              <img src="<?= h($photos[$p['name']]) ?>" alt="<?= h($p['name']) ?>" draggable="false">
            <?php else: ?>
              <?= product_art($p, 200) ?>
            <?php endif; ?>
          </a>
          <div class="card-body">
            <h3><a href="product.php?id=<?= (int)$p['id'] ?>"><?= h($p['name']) ?></a></h3>
            <p><?= h($p['description']) ?></p>
            <?= rate_summary('product-' . $p['id'], (float)$p['avg_rating'], (int)$p['rating_count']) ?>
            <div class="card-foot">
              <b class="price"><?= rupiah((int)$p['price']) ?></b>
              <div class="card-actions">
                <button class="btn btn-sm" type="button" data-add="<?= (int)$p['id'] ?>">Tambah</button>
                <button class="btn btn-sm btn-ghost" type="button" data-rate data-type="product" data-id="<?= (int)$p['id'] ?>" data-title="<?= h($p['name']) ?>">Beri rating</button>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
