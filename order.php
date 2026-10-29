<?php
require __DIR__ . '/includes/bootstrap.php';
$code = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($_GET['code'] ?? ''));
$st = db()->prepare('SELECT * FROM orders WHERE order_code = ?'); $st->execute([$code]);
$o = $st->fetch();
if (!$o) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
$items = db()->prepare('SELECT * FROM order_items WHERE order_id = ?'); $items->execute([$o['id']]); $items = $items->fetchAll();
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $o['status'] === 'menunggu_pembayaran' && $o['payment_method'] !== 'cod') {
    csrf_check();
    try {
        $path = save_upload($_FILES['proof'] ?? [], 'proofs');
        db()->prepare("UPDATE orders SET proof_file = ?, status = 'menunggu_verifikasi' WHERE id = ?")->execute([$path, $o['id']]);
        flash('Bukti pembayaran terkirim. Kami akan memverifikasinya segera.');
        redirect('order.php?code=' . $o['order_code']);
    } catch (RuntimeException $e) { $err = $e->getMessage(); }
}
$msg = flash();
$title = 'Pesanan ' . $o['order_code'];
include __DIR__ . '/includes/header.php';
$wa = 'https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode('Halo, saya ingin menanyakan pesanan ' . $o['order_code']);
?>
<main class="section">
  <h2>Terima kasih, <?= h($o['customer_name']) ?>!</h2>
  <p>Pesanan sudah tersimpan. WhatsApp hanya untuk chat dengan toko, bukan untuk mengirim atau menyimpan pesanan.</p>
  <p style="margin:10px 0 24px">Kode pesanan: <b><?= h($o['order_code']) ?></b> <span class="pill s-<?= h($o['status']) ?>"><?= h(ORDER_STATUS[$o['status']]) ?></span></p>
  <?php if ($msg): ?><p class="ok"><?= h($msg) ?></p><?php endif; ?>
  <?php if ($err): ?><p class="alert"><?= h($err) ?></p><?php endif; ?>
  <div class="checkout-grid">
    <div class="panel">
      <h3>Cara bayar: <?= h(PAY_LABEL[$o['payment_method']]) ?></h3>
      <?php if ($o['payment_method'] === 'cod'): ?>
        <p>Siapkan uang tunai <b><?= rupiah((int)$o['total']) ?></b> saat pesanan tiba.</p>
      <?php else: ?>
        <?php if ($o['payment_method'] === 'transfer'): ?>
          <p>Transfer tepat <b><?= rupiah((int)$o['total']) ?></b> ke:</p>
          <p class="bank"><?= h(BANK_NAME) ?> <b><?= h(BANK_ACCOUNT) ?></b><br>a.n. <?= h(BANK_HOLDER) ?></p>
        <?php else: ?>
          <p>Scan QRIS dan bayar tepat <b><?= rupiah((int)$o['total']) ?></b>.</p>
          <?php if (is_file(__DIR__ . '/' . QRIS_IMAGE)): ?><img class="qris" src="<?= h(QRIS_IMAGE) ?>" alt="QRIS <?= h(STORE_NAME) ?>"><?php else: ?><p class="empty">Gambar QRIS belum dipasang oleh toko. Hubungi kami lewat WhatsApp.</p><?php endif; ?>
        <?php endif; ?>
        <?php if ($o['status'] === 'menunggu_pembayaran'): ?>
          <form method="post" enctype="multipart/form-data" style="margin-top:18px">
            <?= csrf_field() ?>
            <label class="field">Unggah bukti pembayaran (JPG/PNG/WebP, maks 2 MB)<input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required></label>
            <button class="btn" type="submit">Kirim bukti</button>
          </form>
        <?php elseif ($o['proof_file']): ?>
          <p style="margin-top:12px">Bukti sudah kami terima.</p>
        <?php endif; ?>
      <?php endif; ?>
      <p style="margin-top:18px"><a class="btn btn-ghost btn-sm" href="<?= h($wa) ?>" target="_blank" rel="noopener">Chat toko via WhatsApp (opsional)</a></p>
    </div>
    <aside class="panel summary">
      <h3>Rincian</h3>
      <?php foreach ($items as $i): ?><p><span><?= (int)$i['qty'] ?>× <?= h($i['name']) ?></span><b><?= rupiah($i['price'] * $i['qty']) ?></b></p><?php endforeach; ?>
      <p><span>Ongkir</span><b><?= $o['shipping'] ? rupiah((int)$o['shipping']) : 'Gratis' ?></b></p>
      <p class="total"><span>Total</span><b><?= rupiah((int)$o['total']) ?></b></p>
      <small>Dikirim ke: <?= h($o['address']) ?></small>
    </aside>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
