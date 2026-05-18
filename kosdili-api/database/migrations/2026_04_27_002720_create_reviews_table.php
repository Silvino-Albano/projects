<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kos_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');        // 1–5
            $table->text('komentar')->nullable();
            $table->text('reply_owner')->nullable();       // balasan owner
            $table->timestamps();
            $table->unique(['user_id', 'booking_id']);    // 1 review per booking
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};