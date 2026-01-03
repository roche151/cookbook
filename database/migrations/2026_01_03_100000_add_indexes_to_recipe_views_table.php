<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recipe_views', function (Blueprint $table) {
            $table->index(['recipe_id', 'viewed_at'], 'recipe_views_recipe_id_viewed_at_idx');
            $table->index(['recipe_id', 'user_id', 'viewed_at'], 'recipe_views_recipe_id_user_id_viewed_at_idx');
        });
    }

    public function down()
    {
        Schema::table('recipe_views', function (Blueprint $table) {
            $table->dropIndex('recipe_views_recipe_id_viewed_at_idx');
            $table->dropIndex('recipe_views_recipe_id_user_id_viewed_at_idx');
        });
    }
};
