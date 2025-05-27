<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


return new class extends Migration
{
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color'); // red, blue, green, yellow
            $table->string('github_repo')->nullable();
            $table->string('access_token')->unique(); // For team authentication
            $table->boolean('is_finalist')->default(false);
            $table->timestamps();
        });

        // Seed initial teams
        DB::table('teams')->insert([
            ['name' => 'Team Red', 'color' => 'red', 'access_token' => Str::random(32), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Blue', 'color' => 'blue', 'access_token' => Str::random(32), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Green', 'color' => 'green', 'access_token' => Str::random(32), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Yellow', 'color' => 'yellow', 'access_token' => Str::random(32), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('teams');
    }
};