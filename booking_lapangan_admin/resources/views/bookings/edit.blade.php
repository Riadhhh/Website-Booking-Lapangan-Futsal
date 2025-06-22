<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-card {
            border: 1px solid #5a9bd8;
            box-shadow: 0 4px 10px rgba(90, 155, 216, 0.2);
            border-radius: 0.5rem;
        }
        h1 {
            border-bottom: 3px solid #5a9bd8;
            padding-bottom: 0.3rem;
            color: #2c3e50;
            font-weight: 700;
        }
        label {
            color: #1b3b72;
            font-weight: 600;
        }
        input.form-control:focus,
        select.form-select:focus {
            border-color: #3a72af;
            box-shadow: 0 0 8px rgba(58, 114, 175, 0.5);
            outline: none;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #218838);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #19692c);
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
        .payment-proof-link {
            color: #2a64bf;
            font-weight: 600;
            text-decoration: none;
            transition: text-decoration 0.3s;
        }
        .payment-proof-link:hover {
            text-decoration: underline;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }
    </style>

</head>
<body>
@extends('layouts.app')

@section('content')
    <div class="container mt-5 mb-5">
        <h1 class="mb-4">Edit Pemesanan</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan!</strong>
                <ul class="mb-0">
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

        <div class="card">
            <div class="card-body">
                <form action="{{ route('bookings.update', $booking->id) }}" method="POST" oninput="hitungHarga()">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 row">
                        <label for="nama" class="col-sm-3 col-form-label">Nama Lengkap</label>
                        <div class="col-sm-9">
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $booking->nama) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="nomorhp" class="col-sm-3 col-form-label">Nomor Telepon</label>
                        <div class="col-sm-9">
                            <input type="text" name="nomorhp" id="nomorhp" class="form-control"
                                value="{{ old('nomorhp', $booking->nomorhp) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="waktubermain" class="col-sm-3 col-form-label">Waktu Bermain</label>
                        <div class="col-sm-9">
                            <select name="waktubermain" id="waktubermain" class="form-select" required>
                                <option value="Pagi" {{ $booking->waktubermain == 'Pagi' ? 'selected' : '' }}>Pagi (Rp 100.000)</option>
                                <option value="Siang" {{ $booking->waktubermain == 'Siang' ? 'selected' : '' }}>Siang (Rp 110.000)</option>
                                <option value="Malam" {{ $booking->waktubermain == 'Malam' ? 'selected' : '' }}>Malam (Rp 130.000)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="tglpesan" class="col-sm-3 col-form-label">Tanggal Pesan</label>
                        <div class="col-sm-9">
                            <input type="date" name="tglpesan" id="tglpesan" class="form-control"
                                value="{{ old('tglpesan', $booking->tglpesan) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="jam_mulai" class="col-sm-3 col-form-label">Jam Mulai</label>
                        <div class="col-sm-9">
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control"
                                value="{{ old('jam_mulai', $booking->jam_mulai) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="durasi" class="col-sm-3 col-form-label">Durasi Bermain (jam)</label>
                        <div class="col-sm-9">
                            <input type="number" name="durasi" id="durasi" class="form-control"
                                value="{{ old('durasi', $booking->durasi) }}" min="1" max="12" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="airmineral" class="col-sm-3 col-form-label">Air Mineral</label>
                        <div class="col-sm-9">
                            <select name="airmineral" id="airmineral" class="form-select">
                                <option value="0" {{ $booking->airmineral == 0 ? 'selected' : '' }}>Tanpa</option>
                                <option value="25000" {{ $booking->airmineral == 25000 ? 'selected' : '' }}>Termasuk (+ Rp 25.000)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="diskon" class="col-sm-3 col-form-label">Diskon</label>
                        <div class="col-sm-9">
                            <input type="text" name="diskon" id="diskon" class="form-control" readonly value="{{ old('diskon', $booking->diskon) }}%">
                        </div>
                    </div>


                    <div class="mb-3 row">
                        <label for="final" class="col-sm-3 col-form-label">Total Harga (Final)</label>
                        <div class="col-sm-9">
                            <input type="text" name="final" id="final" class="form-control"
                                value="{{ old('final', $booking->final) }}" required readonly>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="status_pembayaran" class="col-sm-3 col-form-label">Status Pembayaran</label>
                        <div class="col-sm-9">
                            <select name="status_pembayaran" id="status_pembayaran" class="form-select" required>
                                <option value="pending" {{ $booking->status_pembayaran == 'pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                                <option value="paid" {{ $booking->status_pembayaran == 'paid' ? 'selected' : '' }}>Lunas</option>
                                <option value="expired" {{ $booking->status_pembayaran == 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="kode_invoice" class="col-sm-3 col-form-label">Kode Invoice</label>
                        <div class="col-sm-9">
                            <input type="text" name="kode_invoice" id="kode_invoice" class="form-control"
                                value="{{ $booking->kode_invoice }}" readonly>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start gap-2 mt-4">
                        <button type="submit" class="btn btn-success">Perbarui</button>
                        <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
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

    let subtotal = hargaPerJam * durasi;
    let diskon = durasi > 3 ? 10 : 0;
    let total = subtotal - (subtotal * (diskon / 100)) + airmineral;

    document.getElementById('diskon').value = diskon + '%';
    document.getElementById('final').value = Math.round(total);
}
</script>
@endsection
</body>
</html>
