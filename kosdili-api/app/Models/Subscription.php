<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Subscription extends Model
{
    protected $table    = 'subscriptions';
    protected $fillable = [
        'user_id', 'plan_id', 'status',
        'mulai_pada', 'berakhir_pada',
        'metode_bayar', 'payment_status',
        'bukti_bayar', 'dikonfirmasi_pada', 'catatan',
    ];

    protected $casts = [
        'mulai_pada'       => 'date',
        'berakhir_pada'    => 'date',
        'dikonfirmasi_pada'=> 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    // ── Scope ───────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('berakhir_pada', '>=', now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('berakhir_pada', '<', now()->toDateString());
    }

    // ── Helper ──────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->berakhir_pada >= Carbon::today();
    }

    public function sisaHari(): int
    {
        return max(0, Carbon::today()->diffInDays($this->berakhir_pada, false));
    }
}
