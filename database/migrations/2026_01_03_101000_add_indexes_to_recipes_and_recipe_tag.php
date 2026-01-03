<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->index('is_public');
            $table->index('user_id');
            $table->index('status');
            $table->index('approved_at');
        });
        Schema::table('recipe_tag', function (Blueprint $table) {
            $table->index('recipe_id');
            $table->index('tag_id');
        });
    }

    public function down()
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['is_public']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['approved_at']);
        });
        Schema::table('recipe_tag', function (Blueprint $table) {
            $table->dropIndex(['recipe_id']);
            $table->dropIndex(['tag_id']);
        });
    }
};
