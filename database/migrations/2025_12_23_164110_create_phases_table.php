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
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active');
            $table->integer('sequence')->default(0);
            $table->string('color', 7)->nullable();
            $table->date('planned_start_at')->nullable();
            $table->date('planned_end_at')->nullable();
            $table->date('actual_start_at')->nullable();
            $table->date('actual_end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'status', 'sequence']);
            $table->index(['company_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
