<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use Illuminate\Http\Request;

class KosController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => Kos::with(['user:id,name,phone', 'zona:id,nama_zona'])
                           ->latest()
                           ->get(),
        ]);
    }

   
    // ✅ Public — detail satu kos
//    public function show($id)
// {
//     $kos = Kos::with([
//         'user:id,name,phone',
//         'zona:id,nama_zona',
//         'kamar',                    // ← tambah kamar
//         'kamar.bookings',           // ← tambah booking tiap kamar
//     ])->find($id);

//     if (!$kos) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Kos tidak ditemukan.',
//         ], 404);
//     }

//     return response()->json(['status' => 'success', 'data' => $kos]);
// }

public function show($id)
{
    $kos = Kos::with([
        'user:id,name,phone',
        'zona:id,nama_zona',
        'kamar',
        'kamar.bookings',
        'reviews.user:id,name',  // ← tambah ini
    ])->find($id);

    if (!$kos) {
        return response()->json(['status' => 'error', 'message' => 'Kos tidak ditemukan.'], 404);
    }

    // Hitung avg rating langsung
    $kos->avg_rating   = round($kos->reviews->avg('rating'), 1);
    $kos->total_review = $kos->reviews->count();

    return response()->json(['status' => 'success', 'data' => $kos]);
}
    

    // ✅ Owner & Admin — tambah kos
//     public function store(Request $request)
// {
//     $request->validate([
//         'zona_id'         => 'required|exists:zonas,id',
//         'nama_kos'        => 'required|string|max:255',
//         'alamat'          => 'required|string',
//         'latitude'        => 'required|numeric|between:-90,90',
//         'longitude'       => 'required|numeric|between:-180,180',
//         'harga_per_bulan' => 'required|numeric|min:0',
//         'deskripsi'       => 'nullable|string',
//         'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
//     ]);

//     // Upload gambar jika ada
//     $gambarPath = null;
//     if ($request->hasFile('gambar')) {
//         $gambarPath = $request->file('gambar')->store('kos', 'public');
//     }

//     $kos = Kos::create([
//         'user_id'         => auth()->id(),
//         'zona_id'         => $request->zona_id,
//         'nama_kos'        => $request->nama_kos,
//         'alamat'          => $request->alamat,
//         'latitude'        => $request->latitude,
//         'longitude'       => $request->longitude,
//         'harga_per_bulan' => $request->harga_per_bulan,
//         'deskripsi'       => $request->deskripsi,
//         'gambar'          => $gambarPath,
//         'status'          => 'pending',
//     ]);

//     return response()->json([
//         'status'  => 'success',
//         'message' => 'Kos berhasil ditambahkan',
//         'data'    => $kos->load('zona:id,nama_zona'),
//     ], 201);
// }

public function store(Request $request)
{
    $request->validate([
        'zona_id'         => 'required|exists:zonas,id',
        'nama_kos'        => 'required|string|max:255',
        'alamat'          => 'required|string',
        'latitude'        => 'required|numeric|between:-90,90',
        'longitude'       => 'required|numeric|between:-180,180',
        'harga_per_bulan' => 'required|numeric|min:0',
        'deskripsi'       => 'nullable|string',
        'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $gambarPath = null;
    if ($request->hasFile('gambar')) {
        $gambarPath = $request->file('gambar')->store('kos', 'public');
    }

    $kos = Kos::create([
        'user_id'         => auth()->id(),
        'zona_id'         => $request->zona_id,
        'nama_kos'        => $request->nama_kos,
        'alamat'          => $request->alamat,
        'latitude'        => $request->latitude,
        'longitude'       => $request->longitude,
        'harga_per_bulan' => $request->harga_per_bulan,
        'deskripsi'       => $request->deskripsi,
        'gambar'          => $gambarPath,
        'status'          => 'pending',
    ]);

    // ✅ FIX: kamar dikirim sebagai JSON string dari FormData — decode dulu
    $kamarData = json_decode($request->input('kamar', '[]'), true);

    if (!empty($kamarData)) {
        foreach ($kamarData as $k) {
            // Hanya simpan jika nomor_kamar diisi
            if (!empty($k['nomor_kamar'])) {
                $kos->kamar()->create([
                    'nomor_kamar' => $k['nomor_kamar'],
                    'harga'       => !empty($k['harga']) ? $k['harga'] : $request->harga_per_bulan,
                    'status'      => $k['status'] ?? 'available',
                ]);
            }
        }
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Kos berhasil ditambahkan',
        'data'    => $kos->load(['zona:id,nama_zona', 'kamar']),
    ], 201);
}
public function update(Request $request, $id)
{
    $kos = Kos::find($id);

    if (!$kos) {
        return response()->json(['status' => 'error', 'message' => 'Kos tidak ditemukan.'], 404);
    }

    // Owner hanya boleh edit kos miliknya sendiri
    if (auth()->user()->role === 'owner' && $kos->user_id !== auth()->id()) {
        return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
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

    // Upload gambar baru jika ada
    if ($request->hasFile('gambar')) {
        // Hapus gambar lama dari storage
        if ($kos->gambar) {
            \Storage::disk('public')->delete($kos->gambar);
        }
        $data['gambar'] = $request->file('gambar')->store('kos', 'public');
    }

    $kos->update($data);

    return response()->json([
        'status'  => 'success',
        'message' => 'Kos berhasil diupdate',
        'data'    => $kos->load('zona:id,nama_zona'),
    ]);
}

    // ✅ Owner & Admin — hapus kos
    public function destroy($id)
    {
        $kos = Kos::find($id);

        if (!$kos) {
            return response()->json(['status' => 'error', 'message' => 'Kos tidak ditemukan.'], 404);
        }

        // Owner hanya boleh hapus kos miliknya sendiri
        if (auth()->user()->role === 'owner' && $kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $kos->delete();

        return response()->json(['status' => 'success', 'message' => 'Kos berhasil dihapus.']);
    }

    // ✅ Admin only — approve/reject kos
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,pending,nonaktif',
        ]);

        $kos = Kos::findOrFail($id);
        $kos->update(['status' => $request->status]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status kos diupdate',
            'data'    => $kos,
        ]);
    }
}