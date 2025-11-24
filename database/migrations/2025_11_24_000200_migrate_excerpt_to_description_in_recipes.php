<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add new column, copy data, then drop old column to avoid requiring doctrine/dbal
        Schema::table('recipes', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        // Copy existing excerpt data into description
        DB::table('recipes')->whereNotNull('excerpt')->update(['description' => DB::raw('excerpt')]);

        // Drop the old column if it exists
        Schema::table('recipes', function (Blueprint $table) {
            if (Schema::hasColumn('recipes', 'excerpt')) {
                $table->dropColumn('excerpt');
            }
        });
    }

    public function down()
    {
        // Recreate excerpt, copy data back, drop description
        Schema::table('recipes', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
        });

        DB::table('recipes')->whereNotNull('description')->update(['excerpt' => DB::raw('description')]);

        Schema::table('recipes', function (Blueprint $table) {
            if (Schema::hasColumn('recipes', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
