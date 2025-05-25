<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tasks table
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id')->nullable();
            $table->text('source')->nullable();
            $table->text('meeting_title')->nullable();
            $table->date('meeting_date')->nullable();
            $table->text('task_name');
            $table->uuid('status_id');
            $table->uuid('priority_id')->nullable();
            $table->uuid('label_id')->nullable();
            $table->uuid('automation_rule_id')->nullable();
            $table->date('due_date')->nullable();
            $table->text('cco')->nullable();
            $table->uuid('created_by');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');

            $table->foreign('status_id')
                ->references('id')
                ->on('task_statuses')
                ->onDelete('restrict');

            $table->foreign('priority_id')
                ->references('id')
                ->on('task_priorities')
                ->onDelete('set null');

            $table->foreign('label_id')
                ->references('id')
                ->on('task_labels')
                ->onDelete('set null');

            $table->foreign('automation_rule_id')
                ->references('id')
                ->on('automation_rules')
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });

        // Task reminders table
        Schema::create('task_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('task_id');
            $table->timestamp('remind_at');
            $table->boolean('is_sent')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });

        // Task field values table
        Schema::create('task_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('field_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('field_id')
                ->references('id')
                ->on('task_custom_fields')
                ->onDelete('cascade');
        });

        // Task attachments table
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->string('filename');
            $table->string('file_hash', 64)->nullable();
            $table->boolean('scanned')->default(false);
            $table->string('scan_result', 100)->nullable();
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_field_values');
        Schema::dropIfExists('task_reminders');
        Schema::dropIfExists('tasks');
    }
};
