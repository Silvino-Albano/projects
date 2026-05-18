<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $fillable = ['nama_zona'];

    public function kos()
    {
        return $this->hasMany(Kos::class);
    }
}