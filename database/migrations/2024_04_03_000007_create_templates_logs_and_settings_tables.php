<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Task templates table
        Schema::create('task_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('template_name');
            $table->uuid('default_status_id')->nullable();
            $table->integer('default_due_days')->nullable();
            $table->timestamps();

            $table->foreign('default_status_id')
                ->references('id')
                ->on('task_statuses')
                ->onDelete('set null');
        });

        // Audit logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->text('action');
            $table->text('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Activity logs table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->text('activity');
            $table->text('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->boolean('dark_mode')->default(false);
            $table->string('language', 10)->default('en');
            $table->string('theme', 50)->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->text('message');
            $table->string('type', 50)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('task_templates');
    }
};
