<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini membuat tabel 'subtasks' untuk menyimpan sub-tugas:
     * - Subtask adalah bagian kecil dari card/task
     * - Memiliki status sendiri dan tracking waktu
     * - Terhubung dengan card parent
     */
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke card parent
            $table->foreignIdFor(\App\Models\Card::class, 'card_id')
                  ->constrained('cards')->onDelete('cascade');
            
            // Informasi dasar subtask
            $table->string('subtask_name');
            $table->text('description')->nullable();
            $table->enum('status', ['to do', 'in progress', 'done'])->default('to do');
            
            // Estimasi dan actual hours
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->default(0.00); // Tambah default 0.00
            
            // Posisi dalam card
            $table->integer('position')->default(0);
            
            // Timestamp
            $table->timestamp("created_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
