<?php
require_once __DIR__ . "/config/configs.php";

$encodedRef = $_GET['pesanan'] ?? null;

if (!$encodedRef) {
    die("Kode pesanan tidak ditemukan.");
}

$kode_invoice = base64_decode($encodedRef);

if (!$kode_invoice) {
    die("Kode invoice tidak valid.");
}

$result = sql("SELECT * FROM pemesanan WHERE kode_invoice = :kode", [":kode" => $kode_invoice]);
$data = $result['data'][0] ?? null;

if (!$data) {
    die("Data pemesanan tidak ditemukan.");
}

function image(string $tipe){
    switch(strtolower($tipe)){
        case "pagi" : return "./res/img/lapangan-pagi.png";
        case "siang" : return "./res/img/lapangan-siang.png";
        case "malam" : return "./res/img/lapangan-malam.png";
        default: return "./res/img/lapangan-default.png";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="./res/css/bootstrap.css" />
    <title>Struk Pemesanan</title>
    <style>
        /* Background sama seperti sebelumnya */
        #bgimg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background: url('./res/img/Lapangan.png') center/cover no-repeat;
            transform: scaleX(-1);
            z-index: -2;
        }
        #overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            transform: scaleX(-1);
            z-index: -1;
        }

        /* Reset margin body */
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        /* Card container */
        .card-struk {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            width: 350px;
            max-width: 90vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Image on top */
        .card-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            object-position: center;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        /* Header text container */
        .card-header {
            background-color: #0d6efd;
            color: white;
            padding: 1.5rem 1rem 1rem;
            text-align: center;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .card-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .card-header p {
            margin: 0.3rem 0 0;
            font-weight: 500;
            font-size: 1rem;
        }

        /* Content body */
        .card-body {
            padding: 1rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        /* Each row of label & value */
        .row-info {
            display: flex;
            justify-content: space-between;
            font-size: 1rem;
        }

        .row-info .label {
            font-weight: 600;
            color: #444;
        }

        .row-info .value {
            font-weight: 700;
            color: #222;
            max-width: 60%;
            text-align: right;
            word-wrap: break-word;
        }

        /* Footer with button */
        .card-footer {
            padding: 1rem 1.5rem 1.5rem;
            text-align: center;
        }

        .card-footer a.btn {
            width: 100%;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.5rem 0;
            border-radius: 12px;
        }

        /* Status pembayaran style */
        .status-pembayaran {
            text-transform: uppercase;
            color: #0d6efd;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div id="bgimg"></div>
    <div id="overlay"></div>

    <div class="card-struk">
        <div class="card-header">
            <h1>LAPANGAN TERMINAL FUTSAL</h1>
            <p>Struk Pemesanan Lapangan</p>
        </div>

        <img src="<?= image($data['waktubermain']) ?>" alt="Lapangan <?= htmlspecialchars($data['waktubermain']) ?>" class="card-img" />

        <div class="card-body">
            <div class="row-info">
                <div class="label">Nama Pemesan</div>
                <div class="value"><?= htmlspecialchars($data['nama']) ?></div>
            </div>
            <div class="row-info">
                <div class="label">Nomor Telepon</div>
                <div class="value"><?= htmlspecialchars($data['nomorhp']) ?></div>
            </div>
            <div class="row-info">
                <div class="label">Waktu Bermain</div>
                <div class="value"><?= htmlspecialchars($data['waktubermain']) ?></div>
            </div>
            <div class="row-info">
                <div class="label">Durasi Bermain</div>
                <div class="value"><?= (int)$data['durasi'] ?> Jam</div>
            </div>
            <div class="row-info">
                <div class="label">Diskon</div>
                <div class="value"><?= ($data['diskon'] > 0) ? ($data['diskon'] * 100) . '%' : 'Tidak Ada' ?></div>
            </div>
            <div class="row-info">
                <div class="label">Total Bayar</div>
                <div class="value">Rp <?= number_format($data['final'], 0, ',', '.') ?></div>
            </div>
            <div class="row-info">
                <div class="label">Kode Invoice</div>
                <div class="value"><?= htmlspecialchars($data['kode_invoice']) ?></div>
            </div>
            <div class="row-info">
                <div class="label">Status Pembayaran</div>
                <div class="value status-pembayaran"><?= htmlspecialchars($data['status_pembayaran'] ?? 'Belum Bayar') ?></div>
            </div>
        </div>

        <div class="card-footer">
            <a href="pemesanan.php" class="btn btn-primary">Pesan Lagi</a>
        </div>
    </div>
</body>
</html>