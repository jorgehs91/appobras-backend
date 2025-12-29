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
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('notifiable'); // Creates notifiable_id and notifiable_type
            $table->string('type'); // Notification type (e.g., 'task_assigned', 'task_overdue')
            $table->json('data')->nullable(); // Additional notification data
            $table->timestamp('read_at')->nullable(); // When notification was read
            $table->json('channels')->nullable(); // Array of channels (e.g., ['email', 'push'])
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index(['notifiable_id', 'notifiable_type']);
            $table->index('read_at');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
