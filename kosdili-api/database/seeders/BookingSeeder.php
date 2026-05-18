<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Booking;
use App\Models\User;
use App\Models\Kamar;

use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $user = User::where('role', 'user')->first();
    $kamar = Kamar::first();

    Booking::create([
        'user_id' => $user->id,
        'kamar_id' => $kamar->id,
        'tanggal_masuk' => now(),
        'tanggal_keluar' => now()->addDays(30),
        'status' => 'confirmed'
    ]);
}
}
