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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Card::class, 'card_id')->nullable()->constrained('cards')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Subtask::class, 'subtask_id')->nullable()->constrained('subtasks')->onDelete('cascade'); // Fix: constrained ke subtasks bukan comments
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->constrained('users')->onDelete('cascade');
            $table->text('comment_text');
            $table->enum("comment_type", ['card', 'subtask']);
            $table->timestamps(); // Laravel expects both created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
