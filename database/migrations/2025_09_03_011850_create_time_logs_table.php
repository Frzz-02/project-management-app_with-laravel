<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini membuat tabel 'time_logs' untuk tracking waktu kerja:
     * - Mencatat waktu mulai dan selesai kerja
     * - Durasi dalam menit
     * - Relasi ke card, subtask, dan user
     */
    public function up(): void
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id(); // Primary key auto increment
            
            // Relasi ke card dan subtask
            $table->foreignIdFor(\App\Models\Card::class, 'card_id')
                  ->constrained('cards')
                  ->onDelete('cascade');
            
            $table->foreignIdFor(\App\Models\Subtask::class, 'subtask_id')
                  ->constrained('subtasks')
                  ->onDelete('cascade');
            
            // Relasi ke user yang melakukan tracking
            $table->foreignIdFor(\App\Models\User::class, 'user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Waktu tracking
            $table->timestamp('start_time'); // Waktu mulai
            $table->timestamp('end_time')->nullable(); // Waktu selesai (nullable untuk ongoing task)
            $table->integer('duration_minutes'); // Durasi dalam menit
            
            // Deskripsi pekerjaan (opsional)
            $table->text('description')->nullable();
            
            // Timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};