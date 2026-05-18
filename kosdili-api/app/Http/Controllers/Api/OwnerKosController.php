<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Kamar;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OwnerKosController extends Controller
{
    // ✅ 1. Lihat semua kos milik owner ini
    public function index()
    {
        $kos = Kos::with(['zona:id,nama_zona', 'kamar'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $kos]);
    }

    // ✅ 2. Detail satu kos milik owner + kamar + booking aktif
    public function show($id)
    {
        $kos = Kos::with([
            'zona:id,nama_zona',
            'kamar',
            'kamar.bookings' => function ($q) {
                $q->with('user:id,name,email,phone')
                  ->whereIn('status', ['pending', 'confirmed'])
                  ->latest();
            },
        ])
        ->where('user_id', auth()->id())
        ->find($id);

        if (!$kos) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kos tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        return response()->json(['status' => 'success', 'data' => $kos]);
    }

    // ✅ 3. Edit kos milik sendiri
    public function update(Request $request, $id)
    {
        $kos = Kos::where('user_id', auth()->id())->find($id);

        if (!$kos) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kos tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $request->validate([
            'zona_id'         => 'sometimes|exists:zonas,id',
            'nama_kos'        => 'sometimes|string|max:255',
            'alamat'          => 'sometimes|string',
            'latitude'        => 'sometimes|numeric|between:-90,90',
            'longitude'       => 'sometimes|numeric|between:-180,180',
            'harga_per_bulan' => 'sometimes|numeric|min:0',
            'deskripsi'       => 'nullable|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only([
            'zona_id', 'nama_kos', 'alamat',
            'latitude', 'longitude', 'harga_per_bulan', 'deskripsi',
        ]);

        if ($request->hasFile('gambar')) {
            if ($kos->gambar) {
                Storage::disk('public')->delete($kos->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('kos', 'public');
        }

        $kos->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kos berhasil diupdate.',
            'data'    => $kos->load(['zona:id,nama_zona', 'kamar']),
        ]);
    }

    // ✅ 4. Hapus kos milik sendiri
    //    Hanya bisa hapus jika tidak ada booking aktif (pending/confirmed)
    public function destroy($id)
    {
        $kos = Kos::with('kamar')->where('user_id', auth()->id())->find($id);

        if (!$kos) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kos tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        // Cek booking aktif di semua kamar kos ini
        $kamarIds = $kos->kamar->pluck('id');
        $adaBookingAktif = Booking::whereIn('kamar_id', $kamarIds)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($adaBookingAktif) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak bisa hapus kos — masih ada booking aktif.',
            ], 422);
        }

        // Hapus gambar dari storage
        if ($kos->gambar) {
            Storage::disk('public')->delete($kos->gambar);
        }

        $kos->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kos berhasil dihapus.',
        ]);
    }

    // ✅ 5. Aktifkan kembali kamar setelah tenant keluar
    //    Owner panggil ini setelah booking selesai / tenant sudah keluar
    public function aktivasiKamar($kamarId)
    {
        $kamar = Kamar::with('kos')->find($kamarId);

        if (!$kamar) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamar tidak ditemukan.',
            ], 404);
        }

        // Pastikan kamar ini milik kos si owner
        if ($kamar->kos->user_id !== auth()->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamar ini bukan milik Anda.',
            ], 403);
        }

        // Tandai booking lama sebagai completed
        Booking::where('kamar_id', $kamarId)
            ->where('status', 'confirmed')
            ->update(['status' => 'completed']);

        // Aktifkan kembali kamar
        $kamar->update(['status' => 'available']);

        return response()->json([
            'status'  => 'success',
            'message' => "Kamar {$kamar->nomor_kamar} berhasil diaktifkan kembali.",
            'data'    => $kamar,
        ]);
    }

    // ✅ 6. Lihat semua kamar + status booking terkini
    public function daftarKamar($kosId)
    {
        $kos = Kos::where('user_id', auth()->id())->find($kosId);

        if (!$kos) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kos tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        $kamar = Kamar::with([
            'bookings' => function ($q) {
                $q->with('user:id,name,email,phone')
                  ->latest()
                  ->limit(1); // booking terbaru saja
            },
        ])
        ->where('kos_id', $kosId)
        ->get();

        return response()->json(['status' => 'success', 'data' => $kamar]);
    }
}