<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table    = 'subscription_plans';
    protected $fillable = [
        'nama', 'slug', 'harga', 'max_properti', 'max_foto',
        'prioritas_listing', 'badge_terverifikasi',
        'statistik_lengkap', 'laporan_pdf',
        'fitur_list', 'is_active', 'urutan',
    ];

    protected $casts = [
        'harga'               => 'decimal:2',
        'prioritas_listing'   => 'boolean',
        'badge_terverifikasi' => 'boolean',
        'statistik_lengkap'   => 'boolean',
        'laporan_pdf'         => 'boolean',
        'is_active'           => 'boolean',
        'fitur_list'          => 'array',   // otomatis decode JSON
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    // Helper: cek apakah paket ini gratis
    public function isGratis(): bool
    {
        return $this->harga == 0;
    }

    // Helper: tampilkan max properti
    public function labelMaxProperti(): string
    {
        return $this->max_properti === -1 ? 'Tidak terbatas' : (string) $this->max_properti;
    }

    // Helper: tampilkan max foto
    public function labelMaxFoto(): string
    {
        return $this->max_foto === -1 ? 'Tidak terbatas' : (string) $this->max_foto;
    }
}
