<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingBaruUntukOwner;
use App\Mail\BookingDikonfirmasi;
use App\Models\Booking;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Komisi;

class BookingController extends Controller
{
    // ─────────────────────────────────────────────────────
    // POST /api/booking
    // User ajukan booking — otomatis kirim email ke owner
    // ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'kamar_id'       => 'required|exists:kamars,id',
            'tanggal_masuk'  => 'required|date|after_or_equal:today',
            'durasi_bulan'   => 'required|integer|min:1|max:24',
            'payment_method' => 'required|in:cash,transfer',
            'catatan'        => 'nullable|string|max:500',
        ]);

        // Load kos dan user pemilik sekaligus untuk kirim email
        $kamar = Kamar::with(['kos.user'])->findOrFail($request->kamar_id);

        if ($kamar->status !== 'available') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamar sudah tidak tersedia.',
            ], 422);
        }

        // Cek booking aktif yang sama
        $existing = Booking::where('kamar_id', $request->kamar_id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah punya booking aktif di kamar ini.',
            ], 422);
        }

        $tanggalMasuk  = Carbon::parse($request->tanggal_masuk);
        $tanggalKeluar = $tanggalMasuk->copy()->addMonths((int) $request->durasi_bulan);

        // Simpan booking
        $booking = Booking::create([
            'user_id'        => auth()->id(),
            'kamar_id'       => $request->kamar_id,
            'tanggal_masuk'  => $tanggalMasuk->toDateString(),
            'tanggal_keluar' => $tanggalKeluar->toDateString(),
            'durasi_bulan'   => (int) $request->durasi_bulan,
            'status'         => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'catatan'        => $request->catatan ?? null,
        ]);

        // Update status kamar
        $kamar->update(['status' => 'booked']);

        // ── Kirim email notifikasi ke OWNER / PEMILIK KOS ─────────────
        // Load relasi lengkap untuk email
        $booking->load(['user', 'kamar.kos.user']);

        $emailOwner = $kamar->kos->user->email ?? null;

        if ($emailOwner) {
            try {
                Mail::to($emailOwner)
                    ->send(new BookingBaruUntukOwner($booking));

                Log::info("Email booking baru dikirim ke owner: {$emailOwner}");

            } catch (\Exception $e) {
                // Kalau email gagal, booking tetap berhasil
                // Jangan blokir response karena masalah email
                Log::error('Gagal kirim email ke owner: ' . $e->getMessage());
            }
        } else {
            Log::warning("Owner kos ID {$kamar->kos->id} tidak punya email, notifikasi tidak dikirim.");
        }
        // ──────────────────────────────────────────────────────────────

        return response()->json([
            'status'  => 'success',
            'message' => 'Booking berhasil diajukan! Pemilik kos akan segera mengkonfirmasi.',
            'data'    => $booking,
            'info'    => [
                'nama_kos'       => $kamar->kos->nama_kos ?? '-',
                'tanggal_masuk'  => $tanggalMasuk->format('d M Y'),
                'tanggal_keluar' => $tanggalKeluar->format('d M Y'),
                'durasi_bulan'   => (int) $request->durasi_bulan,
                'total_harga'    => '$' . number_format($kamar->harga * (int) $request->durasi_bulan, 0),
                'payment_method' => $request->payment_method,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────
    // GET /api/booking/saya
    // User lihat daftar booking miliknya
    // ─────────────────────────────────────────────────────
    public function saya()
    {
        $bookings = Booking::with([
                'kamar:id,nomor_kamar,harga,kos_id',
                'kamar.kos:id,nama_kos,alamat,user_id',
                'kamar.kos.user:id,name',
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $bookings]);
    }

    // ─────────────────────────────────────────────────────
    // GET /api/owner/bookings
    // Owner lihat semua booking masuk ke kos miliknya
    // ─────────────────────────────────────────────────────
    public function ownerIndex()
    {
        $bookings = Booking::with([
                'kamar:id,nomor_kamar,harga,kos_id',
                'kamar.kos:id,nama_kos,user_id',
                'user:id,name,email',
            ])
            ->whereHas('kamar.kos', fn($q) => $q->where('user_id', auth()->id()))
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $bookings]);
    }

    // ─────────────────────────────────────────────────────
    // PATCH /api/owner/booking/{id}/status
    // Owner setujui atau tolak — kirim email ke user/tenant
    // ─────────────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:confirmed,cancelled',
    ]);
 
    $booking = Booking::with([
        'kamar.kos.user',
        'user',
    ])->findOrFail($id);
 
    if ($booking->kamar->kos->user_id !== auth()->id()) {
        return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
    }
 
    if ($booking->status !== 'pending') {
        return response()->json([
            'status'  => 'error',
            'message' => 'Booking ini sudah diproses sebelumnya.',
        ], 422);
    }
 
    // Update status booking
    $booking->update(['status' => $request->status]);
 
    // Update status kamar
    $booking->kamar->update([
        'status' => $request->status === 'cancelled' ? 'available' : 'booked'
    ]);
 
    // ── KODE KOMISI BARU — tambahkan di sini ──────────────────
    $komisiInfo = null;
 
    if ($request->status === 'confirmed') {
 
        $harga     = (float) ($booking->kamar->harga ?? 0);
        $durasi    = (int)   ($booking->durasi_bulan ?? 1);
        $totalSewa = $harga * $durasi;
 
        // Ambil persentase dari config, default 7%
        $persen = (float) config('kosdili.komisi_persen', 7.0);
        $jumlah = Komisi::hitung($totalSewa, $persen);
 
        // Simpan record komisi
        $komisi = Komisi::create([
            'booking_id' => $booking->id,
            'owner_id'   => $booking->kamar->kos->user_id,
            'total_sewa' => $totalSewa,
            'persen'     => $persen,
            'jumlah'     => $jumlah,
            'status'     => 'unpaid',
        ]);
 
        // Update ringkasan komisi di tabel bookings juga
        $booking->update([
            'komisi_jumlah' => $jumlah,
            'komisi_status' => 'unpaid',
        ]);
 
        $komisiInfo = [
            'id'         => $komisi->id,
            'total_sewa' => '$' . number_format($totalSewa, 2),
            'persen'     => $persen . '%',
            'jumlah'     => '$' . number_format($jumlah, 2),
            'pesan'      => "Tagihan komisi {$persen}% = \${$jumlah} dibuat otomatis.",
        ];
    }
    // ── AKHIR KODE KOMISI ─────────────────────────────────────
 
    // Kirim email ke tenant (pencari kos) — tidak berubah
    $emailTenant = $booking->user->email ?? null;
    $emailInfo   = 'Email tidak dikirim.';
 
    if ($emailTenant) {
        try {
            Mail::to($emailTenant)
                ->send(new BookingDikonfirmasi($booking, $request->status));
            $emailInfo = "Email notifikasi dikirim ke {$emailTenant}";
        } catch (\Exception $e) {
            Log::error('Gagal kirim email ke tenant: ' . $e->getMessage());
            $emailInfo = 'Booking diupdate, tapi email gagal terkirim.';
        }
    }
 
    $label = $request->status === 'confirmed' ? 'disetujui' : 'ditolak';
 
    return response()->json([
        'status'      => 'success',
        'message'     => "Booking berhasil {$label}.",
        'email_info'  => $emailInfo,
        'komisi_info' => $komisiInfo, // ← tambah field ini di response
        'data'        => $booking,
    ]);
}
    // ─────────────────────────────────────────────────────
    // PATCH /api/owner/booking/{id}/selesai
    // Owner tandai tenant sudah keluar
    // ─────────────────────────────────────────────────────
    public function selesai($id)
    {
        $booking = Booking::with('kamar.kos')->findOrFail($id);

        if ($booking->kamar->kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $booking->update(['status' => 'completed']);
        $booking->kamar->update(['status' => 'available']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Booking selesai, kamar kembali tersedia.',
            'data'    => $booking,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // PATCH /api/owner/booking/{id}/konfirmasi-cash
    // Owner konfirmasi sudah terima uang cash
    // ─────────────────────────────────────────────────────
    public function konfirmasiCash($id)
    {
        $booking = Booking::with(['kamar.kos', 'user'])->findOrFail($id);

        if ($booking->kamar->kos->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        if ($booking->payment_method !== 'cash') {
            return response()->json(['status' => 'error', 'message' => 'Bukan metode cash.'], 422);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['status' => 'error', 'message' => 'Booking belum disetujui.'], 422);
        }

        $booking->update(['payment_status' => 'paid']);

        try {
            Mail::to($booking->user->email)
                ->send(new \App\Mail\PembayaranDikonfirmasi($booking));
        } catch (\Exception $e) {
            Log::error('Email konfirmasi cash gagal: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayaran cash dikonfirmasi.',
            'data'    => $booking,
        ]);
    }
}