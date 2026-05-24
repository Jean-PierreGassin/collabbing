<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_application_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idea_application_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('type', 30)->default('message');
            $table->mediumText('body')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('idea_application_id')
                ->references('id')
                ->on('idea_applications')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['idea_application_id', 'occurred_at'], 'idea_app_messages_application_occurred_index');
        });
    }
};
