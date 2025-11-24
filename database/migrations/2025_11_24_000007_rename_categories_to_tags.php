<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Rename categories table to tags
        if (Schema::hasTable('categories') && !Schema::hasTable('tags')) {
            Schema::rename('categories', 'tags');
        }

        // Create new pivot table recipe_tag and migrate existing pivot data
        if (!Schema::hasTable('recipe_tag')) {
            Schema::create('recipe_tag', function (Blueprint $table) {
                $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->primary(['tag_id', 'recipe_id']);
            });

            // Copy data from old pivot if it exists
            if (Schema::hasTable('category_recipe')) {
                $rows = DB::table('category_recipe')->select('category_id', 'recipe_id')->get();
                $insert = [];
                foreach ($rows as $r) {
                    $insert[] = ['tag_id' => $r->category_id, 'recipe_id' => $r->recipe_id];
                }
                if (!empty($insert)) {
                    DB::table('recipe_tag')->insert($insert);
                }

                Schema::dropIfExists('category_recipe');
            }
        }
    }

    public function down()
    {
        // Revert pivot
        if (Schema::hasTable('recipe_tag') && !Schema::hasTable('category_recipe')) {
            Schema::create('category_recipe', function (Blueprint $table) {
                $table->foreignId('category_id')->constrained('tags')->cascadeOnDelete();
                $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
                $table->primary(['category_id', 'recipe_id']);
            });

            // Copy back
            $rows = DB::table('recipe_tag')->select('tag_id', 'recipe_id')->get();
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = ['category_id' => $r->tag_id, 'recipe_id' => $r->recipe_id];
            }
            if (!empty($insert)) {
                DB::table('category_recipe')->insert($insert);
            }

            Schema::dropIfExists('recipe_tag');
        }

        // Rename tags back to categories
        if (Schema::hasTable('tags') && !Schema::hasTable('categories')) {
            Schema::rename('tags', 'categories');
        }
    }
};
