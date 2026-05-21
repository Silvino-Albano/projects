<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // GET /api/subscription/plans
    // Public — tampilkan semua paket yang tersedia
    // ─────────────────────────────────────────────────────────
    public function plans()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $plans,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/subscription/saya
    // Owner lihat langganan aktif miliknya
    // ─────────────────────────────────────────────────────────
    public function saya()
    {
        $aktif = Subscription::with('plan')
            ->where('user_id', auth()->id())
            ->active()
            ->latest()
            ->first();

        // Cek apakah ada langganan expired yang belum ditandai
        Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('berakhir_pada', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $riwayat = Subscription::with('plan')
            ->where('user_id', auth()->id())
            ->latest()
            ->take(6)
            ->get();

        return response()->json([
            'status'    => 'success',
            'aktif'     => $aktif,
            'sisa_hari' => $aktif ? $aktif->sisaHari() : 0,
            'riwayat'   => $riwayat,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /api/subscription/berlangganan
    // Owner pilih dan mulai berlangganan
    // ─────────────────────────────────────────────────────────
    public function berlangganan(Request $request)
    {
        $request->validate([
            'plan_id'      => 'required|exists:subscription_plans,id',
            'metode_bayar' => 'required|in:cash,transfer',
            'durasi_bulan' => 'required|integer|min:1|max:12',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Cek apakah masih punya langganan aktif
        $aktif = Subscription::where('user_id', auth()->id())
            ->active()
            ->exists();

        if ($aktif) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda masih memiliki langganan aktif. Tunggu hingga berakhir sebelum berlangganan lagi.',
            ], 422);
        }

        $mulai    = Carbon::today();
        $berakhir = $mulai->copy()->addMonths($request->durasi_bulan);
        $total    = $plan->harga * $request->durasi_bulan;

        // Paket gratis langsung aktif tanpa bayar
        $paymentStatus = $plan->isGratis() ? 'paid' : 'unpaid';
        $status        = $plan->isGratis() ? 'active' : 'active'; // aktif tapi belum dibayar

        $subscription = Subscription::create([
            'user_id'       => auth()->id(),
            'plan_id'       => $plan->id,
            'status'        => $status,
            'mulai_pada'    => $mulai->toDateString(),
            'berakhir_pada' => $berakhir->toDateString(),
            'metode_bayar'  => $request->metode_bayar,
            'payment_status'=> $paymentStatus,
            'catatan'       => $request->catatan ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $plan->isGratis()
                ? 'Berhasil mengaktifkan paket Gratis!'
                : "Berhasil mendaftar paket {$plan->nama}! Silakan upload bukti pembayaran.",
            'data'    => $subscription->load('plan'),
            'info'    => [
                'nama_paket'   => $plan->nama,
                'harga'        => '$' . number_format($plan->harga, 2),
                'durasi'       => $request->durasi_bulan . ' bulan',
                'total'        => '$' . number_format($total, 2),
                'mulai'        => $mulai->format('d M Y'),
                'berakhir'     => $berakhir->format('d M Y'),
                'metode_bayar' => $request->metode_bayar,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────
    // POST /api/subscription/{id}/upload-bukti
    // Owner upload bukti bayar langganan
    // ─────────────────────────────────────────────────────────
    public function uploadBukti(Request $request, $id)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $subscription = Subscription::findOrFail($id);

        if ($subscription->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        if ($subscription->payment_status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'Sudah dibayar.'], 422);
        }

        $path = $request->file('bukti_bayar')->store('bukti_langganan', 'public');
        $subscription->update(['bukti_bayar' => $path]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bukti bayar berhasil diupload. Admin akan konfirmasi segera.',
            'data'    => $subscription,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /api/subscription/{id}/cancel
    // Owner batalkan langganan
    // ─────────────────────────────────────────────────────────
    public function cancel($id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $subscription->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Langganan berhasil dibatalkan.',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/admin/subscriptions
    // Admin lihat semua langganan
    // ─────────────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        $query = Subscription::with(['user:id,name,email', 'plan:id,nama,harga,slug']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $subscriptions = $query->latest()->get();

        // Statistik pendapatan langganan
        $statistik = [
            'total_subscriber'  => $subscriptions->where('status', 'active')->count(),
            'total_pendapatan'  => $subscriptions->where('payment_status', 'paid')
                                    ->sum(fn($s) => $s->plan->harga),
            'total_belum_bayar' => $subscriptions->where('payment_status', 'unpaid')
                                    ->where('status', 'active')->count(),
            'per_plan'          => $subscriptions->where('status', 'active')
                                    ->groupBy('plan_id')
                                    ->map(fn($g) => [
                                        'nama'  => $g->first()->plan->nama,
                                        'count' => $g->count(),
                                    ])->values(),
        ];

        return response()->json([
            'status'    => 'success',
            'statistik' => $statistik,
            'data'      => $subscriptions,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // PATCH /api/admin/subscriptions/{id}/konfirmasi
    // Admin konfirmasi pembayaran langganan
    // ─────────────────────────────────────────────────────────
    public function adminKonfirmasi($id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);

        if ($subscription->payment_status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'Sudah dikonfirmasi.'], 422);
        }

        $subscription->update([
            'payment_status'    => 'paid',
            'dikonfirmasi_pada' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Pembayaran langganan {$subscription->user->name} dikonfirmasi.",
            'data'    => $subscription,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /api/owner/subscription/check
    // Cek batas paket owner — dipakai sebelum upload properti/foto
    // ─────────────────────────────────────────────────────────
    public function checkLimit()
    {
        $user = auth()->user();

        // Ambil langganan aktif
        $subscription = Subscription::with('plan')
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        // Kalau tidak ada langganan, default ke paket Gratis
        if (!$subscription) {
            $plan = SubscriptionPlan::where('slug', 'gratis')->first();
        } else {
            $plan = $subscription->plan;
        }

        // Hitung jumlah properti owner saat ini
        $jumlahProperti = \App\Models\Kos::where('user_id', $user->id)->count();

        $bisaAddProperti = $plan->max_properti === -1
            || $jumlahProperti < $plan->max_properti;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'plan'              => $plan->nama,
                'max_properti'      => $plan->labelMaxProperti(),
                'max_foto'          => $plan->labelMaxFoto(),
                'jumlah_properti'   => $jumlahProperti,
                'bisa_add_properti' => $bisaAddProperti,
                'prioritas_listing' => $plan->prioritas_listing,
                'badge'             => $plan->badge_terverifikasi,
                'statistik_lengkap' => $plan->statistik_lengkap,
                'sisa_hari'         => $subscription ? $subscription->sisaHari() : 0,
            ],
        ]);
    }
}
