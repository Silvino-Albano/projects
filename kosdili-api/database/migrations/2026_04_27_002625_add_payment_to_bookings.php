<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash')->after('status');
            $table->enum('payment_status', ['unpaid', 'pending_verification', 'paid'])->default('unpaid')->after('payment_method');
            $table->string('payment_proof')->nullable()->after('payment_status');
        });

        // Gunakan Raw SQL untuk mengubah ENUM di PostgreSQL
        // 1. Hapus constraint lama jika ada (opsional tapi disarankan)
        // 2. Ubah tipe data dan tambahkan check constraint baru
        DB::statement("ALTER TABLE bookings ALTER COLUMN status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed'))");
        DB::statement("ALTER TABLE bookings ALTER COLUMN status SET DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_proof']);
            
            // Kembalikan ke status awal jika diperlukan
            // DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check");
            // DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('pending', 'confirmed', 'cancelled'))");
        });
    }
};