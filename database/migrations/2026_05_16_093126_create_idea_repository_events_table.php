<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('idea_repository_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idea_id');
            $table->string('type');
            $table->string('summary');
            $table->timestamp('occurred_at')->index();
            $table->string('dedupe_key');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('idea_id')->references('id')->on('ideas')->cascadeOnDelete();
            $table->unique(['idea_id', 'dedupe_key']);
            $table->index(['idea_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_repository_events');
    }
};
