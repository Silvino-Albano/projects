<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
 
    public function up(): void
    {
        // ── Tabel komisi ────────────────────────────────────────
        Schema::create('komisi', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade');
 
            $table->foreignId('owner_id')
                  ->constrained('users')
                  ->onDelete('cascade');
 
            $table->decimal('total_sewa', 10, 2);
            $table->decimal('persen', 5, 2)->default(7.00);
            $table->decimal('jumlah', 10, 2);
 
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
 
            $table->string('bukti_bayar')->nullable();
            $table->timestamp('dibayar_pada')->nullable();
            $table->text('catatan_admin')->nullable();
 
            $table->timestamps();
        });
 
        // ── Tambah kolom komisi ke tabel bookings ───────────────
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('komisi_jumlah', 10, 2)
                  ->nullable()
                  ->after('payment_status');
 
            $table->enum('komisi_status', ['unpaid', 'paid'])
                  ->default('unpaid')
                  ->after('komisi_jumlah');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('komisi');
 
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['komisi_jumlah', 'komisi_status']);
        });
    }
};