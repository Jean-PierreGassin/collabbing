<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_application_read_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idea_application_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('idea_application_id')
                ->references('id')
                ->on('idea_applications')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['idea_application_id', 'user_id'], 'idea_application_read_state_unique');
        });
    }
};
