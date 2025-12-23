<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mapear antigos para novos: planned->planning, paused->on_hold, cancelled->canceled
        DB::table('projects')->where('status', 'planned')->update(['status' => 'planning']);
        DB::table('projects')->where('status', 'paused')->update(['status' => 'on_hold']);
        DB::table('projects')->where('status', 'cancelled')->update(['status' => 'canceled']);
    }

    public function down(): void
    {
        DB::table('projects')->where('status', 'planning')->update(['status' => 'planned']);
        DB::table('projects')->where('status', 'on_hold')->update(['status' => 'paused']);
        DB::table('projects')->where('status', 'canceled')->update(['status' => 'cancelled']);
    }
};


