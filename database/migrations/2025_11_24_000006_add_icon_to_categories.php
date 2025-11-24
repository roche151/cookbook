<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('sort_order');
        });

        // Set icon classes for the seeded categories
        DB::table('categories')->where('slug', 'breakfast')->update(['icon' => 'fa-solid fa-mug-hot']);
        DB::table('categories')->where('slug', 'lunch')->update(['icon' => 'fa-solid fa-bowl-food']);
        DB::table('categories')->where('slug', 'dinner')->update(['icon' => 'fa-solid fa-utensils']);
        DB::table('categories')->where('slug', 'dessert')->update(['icon' => 'fa-solid fa-cake-candles']);
        DB::table('categories')->where('slug', 'snack')->update(['icon' => 'fa-solid fa-cookie-bite']);
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
