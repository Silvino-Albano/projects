<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Kamar extends Model
{
    protected $table = 'kamars';
    protected $fillable = [
        'kos_id',
        'harga',
        'status',
        'fasilitas'
    ];

 public function kos()
{
    return $this->belongsTo(Kos::class);
}

public function bookings()
{
    return $this->hasMany(Booking::class);
}

}
