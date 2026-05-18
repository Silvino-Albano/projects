<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class KosSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', 'owner')->first();

        // Ambil zona berdasarkan nama
        $zonaSentral  = Zona::where('nama_zona', 'Zona Sentral Dili')->first();
        $zonaFarol    = Zona::where('nama_zona', 'Zona Farol')->first();
        $zonaComoro   = Zona::where('nama_zona', 'Zona Comoro')->first();
        $zonaBecora   = Zona::where('nama_zona', 'Zona Becora')->first();
        $zonaTaibessi = Zona::where('nama_zona', 'Zona Taibessi')->first();

        $kos = [
            [
                'user_id'         => $owner->id,
                'zona_id'         => $zonaSentral->id,
                'nama_kos'        => 'Kos Dili Indah',
                'alamat'          => 'Rua Av. Bispo Medeiros, Dili Center',
                'latitude'        => -8.5586000,
                'longitude'       => 125.5736000,
                'harga_per_bulan' => 150.00,
                'deskripsi'       => 'Kos nyaman di pusat kota, dekat kantor pemerintah dan pasar.',
                'status'          => 'aktif',
            ],
            [
                'user_id'         => $owner->id,
                'zona_id'         => $zonaFarol->id,
                'nama_kos'        => 'Kos Pantai Farol',
                'alamat'          => 'Rua de Farol, Dili',
                'latitude'        => -8.5600000,
                'longitude'       => 125.5800000,
                'harga_per_bulan' => 120.00,
                'deskripsi'       => 'View laut langsung, cocok untuk mahasiswa dan pekerja.',
                'status'          => 'aktif',
            ],
            [
                'user_id'         => $owner->id,
                'zona_id'         => $zonaComoro->id,
                'nama_kos'        => 'Kos Comoro Asri',
                'alamat'          => 'Rua Comoro, Dili',
                'latitude'        => -8.5621000,
                'longitude'       => 125.5534000,
                'harga_per_bulan' => 100.00,
                'deskripsi'       => 'Dekat bandara, strategis dan aman.',
                'status'          => 'aktif',
            ],
            [
                'user_id'         => $owner->id,
                'zona_id'         => $zonaBecora->id,
                'nama_kos'        => 'Kos Becora Damai',
                'alamat'          => 'Rua Becora, Dili',
                'latitude'        => -8.5423000,
                'longitude'       => 125.6012000,
                'harga_per_bulan' => 90.00,
                'deskripsi'       => 'Tenang dan cocok untuk keluarga kecil.',
                'status'          => 'aktif',
            ],
            [
                'user_id'         => $owner->id,
                'zona_id'         => $zonaTaibessi->id,
                'nama_kos'        => 'Kos Taibessi Murah',
                'alamat'          => 'Rua Taibessi, Dili',
                'latitude'        => -8.5710000,
                'longitude'       => 125.5890000,
                'harga_per_bulan' => 75.00,
                'deskripsi'       => 'Harga ekonomis, fasilitas lengkap.',
                'status'          => 'pending',
            ],
        ];

        foreach ($kos as $item) {
            Kos::create($item);
        }
    }
}