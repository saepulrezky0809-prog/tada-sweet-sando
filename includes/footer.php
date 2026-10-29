<footer class="site-footer">
  <div class="footer-big">Tada!</div>
  <p>Tada Sweet Sando &copy; <?= date('Y') ?>. Dibuat segar setiap pagi.</p>
  <div class="footer-contact">
    <h2>Contact</h2>
    <a class="contact-link" href="https://wa.me/<?= h(WA_NUMBER) ?>?text=Halo%20Tada%20Sweet%20Sando" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.4L3 20l.9-4.7a8.5 8.5 0 1 1 16.6-3.6Z"/><path d="M8.2 8.3c.4 2.7 2.7 5 5.4 5.6l1-1 2 .8c.3.1.4.4.3.7-.3 1-1.2 1.6-2.2 1.5-3.7-.4-7.5-4-7.8-7.7-.1-1 .5-1.9 1.5-2.2.3-.1.6.1.7.3l.8 2-1.7 0Z"/></svg>
      <span>WhatsApp</span>
    </a>
    <a class="contact-link" href="https://www.instagram.com/" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.8" r=".7" class="icon-fill"/></svg>
      <span>Instagram</span>
    </a>
  </div>
</footer>

<dialog id="cartModal" class="cart-modal" data-wa-number="<?= h(WA_NUMBER) ?>">
  <button type="button" class="close" data-cart-close aria-label="Tutup keranjang">×</button>
  <h2 data-cart-title>Keranjang</h2>
  <p class="cart-message" data-cart-message role="status"></p>
  <div class="cart-items" data-cart-items></div>
  <div class="cart-total"><span>Total</span><strong data-cart-total>Rp0</strong></div>
  <button class="btn cart-checkout" type="button" data-cart-checkout disabled>Lanjut checkout</button>
  <form class="cart-customer-form" data-cart-customer-form hidden>
    <?= csrf_field() ?>
    <label>Nama<input name="customer_name" maxlength="80" autocomplete="name" required></label>
    <label>Nomor telepon<input name="phone" type="tel" maxlength="20" autocomplete="tel" required></label>
    <label>Alamat pengantaran<textarea name="address" maxlength="300" rows="3" autocomplete="street-address" required></textarea></label>
    <label>Catatan (opsional)<textarea name="note" maxlength="200" rows="2"></textarea></label>
    <label>Metode pembayaran
      <select name="payment_method" required>
        <option value="cod">Bayar di tempat (COD)</option>
        <option value="transfer">Transfer bank</option>
        <option value="qris">QRIS</option>
      </select>
    </label>
    <div class="cart-form-actions">
      <button class="btn btn-ghost" type="button" data-cart-back>Kembali</button>
      <button class="btn" type="submit" data-cart-submit>Simpan pesanan</button>
    </div>
  </form>
</dialog>

<dialog id="rateModal">
  <form id="rateForm" method="dialog">
    <button type="button" class="close" data-close aria-label="Tutup">×</button>
    <h3 id="rateTitle">Beri rating</h3>
    <input type="hidden" name="type"><input type="hidden" name="id">
    <div class="star-input" role="radiogroup" aria-label="Pilih bintang">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" name="rating" id="st<?= $i ?>" value="<?= $i ?>" required><label for="st<?= $i ?>" title="<?= $i ?> bintang">★</label>
      <?php endfor; ?>
    </div>
    <label class="field">Nama<input name="name" maxlength="40" required placeholder="Nama kamu"></label>
    <label class="field">Ulasan (opsional)<textarea name="comment" maxlength="300" rows="3" placeholder="Ceritakan rasanya…"></textarea></label>
    <p class="form-msg" id="rateMsg" role="status"></p>
    <button class="btn" type="submit">Kirim rating</button>
  </form>
</dialog>
<script>
(() => {
  const loader = document.querySelector('[data-site-loader]');
  if (!loader) return;
  let dismissed = false;
  const dismiss = () => {
    if (dismissed) return;
    dismissed = true;
    const elapsed = Date.now() - Number(document.documentElement.dataset.loaderStarted || Date.now());
    window.setTimeout(() => {
      loader.classList.add('is-hidden');
      window.setTimeout(() => {
        loader.remove();
        document.documentElement.classList.remove('welcome-active');
      }, 400);
    }, Math.max(0, 1600 - elapsed));
  };
  if (document.readyState === 'complete') dismiss();
  else window.addEventListener('load', dismiss, { once: true });
  window.setTimeout(dismiss, 4000);
})();
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
