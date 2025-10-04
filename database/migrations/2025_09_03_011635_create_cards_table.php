<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migration ini membuat tabel 'cards' untuk menyimpan kartu tugas:
     * - Kartu adalah task/issue dalam board kanban
     * - Memiliki status, prioritas, estimasi waktu, dll
     * - Terhubung dengan board dan user
     */
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke board
            $table->foreignIdFor(\App\Models\Board::class, 'board_id')
                  ->constrained('boards')->onDelete('cascade');
            
            // Informasi dasar card
            $table->string('card_title');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            
            // User yang membuat card
            $table->foreignIdFor(\App\Models\User::class, 'created_by')
                  ->nullable()->constrained('users')->onDelete('set null');
            
            // Timestamps dan deadline
            $table->timestamp("created_at")->useCurrent();
            $table->date("due_date")->nullable();
            
            // Status dan prioritas
            $table->enum('status', ['todo', 'in progress','review', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium','high'])->default('medium'); // Tambah default
            
            // Estimasi dan actual hours
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->default(0.00); // Tambah default 0.00
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
