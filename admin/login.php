<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (!empty($_SESSION['admin_id'])) {
    redirect('index.php');
}
$adminCount = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $query = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $query->execute([$username]);
    $admin = $query->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('index.php');
    }
    $error = 'Username atau password salah.';
}
$title = 'Masuk Admin | Tada Sweet Sando';
include __DIR__ . '/_auth-layout.php';
?>
<form class="login" method="post">
  <a class="brand" href="../index.php">TADA! <small>sweet sando</small></a>
  <h1>Masuk admin</h1>
  <?php if ($message = flash()): ?><p class="ok" role="status"><?= h($message) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="alert" role="alert"><?= h($error) ?></p><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username
    <input name="username" autocomplete="username" required value="<?= h($_POST['username'] ?? '') ?>">
  </label>
  <label>Password
    <span class="password-field">
      <input type="password" name="password" autocomplete="current-password" required>
      <button class="password-toggle" type="button" data-toggle-password aria-label="Lihat password" aria-pressed="false" title="Lihat password">
        <svg data-eye-open viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg data-eye-closed viewBox="0 0 24 24" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.4 15.4 0 0 1-3.1 3.8M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 5.8-1.7"/></svg>
      </button>
    </span>
  </label>
  <button class="btn" type="submit">Masuk</button>
  <?php if ($adminCount === 0): ?><p>Belum ada akun? <a href="setup.php">Buat admin pertama</a></p><?php endif; ?>
  <a href="../index.php">Kembali ke toko</a>
</form>
<?php include __DIR__ . '/_auth-footer.php'; ?>
