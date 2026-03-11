<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the progress table
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->json('completed_topics')->nullable();
            $table->integer('completed_topics_count')->default(0);
            $table->unsignedBigInteger('last_topic_id')->nullable();
            $table->json('last_position')->nullable();
            $table->float('score')->default(0);
            $table->integer('time_spent')->default(0);
            $table->timestamps();

            // Уникальный ключ для пары пользователь-курс
            $table->unique(['user_id', 'course_id']);
            
            // Don't add the foreign key constraint that references topics here
            // We'll add it in a later migration
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('progress');
    }
} 