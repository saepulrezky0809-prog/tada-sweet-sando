<?php
// ====== Pengaturan: sesuaikan dengan server kamu ======
const DB_HOST = '127.0.0.1';
const DB_NAME = 'tada_sando';
const DB_USER = 'root';
const DB_PASS = '';
const WA_NUMBER = '6285141521171';   // nomor WhatsApp dalam format internasional
const HASH_SALT = 'ganti-teks-rahasia-ini';
const STORE_NAME = 'Tada Sweet Sando';
const BANK_NAME = 'Isi nama bank di config.php';
const BANK_ACCOUNT = 'Isi nomor rekening di config.php';
const BANK_HOLDER = 'Isi nama pemilik rekening di config.php';
const QRIS_IMAGE = 'assets/qris.png';

const PAY_LABEL = [
    'cod' => 'Bayar di tempat',
    'transfer' => 'Transfer bank',
    'qris' => 'QRIS',
];
const ORDER_STATUS = [
    'menunggu_pembayaran' => 'Menunggu pembayaran',
    'menunggu_verifikasi' => 'Menunggu verifikasi',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
