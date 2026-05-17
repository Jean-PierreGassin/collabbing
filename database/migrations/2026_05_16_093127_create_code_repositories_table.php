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
        Schema::create('code_repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idea_id');
            $table->string('provider', 50);
            $table->string('status', 30)->default('planned')->index();
            $table->string('provider_repository_id')->nullable();
            $table->string('owner')->nullable();
            $table->string('name');
            $table->string('full_name')->nullable();
            $table->string('html_url')->nullable();
            $table->string('default_branch')->nullable();
            $table->unsignedInteger('open_issues_count')->default(0);
            $table->unsignedInteger('stargazers_count')->default(0);
            $table->unsignedInteger('forks_count')->default(0);
            $table->string('latest_commit_sha')->nullable();
            $table->string('latest_commit_message')->nullable();
            $table->string('latest_commit_author')->nullable();
            $table->timestamp('pushed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('missing_at')->nullable();
            $table->timestamp('sync_due_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('idea_id')->references('id')->on('ideas')->cascadeOnDelete();
            $table->unique(['provider', 'owner', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_repositories');
    }
};
