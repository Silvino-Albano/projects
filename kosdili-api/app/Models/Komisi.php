<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komisi extends Model
{
    protected $table    = 'komisi';

    protected $fillable = [
        'booking_id',
        'owner_id',
        'total_sewa',
        'persen',
        'jumlah',
        'status',
        'bukti_bayar',
        'dibayar_pada',
        'catatan_admin',
    ];

    protected $casts = [
        'total_sewa'   => 'decimal:2',
        'persen'       => 'decimal:2',
        'jumlah'       => 'decimal:2',
        'dibayar_pada' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // ── Helper: hitung nominal komisi ──────────────────────────
    // Contoh: Komisi::hitung(240, 7) → 16.80
    public static function hitung(float $totalSewa, float $persen = 7.0): float
    {
        return round($totalSewa * $persen / 100, 2);
    }

    // ── Scope query ───────────────────────────────────────────
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }
}