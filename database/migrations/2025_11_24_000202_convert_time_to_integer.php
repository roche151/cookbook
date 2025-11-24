<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add a temporary integer column
        Schema::table('recipes', function (Blueprint $table) {
            $table->integer('time_new')->nullable();
        });

        // Populate time_new by parsing existing `time` values
        $recipes = DB::table('recipes')->select('id', 'time')->get();
        foreach ($recipes as $r) {
            $mins = null;
            if (!is_null($r->time)) {
                $t = trim((string)$r->time);
                // If it's purely numeric, treat as minutes
                if (preg_match('/^\d+$/', $t)) {
                    $mins = (int)$t;
                } else {
                    // Extract hours and minutes
                    $hours = 0;
                    $minutes = 0;
                    if (preg_match('/(\d+)\s*(?:hours?|hrs?|h)\b/i', $t, $m)) {
                        $hours = (int)$m[1];
                    }
                    if (preg_match('/(\d+)\s*(?:minutes?|mins?|m)\b/i', $t, $m2)) {
                        $minutes = (int)$m2[1];
                    }
                    // Also support patterns like '1 hr' or '1 h' captured above; if still zero and string contains digits, use those as minutes
                    if ($hours === 0 && $minutes === 0) {
                        if (preg_match('/(\d+)/', $t, $m3)) {
                            $minutes = (int)$m3[1];
                        }
                    }
                    $mins = ($hours * 60) + $minutes;
                }
            }

            DB::table('recipes')->where('id', $r->id)->update(['time_new' => $mins]);
        }

        // Drop old `time` column and create a new integer `time` column, populate it from `time_new`, then drop `time_new`.
        Schema::table('recipes', function (Blueprint $table) {
            // Drop original `time` (string)
            $table->dropColumn('time');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->integer('time')->nullable();
        });

        // Copy values from time_new to new time column
        DB::statement('UPDATE recipes SET time = time_new');

        // Drop temporary column
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('time_new');
        });
    }

    public function down()
    {
        // Reverse: add a string `time_old`, populate from integer `time` as human readable, drop integer `time`, rename back
        Schema::table('recipes', function (Blueprint $table) {
            $table->text('time_old')->nullable();
        });

        $recipes = DB::table('recipes')->select('id', 'time')->get();
        foreach ($recipes as $r) {
            $display = null;
            if (!is_null($r->time)) {
                $total = (int)$r->time;
                $h = intdiv($total, 60);
                $m = $total % 60;
                $parts = [];
                if ($h > 0) $parts[] = $h . ' hour' . ($h === 1 ? '' : 's');
                if ($m > 0) $parts[] = $m . ' minute' . ($m === 1 ? '' : 's');
                $display = $parts ? implode(' ', $parts) : null;
            }
            DB::table('recipes')->where('id', $r->id)->update(['time_old' => $display]);
        }

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->string('time')->nullable();
        });

        DB::statement('UPDATE recipes SET time = time_old');

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('time_old');
        });
    }
};
