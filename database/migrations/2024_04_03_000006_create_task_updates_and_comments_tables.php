<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Task updates table
        Schema::create('task_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('user_id')->nullable();
            $table->text('content');
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Task mentions table
        Schema::create('task_mentions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('update_id');
            $table->uuid('mentioned_user_id');
            $table->timestamps();

            $table->foreign('update_id')
                ->references('id')
                ->on('task_updates')
                ->onDelete('cascade');

            $table->foreign('mentioned_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Task next steps table
        Schema::create('task_next_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->text('content');
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });

        // Task PMO comments table
        Schema::create('task_pmo_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->text('content');
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });

        // Task assignees pivot table
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('user_id');
            $table->primary(['task_id', 'user_id']);
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Subtasks table
        Schema::create('subtasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_task_id');
            $table->text('title');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('parent_task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });

        // Task dependencies table
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->uuid('parent_task_id');
            $table->uuid('dependent_task_id');
            $table->primary(['parent_task_id', 'dependent_task_id']);
            $table->timestamps();

            $table->foreign('parent_task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('dependent_task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('subtasks');
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('task_pmo_comments');
        Schema::dropIfExists('task_next_steps');
        Schema::dropIfExists('task_mentions');
        Schema::dropIfExists('task_updates');
    }
};
