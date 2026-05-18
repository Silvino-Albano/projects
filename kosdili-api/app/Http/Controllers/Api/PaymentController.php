<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // User — upload bukti transfer
    public function uploadBukti(Request $request, $bookingId)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $booking = Booking::with('kamar.kos')->findOrFail($bookingId);

        // Hanya pemilik booking
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        if ($booking->payment_method !== 'transfer') {
            return response()->json(['status' => 'error', 'message' => 'Booking ini menggunakan metode cash.'], 422);
        }

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $booking->update([
            'payment_proof'  => $path,
            'payment_status' => 'pending_verification',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bukti transfer berhasil diupload. Menunggu konfirmasi owner.',
            'data'    => $booking,
        ]);
    }

    // Owner — konfirmasi pembayaran
    public function konfirmasiPembayaran(Request $request, $bookingId)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        $booking = Booking::with('kamar.kos')->findOrFail($bookingId);

        if ($booking->kamar->kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $booking->update(['payment_status' => $request->payment_status]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status pembayaran diupdate.',
            'data'    => $booking,
        ]);
    }

    // Owner — lihat bukti transfer
    public function lihatBukti($bookingId)
    {
        $booking = Booking::with('kamar.kos')->findOrFail($bookingId);

        if ($booking->kamar->kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $booking]);
    }
}