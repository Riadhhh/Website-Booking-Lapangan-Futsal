<?php
require_once __DIR__ . "/config/configs.php";
require_once __DIR__ . "/config/tripay_config.php";
require_once __DIR__ . "/config/tripay_function.php";

define("DISCOUNT", 0.1);
define("LAMA_HARI_DSC", 3);
define("WAKTU_BERMAIN", [
    "100000" => "Pagi",
    "110000" => "Siang",
    "130000" => "Malam"
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {

    $nama       = htmlspecialchars(trim($_POST['pemesan'] ?? ''));
    $nohp       = htmlspecialchars(trim($_POST['nomorhp'] ?? ''));
    $hargaLap   = intval($_POST['waktubermain'] ?? 0);
    $tglpesan   = htmlspecialchars($_POST['tanggalpesan'] ?? '');
    $jamMulai   = htmlspecialchars($_POST['jammulai'] ?? '');
    $jamMulaiTime = strtotime($jamMulai);
    $batasAwal    = strtotime('08:00');
    $batasAkhir   = strtotime('23:00');

    if ($jamMulaiTime < $batasAwal || $jamMulaiTime > $batasAkhir) {
        echo "<script>alert('Jam bermain hanya diperbolehkan antara pukul 08:00 hingga 23:00.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    $durasi     = intval($_POST['durasibermain'] ?? 0);
    $airmineral = isset($_POST['water']) ? intval($_POST['water']) : 0;

    $waktuMain = WAKTU_BERMAIN[$hargaLap] ?? "Tidak diketahui";

    if (!preg_match('/^08[0-9]{8,12}$/', $nohp)) {
        die("Nomor HP tidak valid!");
    }

    $jamMain = intval(str_replace(':', '', $jamMulai));
    $validasiWaktu = [
        "100000" => [800, 1200],
        "110000" => [1200, 1700],
        "130000" => [1700, 2300] 
    ];
    
    if (!isset($validasiWaktu[$hargaLap])) {
        echo "<script>alert('Waktu bermain tidak valid.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    [$start, $end] = $validasiWaktu[$hargaLap];
    if ($jamMain < $start || $jamMain >= $end) {
        echo "<script>alert('Jam mulai tidak sesuai dengan kategori waktu bermain yang dipilih.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    $totalHarga = $durasi * $hargaLap;
    $diskon     = ($durasi > LAMA_HARI_DSC) ? DISCOUNT : 0;
    $potongan   = $totalHarga * $diskon;
    $finalHarga = $totalHarga - $potongan + $airmineral;

    $kodeInvoice = 'INV' . time();

    date_default_timezone_set('Asia/Jakarta');
    $now = new DateTime();
    $bookingDatetime = DateTime::createFromFormat('Y-m-d H:i', "$tglpesan $jamMulai");

    if (!$bookingDatetime || $bookingDatetime < $now) {
        echo "<script>alert('Anda tidak bisa membooking waktu yang telah lewat. Silakan pilih waktu yang sesuai.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    $maxBookingDate = (clone $now)->modify('+30 days');
    if ($bookingDatetime > $maxBookingDate) {
        echo "<script>alert('Anda hanya bisa membooking maksimal 30 hari ke depan.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    // Cek jadwal booking
    $cekBentrok = sql("SELECT COUNT(*) as total FROM pemesanan 
        WHERE tglpesan = :tgl
        AND (
            TIME(:jam) < ADDTIME(jam_mulai, SEC_TO_TIME(durasi * 3600)) 
            AND ADDTIME(TIME(:jam), SEC_TO_TIME(:durasi * 3600)) > jam_mulai
        )
        AND status_pembayaran = 'success'", [
        ":tgl" => $tglpesan,
        ":jam" => $jamMulai,
        ":durasi" => $durasi
    ])[0]['total'];

    if ($cekBentrok > 0) {
        echo "<script>alert('Waktu bentrok dengan jadwal yang sudah dibooking.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    sql("DELETE FROM pemesanan 
        WHERE status_pembayaran = 'pending' 
        AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 60");

    $cekBooking = sql("SELECT COUNT(*) as total FROM pemesanan 
        WHERE tglpesan = :tgl 
        AND jam_mulai = :jam 
        AND waktubermain = :waktu 
        AND status_pembayaran IN ('pending', 'paid')", [
        ':tgl'   => $tglpesan,
        ':jam'   => $jamMulai,
        ':waktu' => $waktuMain
    ]);

    if ($cekBooking[0]['total'] > 0) {
        echo "<script>alert('Waktu yang Anda pilih sudah dibooking. Silakan pilih waktu lain.'); window.location.href='pemesanan.php';</script>";
        exit;
    }

    $res = createTripayInvoice($finalHarga, $nama, $nohp, $kodeInvoice);

    if ($res['success'] === true) {
        $paymentUrl = $res['data']['checkout_url'] ?? '';

        sql("INSERT INTO pemesanan 
            (nama, nomorhp, waktubermain, tglpesan, jam_mulai, durasi, airmineral, diskon, final, kode_invoice, status_pembayaran)
            VALUES 
            (:nama, :nohp, :waktu, :tgl, :jam, :durasi, :air, :diskon, :final, :kode, 'pending')", [
            ":nama"   => $nama,
            ":nohp"   => $nohp,
            ":waktu"  => $waktuMain,
            ":tgl"    => $tglpesan,
            ":jam"    => $jamMulai,
            ":durasi" => $durasi,
            ":air"    => $airmineral,
            ":diskon" => $diskon,
            ":final"  => $finalHarga,
            ":kode"   => $kodeInvoice
        ]);
        header("Location: $paymentUrl");
        exit;
    } else {
        echo "<h3>Gagal membuat invoice:</h3>";
        echo "<pre>";
        print_r($res);
        echo "</pre>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="./res/css/bootstrap.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="icon" href="res/img/favicon.ico" type="image/jpeg">
    <script src="./res/js/jquery.js"></script>
    <title>Pemesanan Lapangan</title>
</head>
<body class="d-flex flex-column vh-100 w-100 align-items-center">
    <form class="d-flex flex-column mt-2 pb-5" style="width:400px" action="proses_pembayaran.php" method="POST">
        <div class="d-flex flex-row align-items-center gap-3 mb-3">
            <a href="index.php" class="fas fa-house fs-6 text-primary"></a>|
            <p class="fs-3 fw-bold m-0">Pesan Lapangan</p>
        </div>

        <div class="mb-3">
            <label for="namapemesan" class="form-label">Nama lengkap</label>
            <input type="text" class="form-control" id="namapemesan" name="pemesan" required
                value="<?= isset($_POST['pemesan']) ? htmlspecialchars($_POST['pemesan']) : '' ?>">
        </div>

        <div class="mt-1 mb-3">
            <label for="nomorhp" class="form-label">Nomor Telepon</label>
            <input type="tel" pattern="08[0-9]{8,12}" class="form-control" id="nomorhp" name="nomorhp" required
                value="<?= isset($_POST['nomorhp']) ? htmlspecialchars($_POST['nomorhp']) : '' ?>">
            <?php if (!empty($nohp_invalid)) : ?>
                <div class="form-text text-danger" style="font-size:.9rem">
                    <?= htmlspecialchars($nohp_invalid) ?>
                </div>
            <?php endif; ?>
        </div>

        <label for="waktubermain" class="mb-2">Waktu Bermain</label>
        <select class="form-select" id="waktubermain" name="waktubermain" required>
            <option value="0" <?= (isset($_POST['waktubermain']) && $_POST['waktubermain'] == '0') ? 'selected' : '' ?>>- Pilih Waktu Bermain -</option>
            <option value="100000" <?= (isset($_POST['waktubermain']) && $_POST['waktubermain'] == '100000') ? 'selected' : '' ?>>Pagi (08.00–12.00) - Rp 100.000</option>
            <option value="110000" <?= (isset($_POST['waktubermain']) && $_POST['waktubermain'] == '110000') ? 'selected' : '' ?>>Siang (12.00–17.00) - Rp 110.000</option>
            <option value="130000" <?= (isset($_POST['waktubermain']) && $_POST['waktubermain'] == '130000') ? 'selected' : '' ?>>Malam (17.00–23.00) - Rp 130.000</option>
        </select>
        <?php if (!empty($error_waktubermain)) : ?>
            <div class="form-text text-danger" style="font-size:.9rem"><?= htmlspecialchars($error_waktubermain) ?></div>
        <?php endif; ?>
        <small class="form-text text-muted mb-3">
            <b>Jam Berlaku:</b> Pagi (08.00–12.00), Siang (12.00–17.00), Malam (17.00–23.00)
        </small>

        <div class="mt-3">
            <label for="tanggalpesan" class="form-label">Tanggal pesan</label>
            <input type="date" class="form-control" id="tanggalpesan" name="tanggalpesan" required>
            <div id="tanggal-error" class="form-text text-danger d-none" style="font-size:.9rem">Tanggal tidak boleh sebelum hari ini.</div>
        </div>    
        <div class="mt-3">
            <label for="jammulai" class="form-label">Jam Mulai Bermain</label>
            <input type="time" class="form-control" id="jammulai" name="jammulai" min="08:00" max="23:00" required>
            <div id="jam-error" class="form-text text-danger d-none" style="font-size:.9rem">Jam mulai tidak boleh sebelum waktu sekarang jika tanggal hari ini.</div>
        </div>

        <div class="mt-3">
            <label for="durasibermain" class="form-label">Durasi bermain futsal (jam) (Lebih 3 jam diskon 10%)</label>
            <input type="number" min="1" class="form-control" id="durasibermain" name="durasibermain" required
                value="<?= isset($_POST['durasibermain']) ? (int)$_POST['durasibermain'] : '' ?>">
            <?php if (!empty($error_durasibermain)) : ?>
                <div class="form-text text-danger" style="font-size:.9rem"><?= htmlspecialchars($error_durasibermain) ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-row align-items-center gap-3 mt-3">
            <input class="form-check-input" type="checkbox" id="water" value="25000" name="water"
                <?= isset($_POST['water']) ? 'checked' : '' ?>>
            <label for="water">Termasuk Air Mineral (Rp 25.000,-)</label>
        </div>

        <div class="input-group mt-3">
            <input type="text" readonly class="form-control" placeholder="Harga total" id="final" name="harga_total_display"
                value="<?= isset($_POST['harga_total_display']) ? htmlspecialchars($_POST['harga_total_display']) : '' ?>">
            <input type="hidden" name="harga_total" id="harga_total_hidden" value="<?= isset($_POST['harga_total']) ? htmlspecialchars($_POST['harga_total']) : '' ?>">
        </div>

        <button type="submit" name="pesan" class="btn btn-primary mt-4" id="submit">Buat pesanan</button>
    </form>


<script>
    document.addEventListener('DOMContentLoaded', function () {
    const waktubermain = document.getElementById('waktubermain');
    const jammulai = document.getElementById('jammulai');
    const tanggalInput = document.getElementById('tanggalpesan');
    const form = document.getElementById('form-pemesanan');
    const tanggalError = document.getElementById('tanggal-error');
    const jamError = document.getElementById('jam-error');

    const today = new Date().toISOString().split('T')[0];
    tanggalInput.setAttribute('min', today);

    function updateJamRange() {
        const waktu = waktubermain.value;

        if (waktu === '100000') {
            jammulai.min = "08:00";
            jammulai.max = "11:59";
        } else if (waktu === '110000') {
            jammulai.min = "12:00";
            jammulai.max = "16:59";
        } else if (waktu === '130000') {
            jammulai.min = "17:00";
            jammulai.max = "22:59";
        } else {
            jammulai.min = "08:00";
            jammulai.max = "23:00";
        }

        if (jammulai.value && (jammulai.value < jammulai.min || jammulai.value > jammulai.max)) {
            jammulai.value = "";
        }
    }

    waktubermain.addEventListener('change', updateJamRange);
    updateJamRange();

    tanggalInput.addEventListener('change', () => {
        tanggalError.classList.add('d-none');
        jamError.classList.add('d-none');

        const selectedDate = new Date(tanggalInput.value);
        const now = new Date();

        if (selectedDate.toDateString() === now.toDateString()) {
            let hours = now.getHours();
            let minutes = now.getMinutes();
            if (minutes < 10) minutes = '0' + minutes;
            const timeNow = `${hours}:${minutes}`;
            jammulai.setAttribute('min', timeNow);
        } else {
            updateJamRange();
        }
    });

    form.addEventListener('submit', function (e) {
        tanggalError.classList.add('d-none');
        jamError.classList.add('d-none');

        const selectedDate = new Date(tanggalInput.value);
        const now = new Date();

        if (selectedDate < new Date(today)) {
            e.preventDefault();
            tanggalError.classList.remove('d-none');
            return;
        }

        if (selectedDate.toDateString() === now.toDateString()) {
            const selectedTime = jammulai.value;
            if (!selectedTime) {
                e.preventDefault();
                jamError.classList.remove('d-none');
                return;
            }
            const [jam, menit] = selectedTime.split(':').map(Number);
            if (jam < now.getHours() || (jam === now.getHours() && menit < now.getMinutes())) {
                e.preventDefault();
                jamError.classList.remove('d-none');
                return;
            }
        }
    });
});

const calc = () => {
    const presdis = 0.1;
    const lapangan = parseFloat($("#waktubermain").val());
    const durasi = parseFloat($("#durasibermain").val());
    var airmineral = 0;

    if (isNaN(durasi) || durasi <= 0) {
        $("#durasi-notice").removeClass("d-none").addClass("d-flex");
        return false;
    } else {
        $("#durasi-notice").removeClass("d-flex").addClass("d-none");
    }

    if (isNaN(lapangan) || lapangan <= 0) {
        $("#waktubermain-notice").removeClass("d-none").addClass("d-flex");
        return false;
    } else {
        $("#waktubermain-notice").removeClass("d-flex").addClass("d-none");
    }

    if ($("#water").is(":checked")) {
        airmineral = parseFloat($("#water").val());
    }

    let harga = lapangan * durasi;
    if (durasi > 3) {
        harga -= harga * presdis;
    }
    harga += airmineral;

    $("#final").val("Rp " + harga.toLocaleString("id-ID"));
    return true;
}

$("#water").click(() => {
    $("#submit").attr("type", calc() ? "submit" : "button");
});

$("#final,#durasibermain,#waktubermain").on("input", () => {
    $("#submit").attr("type", calc() ? "submit" : "button");
});
</script>
</body>
</html>