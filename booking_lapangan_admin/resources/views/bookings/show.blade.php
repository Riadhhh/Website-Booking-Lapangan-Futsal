<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Booking - Admin</title>
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
        .data-value {
            font-weight: 500;
            color: #34495e;
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
    </style>
</head>
<body>
@extends('layouts.app')

@section('content')
    <div class="container mt-5 mb-5">
        <h1 class="mb-4">Detail Pemesanan</h1>

        <div class="card p-4 shadow-sm">
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                <div class="col-sm-9">{{ $booking->nama }}</div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Nomor Telepon</label>
                <div class="col-sm-9">{{ $booking->nomorhp }}</div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Waktu Bermain</label>
                <div class="col-sm-9">{{ $booking->waktubermain }}</div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Tanggal Pesan</label>
                <div class="col-sm-9">
                    {{ \Carbon\Carbon::parse($booking->tglpesan)->translatedFormat('d F Y') }}
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Jam Mulai</label>
                <div class="col-sm-9">{{ $booking->jam_mulai }}</div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Durasi Bermain</label>
                <div class="col-sm-9">{{ $booking->durasi }} jam</div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Air Mineral</label>
                <div class="col-sm-9">
                    {{ $booking->airmineral == 25000 ? 'Termasuk (+Rp 25.000)' : 'Tanpa' }}
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Diskon</label>
                <div class="col-sm-9">
                    {{ $booking->diskon ?? 0 }}%
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Total Harga</label>
                <div class="col-sm-9">
                    Rp {{ number_format($booking->final, 0, ',', '.') }}
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Status Pembayaran</label>
                <div class="col-sm-9">
                    <span class="badge
                        {{ $booking->status_pembayaran === 'paid' ? 'bg-success' :
                        ($booking->status_pembayaran === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                        {{ strtoupper($booking->status_pembayaran) }}
                    </span>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label">Kode Invoice</label>
                <div class="col-sm-9">{{ $booking->kode_invoice }}</div>
            </div>

            <a href="{{ route('bookings.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </div>
    </div>
    @endsection
</body>
</html>
