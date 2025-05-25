<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Task statuses table
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status_name')->unique();
            $table->string('status_color', 20);
            $table->timestamps();
        });

        // Task priorities table
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // Task labels table
        Schema::create('task_labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label_name')->unique();
            $table->string('label_color', 20);
            $table->timestamps();
        });

        // Automation rules table
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rule_name')->unique();
            $table->text('trigger_event');
            $table->text('action');
            $table->timestamps();
        });

        // Task custom fields table
        Schema::create('task_custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->enum('field_type', ['text', 'number', 'date', 'select']);
            $table->text('options')->nullable(); // For select type fields
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_custom_fields');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('task_priorities');
        Schema::dropIfExists('task_statuses');
    }
};
