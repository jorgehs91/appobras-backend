<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('company_id', 'fk_projects_company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');

            $table->unique(['company_id', 'name'], 'uq_projects_company_name');
            $table->index(['company_id'], 'idx_projects_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};


