<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('current_project_id')->nullable()->after('current_company_id');
            $table->foreign('current_project_id', 'fk_users_current_project_id')
                ->references('id')->on('projects')
                ->onDelete('set null');
            $table->index(['current_project_id'], 'idx_users_current_project_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('fk_users_current_project_id');
            $table->dropIndex('idx_users_current_project_id');
            $table->dropColumn('current_project_id');
        });
    }
};


