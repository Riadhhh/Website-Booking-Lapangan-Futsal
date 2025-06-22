<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Booking - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-card {
            border: 1px solid #5a9bd8;
            box-shadow: 0 4px 10px rgba(90, 155, 216, 0.2);
            border-radius: 0.5rem;
            padding: 2rem;
        }
        h1 {
            border-bottom: 3px solid #5a9bd8;
            padding-bottom: 0.3rem;
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        label {
            color: #1b3b72;
            font-weight: 600;
        }
        .btn-success {
            background-color: #3a9bd8;
            border: none;
        }
        .btn-success:hover {
            background-color: #2a7cc8;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            opacity: 0.85;
        }
        .btn-secondary:hover {
            opacity: 1;
            background-color: #5a6268;
        }
        .alert-danger {
            border-color: #d9534f;
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <h1 class="mb-4">Tambah Pemesanan Baru</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->has('overlap'))
        <div class="alert alert-warning">
            ⚠️ {{ $errors->first('overlap') }}
        </div>
    @endif

    <form action="{{ route('bookings.store') }}" method="POST" onsubmit="return prepareAndValidateForm()">
        @csrf

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="nomorhp" class="form-label">Nomor Telepon</label>
            <input type="text" name="nomorhp" id="nomorhp" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="waktubermain" class="form-label">Waktu Bermain</label>
            <select name="waktubermain" id="waktubermain" class="form-select" required onchange="hitungHarga()">
                <option value="">Pilih Waktu</option>
                <option value="Pagi">Pagi (Rp 100.000)</option>
                <option value="Siang">Siang (Rp 110.000)</option>
                <option value="Malam">Malam (Rp 130.000)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="tglpesan" class="form-label">Tanggal Pesan</label>
            <input type="date" name="tglpesan" id="tglpesan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="jam_mulai" class="form-label">Jam Mulai Bermain</label>
            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="durasi" class="form-label">Durasi Bermain (jam)</label>
            <input type="number" name="durasi" id="durasi" class="form-control" min="1" max="12" required oninput="hitungHarga()">
        </div>

        <div class="mb-3">
            <label for="airmineral" class="form-label">Air Mineral</label>
            <select name="airmineral" id="airmineral" class="form-select" onchange="hitungHarga()">
                <option value="0">Tanpa</option>
                <option value="25000">Termasuk (+ Rp 25.000)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="diskon" class="form-label">Diskon</label>
            <input type="text" name="diskon" id="diskon" class="form-control" readonly value="0">
        </div>

        <div class="mb-3">
            <label for="final" class="form-label">Total Harga (Final)</label>
            <input type="text" name="final" id="final" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
            <select name="status_pembayaran" id="status_pembayaran" class="form-select" required>
                <option value="pending">Menunggu Bayar</option>
                <option value="paid">Lunas</option>
                <option value="expired">Kedaluwarsa</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="kode_invoice" class="form-label">Kode Invoice (Opsional)</label>
            <input type="text" name="kode_invoice" id="kode_invoice" class="form-control">
        </div>

        <div class="d-flex">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary ms-2">Kembali</a>
        </div>
    </form>
</div>

<script>
function hitungHarga() {
    const waktu = document.getElementById('waktubermain').value;
    const durasi = parseInt(document.getElementById('durasi').value) || 0;
    const airmineral = parseInt(document.getElementById('airmineral').value) || 0;

    let hargaPerJam = 0;
    if (waktu === 'Pagi') hargaPerJam = 100000;
    else if (waktu === 'Siang') hargaPerJam = 110000;
    else if (waktu === 'Malam') hargaPerJam = 130000;

    let subtotal = hargaPerJam * durasi + airmineral;
    let diskonPersen = 0;

    if (durasi > 3) {
        diskonPersen = 10;
        subtotal = subtotal - (subtotal * diskonPersen / 100);
    }

    document.getElementById('diskon').value = diskonPersen + '%';
    document.getElementById('final').value = 'Rp ' + subtotal.toLocaleString('id-ID');

    document.getElementById('diskon').dataset.raw = diskonPersen;
    document.getElementById('final').dataset.raw = subtotal;
}

function prepareAndValidateForm() {
    const durasi = parseInt(document.getElementById('durasi').value);
    if (durasi < 1 || durasi > 12) {
        alert('Durasi bermain harus antara 1 sampai 12 jam.');
        return false;
    }

    const diskonInput = document.getElementById('diskon');
    const finalInput = document.getElementById('final');

    diskonInput.value = diskonInput.dataset.raw || 0;
    finalInput.value = finalInput.dataset.raw || 0;

    return true;
}
document.addEventListener('DOMContentLoaded', hitungHarga);
</script>
@endsection
</body>
</html>
