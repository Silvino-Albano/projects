<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ZonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('zonas')->insert([
            ['nama_zona' => 'Zona Sentral Dili',    'created_at' => now(), 'updated_at' => now()],
            ['nama_zona' => 'Zona Farol',            'created_at' => now(), 'updated_at' => now()],
            ['nama_zona' => 'Zona Comoro',           'created_at' => now(), 'updated_at' => now()],
            ['nama_zona' => 'Zona Becora',           'created_at' => now(), 'updated_at' => now()],
            ['nama_zona' => 'Zona Taibessi',         'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
