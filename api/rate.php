<?php
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

function out(array $d, int $code = 200) { http_response_code($code); echo json_encode($d); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'Metode tidak valid.'], 405);

$type    = $_POST['type'] ?? '';
$id      = (int)($_POST['id'] ?? 0);
$rating  = (int)($_POST['rating'] ?? 0);
$name    = trim(mb_substr($_POST['name'] ?? '', 0, 40));
$comment = trim(mb_substr($_POST['comment'] ?? '', 0, 300));

if (!in_array($type, ['product', 'site'], true)) out(['ok' => false, 'error' => 'Jenis rating tidak valid.'], 422);
if ($rating < 1 || $rating > 5) out(['ok' => false, 'error' => 'Pilih 1 sampai 5 bintang.'], 422);
if (mb_strlen($name) < 2) out(['ok' => false, 'error' => 'Isi nama minimal 2 huruf.'], 422);

$pdo = db();
$ip  = ip_hash();

if ($type === 'product') {
    $chk = $pdo->prepare('SELECT COUNT(*) FROM products WHERE id = ?');
    $chk->execute([$id]);
    if (!$chk->fetchColumn()) out(['ok' => false, 'error' => 'Produk tidak ditemukan.'], 404);

    $dup = $pdo->prepare('SELECT COUNT(*) FROM product_ratings WHERE product_id = ? AND ip_hash = ? AND created_at > NOW() - INTERVAL 1 DAY');
    $dup->execute([$id, $ip]);
    if ($dup->fetchColumn()) out(['ok' => false, 'error' => 'Kamu sudah menilai menu ini hari ini. Coba lagi besok ya.'], 429);

    $pdo->prepare('INSERT INTO product_ratings (product_id, name, rating, comment, ip_hash) VALUES (?,?,?,?,?)')
        ->execute([$id, $name, $rating, $comment, $ip]);
    $s = $pdo->prepare('SELECT AVG(rating) a, COUNT(*) c FROM product_ratings WHERE product_id = ?');
    $s->execute([$id]);
    $key = 'product-' . $id;
} else {
    $dup = $pdo->prepare('SELECT COUNT(*) FROM site_ratings WHERE ip_hash = ? AND created_at > NOW() - INTERVAL 1 DAY');
    $dup->execute([$ip]);
    if ($dup->fetchColumn()) out(['ok' => false, 'error' => 'Kamu sudah menilai website ini hari ini. Terima kasih!'], 429);

    $pdo->prepare('INSERT INTO site_ratings (name, rating, comment, ip_hash) VALUES (?,?,?,?)')
        ->execute([$name, $rating, $comment, $ip]);
    $s = $pdo->query('SELECT AVG(rating) a, COUNT(*) c FROM site_ratings');
    $key = 'site';
}
$row = $s->fetch();
out(['ok' => true, 'key' => $key, 'avg' => round((float)$row['a'], 1), 'count' => (int)$row['c'],
     'review' => ['name' => $name, 'rating' => $rating, 'comment' => $comment]]);
