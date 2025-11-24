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
            $table->integer('sort_order')->default(0)->after('slug');
        });

        // Set the desired ordering: Breakfast, Lunch, Dinner, Dessert, Snack
        $orders = [
            'breakfast' => 1,
            'lunch' => 2,
            'dinner' => 3,
            'dessert' => 4,
            'snack' => 5,
        ];

        foreach ($orders as $slug => $order) {
            DB::table('categories')->where('slug', $slug)->update(['sort_order' => $order]);
        }
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
