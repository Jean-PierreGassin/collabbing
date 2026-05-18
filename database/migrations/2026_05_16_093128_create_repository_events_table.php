<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repository_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_repository_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('summary');
            $table->timestamp('occurred_at')->index();
            $table->string('dedupe_key');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['code_repository_id', 'dedupe_key']);
            $table->index(['code_repository_id', 'occurred_at']);
        });
    }
};
