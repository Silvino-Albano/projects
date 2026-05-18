<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambah durasi_bulan setelah tanggal_keluar
            $table->unsignedTinyInteger('durasi_bulan')->default(1)->after('tanggal_keluar');

            // Tambah catatan dari user saat booking
            $table->text('catatan')->nullable()->after('durasi_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['durasi_bulan', 'catatan']);
        });
    }
};