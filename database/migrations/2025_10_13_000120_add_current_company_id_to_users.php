<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('current_company_id')->nullable()->after('email_verified_at');
            $table->foreign('current_company_id')->references('id')->on('companies')->nullOnDelete();
            $table->index('current_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_company_id']);
            $table->dropIndex(['current_company_id']);
            $table->dropColumn('current_company_id');
        });
    }
};


