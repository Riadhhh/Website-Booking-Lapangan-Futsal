<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Booking Lapangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow rounded-4 border-0">
            <div class="card-body p-4">
                <h1 class="mb-4 text-primary border-bottom pb-2">
                    <i class="bi bi-calendar2-check"></i> Daftar Booking Lapangan
                </h1>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Booking
                    </a>
                    <form action="{{ route('bookings.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>

                <form action="{{ route('bookings.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">-- Filter Status Pembayaran --</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-filter"></i> Terapkan Filter
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-hover">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>No. Telepon</th>
                                <th>Tanggal</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Durasi (jam)</th>
                                <th>Kode Invoice</th>
                                <th>Status</th>
                                <th>Total Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($bookings as $booking)
                            @php
                                $jamMulai = \Carbon\Carbon::parse($booking->jam_mulai);
                                $jamSelesai = $jamMulai->copy()->addHours($booking->durasi);
                            @endphp
                            <tr>
                                <td>{{ $booking->nama }}</td>
                                <td>{{ $booking->nomorhp }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->tglpesan)->translatedFormat('d F Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} WIB</td>
                                <td>{{ $jamSelesai->format('H:i') }} WIB</td>
                                <td>{{ $booking->durasi }}</td>
                                <td>{{ $booking->kode_invoice }}</td>
                                <td>
                                    <span class="badge
                                        {{ $booking->status_pembayaran === 'paid' ? 'bg-success' :
                                        ($booking->status_pembayaran === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ strtoupper($booking->status_pembayaran) }}
                                    </span>
                                </td>
                                <td>Rp{{ number_format($booking->final, 0, ',', '.') }}</td>
                                <td class="d-flex gap-1 justify-content-center flex-wrap">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($booking->status_pembayaran !== 'paid')
                                    <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-muted text-center">Tidak ada data booking ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
