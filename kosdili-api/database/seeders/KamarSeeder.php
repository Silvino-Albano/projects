<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Kamar;
use App\Models\Kos;

use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    $kos = Kos::all();

    foreach ($kos as $k) {
        for ($i = 1; $i <= 3; $i++) {
            Kamar::create([
                'kos_id' => $k->id,
                'harga' => rand(500000, 1500000),
                'status' => 'available',
                'fasilitas' => 'AC, WiFi, Kamar mandi dalam'
            ]);
        }
    }
}
}
