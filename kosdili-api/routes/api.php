<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KosController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ZonaController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AdminKosController;
use App\Http\Controllers\Api\OwnerKosController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\KomisiController;
use App\Http\Controllers\Api\SubscriptionController;


// Public routes
// ✅ Public — semua orang bisa lihat
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/resend-verification',  [AuthController::class, 'resendVerification']);


Route::get('/kos',       [KosController::class,  'index']);
Route::get('/kos/{id}',  [KosController::class,  'show']);
Route::get('/zonas',     [ZonaController::class,  'index']);



// ✅ Semua user yang login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ✅ Hanya owner & admin — CRUD kos
Route::middleware(['auth:sanctum', 'role:owner,admin'])->group(function () {
    Route::post('/kos',          [KosController::class, 'store']);
    Route::put('/kos/{id}',      [KosController::class, 'update']);
    Route::delete('/kos/{id}',   [KosController::class, 'destroy']);
});

// ✅ Hanya admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users',         [AuthController::class, 'allUsers']);
    Route::put('/kos/{id}/status', [KosController::class, 'updateStatus']);
});


// Protected — harus login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Booking — hanya user & admin (owner tidak booking kamarnya sendiri)
    Route::post('/booking',  [BookingController::class, 'store']);
    Route::get('/booking',   [BookingController::class, 'index']);  // riwayat booking
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);

    // User — booking
    Route::post('/booking', [BookingController::class, 'store']);
    Route::get('/booking',  [BookingController::class, 'index']);

    // ✅ Owner & Admin — kelola booking
    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/owner/bookings',            [BookingController::class, 'ownerBookings']);
        Route::put('/owner/bookings/{id}/status',[BookingController::class, 'updateStatus']);
    });
});


// Admin only
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/kos',              [AdminKosController::class, 'index']);
    Route::put('/admin/kos/{id}/status',  [AdminKosController::class, 'updateStatus']);
});

// ✅ Owner — kelola kos milik sendiri
Route::middleware(['auth:sanctum', 'role:owner,admin'])->group(function () {
    Route::get('/owner/kos',                        [OwnerKosController::class, 'index']);
    Route::get('/owner/kos/{id}',                   [OwnerKosController::class, 'show']);
    Route::post('/owner/kos/{id}',                  [OwnerKosController::class, 'update']);  // POST karena FormData
    Route::delete('/owner/kos/{id}',                [OwnerKosController::class, 'destroy']);
    Route::post('/owner/kamar/{kamarId}/aktivasi',  [OwnerKosController::class, 'aktivasiKamar']);
    Route::get('/owner/kos/{kosId}/kamar',          [OwnerKosController::class, 'daftarKamar']);
});




// ✅ Public — lihat review kos
Route::get('/kos/{kosId}/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
 
    // Booking — user buat dan lihat booking sendiri
    Route::post('/booking',         [BookingController::class, 'store']);   // ← INI YANG KURANG
    Route::get('/booking/saya',     [BookingController::class, 'saya']);
 
    // Payment — user upload bukti transfer
    Route::post('/booking/{id}/upload-bukti', [PaymentController::class, 'uploadBukti']);
 
    // Review — user buat review
    Route::post('/reviews',          [ReviewController::class, 'store']);
    Route::get('/reviews/eligible',  [ReviewController::class, 'bisaReview']);
});

// Owner
Route::middleware(['auth:sanctum', 'role:owner,admin'])->group(function () {
 
    // Booking — owner kelola
    Route::get('/owner/bookings',                         [BookingController::class, 'ownerIndex']);
    Route::patch('/owner/booking/{id}/status',            [BookingController::class, 'updateStatus']);
    Route::patch('/owner/booking/{id}/selesai',           [BookingController::class, 'selesai']);
    Route::patch('/owner/booking/{id}/konfirmasi-cash',   [BookingController::class, 'konfirmasiCash']);
 
    // Payment — owner lihat bukti & konfirmasi transfer
    Route::get('/booking/{id}/bukti',                     [PaymentController::class, 'lihatBukti']);
    Route::put('/booking/{id}/payment-status',            [PaymentController::class, 'konfirmasiPembayaran']);
 
    // Review — owner reply
    Route::post('/reviews/{id}/reply',                    [ReviewController::class, 'reply']);
});

// Owner — lihat dan bayar komisi
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/owner/komisi',             [KomisiController::class, 'ownerIndex']);
    Route::post('/owner/komisi/{id}/bayar', [KomisiController::class, 'ownerBayar']);
});

// Admin — kelola semua komisi
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/komisi',                    [KomisiController::class, 'adminIndex']);
    Route::get('/admin/komisi/ringkasan',           [KomisiController::class, 'adminRingkasan']);
    Route::patch('/admin/komisi/{id}/konfirmasi',  [KomisiController::class, 'adminKonfirmasi']);
});

// public
Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/subscription/saya',                    [SubscriptionController::class, 'saya']);
    Route::post('/subscription/berlangganan',           [SubscriptionController::class, 'berlangganan']);
    Route::post('/subscription/{id}/upload-bukti',      [SubscriptionController::class, 'uploadBukti']);
    Route::post('/subscription/{id}/cancel',            [SubscriptionController::class, 'cancel']);
    Route::get('/owner/subscription/check',             [SubscriptionController::class, 'checkLimit']);
});

// Admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/subscriptions',                  [SubscriptionController::class, 'adminIndex']);
    Route::patch('/admin/subscriptions/{id}/konfirmasi',[SubscriptionController::class, 'adminKonfirmasi']);
});