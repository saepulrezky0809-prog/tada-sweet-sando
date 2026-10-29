<?php
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

function cart_response(): void {
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) $cart = [];
    $cart = array_filter($cart, static fn($qty, $id) => ctype_digit((string)$id) && (int)$id > 0 && (int)$qty > 0, ARRAY_FILTER_USE_BOTH);
    if (!$cart) {
        $_SESSION['cart'] = [];
        echo json_encode(['ok' => true, 'items' => [], 'count' => 0, 'total' => 0]);
        exit;
    }

    $ids = array_map('intval', array_keys($cart));
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT id, name, price FROM products WHERE is_active = 1 AND id IN ($marks)");
    $st->execute($ids);
    $products = $st->fetchAll();
    $items = [];
    $total = 0;
    $count = 0;
    $active = [];
    foreach ($products as $product) {
        $id = (int)$product['id'];
        $qty = min(20, (int)$cart[$id]);
        $subtotal = (int)$product['price'] * $qty;
        $items[] = ['id' => $id, 'name' => $product['name'], 'price' => (int)$product['price'], 'qty' => $qty, 'subtotal' => $subtotal];
        $active[$id] = $qty;
        $count += $qty;
        $total += $subtotal;
    }
    $_SESSION['cart'] = $active;
    echo json_encode(['ok' => true, 'items' => $items, 'count' => $count, 'total' => $total]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') cart_response();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'error' => 'Metode tidak diizinkan.']));
}
csrf_check();

$action = $_POST['action'] ?? 'add';
if ($action === 'checkout') {
    $name = trim((string)($_POST['customer_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));
    $payment = $_POST['payment_method'] ?? '';
    $phoneDigits = preg_replace('/\D/', '', $phone);

    if ($name === '' || mb_strlen($name) > 80) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Nama wajib diisi (maksimal 80 karakter).']));
    }
    if (!preg_match('/^\+?[0-9][0-9\s().-]{7,19}$/', $phone) || strlen($phoneDigits) < 8 || strlen($phoneDigits) > 15) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Nomor telepon tidak valid.']));
    }
    if ($address === '' || mb_strlen($address) > 300) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Alamat wajib diisi (maksimal 300 karakter).']));
    }
    if (mb_strlen($note) > 200) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Catatan maksimal 200 karakter.']));
    }
    if (!in_array($payment, ['cod', 'transfer', 'qris'], true)) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Pilih metode pembayaran yang valid.']));
    }

    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart) || !$cart) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Keranjang masih kosong.']));
    }
    $cart = array_filter($cart, static fn($qty, $id) => ctype_digit((string)$id) && (int)$id > 0 && (int)$qty > 0, ARRAY_FILTER_USE_BOTH);
    if (!$cart) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Keranjang masih kosong.']));
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();
        $ids = array_map('intval', array_keys($cart));
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $productsQuery = $pdo->prepare("SELECT id, name, price FROM products WHERE is_active = 1 AND id IN ($marks) FOR UPDATE");
        $productsQuery->execute($ids);
        $products = $productsQuery->fetchAll();
        if (count($products) !== count($cart)) {
            $pdo->rollBack();
            http_response_code(409);
            exit(json_encode(['ok' => false, 'error' => 'Ada menu di keranjang yang sudah tidak tersedia. Periksa kembali keranjang.']));
        }

        $items = [];
        $subtotal = 0;
        foreach ($products as $product) {
            $id = (int)$product['id'];
            $qty = (int)$cart[$id];
            if ($qty < 1 || $qty > 20) {
                $pdo->rollBack();
                http_response_code(422);
                exit(json_encode(['ok' => false, 'error' => 'Jumlah menu di keranjang tidak valid.']));
            }
            $price = (int)$product['price'];
            $subtotal += $price * $qty;
            $items[] = ['id' => $id, 'name' => $product['name'], 'price' => $price, 'qty' => $qty];
        }

        $orderCode = '';
        $checkCode = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE order_code = ?');
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'TS' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
            $checkCode->execute([$candidate]);
            if (!$checkCode->fetchColumn()) {
                $orderCode = $candidate;
                break;
            }
        }
        if ($orderCode === '') throw new RuntimeException('Kode pesanan tidak dapat dibuat. Silakan coba lagi.');

        $shipping = 0;
        $status = $payment === 'cod' ? 'diproses' : 'menunggu_pembayaran';
        $orderInsert = $pdo->prepare('INSERT INTO orders (order_code, customer_name, phone, address, note, subtotal, shipping, total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $orderInsert->execute([$orderCode, $name, $phone, $address, $note, $subtotal, $shipping, $subtotal + $shipping, $payment, $status]);
        $orderId = (int)$pdo->lastInsertId();
        $itemInsert = $pdo->prepare('INSERT INTO order_items (order_id, product_id, name, price, qty) VALUES (?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $itemInsert->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty']]);
        }
        $pdo->commit();
        $_SESSION['cart'] = [];
        echo json_encode(['ok' => true, 'code' => $orderCode, 'status' => $status]);
        exit;
    } catch (PDOException $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Checkout database error: ' . $error->getMessage());
        http_response_code(500);
        exit(json_encode(['ok' => false, 'error' => 'Pesanan gagal disimpan ke database. Keranjang tetap tersimpan; silakan coba lagi.']));
    } catch (RuntimeException $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        exit(json_encode(['ok' => false, 'error' => $error->getMessage()]));
    }
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!in_array($action, ['add', 'set', 'remove', 'clear'], true)) {
    http_response_code(422);
    exit(json_encode(['ok' => false, 'error' => 'Aksi keranjang tidak valid.']));
}
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    cart_response();
}
if (!$id || $id < 1) {
    http_response_code(422);
    exit(json_encode(['ok' => false, 'error' => 'Menu tidak valid.']));
}

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) $cart = [];
if ($action === 'remove') {
    unset($cart[$id]);
} else {
    $st = db()->prepare('SELECT COUNT(*) FROM products WHERE id = ? AND is_active = 1');
    $st->execute([$id]);
    if (!$st->fetchColumn()) {
        http_response_code(404);
        exit(json_encode(['ok' => false, 'error' => 'Menu tidak tersedia.']));
    }
    $qty = $action === 'add' ? (int)($cart[$id] ?? 0) + 1 : filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_INT);
    if ($qty === false || $qty < 0) {
        http_response_code(422);
        exit(json_encode(['ok' => false, 'error' => 'Jumlah menu tidak valid.']));
    }
    if ($qty === 0) unset($cart[$id]);
    else $cart[$id] = min(20, $qty);
}
$_SESSION['cart'] = $cart;
cart_response();
