<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat table zonas dulu kalau belum ada
        if (!Schema::hasTable('zonas')) {
            Schema::create('zonas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_zona');
                $table->timestamps();
            });
        }

        Schema::table('kos', function (Blueprint $table) {
            // Tambah zona_id
            if (!Schema::hasColumn('kos', 'zona_id')) {
                $table->foreignId('zona_id')
                      ->after('user_id')
                      ->constrained('zonas')
                      ->onDelete('no action');
            }

            // Tambah harga_per_bulan
            if (!Schema::hasColumn('kos', 'harga_per_bulan')) {
                $table->decimal('harga_per_bulan', 8, 2)
                      ->after('longitude');
            }

            // Tambah status
            if (!Schema::hasColumn('kos', 'status')) {
                $table->string('status')
                      ->default('pending')
                      ->after('harga_per_bulan');
            }

            // latitude & longitude jadi NOT NULL
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->dropForeign(['zona_id']);
            $table->dropColumn(['zona_id', 'harga_per_bulan', 'status']);
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
        });
    }
};