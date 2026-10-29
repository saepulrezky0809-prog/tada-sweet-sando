<?php
require_once __DIR__ . '/../includes/bootstrap.php';
admin_required();

$stats = db()->query("SELECT
    COUNT(*) AS order_count,
    SUM(status IN ('menunggu_pembayaran', 'menunggu_verifikasi')) AS pending_count,
    SUM(status IN ('diproses', 'selesai') AND created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')) AS confirmed_count,
    COALESCE(SUM(CASE WHEN status IN ('diproses', 'selesai') AND created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') THEN total ELSE 0 END), 0) AS confirmed_sales
  FROM orders")->fetch();
$stats['pending_count'] = (int)$stats['pending_count'];
$stats['confirmed_count'] = (int)$stats['confirmed_count'];
$stats['active_products'] = (int)db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$orders = db()->query("SELECT o.id, o.order_code, o.customer_name, o.total, o.payment_method, o.status, o.created_at,
    GROUP_CONCAT(CONCAT(oi.name, ' × ', oi.qty) ORDER BY oi.id SEPARATOR ', ') AS items
  FROM orders o
  LEFT JOIN order_items oi ON oi.order_id = o.id
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT 8")->fetchAll();

$adminTitle = 'Ringkasan Admin | Tada Sweet Sando';
$activePage = 'dashboard';
include __DIR__ . '/_layout.php';
?>
<h1>Ringkasan toko</h1>
<p>Halo, <?= h($_SESSION['admin_username'] ?? 'Admin') ?>. Berikut ringkasan aktivitas toko.</p>
<section class="stats" aria-label="Statistik toko">
  <article class="stat"><span>Total pesanan</span><b><?= (int)$stats['order_count'] ?></b></article>
  <article class="stat"><span>Perlu tindak lanjut</span><b><?= $stats['pending_count'] ?></b></article>
  <article class="stat"><span>Pesanan terkonfirmasi bulan ini</span><b><?= $stats['confirmed_count'] ?></b></article>
  <article class="stat"><span>Penjualan terkonfirmasi bulan ini</span><b><?= rupiah((int)$stats['confirmed_sales']) ?></b></article>
  <article class="stat"><span>Menu aktif</span><b><?= $stats['active_products'] ?></b></article>
</section>
<h2>Pesanan terbaru</h2>
<p><a class="btn btn-sm" href="orders.php">Kelola semua pesanan</a></p>
<div class="table-wrap">
  <table>
    <thead><tr><th>Kode / Waktu</th><th>Pelanggan</th><th>Pesanan</th><th>Total</th><th>Status</th></tr></thead>
    <tbody>
      <?php if (!$orders): ?>
        <tr><td colspan="5">Belum ada pesanan.</td></tr>
      <?php else: foreach ($orders as $order): ?>
        <tr>
          <td><?= h($order['order_code']) ?><br><small><?= h(date('d/m/Y H:i', strtotime($order['created_at']))) ?></small></td>
          <td><?= h($order['customer_name']) ?><br><small><?= h(PAY_LABEL[$order['payment_method']] ?? $order['payment_method']) ?></small></td>
          <td><?= h($order['items'] ?: 'Tidak ada rincian item') ?></td>
          <td><?= rupiah((int)$order['total']) ?></td>
          <td><span class="pill s-<?= h($order['status']) ?>"><?= h(ORDER_STATUS[$order['status']] ?? $order['status']) ?></span></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</main>
</body>
</html>
