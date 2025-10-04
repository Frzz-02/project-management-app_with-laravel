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
        Schema::create('card_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Card::class, 'card_id')->constrained('cards')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['assigned', 'in progress', 'completed'])->default('assigned');
            $table->timestamp("started_at")->nullable();
            $table->timestamp("completed_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_assignments');
    }
};
