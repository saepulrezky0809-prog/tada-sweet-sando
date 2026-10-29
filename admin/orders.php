<?php
require_once __DIR__ . '/../includes/bootstrap.php';
admin_required();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if (!$orderId || !is_string($status) || !array_key_exists($status, ORDER_STATUS)) {
        flash('Perubahan status tidak valid.');
    } else {
        $update = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $update->execute([$status, $orderId]);
        flash($update->rowCount() > 0 ? 'Status pesanan berhasil diperbarui.' : 'Status pesanan tidak berubah.');
    }
    redirect('orders.php');
}

$orders = db()->query("SELECT o.id, o.order_code, o.customer_name, o.phone, o.address, o.note, o.total, o.payment_method, o.status, o.proof_file, o.created_at,
    GROUP_CONCAT(CONCAT(oi.name, ' × ', oi.qty, ' (', oi.price, ')') ORDER BY oi.id SEPARATOR ', ') AS items
  FROM orders o
  LEFT JOIN order_items oi ON oi.order_id = o.id
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT 100")->fetchAll();

$adminTitle = 'Pesanan | Admin Tada Sweet Sando';
$activePage = 'orders';
include __DIR__ . '/_layout.php';
?>
<h1>Pesanan</h1>
<p>Menampilkan hingga 100 pesanan terbaru. Perbarui status setelah pesanan ditinjau.</p>
<div class="table-wrap">
  <table>
    <thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Rincian</th><th>Total / Bayar</th><th>Status</th><th>Ubah status</th></tr></thead>
    <tbody>
      <?php if (!$orders): ?>
        <tr><td colspan="6">Belum ada pesanan.</td></tr>
      <?php else: foreach ($orders as $order): ?>
        <tr>
          <td><b><?= h($order['order_code']) ?></b><br><small><?= h(date('d/m/Y H:i', strtotime($order['created_at']))) ?></small></td>
          <td>
            <?= h($order['customer_name']) ?><br>
            <a href="tel:<?= h($order['phone']) ?>"><?= h($order['phone']) ?></a><br>
            <?= h($order['address']) ?>
            <?php if ($order['note'] !== ''): ?><br><small>Catatan: <?= h($order['note']) ?></small><?php endif; ?>
          </td>
          <td><?= h($order['items'] ?: 'Tidak ada rincian item') ?>
            <?php if ($order['proof_file']): ?><br><a href="../<?= h($order['proof_file']) ?>" target="_blank" rel="noopener">Lihat bukti bayar</a><?php endif; ?>
          </td>
          <td><?= rupiah((int)$order['total']) ?><br><small><?= h(PAY_LABEL[$order['payment_method']] ?? $order['payment_method']) ?></small></td>
          <td><span class="pill s-<?= h($order['status']) ?>"><?= h(ORDER_STATUS[$order['status']] ?? $order['status']) ?></span></td>
          <td>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
              <label class="sr-only" for="status-<?= (int)$order['id'] ?>">Status pesanan <?= h($order['order_code']) ?></label>
              <select id="status-<?= (int)$order['id'] ?>" name="status">
                <?php foreach (ORDER_STATUS as $value => $label): ?>
                  <option value="<?= h($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm" type="submit">Simpan</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</main>
</body>
</html>
