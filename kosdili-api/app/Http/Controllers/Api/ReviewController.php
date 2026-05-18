<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Kos;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Public — lihat semua review 1 kos
    public function index($kosId)
    {
        $reviews = Review::with('user:id,name')
            ->where('kos_id', $kosId)
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating');

        return response()->json([
            'status' => 'success',
            'data'   => $reviews,
            'avg_rating' => round($avgRating, 1),
        ]);
    }

    // User — buat review (hanya jika booking completed)
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating'     => 'required|integer|min:1|max:5',
            'komentar'   => 'nullable|string|max:1000',
        ]);

        $booking = Booking::with('kamar.kos')->findOrFail($request->booking_id);

        // Hanya pemilik booking
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        // Hanya booking yang sudah completed
        if ($booking->status !== 'completed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Review hanya bisa dibuat setelah masa sewa selesai.',
            ], 422);
        }

        // Cek sudah pernah review booking ini
        $sudahReview = Review::where('booking_id', $request->booking_id)->exists();
        if ($sudahReview) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah memberikan review untuk booking ini.',
            ], 422);
        }

        $review = Review::create([
            'user_id'    => auth()->id(),
            'kos_id'     => $booking->kamar->kos->id,
            'booking_id' => $request->booking_id,
            'rating'     => $request->rating,
            'komentar'   => $request->komentar,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Review berhasil dikirim. Terima kasih!',
            'data'    => $review->load('user:id,name'),
        ], 201);
    }

    // Owner — reply review
    public function reply(Request $request, $reviewId)
    {
        $request->validate([
            'reply_owner' => 'required|string|max:500',
        ]);

        $review = Review::with('kos')->findOrFail($reviewId);

        if ($review->kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $review->update(['reply_owner' => $request->reply_owner]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Balasan berhasil disimpan.',
            'data'    => $review,
        ]);
    }

    // User — lihat booking yang bisa di-review
    public function bisaReview()
    {
        $bookings = Booking::with(['kamar.kos:id,nama_kos', 'review'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $bookings]);
    }
}