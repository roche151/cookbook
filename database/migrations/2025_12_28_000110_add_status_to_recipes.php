<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('is_public');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->index(['status', 'is_public']);
        });

        // Backfill existing recipes: mark current records as approved; set approved_at for public ones
        DB::table('recipes')->update([
            'status' => 'approved',
        ]);

        DB::table('recipes')
            ->where('is_public', true)
            ->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_public']);
            $table->dropColumn(['status', 'approved_at']);
        });
    }
};
