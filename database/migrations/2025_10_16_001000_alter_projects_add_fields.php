<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('status')->default('planning')->after('description');
            $table->timestamp('archived_at')->nullable()->after('status');
            $table->date('start_date')->nullable()->after('archived_at');
            $table->date('end_date')->nullable()->after('start_date');
            $table->date('actual_start_date')->nullable()->after('end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->decimal('planned_budget_amount', 15, 2)->nullable()->after('actual_end_date');
            $table->unsignedBigInteger('manager_user_id')->nullable()->after('planned_budget_amount');
            $table->string('address')->nullable()->after('manager_user_id');

            $table->foreign('manager_user_id', 'fk_projects_manager_user_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->index(['status'], 'idx_projects_status');
            $table->index(['manager_user_id'], 'idx_projects_manager_user_id');
            $table->index(['archived_at'], 'idx_projects_archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign('fk_projects_manager_user_id');
            $table->dropIndex('idx_projects_status');
            $table->dropIndex('idx_projects_manager_user_id');
            $table->dropIndex('idx_projects_archived_at');
            $table->dropColumn([
                'status',
                'archived_at',
                'start_date',
                'end_date',
                'actual_start_date',
                'actual_end_date',
                'planned_budget_amount',
                'manager_user_id',
                'address',
            ]);
        });
    }
};


