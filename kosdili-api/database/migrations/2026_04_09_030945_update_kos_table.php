<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('kos', function (Blueprint $table) {
        // hapus harga
        $table->dropColumn('harga');

        // tambah lokasi
        $table->decimal('latitude', 10, 7)->nullable()->after('alamat');
        $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
    });
}

public function down(): void
{
    Schema::table('kos', function (Blueprint $table) {
        $table->decimal('harga', 10, 2);
        $table->dropColumn(['latitude', 'longitude']);
    });
}
};
