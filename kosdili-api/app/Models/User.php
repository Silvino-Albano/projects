<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ← tambah ini
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// BUG FIX: Tambah HasApiTokens — wajib untuk Laravel Sanctum


class User extends Authenticatable
{
    // BUG FIX: HasApiTokens harus ada agar createToken() berfungsi
   use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',      // ← tambah
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
        public function kos()
    {
        return $this->hasMany(Kos::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
