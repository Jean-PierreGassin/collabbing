<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea_applications', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->string('contribution_type', 40)->nullable();
            $table->string('first_action', 280)->nullable();
            $table->text('approval_note')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->index(['status', 'created_at'], 'idea_applications_status_created_index');
        });
    }
};
