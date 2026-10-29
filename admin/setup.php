<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminCount = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount > 0) {
    redirect('login.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
        $error = 'Username harus 3–40 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif (strlen($password) < 10) {
        $error = 'Password harus terdiri dari minimal 10 karakter.';
    } else {
        $insert = db()->prepare('INSERT INTO admins (username, password_hash) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM admins)');
        $insert->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        if ($insert->rowCount() !== 1) {
            redirect('login.php');
        }
        flash('Akun admin berhasil dibuat. Silakan masuk.');
        redirect('login.php');
    }
}
$title = 'Buat Admin | Tada Sweet Sando';
include __DIR__ . '/_auth-layout.php';
?>
<form class="login" method="post">
  <a class="brand" href="../index.php">TADA! <small>sweet sando</small></a>
  <h1>Buat admin pertama</h1>
  <p>Halaman ini hanya tersedia sebelum akun admin pertama dibuat.</p>
  <?php if ($error): ?><p class="alert" role="alert"><?= h($error) ?></p><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username
    <input name="username" autocomplete="username" minlength="3" maxlength="40" required value="<?= h($_POST['username'] ?? '') ?>">
  </label>
  <label>Password (minimal 10 karakter)
    <span class="password-field">
      <input type="password" name="password" autocomplete="new-password" minlength="10" required>
      <button class="password-toggle" type="button" data-toggle-password aria-label="Lihat password" aria-pressed="false" title="Lihat password">
        <svg data-eye-open viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg data-eye-closed viewBox="0 0 24 24" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.4 15.4 0 0 1-3.1 3.8M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 5.8-1.7"/></svg>
      </button>
    </span>
  </label>
  <button class="btn" type="submit">Buat akun</button>
  <a href="../index.php">Kembali ke toko</a>
</form>
<?php include __DIR__ . '/_auth-footer.php'; ?>
