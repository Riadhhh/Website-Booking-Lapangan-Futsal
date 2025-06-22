<?php
require_once __DIR__ . '/config/configs.php';
require_once __DIR__ . '/config/tripay_config.php';

define("DISKON", 0.1);
define("WAKTU_BERMAIN", [
    "100000" => "Pagi",
    "110000" => "Siang",
    "130000" => "Malam"
]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak sah.");
}

$nama       = htmlspecialchars(trim($_POST['pemesan'] ?? ''));
$nomorhp    = htmlspecialchars(trim($_POST['nomorhp'] ?? ''));
$hargaLap   = intval($_POST['waktubermain'] ?? 0);
$tanggal    = htmlspecialchars(trim($_POST['tanggalpesan'] ?? ''));
$jamMulai   = htmlspecialchars(trim($_POST['jammulai'] ?? ''));
$durasi     = intval($_POST['durasibermain'] ?? 0);
$airmineral = isset($_POST['water']) ? intval($_POST['water']) : 0;
$tanggalpesan = $_POST['tanggalpesan'];
$jammulai = $_POST['jammulai'];
$waktu_pesan = strtotime($tanggalpesan . ' ' . $jammulai);
$waktu_sekarang = time();

if ($waktu_pesan < $waktu_sekarang) {
    session_start();
    $_SESSION['error_waktu'] = "Tanggal dan jam bermain tidak boleh sebelum waktu sekarang.";
    header("Location: pemesanan.php");
    exit;
}

if (!preg_match('/^08[0-9]{8,12}$/', $nomorhp)) {
    die("Nomor HP tidak valid.");
}

$subTotal     = $hargaLap * $durasi;
$diskon       = ($durasi > 3) ? DISKON : 0;
$hargaDiskon  = $subTotal * $diskon;
$total        = $subTotal - $hargaDiskon + $airmineral;
$kodeInvoice  = 'INV-' . time() . '-' . rand(100, 999);
$namaWaktu    = WAKTU_BERMAIN[$hargaLap] ?? "Tidak diketahui";

sql("INSERT INTO pemesanan 
    (nama, nomorhp, waktubermain, tglpesan, jam_mulai, durasi, airmineral, diskon, final, kode_invoice, status_pembayaran)
    VALUES 
    (:nama, :nomorhp, :waktu, :tgl, :jam, :durasi, :air, :diskon, :final, :kode, 'pending')", [
    ':nama'    => $nama,
    ':nomorhp' => $nomorhp,
    ':waktu'   => $namaWaktu,
    ':tgl'     => $tanggal,
    ':jam'     => $jamMulai,
    ':durasi'  => $durasi,
    ':air'     => $airmineral,
    ':diskon'  => $diskon,
    ':final'   => $total,
    ':kode'    => $kodeInvoice
]);

$signature = hash_hmac('sha256', TRIPAY_MERCHANT_CODE . $kodeInvoice . $total, TRIPAY_PRIVATE_KEY);

$current_url   = "https://8ea5-2001-448a-10ce-318d-a451-7e7d-a26e-34d0.ngrok-free.app/akhir"; //Sesuaikan dengan URL yang di punya
$return_url    = $current_url . "/strukpemesanan.php?pesanan=" . urlencode(base64_encode($kodeInvoice));
$callback_url  = $current_url . "/callback.php";

$payload = [
    'method'         => 'QRIS',
    'merchant_ref'   => $kodeInvoice,
    'amount'         => $total,
    'customer_name'  => $nama,
    'customer_email' => 'default@email.com',
    'customer_phone' => $nomorhp,
    'order_items'    => [[
        'sku'      => 'LAPANGAN-001',
        'name'     => 'Booking Lapangan Futsal',
        'price'    => $total,
        'quantity' => 1
    ]],
    'return_url'     => $return_url,
    'callback_url'   => $callback_url,
    'expired_time'   => time() + (60 * 60 * 4), // 4 jam
    'signature'      => $signature
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => TRIPAY_API_URL . '/transaction/create',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . TRIPAY_API_KEY],
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($payload),
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
]);

$response   = curl_exec($curl);
$http_code  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error      = curl_error($curl);
curl_close($curl);

if ($error) {
    die("Gagal koneksi ke Tripay: $error");
}

$result = json_decode($response, true);

if ($result && isset($result['success']) && $result['success'] === true) {
    header("Location: " . $result['data']['checkout_url']);
    exit;
} else {
    echo "<h3>Gagal membuat pembayaran:</h3>";
    echo "Status HTTP: $http_code<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}