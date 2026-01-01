<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old tables after data migration
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('documents');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration should not be reversed as data has been migrated
        // If needed, tables should be recreated manually
    }
};
