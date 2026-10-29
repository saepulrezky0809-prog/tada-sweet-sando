<?php
require __DIR__ . '/includes/bootstrap.php';
$pdo = db();
$site = $pdo->query('SELECT COALESCE(AVG(rating),0) a, COUNT(*) c FROM site_ratings')->fetch();
$dist = array_fill(1, 5, 0);
foreach ($pdo->query('SELECT rating, COUNT(*) c FROM site_ratings GROUP BY rating') as $d) $dist[(int)$d['rating']] = (int)$d['c'];
$reviews = $pdo->query('SELECT * FROM site_ratings ORDER BY id DESC LIMIT 6')->fetchAll();
$title = 'Rating | Tada Sweet Sando';
include __DIR__ . '/includes/header.php';
?>
<main>
  <section class="section" id="rating">
    <div class="rating-wrap">
      <div class="rating-box">
        <h2>Rating website</h2>
        <div class="big-score"><span id="siteAvg"><?= $site['c'] ? number_format((float)$site['a'], 1) : '–' ?></span></div>
        <?= rate_summary('site', (float)$site['a'], (int)$site['c']) ?>
        <ul class="bars">
          <?php for ($i = 5; $i >= 1; $i--): $pct = $site['c'] ? round($dist[$i] / $site['c'] * 100) : 0; ?>
          <li><span><?= $i ?>★</span><i><b style="width:<?= $pct ?>%"></b></i><span><?= $dist[$i] ?></span></li>
          <?php endfor; ?>
        </ul>
        <button class="btn" data-rate data-type="site" data-id="0" data-title="Beri rating website">Tulis ulasan</button>
      </div>
      <div class="reviews" id="siteReviews">
        <?php foreach ($reviews as $r) echo review_html($r); ?>
        <?php if (!$reviews): ?><p class="empty">Belum ada ulasan. Jadilah yang pertama!</p><?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
