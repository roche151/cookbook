<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('recipes') && Schema::hasColumn('recipes', 'category')) {
            Schema::table('recipes', function (Blueprint $table) {
                // Note: dropping columns may require doctrine/dbal for some drivers
                $table->dropColumn('category');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('recipes') && ! Schema::hasColumn('recipes', 'category')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->string('category')->nullable();
            });
        }
    }
};
