<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini membuat tabel 'card_reviews' untuk menyimpan history approve/reject task:
     * - Team lead bisa approve atau reject card/task
     * - Setiap review memiliki status (approved/rejected)
     * - Team lead bisa memberikan keterangan/notes (opsional)
     * - Menyimpan timestamp untuk audit trail
     */
    public function up(): void
    {
        Schema::create('card_reviews', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke card yang direview
            $table->foreignIdFor(\App\Models\Card::class, 'card_id')
                  ->constrained('cards')->onDelete('cascade');
            
            // User yang melakukan review (team lead)
            $table->foreignIdFor(\App\Models\User::class, 'reviewed_by')
                  ->constrained('users')->onDelete('cascade');
            
            // Status review: approved atau rejected
            $table->enum('status', ['approved', 'rejected']);
            
            // Keterangan dari team lead (opsional)
            // Bisa berisi alasan reject, feedback, atau catatan lainnya
            $table->text('notes')->nullable();
            
            // Timestamp untuk audit trail
            $table->timestamp('reviewed_at')->useCurrent();
            
            // Index untuk query performa
            $table->index(['card_id', 'reviewed_at']); // Query history berdasarkan card dan waktu
            $table->index('reviewed_by'); // Query siapa yang melakukan review
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_reviews');
    }
};
