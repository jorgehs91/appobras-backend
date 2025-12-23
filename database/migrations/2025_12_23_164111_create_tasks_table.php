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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('phase_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['backlog', 'in_progress', 'in_review', 'done', 'canceled'])->default('backlog');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->integer('order_in_phase')->default(0);
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('contractor_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->date('planned_start_at')->nullable();
            $table->date('planned_end_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'phase_id', 'status']);
            $table->index(['assignee_id']);
            $table->index(['contractor_id']);
            $table->index(['planned_end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
