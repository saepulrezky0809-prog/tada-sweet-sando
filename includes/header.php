<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title ?? 'Tada Sweet Sando') ?></title>
<meta name="description" content="Tada Sweet Sando: sando buah dan krim lembut, dibuat segar setiap hari.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bagel+Fat+One&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script>document.documentElement.classList.add('welcome-active');document.documentElement.dataset.loaderStarted=Date.now();</script>
</head>
<body>
<div class="site-loader" data-site-loader role="status" aria-label="Memuat Tada Sweet Sando">
  <img src="assets/tada-sweet-sando-logo.png" alt="">
  <span class="loader-dots" aria-hidden="true"><i></i><i></i><i></i></span>
</div>
<header class="site-header">
  <a class="logo" href="index.php" aria-label="Tada Sweet Sando - Home">
    <img src="assets/tada-sweet-sando-logo.png" alt="Tada Sweet Sando">
    <span class="logo-text"><strong>TADA!</strong><small>sweet sando</small></span>
  </a>
  <nav>
    <a class="btn btn-sm btn-ghost" href="index.php">Home</a>
    <a class="btn btn-sm btn-ghost" href="story.php">About Tada</a>
    <button class="btn btn-sm btn-ghost cart-open" type="button" data-cart-open aria-label="Buka keranjang">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 1.9-1.5L21 8H6"/><circle cx="10" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
      <span class="cart-count" data-cart-count>0</span>
    </button>
  </nav>
</header>
<a class="rating-fab" href="rating.php" aria-label="Beri rating" title="Beri rating">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2.8 2.8 5.7 6.3.9-4.6 4.5 1.1 6.3-5.6-3-5.6 3 1.1-6.3-4.6-4.5 6.3-.9L12 2.8Z"/></svg>
</a>
<div class="marquee" aria-hidden="true"><div class="marquee-track">
  <?php for ($i = 0; $i < 2; $i++): ?>
    <div class="marquee-group"><span>Strawberry</span><span>Mangga</span><span>Anggur</span><span>Mix</span></div>
  <?php endfor; ?>
</div></div>
