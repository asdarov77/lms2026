<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Skip creating the table if it already exists
        if (!Schema::hasTable('progress')) {
            Schema::create('progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
                $table->json('completed_topics')->nullable();
                $table->integer('completed_topics_count')->default(0);
                $table->foreignId('last_topic_id')->nullable()->constrained('topics')->nullOnDelete();
                $table->json('last_position')->nullable();
                $table->float('score')->nullable();
                $table->integer('time_spent')->default(0);
                $table->timestamps();
                
                // Уникальный индекс для связки пользователь-курс
                $table->unique(['user_id', 'course_id']);
            });
        }
        // If the table exists, we might want to modify it to ensure it has all necessary columns
        else {
            // This section can be used for future schema modifications
            // if the progress table structure needs updates
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Only drop if exists
        if (Schema::hasTable('progress')) {
            Schema::dropIfExists('progress');
        }
    }
}; 