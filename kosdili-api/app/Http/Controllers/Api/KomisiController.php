<?php
// app/Http/Controllers/Api/KomisiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Komisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KomisiController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // GET /api/owner/komisi
    // Owner lihat semua tagihan komisi miliknya
    // ─────────────────────────────────────────────────────────
    public function ownerIndex()
    {
        $komisi = Komisi::with([
                'booking:id,tanggal_masuk,tanggal_keluar,durasi_bulan,payment_method',
                'booking.kamar:id,nomor_kamar,harga,kos_id',
                'booking.kamar.kos:id,nama_kos',
            ])
            ->byOwner(auth()->id())
            ->latest()
            ->get();

        $ringkasan = [
            'total_tagihan' => $komisi->count(),
            'total_unpaid'  => $komisi->where('status', 'unpaid')->sum('jumlah'),
            'total_paid'    => $komisi->where('status', 'paid')->sum('jumlah'),
            'total_semua'   => $komisi->sum('jumlah'),
        ];

        return response()->json([
            'status'    => 'success',
            'ringkasan' => $ringkasan,
            'data'      => $komisi,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /api/owner/komisi/{id}/bayar
    // Owner upload bukti bayar komisi ke admin
    // ─────────────────────────────────────────────────────────
    public function ownerBayar(Request $request, $id)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $komisi = Komisi::findOrFail($id);

        if ($komisi->owner_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        if ($komisi->status === 'paid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Komisi ini sudah dibayar sebelumnya.',
            ], 422);
        }

        $path = $request->file('bukti_bayar')->store('bukti_komisi', 'public');

        $komisi->update(['bukti_bayar' => $path]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.',
            'data'    => $komisi,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/admin/komisi
    // Admin lihat semua komisi dari semua owner
    // ─────────────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        $query = Komisi::with([
            'booking:id,tanggal_masuk,tanggal_keluar,durasi_bulan',
            'booking.kamar:id,nomor_kamar,kos_id',
            'booking.kamar.kos:id,nama_kos',
            'owner:id,name,email',
        ]);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }

        $komisi = $query->latest()->get();

        $statistik = [
            'total_komisi_semua' => $komisi->sum('jumlah'),
            'total_sudah_bayar'  => $komisi->where('status', 'paid')->sum('jumlah'),
            'total_belum_bayar'  => $komisi->where('status', 'unpaid')->sum('jumlah'),
            'jumlah_owner_aktif' => $komisi->unique('owner_id')->count(),
            'jumlah_transaksi'   => $komisi->count(),
        ];

        return response()->json([
            'status'    => 'success',
            'statistik' => $statistik,
            'data'      => $komisi,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/admin/komisi/ringkasan
    // Admin lihat ringkasan pendapatan per bulan
    // ─────────────────────────────────────────────────────────
    public function adminRingkasan()
    {
        $perBulan = Komisi::where('status', 'paid')
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as bulan, SUM(jumlah) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $topOwner = Komisi::with('owner:id,name,email')
            ->selectRaw('owner_id, SUM(jumlah) as total_komisi, COUNT(*) as jumlah_booking')
            ->groupBy('owner_id')
            ->orderByDesc('total_komisi')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_semua'    => number_format(Komisi::sum('jumlah'), 2),
                'total_diterima' => number_format(Komisi::where('status', 'paid')->sum('jumlah'), 2),
                'total_pending'  => number_format(Komisi::where('status', 'unpaid')->sum('jumlah'), 2),
                'per_bulan'      => $perBulan,
                'top_owner'      => $topOwner,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // PATCH /api/admin/komisi/{id}/konfirmasi
    // Admin konfirmasi komisi sudah diterima
    // ─────────────────────────────────────────────────────────
    public function adminKonfirmasi(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:300',
        ]);

        $komisi = Komisi::with(['booking', 'owner'])->findOrFail($id);

        if ($komisi->status === 'paid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Komisi ini sudah dikonfirmasi sebelumnya.',
            ], 422);
        }

        $komisi->update([
            'status'        => 'paid',
            'dibayar_pada'  => now(),
            'catatan_admin' => $request->catatan_admin ?? 'Dikonfirmasi oleh admin.',
        ]);

        // Update juga kolom komisi_status di tabel bookings
        $komisi->booking->update(['komisi_status' => 'paid']);

        return response()->json([
            'status'  => 'success',
            'message' => "Komisi dari {$komisi->owner->name} berhasil dikonfirmasi.",
            'data'    => $komisi,
        ]);
    }
}
