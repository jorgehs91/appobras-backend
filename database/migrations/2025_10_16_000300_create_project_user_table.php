<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('Viewer');
            $table->timestamp('joined_at')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();

            $table->primary(['project_id', 'user_id'], 'pk_project_user');
            $table->foreign('project_id', 'fk_project_user_project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
            $table->foreign('user_id', 'fk_project_user_user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->index(['user_id'], 'idx_project_user_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};


