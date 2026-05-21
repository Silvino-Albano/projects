<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
      public function up(): void
    {
        // ── Tabel paket langganan ───────────────────────────────
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');               // Gratis, Pro, Bisnis
            $table->string('slug')->unique();     // gratis, pro, bisnis
            $table->decimal('harga', 10, 2)->default(0);  // per bulan USD
            $table->integer('max_properti');      // -1 = unlimited
            $table->integer('max_foto');          // -1 = unlimited
            $table->boolean('prioritas_listing')->default(false);
            $table->boolean('badge_terverifikasi')->default(false);
            $table->boolean('statistik_lengkap')->default(false);
            $table->boolean('laporan_pdf')->default(false);
            $table->json('fitur_list');           // array deskripsi fitur untuk tampilan
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0); // urutan tampil di halaman
            $table->timestamps();
        });

        // ── Tabel langganan owner ───────────────────────────────
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('plan_id')
                  ->constrained('subscription_plans')
                  ->onDelete('cascade');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->date('mulai_pada');
            $table->date('berakhir_pada');
            $table->enum('metode_bayar', ['cash', 'transfer'])->default('transfer');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->string('bukti_bayar')->nullable();
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // ── Seed data paket default ─────────────────────────────
        DB::table('subscription_plans')->insert([
            [
                'nama'               => 'Gratis',
                'slug'               => 'gratis',
                'harga'              => 0.00,
                'max_properti'       => 1,
                'max_foto'           => 3,
                'prioritas_listing'  => false,
                'badge_terverifikasi'=> false,
                'statistik_lengkap'  => false,
                'laporan_pdf'        => false,
                'fitur_list'         => json_encode([
                    '1 properti',
                    '3 foto per properti',
                    'Listing dasar',
                    'Notifikasi booking via email',
                ]),
                'is_active'          => true,
                'urutan'             => 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama'               => 'Pro',
                'slug'               => 'pro',
                'harga'              => 5.00,
                'max_properti'       => 5,
                'max_foto'           => 20,
                'prioritas_listing'  => true,
                'badge_terverifikasi'=> true,
                'statistik_lengkap'  => true,
                'laporan_pdf'        => false,
                'fitur_list'         => json_encode([
                    '5 properti',
                    '20 foto per properti',
                    'Listing prioritas (tampil lebih atas)',
                    'Badge "Terverifikasi"',
                    'Statistik booking lengkap',
                    'Notifikasi booking via email',
                ]),
                'is_active'          => true,
                'urutan'             => 2,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama'               => 'Bisnis',
                'slug'               => 'bisnis',
                'harga'              => 12.00,
                'max_properti'       => -1,
                'max_foto'           => -1,
                'prioritas_listing'  => true,
                'badge_terverifikasi'=> true,
                'statistik_lengkap'  => true,
                'laporan_pdf'        => true,
                'fitur_list'         => json_encode([
                    'Properti tidak terbatas',
                    'Foto tidak terbatas',
                    'Listing di posisi paling atas',
                    'Badge "Terverifikasi"',
                    'Statistik & laporan PDF bulanan',
                    'Support prioritas dari admin',
                ]),
                'is_active'          => true,
                'urutan'             => 3,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
