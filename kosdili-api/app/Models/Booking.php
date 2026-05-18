<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings'; // penting!

    protected $fillable = [
        'user_id',
        'kamar_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'payment_method', 
        'payment_status', 
        'payment_proof'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function kamar()
{
    return $this->belongsTo(Kamar::class);
}

public function review()
{
    return $this->hasOne(Review::class);
}


}