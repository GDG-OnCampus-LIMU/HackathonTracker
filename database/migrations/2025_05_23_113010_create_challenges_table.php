<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('phase', ['warmup', 'momentum', 'deepdive', 'finale']);
            $table->integer('points');
            $table->integer('order_in_phase'); // 1 or 2 for regular phases, 1 for finale
            $table->text('test_script'); // The test script to run
            $table->string('expected_output')->nullable(); // For simple output matching
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed challenges
        DB::table('challenges')->insert([
            // Warmup Phase (0:10-0:30)
            ['name' => 'Hello Hackathon', 'description' => 'Print "Hello Hackathon"', 'phase' => 'warmup', 
             'points' => 10, 'order_in_phase' => 1, 'test_script' => 'echo "Hello Hackathon"', 
             'expected_output' => 'Hello Hackathon', 'created_at' => now(), 'updated_at' => now()],
            
            ['name' => 'Sum Two Numbers', 'description' => 'Read two numbers and print their sum', 'phase' => 'warmup', 
             'points' => 10, 'order_in_phase' => 2, 'test_script' => 'python test_sum.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],

            // Momentum Phase (0:30-1:00)
            ['name' => 'Fibonacci Sequence', 'description' => 'Generate first N fibonacci numbers', 'phase' => 'momentum', 
             'points' => 20, 'order_in_phase' => 1, 'test_script' => 'python test_fibonacci.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],
            
            ['name' => 'Palindrome Checker', 'description' => 'Check if string is palindrome', 'phase' => 'momentum', 
             'points' => 20, 'order_in_phase' => 2, 'test_script' => 'python test_palindrome.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],

            // Deep Dive Phase (1:00-1:40)
            ['name' => 'Binary Tree Traversal', 'description' => 'Implement inorder traversal', 'phase' => 'deepdive', 
             'points' => 30, 'order_in_phase' => 1, 'test_script' => 'python test_tree.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],
            
            ['name' => 'Dynamic Programming', 'description' => 'Solve knapsack problem', 'phase' => 'deepdive', 
             'points' => 30, 'order_in_phase' => 2, 'test_script' => 'python test_knapsack.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],

            // Finale Phase (1:40-2:00)
            ['name' => 'Boss Challenge', 'description' => 'Optimize the delivery route algorithm', 'phase' => 'finale', 
             'points' => 0, 'order_in_phase' => 1, 'test_script' => 'python test_boss.py', 
             'expected_output' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('challenges');
    }
};