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
        $tables = ['projects', 'phases', 'tasks', 'contractors'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');

                // Adicionar índices para melhorar performance de queries
                $table->index('created_by', "idx_{$tableName}_created_by");
                $table->index('updated_by', "idx_{$tableName}_updated_by");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['projects', 'phases', 'tasks', 'contractors'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign(["{$tableName}_created_by_foreign"]);
                $table->dropForeign(["{$tableName}_updated_by_foreign"]);
                $table->dropIndex("idx_{$tableName}_created_by");
                $table->dropIndex("idx_{$tableName}_updated_by");
                $table->dropColumn(['created_by', 'updated_by']);
            });
        }
    }
};
