<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->string('collaboration_stage', 40)->nullable();
            $table->json('help_wanted')->nullable();
            $table->string('help_wanted_note', 240)->nullable();
            $table->text('first_contribution')->nullable();
            $table->boolean('applications_open')->default(true)->index();
            $table->string('applications_closed_note', 240)->nullable();
            $table->string('communication_style', 40)->nullable();
            $table->string('communication_note', 240)->nullable();
            $table->mediumText('getting_started_notes')->nullable();
            $table->timestamp('getting_started_notes_updated_at')->nullable();
        });
    }
};
