<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                ->orWhere('nomorhp', 'like', "%$search%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('tglpesan', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        $bookings = $query->orderByDesc('tglpesan')->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        return view('bookings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomorhp' => 'required|string|max:20',
            'waktubermain' => 'required|in:Pagi,Siang,Malam',
            'tglpesan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'durasi' => 'required|integer|min:1|max:12',
            'airmineral' => 'nullable|integer',
            'diskon' => 'nullable|integer|min:0|max:100',
            'final' => 'required|numeric',
            'status_pembayaran' => 'required|in:paid,pending,expired',
            'kode_invoice' => 'nullable|string|max:255'
        ]);

        $validated['durasi'] = (int) $validated['durasi'];
        $validated['airmineral'] = (int) ($validated['airmineral'] ?? 0);
        $validated['diskon'] = (int) ($validated['diskon'] ?? 0);
        $validated['final'] = (float) $validated['final'];

        $jamMulai = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
        $jamSelesai = $jamMulai->copy()->addHours($validated['durasi']);

        $overlap = Pemesanan::where('tglpesan', $validated['tglpesan'])
            ->where('waktubermain', $validated['waktubermain'])
            ->where(function ($query) use ($validated, $jamMulai, $jamSelesai) {
                $query->whereBetween('jam_mulai', [$jamMulai->format('H:i'), $jamSelesai->format('H:i')])
                    ->orWhereRaw('? BETWEEN jam_mulai AND ADDTIME(jam_mulai, SEC_TO_TIME(durasi * 3600))', [$jamMulai->format('H:i')]);
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['overlap' => 'Waktu tersebut sudah dibooking oleh pelanggan lain.'])->withInput();
        }

        Pemesanan::create($validated);

        return redirect()->route('bookings.index')->with('success', 'Pemesanan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $booking = Pemesanan::findOrFail($id);
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = Pemesanan::findOrFail($id);
        return view('bookings.edit', compact('booking'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomorhp' => 'required|string|max:20',
            'waktubermain' => 'required|in:Pagi,Siang,Malam',
            'tglpesan' => 'required|date',
            'jam_mulai' => 'required',
            'durasi' => 'required|integer|min:1|max:12',
            'airmineral' => 'nullable|integer',
            'final' => 'required|numeric',
            'status_pembayaran' => 'required|in:PAID,UNPAID,EXPIRED',
            'kode_invoice' => 'nullable|string|max:255',
        ]);

        $validated['diskon'] = ($validated['durasi'] > 3) ? 10 : 0;
        $validated['durasi'] = (int) $validated['durasi'];
        $validated['airmineral'] = (int) ($validated['airmineral'] ?? 0);
        $validated['diskon'] = (int) ($validated['diskon'] ?? 0);
        $validated['final'] = (float) $validated['final'];
        $jamMulai = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
        $jamSelesai = $jamMulai->copy()->addHours($validated['durasi']);

        $overlap = Pemesanan::where('id', '!=', $id)
            ->where('tglpesan', $validated['tglpesan'])
            ->where('waktubermain', $validated['waktubermain'])
            ->where(function ($query) use ($validated, $jamMulai, $jamSelesai) {
                $query->whereBetween('jam_mulai', [$jamMulai->format('H:i'), $jamSelesai->format('H:i')])
                    ->orWhereRaw('? BETWEEN jam_mulai AND ADDTIME(jam_mulai, SEC_TO_TIME(durasi * 3600))', [$jamMulai->format('H:i')]);
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['overlap' => 'Waktu tersebut sudah dibooking oleh pelanggan lain.'])->withInput();
        }

        $booking = Pemesanan::findOrFail($id);
        $booking->update($validated);

        return redirect()->route('bookings.index')->with('success', 'Pemesanan berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $booking = Pemesanan::findOrFail($id);
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Pemesanan berhasil dihapus.');
    }
}
