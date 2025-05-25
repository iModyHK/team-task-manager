<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->uuid('leader_id');
            $table->timestamps();

            $table->foreign('leader_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict'); // Prevent deletion of user if they're a team leader
        });

        // Team members pivot table
        Schema::create('team_members', function (Blueprint $table) {
            $table->uuid('team_id');
            $table->uuid('user_id');
            $table->primary(['team_id', 'user_id']);

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
