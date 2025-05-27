<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->text('code'); // The submitted code
            $table->enum('status', ['pending', 'testing', 'passed', 'failed'])->default('pending');
            $table->text('test_output')->nullable(); // Output from test execution
            $table->text('error_message')->nullable(); // Error if test failed
            $table->integer('execution_time')->nullable(); // In milliseconds
            $table->timestamps();
            
            // Ensure a team can only have one successful submission per challenge
            $table->unique(['team_id', 'challenge_id', 'status'], 'unique_successful_submission');
        });
    }

    public function down()
    {
        Schema::dropIfExists('submissions');
    }
};