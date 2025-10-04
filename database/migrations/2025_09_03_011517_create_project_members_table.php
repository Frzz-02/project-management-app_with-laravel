<?php

use App\Models\Project;
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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class, 'project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['team lead', 'developer', 'designer'])->default('developer');
            $table->timestamp("joined_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
