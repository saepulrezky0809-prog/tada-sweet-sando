<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
csrf_check();
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);
flash('Anda sudah keluar dari admin.');
redirect('login.php');
