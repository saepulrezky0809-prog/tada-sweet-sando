<?php
require_once __DIR__ . '/../config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }
function ip_hash(): string { return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . HASH_SALT); }
function csrf_field(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf_token']) . '">';
}
function csrf_check(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($expected) || $expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}
function redirect(string $location): void {
    header('Location: ' . $location);
    exit;
}
function flash(?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash_message'] = $message;
        return null;
    }
    $current = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return is_string($current) ? $current : null;
}
function admin_required(): void {
    if (empty($_SESSION['admin_id'])) redirect('login.php');
}
function cart_count(): int {
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? array_sum(array_map('intval', $cart)) : 0;
}
function save_upload(array $file, string $kind): string {
    $directories = ['products' => 'products', 'proofs' => 'proofs'];
    if (!isset($directories[$kind])) throw new RuntimeException('Jenis unggahan tidak valid.');
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Pilih file gambar yang valid.');
    if (!isset($file['tmp_name'], $file['size']) || (int)$file['size'] < 1 || (int)$file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Unggahan gambar tidak valid.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('File harus berupa gambar JPG, PNG, atau WebP.');
    }
    $relative = 'uploads/' . $directories[$kind];
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $directories[$kind];
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Gambar gagal disimpan.');
    }
    return $relative . '/' . $filename;
}

// Bintang: lebar isi dihitung dari rata-rata (0-5)
function stars(float $avg): string {
    $p = round($avg / 5 * 100);
    return '<span class="stars" style="--p:' . $p . '%" role="img" aria-label="Rating ' . number_format($avg, 1) . ' dari 5">★★★★★</span>';
}
function rate_summary(string $key, float $avg, int $count): string {
    $label = $count ? number_format($avg, 1) : 'Baru';
    return '<span class="rate-summary" data-key="' . h($key) . '">' . stars($avg)
         . ' <b class="avg">' . $label . '</b> <span class="count">(' . $count . ')</span></span>';
}
// Ilustrasi sando (SVG) dengan warna isian per produk
function sando_svg(string $fill, int $w = 220): string {
    $fill = h($fill);
    return '<svg class="sando" width="' . $w . '" viewBox="0 0 220 170" aria-hidden="true">
      <rect x="20" y="108" width="180" height="40" rx="14" fill="#E9B467"/>
      <rect x="26" y="112" width="168" height="32" rx="10" fill="#FFF3D3"/>
      <rect x="22" y="84" width="176" height="30" rx="15" fill="#FFFDF6"/>
      <circle cx="58" cy="94" r="13" fill="' . $fill . '"/><circle cx="96" cy="92" r="12" fill="' . $fill . '"/>
      <circle cx="133" cy="94" r="13" fill="' . $fill . '"/><circle cx="168" cy="92" r="11" fill="' . $fill . '"/>
      <rect x="20" y="42" width="180" height="48" rx="16" fill="#E9B467"/>
      <rect x="26" y="48" width="168" height="38" rx="11" fill="#FFF3D3"/>
      <circle cx="60" cy="66" r="2.5" fill="#F3D9A0"/><circle cx="110" cy="72" r="2.5" fill="#F3D9A0"/><circle cx="154" cy="64" r="2.5" fill="#F3D9A0"/>
    </svg>';
}
function product_art(array $product, int $width = 220): string {
    if (!empty($product['image'])) {
        return '<img class="product-photo" src="' . h($product['image']) . '" width="' . $width . '" alt="' . h($product['name']) . '">';
    }
    return sando_svg((string)$product['fill_color'], $width);
}
function review_html(array $r): string {
    return '<article class="review"><div class="review-top"><b>' . h($r['name']) . '</b>' . stars((float)$r['rating'])
         . '</div>' . ($r['comment'] !== '' ? '<p>' . h($r['comment']) . '</p>' : '') . '</article>';
}
