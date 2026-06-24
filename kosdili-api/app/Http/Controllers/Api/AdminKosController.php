<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\KosStatusMail;

class AdminKosController extends Controller
{
    // Admin — lihat semua kos (filter by status)
    // public function index(Request $request)
    // {
    //     $query = Kos::with(['user:id,name,email', 'zona:id,nama_zona']);

    //     if ($request->has('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'data'   => $query->latest()->get(),
    //     ]);
    // }
public function index(Request $request)
    {
        $query = Kos::with([
            'user:id,name,email',
            'zona:id,nama_zona',
        ]);
 
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
 
        $kosList = $query->latest()->get();
 
        // Hitung jumlah per status
        $counts = [
            'pending'  => Kos::where('status', 'pending')->count(),
            'aktif'    => Kos::where('status', 'aktif')->count(),
            'nonaktif' => Kos::where('status', 'nonaktif')->count(),
            'total'    => Kos::count(),
        ];
 
        return response()->json([
            'status' => 'success',
            'counts' => $counts,
            'data'   => $kosList,
        ]);
    }
    // Admin — update status kos (pending → aktif / nonaktif)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,nonaktif,pending',
        ]);

        $kos = Kos::with('user')->findOrFail($id);
        $kos->update(['status' => $request->status]);

        // Kirim notifikasi email ke owner
        if ($kos->user && $kos->user->email) {
            Mail::to($kos->user->email)
                ->queue(new KosStatusMail($kos, $request->status));
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Status kos berhasil diupdate.',
            'data'    => $kos,
        ]);
    }
}