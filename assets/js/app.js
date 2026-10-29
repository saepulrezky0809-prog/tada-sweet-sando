(() => {
  const modal = document.getElementById('rateModal');
  const form = document.getElementById('rateForm');
  const msg = document.getElementById('rateMsg');
  const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const starsHtml = v => `<span class="stars" style="--p:${v / 5 * 100}%" role="img" aria-label="Rating ${v} dari 5">★★★★★</span>`;

  // Buka / tutup modal rating
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-rate]');
    if (btn) {
      form.reset(); msg.textContent = ''; msg.className = 'form-msg';
      form.elements.type.value = btn.dataset.type;
      form.elements.id.value = btn.dataset.id;
      document.getElementById('rateTitle').textContent = btn.dataset.title;
      try { form.elements.name.value = localStorage.getItem('tada_name') || ''; } catch (_) {}
      modal.showModal();
    }
    if (e.target.closest('[data-close]') || e.target === modal) modal.close();
  });

  // Kirim rating via fetch
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const submit = form.querySelector('[type=submit]');
    submit.disabled = true;
    try {
      const res = await fetch('api/rate.php', { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (!data.ok) { msg.textContent = data.error; msg.className = 'form-msg err'; return; }
      try { localStorage.setItem('tada_name', form.elements.name.value); } catch (_) {}

      document.querySelectorAll(`.rate-summary[data-key="${data.key}"]`).forEach(el => {
        el.querySelector('.stars').style.setProperty('--p', data.avg / 5 * 100 + '%');
        el.querySelector('.avg').textContent = data.avg.toFixed(1);
        el.querySelector('.count').textContent = `(${data.count})`;
      });
      if (data.key === 'site') { const a = document.getElementById('siteAvg'); if (a) a.textContent = data.avg.toFixed(1); }

      const list = document.getElementById(data.key === 'site' ? 'siteReviews' : 'productReviews');
      if (list) {
        list.querySelector('.empty')?.remove();
        const r = data.review;
        list.insertAdjacentHTML('afterbegin', `<article class="review fresh"><div class="review-top"><b>${esc(r.name)}</b>${starsHtml(r.rating)}</div>${r.comment ? `<p>${esc(r.comment)}</p>` : ''}</article>`);
      }
      modal.close();
    } catch (_) {
      msg.textContent = 'Koneksi bermasalah. Coba lagi.'; msg.className = 'form-msg err';
    } finally { submit.disabled = false; }
  });

  // Filter kategori menu
  document.querySelectorAll('.chip').forEach(chip => chip.addEventListener('click', () => {
    document.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === chip));
    const f = chip.dataset.filter;
    document.querySelectorAll('.card').forEach(card => { card.hidden = f !== 'all' && card.dataset.cat !== f; });
  }));

  // Keranjang belanja berbasis sesi
  const cartModal = document.getElementById('cartModal');
  if (cartModal) {
    const cartItems = cartModal.querySelector('[data-cart-items]');
    const cartCount = document.querySelector('[data-cart-count]');
    const cartTotal = cartModal.querySelector('[data-cart-total]');
    const cartMessage = cartModal.querySelector('[data-cart-message]');
    const checkout = cartModal.querySelector('[data-cart-checkout]');
    const customerForm = cartModal.querySelector('[data-cart-customer-form]');
    const cartTitle = cartModal.querySelector('[data-cart-title]');
    const csrf = cartModal.querySelector('[name="csrf_token"]');
    let cartQueue = Promise.resolve();
    const money = value => `Rp${Number(value).toLocaleString('id-ID')}`;

    const renderCart = data => {
      if (cartCount) cartCount.textContent = data.count;
      cartTotal.textContent = money(data.total);
      cartItems.replaceChildren();
      if (!data.items.length) {
        const empty = document.createElement('p');
        empty.className = 'empty';
        empty.textContent = 'Keranjang masih kosong.';
        cartItems.append(empty);
        checkout.disabled = true;
        return;
      }

      checkout.disabled = false;

      data.items.forEach(item => {
        const row = document.createElement('article');
        row.className = 'cart-item';
        const info = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = item.name;
        const price = document.createElement('small');
        price.textContent = `${money(item.price)} per buah`;
        info.append(name, price);

        const controls = document.createElement('div');
        controls.className = 'cart-controls';
        const decrease = document.createElement('button');
        decrease.type = 'button';
        decrease.dataset.cartAction = 'set';
        decrease.dataset.id = item.id;
        decrease.dataset.qty = item.qty - 1;
        decrease.textContent = '−';
        decrease.setAttribute('aria-label', `Kurangi ${item.name}`);
        const qty = document.createElement('span');
        qty.textContent = item.qty;
        const increase = document.createElement('button');
        increase.type = 'button';
        increase.dataset.cartAction = 'set';
        increase.dataset.id = item.id;
        increase.dataset.qty = Math.min(20, item.qty + 1);
        increase.textContent = '+';
        increase.disabled = item.qty >= 20;
        increase.setAttribute('aria-label', `Tambah ${item.name}`);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'cart-remove';
        remove.dataset.cartAction = 'remove';
        remove.dataset.id = item.id;
        remove.textContent = 'Hapus';
        controls.append(decrease, qty, increase, remove);

        const subtotal = document.createElement('b');
        subtotal.className = 'cart-subtotal';
        subtotal.textContent = money(item.subtotal);
        row.append(info, controls, subtotal);
        cartItems.append(row);
      });
    };

    const requestCart = values => {
      cartQueue = cartQueue.then(async () => {
        cartMessage.textContent = '';
        cartMessage.classList.remove('err');
        try {
          const options = values
            ? { method: 'POST', body: new URLSearchParams({ ...values, csrf_token: csrf.value }) }
            : { method: 'GET' };
          const response = await fetch('api/cart.php', options);
          const data = await response.json();
          if (!response.ok || !data.ok) throw new Error(data.error || 'Keranjang gagal diperbarui.');
          renderCart(data);
        } catch (error) {
          cartMessage.textContent = error.message || 'Koneksi bermasalah. Coba lagi.';
          cartMessage.classList.add('err');
        }
      });
      return cartQueue;
    };

    document.addEventListener('click', event => {
      const open = event.target.closest('[data-cart-open]');
      if (open) {
        requestCart();
        cartModal.showModal();
      }

      const close = event.target.closest('[data-cart-close]');
      if (close || event.target === cartModal) cartModal.close();

      if (event.target.closest('[data-cart-checkout]') && !checkout.disabled) {
        cartItems.hidden = true;
        cartTotal.hidden = true;
        checkout.hidden = true;
        customerForm.hidden = false;
        cartTitle.textContent = 'Data pesanan';
        customerForm.querySelector('[name="customer_name"]').focus();
      }

      if (event.target.closest('[data-cart-back]')) {
        customerForm.hidden = true;
        cartItems.hidden = false;
        cartTotal.hidden = false;
        checkout.hidden = false;
        cartTitle.textContent = 'Keranjang';
        cartMessage.textContent = '';
        cartMessage.classList.remove('err');
      }

      const add = event.target.closest('[data-add]');
      if (add) {
        customerForm.hidden = true;
        cartItems.hidden = false;
        cartTotal.hidden = false;
        checkout.hidden = false;
        cartTitle.textContent = 'Keranjang';
        cartModal.showModal();
        requestCart({ action: 'add', id: add.dataset.add });
      }

      const action = event.target.closest('[data-cart-action]');
      if (action) requestCart({
        action: action.dataset.cartAction,
        id: action.dataset.id,
        qty: action.dataset.qty || '0'
      });
    });

    customerForm.addEventListener('submit', event => {
      event.preventDefault();
      if (!customerForm.reportValidity()) return;
      const submit = customerForm.querySelector('[data-cart-submit]');
      submit.disabled = true;
      cartMessage.textContent = '';
      cartMessage.classList.remove('err');
      cartQueue = cartQueue.then(async () => {
        try {
          const body = new URLSearchParams(new FormData(customerForm));
          body.set('action', 'checkout');
          const response = await fetch('api/cart.php', { method: 'POST', body });
          const data = await response.json();
          if (!response.ok || !data.ok) throw new Error(data.error || 'Pesanan gagal disimpan.');
          window.location.assign(`order.php?code=${encodeURIComponent(data.code)}`);
        } catch (error) {
          cartMessage.textContent = error.message || 'Koneksi bermasalah. Pesanan belum tersimpan.';
          cartMessage.classList.add('err');
          submit.disabled = false;
        }
      });
    });

    requestCart();
  }
})();
