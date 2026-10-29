# Tada Sweet Sando (PHP + MySQL)

1. Buat database: import `database.sql` lewat phpMyAdmin atau `mysql -u root -p < database.sql`.
2. Edit `config.php` (user/password DB, nomor WhatsApp, HASH_SALT).
3. Letakkan folder ini di `htdocs` (XAMPP) atau `www` (Laragon), lalu buka `http://localhost/tada-sweet-sando/`.
   Atau jalankan: `php -S localhost:8000` di dalam folder ini.

Fitur rating: 1 rating per menu / website per perangkat (IP) per 24 jam. Rata-rata dihitung dari database.

## Admin

Pastikan MySQL aktif dan `database.sql` sudah diimpor, lalu buka `http://localhost/tada-sweet-sando/admin/` atau `http://localhost/tada-sweet-sando/admin/setup.php` untuk membuat akun admin pertama. Gunakan password minimal 10 karakter. Setelah itu masuk melalui `http://localhost/tada-sweet-sando/admin/login.php`. Halaman setup akan menolak pembuatan akun tambahan setelah admin pertama dibuat.

Dashboard admin menampilkan ringkasan penjualan dan pesanan. Buka menu **Pesanan** untuk melihat detail pelanggan, bukti pembayaran, serta memperbarui status pesanan.

Pengaturan rekening transfer dan informasi toko tersedia di `config.php`. Pastikan juga nilai `HASH_SALT` diganti sebelum situs digunakan.

Checkout menyimpan data pelanggan, metode pembayaran, dan rincian item ke tabel `orders` dan `order_items`. WhatsApp hanya menjadi tautan chat opsional setelah pesanan berhasil disimpan.
