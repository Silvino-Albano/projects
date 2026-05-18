<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kos extends Model
{
   protected $fillable = [
        'user_id',
        'zona_id',
        'nama_kos',
        'alamat',
        'latitude',
        'longitude',
        'harga_per_bulan',
        'deskripsi',
        'gambar',
        'status',
    ];

    

public function owner()
{
    return $this->belongsTo(User::class, 'user_id');
}

 public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke kamar
    public function kamar()
    {
        return $this->hasMany(Kamar::class);
    }


    protected $casts = [
        'latitude'       => 'float',
        'longitude'      => 'float',
        'harga_per_bulan'=> 'float',
    ];

    // Relasi ke User


    // Relasi ke Zona
    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }

    public function reviews()
{
    return $this->hasMany(Review::class);
}

}
